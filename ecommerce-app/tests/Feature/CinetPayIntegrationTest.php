<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Services\CinetPayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class CinetPayIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create(['price' => 100]); // Prix multiple de 5
    }

    /** @test */
    public function it_validates_amount_is_multiple_of_five()
    {
        // Créer un produit avec un prix qui n'est pas un multiple de 5
        $product = Product::factory()->create(['price' => 97]); // Pas un multiple de 5
        
        // Ajouter au panier
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $this->actingAs($this->user);

        // Tenter de créer une commande avec paiement en ligne
        $response = $this->postJson('/api/orders', [
            'address' => '123 Rue Test',
            'phone' => '+221123456789',
            'payment_method' => 'orange_money'
        ]);

        $response->assertStatus(400)
                ->assertJson(['error' => 'Le montant total doit être un multiple de 5 pour utiliser les paiements en ligne']);
    }

    /** @test */
    public function it_allows_cash_on_delivery_for_non_multiple_amounts()
    {
        // Créer un produit avec un prix qui n'est pas un multiple de 5
        $product = Product::factory()->create(['price' => 97]);
        
        // Ajouter au panier
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 1
        ]);

        $this->actingAs($this->user);

        // Créer une commande avec paiement à la livraison
        $response = $this->postJson('/api/orders', [
            'address' => '123 Rue Test',
            'phone' => '+221123456789',
            'payment_method' => 'cash_on_delivery'
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_validates_description_has_no_special_characters()
    {
        // Ajouter un produit au panier
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        $this->actingAs($this->user);

        // Créer une commande
        $orderResponse = $this->postJson('/api/orders', [
            'address' => '123 Rue Test',
            'phone' => '+221123456789',
            'payment_method' => 'orange_money'
        ]);

        $orderResponse->assertStatus(200);
        $order = Order::first();

        // Tenter d'initier un paiement avec une description contenant des caractères spéciaux
        $response = $this->postJson('/api/payments/cinetpay/initiate', [
            'order_id' => $order->id,
            'payment_method' => 'orange_money',
            'description' => 'Commande #123 avec $ et & caractères'
        ]);

        $response->assertStatus(422); // Erreur de validation
    }

    /** @test */
    public function it_sanitizes_description_before_sending_to_cinetpay()
    {
        $cinetPayService = new CinetPayService();
        
        // Test de la méthode sanitizeDescription (via réflexion car elle est privée)
        $reflection = new \ReflectionClass($cinetPayService);
        $method = $reflection->getMethod('sanitizeDescription');
        $method->setAccessible(true);

        $result = $method->invoke($cinetPayService, 'Commande #123 avec $ et & caractères');
        
        $this->assertEquals('Commande 123 avec  et  caractères', $result);
    }

    /** @test */
    public function it_uses_correct_cinetpay_channels()
    {
        $paymentController = new \App\Http\Controllers\PaymentController(
            app(\App\Services\PaymentService::class),
            app(CinetPayService::class)
        );

        // Test des canaux via réflexion
        $reflection = new \ReflectionClass($paymentController);
        $method = $reflection->getMethod('getCinetPayChannel');
        $method->setAccessible(true);

        $this->assertEquals('MOBILE_MONEY', $method->invoke($paymentController, 'orange_money'));
        $this->assertEquals('MOBILE_MONEY', $method->invoke($paymentController, 'wave'));
        $this->assertEquals('CREDIT_CARD', $method->invoke($paymentController, 'visa'));
        $this->assertEquals('CREDIT_CARD', $method->invoke($paymentController, 'mastercard'));
        $this->assertEquals('ALL', $method->invoke($paymentController, 'unknown'));
    }

    /** @test */
    public function it_sends_correct_parameters_to_cinetpay_api()
    {
        Http::fake([
            'api-checkout.cinetpay.com/v2/payment' => Http::response([
                'code' => '201',
                'data' => [
                    'payment_url' => 'https://secure.cinetpay.com/pay/123',
                    'token' => 'test_token_123'
                ]
            ], 200)
        ]);

        // Ajouter un produit au panier
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        $this->actingAs($this->user);

        // Créer une commande
        $orderResponse = $this->postJson('/api/orders', [
            'address' => '123 Rue Test',
            'phone' => '+221123456789',
            'payment_method' => 'orange_money'
        ]);

        $orderResponse->assertStatus(200);
        $order = Order::first();

        // Initier un paiement CinetPay
        $response = $this->postJson('/api/payments/cinetpay/initiate', [
            'order_id' => $order->id,
            'payment_method' => 'orange_money',
            'currency' => 'XOF'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'payment_url',
                    'transaction_id',
                    'message'
                ]);

        // Vérifier que les bons paramètres ont été envoyés à CinetPay
        Http::assertSent(function ($request) {
            $data = $request->data();
            return isset($data['apikey']) &&
                   isset($data['site_id']) &&
                   isset($data['transaction_id']) &&
                   isset($data['amount']) &&
                   $data['amount'] === 100 &&
                   isset($data['currency']) &&
                   $data['currency'] === 'XOF' &&
                   isset($data['description']) &&
                   isset($data['notify_url']) &&
                   isset($data['return_url']) &&
                   isset($data['channels']) &&
                   $data['channels'] === 'MOBILE_MONEY';
        });
    }
}