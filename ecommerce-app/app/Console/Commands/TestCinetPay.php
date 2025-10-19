<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CinetPayService;
use Illuminate\Support\Facades\Log;

class TestCinetPay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cinetpay:test {--verify= : Transaction ID to verify}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test CinetPay integration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing CinetPay integration...');
        
        // Vérifier la configuration
        $this->checkConfiguration();
        
        // Test de vérification si un ID de transaction est fourni
        if ($transactionId = $this->option('verify')) {
            $this->testVerification($transactionId);
        } else {
            $this->info('Use --verify=TRANSACTION_ID to test payment verification');
        }
    }
    
    private function checkConfiguration()
    {
        $this->info('Checking CinetPay configuration...');
        
        $config = [
            'API_KEY' => config('services.cinetpay.api_key'),
            'SITE_ID' => config('services.cinetpay.site_id'),
            'BASE_URL' => config('services.cinetpay.base_url'),
            'NOTIFY_URL' => config('services.cinetpay.notify_url'),
            'RETURN_URL' => config('services.cinetpay.return_url'),
            'ENVIRONMENT' => config('services.cinetpay.environment'),
        ];
        
        foreach ($config as $key => $value) {
            if (empty($value)) {
                $this->error("❌ {$key} is not configured");
            } else {
                $this->info("✅ {$key}: " . ($key === 'API_KEY' ? str_repeat('*', strlen($value)) : $value));
            }
        }
    }
    
    private function testVerification($transactionId)
    {
        $this->info("Testing payment verification for transaction: {$transactionId}");
        
        $cinetPayService = app(CinetPayService::class);
        $result = $cinetPayService->verifyPayment($transactionId);
        
        if ($result['success']) {
            $this->info('✅ Verification successful');
            $this->table(
                ['Field', 'Value'],
                [
                    ['Status', $result['status']],
                    ['Amount', $result['amount']],
                    ['Currency', $result['currency']],
                    ['Payment Method', $result['payment_method']],
                    ['Payment Date', $result['payment_date']],
                ]
            );
        } else {
            $this->error('❌ Verification failed: ' . $result['message']);
        }
    }
}















