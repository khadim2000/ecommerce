<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Support\Facades\Http;

class TestFrontendPayTech extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paytech:test-frontend';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test frontend PayTech integration via HTTP requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing frontend PayTech integration...');
        
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
        
        // Simuler les données du checkout Vue.js
        $checkoutData = [
            'address' => 'Adresse de test - Frontend PayTech',
            'phone' => '+221771234567',
            'payment_method' => 'orange_money'
        ];
        
        $this->info('Step 1: Testing order creation (POST /api/orders)...');
        
        try {
            // Simuler la création de commande via l'API
            $orderResponse = Http::post('http://localhost:8000/api/orders', $checkoutData);
            
            if ($orderResponse->successful()) {
                $orderData = $orderResponse->json();
                $orderId = $orderData['id'];
                
                $this->info('✅ Order created successfully!');
                $this->line('Order ID: ' . $orderId);
                $this->line('Order status: ' . $orderData['status']);
                $this->line('Payment method: ' . $orderData['payment_method']);
                
                $this->info('Step 2: Testing PayTech payment initiation (POST /orders/{id}/pay)...');
                
                // Simuler l'initiation du paiement PayTech
                $paymentData = [
                    'currency' => 'XOF',
                    'payment_method' => $checkoutData['payment_method'],
                    'description' => "Commande {$orderId} - Test Frontend PayTech"
                ];
                
                $paymentResponse = Http::post("http://localhost:8000/orders/{$orderId}/pay", $paymentData);
                
                if ($paymentResponse->successful() || $paymentResponse->status() === 302) {
                    $this->info('✅ PayTech payment initiated successfully!');
                    
                    // Vérifier la redirection
                    if ($paymentResponse->status() === 302) {
                        $redirectUrl = $paymentResponse->header('Location');
                        $this->line('Redirect URL: ' . $redirectUrl);
                        
                        if (str_contains($redirectUrl, 'paytech.sn')) {
                            $this->info('✅ Correct PayTech redirect URL!');
                        } else {
                            $this->warn('⚠️ Unexpected redirect URL');
                        }
                    }
                    
                    // Vérifier que la commande a été mise à jour
                    $updatedOrder = Order::find($orderId);
                    if ($updatedOrder) {
                        $this->line('Order payment status: ' . $updatedOrder->payment_status);
                        if ($updatedOrder->cinetpay_transaction_id) {
                            $this->line('Transaction ID: ' . $updatedOrder->cinetpay_transaction_id);
                        }
                        if ($updatedOrder->cinetpay_payment_url) {
                            $this->line('Payment URL: ' . $updatedOrder->cinetpay_payment_url);
                        }
                    }
                    
                } else {
                    $this->error('❌ PayTech payment initiation failed!');
                    $this->line('Status code: ' . $paymentResponse->status());
                    $this->line('Response: ' . $paymentResponse->body());
                }
                
            } else {
                $this->error('❌ Order creation failed!');
                $this->line('Status code: ' . $orderResponse->status());
                $this->line('Response: ' . $orderResponse->body());
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            $this->line('This might be normal if the server is not running.');
            $this->line('Make sure to run: php artisan serve');
        }
        
        $this->info('Frontend PayTech integration test completed!');
        $this->line('');
        $this->line('To test manually:');
        $this->line('1. Start the server: php artisan serve');
        $this->line('2. Visit: http://localhost:8000/checkout');
        $this->line('3. Fill the form and select Orange Money, Wave, Visa, or Mastercard');
        $this->line('4. Submit the order to test PayTech integration');
        
        return 0;
    }
}