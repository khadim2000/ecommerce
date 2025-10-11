<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Services\PayTechService;

class TestOrderPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paytech:test-order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test complete order payment flow with PayTech';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing complete order payment flow...');
        
        // Créer un utilisateur de test
        $user = User::first();
        if (!$user) {
            $this->error('No user found. Please create a user first.');
            return 1;
        }
        
        // Créer un produit de test
        $product = Product::first();
        if (!$product) {
            $this->error('No product found. Please create a product first.');
            return 1;
        }
        
        $this->line('Using user: ' . $user->name);
        $this->line('Using product: ' . $product->name . ' (Price: ' . $product->price . ' XOF)');
        
        // Créer une commande de test
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => $product->price,
            'address' => 'Adresse de test',
            'phone' => '+221123456789',
            'payment_method' => 'orange_money',
            'payment_status' => 'unpaid'
        ]);
        
        $this->info('Order created with ID: ' . $order->id);
        
        // Tester le paiement PayTech
        $payTechService = new PayTechService();
        
        $paymentData = [
            'transaction_id' => 'PT_' . time() . '_' . $order->id,
            'amount' => (int) $order->total,
            'currency' => 'XOF',
            'description' => "Commande {$order->id} - E-commerce",
            'payment_method' => 'ORANGE_MONEY',
            'customer_name' => $order->user->name ?? 'Client',
            'customer_email' => $order->user->email ?? 'client@example.com',
            'customer_phone' => $order->phone ?? '',
        ];
        
        $this->line('Initiating PayTech payment...');
        
        try {
            $result = $payTechService->initiatePayment($paymentData);
            
            if ($result['success']) {
                $this->info('✅ PayTech payment initiated successfully!');
                $this->line('Payment URL: ' . $result['payment_url']);
                $this->line('Transaction ID: ' . $result['transaction_id']);
                
                // Mettre à jour la commande
                $order->update([
                    'payment_status' => 'pending',
                    'cinetpay_transaction_id' => $result['transaction_id'],
                    'cinetpay_token' => $result['token'],
                    'cinetpay_payment_url' => $result['payment_url'],
                    'cinetpay_payment_method' => 'orange_money',
                ]);
                
                $this->info('Order updated with PayTech transaction details');
                $this->line('Order ID: ' . $order->id);
                $this->line('Payment Status: ' . $order->payment_status);
                $this->line('Transaction ID: ' . $order->cinetpay_transaction_id);
                
            } else {
                $this->error('❌ PayTech payment initiation failed!');
                $this->line('Error: ' . $result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
        }
        
        return 0;
    }
}