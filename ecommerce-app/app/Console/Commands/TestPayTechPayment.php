<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PayTechService;

class TestPayTechPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paytech:test-payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PayTech payment initiation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing PayTech payment initiation...');
        
        $payTechService = new PayTechService();
        
        // Test data
        $paymentData = [
            'transaction_id' => 'TEST_' . time(),
            'amount' => 1000, // 1000 centimes = 10 XOF
            'currency' => 'XOF',
            'description' => 'Test de paiement PayTech',
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
            'customer_phone' => '+221123456789',
            'payment_method' => 'ORANGE_MONEY',
        ];
        
        $this->line('Payment data:');
        $this->line('- Transaction ID: ' . $paymentData['transaction_id']);
        $this->line('- Amount: ' . $paymentData['amount'] . ' centimes');
        $this->line('- Currency: ' . $paymentData['currency']);
        $this->line('- Description: ' . $paymentData['description']);
        
        try {
            $result = $payTechService->initiatePayment($paymentData);
            
            if ($result['success']) {
                $this->info('✅ Payment initiation successful!');
                $this->line('Payment URL: ' . $result['payment_url']);
                $this->line('Transaction ID: ' . $result['transaction_id']);
            } else {
                $this->error('❌ Payment initiation failed!');
                $this->line('Error: ' . $result['message']);
            }
            
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
        }
        
        return 0;
    }
}