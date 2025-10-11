<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    private $orangeMoneyConfig;
    private $waveConfig;

    public function __construct()
    {
        $this->orangeMoneyConfig = [
            'client_id' => config('services.orange_money.client_id'),
            'client_secret' => config('services.orange_money.client_secret'),
            'base_url' => config('services.orange_money.base_url', 'https://api.orange.com'),
            'merchant_key' => config('services.orange_money.merchant_key'),
        ];

        $this->waveConfig = [
            'client_id' => config('services.wave.client_id'),
            'client_secret' => config('services.wave.client_secret'),
            'base_url' => config('services.wave.base_url', 'https://api.wave.com'),
            'merchant_key' => config('services.wave.merchant_key'),
        ];
    }

    /**
     * Initier un paiement Orange Money
     */
    public function initiateOrangeMoneyPayment($amount, $phoneNumber, $orderId, $description = '')
    {
        try {
            // Mode développement - simulation
            if (config('app.env') === 'local' || !$this->orangeMoneyConfig['client_id']) {
                return [
                    'success' => true,
                    'payment_url' => route('payment.orange.success') . '?pay_token=DEMO_' . $orderId . '_' . time(),
                    'transaction_id' => 'DEMO_' . $orderId . '_' . time(),
                    'data' => [
                        'status' => 'PENDING',
                        'message' => 'Paiement simulé - Mode développement'
                    ]
                ];
            }

            // Obtenir le token d'accès
            $accessToken = $this->getOrangeMoneyAccessToken();
            
            if (!$accessToken) {
                throw new \Exception('Impossible d\'obtenir le token d\'accès Orange Money');
            }

            // Préparer les données de paiement
            $paymentData = [
                'merchant_key' => $this->orangeMoneyConfig['merchant_key'],
                'currency' => 'XOF',
                'order_id' => $orderId,
                'amount' => $amount,
                'return_url' => route('payment.orange.success'),
                'cancel_url' => route('payment.orange.cancel'),
                'notif_url' => route('payment.orange.notify'),
                'lang' => 'fr',
                'reference' => 'ORDER_' . $orderId,
            ];

            // Appeler l'API Orange Money
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->orangeMoneyConfig['base_url'] . '/orange-money-webpay/dev/v1/webpayment', $paymentData);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'payment_url' => $data['payment_url'] ?? null,
                    'transaction_id' => $data['pay_token'] ?? null,
                    'data' => $data
                ];
            }

            throw new \Exception('Erreur API Orange Money: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Erreur paiement Orange Money: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Initier un paiement Wave
     */
    public function initiateWavePayment($amount, $phoneNumber, $orderId, $description = '')
    {
        try {
            // Mode développement - simulation
            if (config('app.env') === 'local' || !$this->waveConfig['client_id']) {
                return [
                    'success' => true,
                    'payment_url' => route('payment.wave.success') . '?transaction_id=DEMO_' . $orderId . '_' . time(),
                    'transaction_id' => 'DEMO_' . $orderId . '_' . time(),
                    'data' => [
                        'status' => 'PENDING',
                        'message' => 'Paiement simulé - Mode développement'
                    ]
                ];
            }

            // Obtenir le token d'accès Wave
            $accessToken = $this->getWaveAccessToken();
            
            if (!$accessToken) {
                throw new \Exception('Impossible d\'obtenir le token d\'accès Wave');
            }

            // Préparer les données de paiement
            $paymentData = [
                'amount' => $amount,
                'currency' => 'XOF',
                'phone_number' => $phoneNumber,
                'order_id' => $orderId,
                'description' => $description,
                'callback_url' => route('payment.wave.callback'),
                'return_url' => route('payment.wave.success'),
                'cancel_url' => route('payment.wave.cancel'),
            ];

            // Appeler l'API Wave
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->waveConfig['base_url'] . '/v1/payments', $paymentData);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'payment_url' => $data['payment_url'] ?? null,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'data' => $data
                ];
            }

            throw new \Exception('Erreur API Wave: ' . $response->body());

        } catch (\Exception $e) {
            Log::error('Erreur paiement Wave: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtenir le token d'accès Orange Money
     */
    private function getOrangeMoneyAccessToken()
    {
        try {
            $response = Http::asForm()->post($this->orangeMoneyConfig['base_url'] . '/oauth/v2/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->orangeMoneyConfig['client_id'],
                'client_secret' => $this->orangeMoneyConfig['client_secret'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erreur token Orange Money: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir le token d'accès Wave
     */
    private function getWaveAccessToken()
    {
        try {
            $response = Http::asForm()->post($this->waveConfig['base_url'] . '/oauth/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->waveConfig['client_id'],
                'client_secret' => $this->waveConfig['client_secret'],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Erreur token Wave: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function checkPaymentStatus($provider, $transactionId)
    {
        try {
            if ($provider === 'orange_money') {
                return $this->checkOrangeMoneyStatus($transactionId);
            } elseif ($provider === 'wave') {
                return $this->checkWaveStatus($transactionId);
            }

            return ['success' => false, 'error' => 'Provider non supporté'];
        } catch (\Exception $e) {
            Log::error('Erreur vérification statut: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifier le statut Orange Money
     */
    private function checkOrangeMoneyStatus($transactionId)
    {
        $accessToken = $this->getOrangeMoneyAccessToken();
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ])->get($this->orangeMoneyConfig['base_url'] . '/orange-money-webpay/dev/v1/transactionstatus/' . $transactionId);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        }

        return ['success' => false, 'error' => 'Impossible de vérifier le statut'];
    }

    /**
     * Vérifier le statut Wave
     */
    private function checkWaveStatus($transactionId)
    {
        $accessToken = $this->getWaveAccessToken();
        
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
        ])->get($this->waveConfig['base_url'] . '/v1/payments/' . $transactionId);

        if ($response->successful()) {
            return ['success' => true, 'data' => $response->json()];
        }

        return ['success' => false, 'error' => 'Impossible de vérifier le statut'];
    }
}
