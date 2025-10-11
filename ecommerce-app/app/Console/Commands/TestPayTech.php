<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PayTechService;

class TestPayTech extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paytech:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test PayTech service configuration and API connection';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing PayTech service...');
        
        // Test configuration
        $this->info('Checking configuration...');
        $apiKey = config('services.paytech.api_key');
        $secretKey = config('services.paytech.secret_key');
        $baseUrl = config('services.paytech.base_url');
        $environment = config('services.paytech.environment');
        
        $this->line("API Key: " . ($apiKey ? 'Set' : 'Not set'));
        $this->line("Secret Key: " . ($secretKey ? 'Set' : 'Not set'));
        $this->line("Base URL: {$baseUrl}");
        $this->line("Environment: {$environment}");
        
        if (!$apiKey || !$secretKey) {
            $this->error('PayTech credentials are not configured!');
            $this->line('Please add these variables to your .env file:');
            $this->line('PAYTECH_API_KEY=your_api_key');
            $this->line('PAYTECH_SECRET_KEY=your_secret_key');
            $this->line('PAYTECH_ENVIRONMENT=sandbox');
            return 1;
        }
        
        // Test service initialization
        try {
            $payTechService = new PayTechService();
            $this->info('PayTech service initialized successfully!');
            
            // Test payment methods
            $methods = $payTechService->getAvailablePaymentMethods();
            $this->info('Available payment methods:');
            foreach ($methods as $key => $method) {
                $this->line("- {$key}: {$method['name']}");
            }
            
            $this->info('PayTech service is ready to use!');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('Error initializing PayTech service: ' . $e->getMessage());
            return 1;
        }
    }
}