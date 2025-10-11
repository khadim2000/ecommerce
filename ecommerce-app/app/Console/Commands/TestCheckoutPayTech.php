<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Services\PayTechService;
use Illuminate\Http\Request;

class TestCheckoutPayTech extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paytech:test-checkout';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test complete checkout flow with PayTech integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing complete checkout flow with PayTech...');
        
        // Vérifier qu'il y a un utilisateur et un produit
        $user = User::first();
        $product = Product::first();
        
        if (!$user) {
            $this->error('No user found. Please create a user first.');
            return 1;
        }
        
        if (!$product) {
            $this->error('No product found. Please create a product first.');
            return 1;
        }
        
        $this->line('Using user: ' . $user->name);
        $this->line('Using product: ' . $product->name . ' (Price: ' . $product->price . ' XOF)');
        
        // Simuler les données du checkout
        $checkoutData = [
            'address' => 'Adresse de test - Checkout PayTech',
            'phone' => '+221771234567',
            'payment_method' => 'orange_money'
        ];
        
        $this->info('Step 1: Creating order...');
        
        // Créer une commande (simulation du OrderController::store)
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => $product->price,
            'address' => $checkoutData['address'],
            'phone' => $checkoutData['phone'],
            'payment_method' => $checkoutData['payment_method'],
            'payment_status' => 'unpaid'
        ]);
        
        $this->info('✅ Order created with ID: ' . $order->id);
        $this->line('Order total: ' . $order->total . ' XOF');
        $this->line('Payment method: ' . $order->payment_method);
        
        $this->info('Step 2: Initiating PayTech payment...');
        
        // Simuler le PaymentController::payOrder
        $paymentController = new \App\Http\Controllers\PaymentController(new PayTechService());
        
        $paymentData = [
            'order_id' => $order->id,
            'currency' => 'XOF',
            'payment_method' => $checkoutData['payment_method'],
            'description' => "Commande {$order->id} - Test Checkout PayTech"
        ];
        
        try {
            // Simuler la requête
            $request = Request::create('/orders/' . $order->id . '/pay', 'POST', $paymentData);
            $request->setRouteResolver(function () use ($order) {
                $route = new \Illuminate\Routing\Route('POST', '/orders/{order}/pay', []);
                $route->setParameter('order', $order->id);
                return $route;
            });
            
            $response = $paymentController->initiatePayTechPayment($request);
            
            if ($response->getStatusCode() === 302) {
                $this->info('✅ PayTech payment initiated successfully!');
                $this->line('Redirect URL: ' . $response->headers->get('Location'));
                
                // Vérifier que la commande a été mise à jour
                $order->refresh();
                $this->line('Order payment status: ' . $order->payment_status);
                $this->line('Transaction ID: ' . $order->cinetpay_transaction_id);
                $this->line('Payment URL: ' . $order->cinetpay_payment_url);
                
                $this->info('Step 3: Testing payment verification...');
                
                // Simuler la vérification de paiement
                $verificationResponse = $paymentController->verification($request, $order->cinetpay_transaction_id);
                
                if ($verificationResponse) {
                    $this->info('✅ Payment verification successful!');
                } else {
                    $this->warn('⚠️ Payment verification pending (normal for test mode)');
                }
                
            } else {
                $this->error('❌ PayTech payment initiation failed!');
                $this->line('Status code: ' . $response->getStatusCode());
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            $this->line('Stack trace: ' . $e->getTraceAsString());
        }
        
        $this->info('Checkout flow test completed!');
        return 0;
    }
}