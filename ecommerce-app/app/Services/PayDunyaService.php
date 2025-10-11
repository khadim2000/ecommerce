<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayDunyaService
{
    private $masterKey;
    private $privateKey;
    private $token;
    private $baseUrl;
    private $notifyUrl;
    private $returnUrl;
    private $cancelUrl;
    private $environment;

    public function __construct()
    {
        $this->masterKey = config('services.paydunya.master_key');
        $this->privateKey = config('services.paydunya.private_key');
        $this->token = config('services.paydunya.token');
        $this->environment = config('services.paydunya.environment', 'test');
        $this->baseUrl = $this->environment === 'prod' 
            ? 'https://app.paydunya.com' 
            : 'https://app.paydunya.com';
        $this->notifyUrl = config('services.paydunya.notify_url', url('/payment/notify'));
        $this->returnUrl = config('services.paydunya.return_url', url('/payment/success'));
        $this->cancelUrl = config('services.paydunya.cancel_url', url('/payment/cancel'));
    }

    /**
     * Initiate a payment with PayDunya
     */
    public function initiatePayment(array $paymentData)
    {
        try {
            // Validation du montant
            if (!is_numeric($paymentData['amount']) || $paymentData['amount'] <= 0) {
                return [
                    'success' => false,
                    'message' => 'Le montant doit être un nombre positif'
                ];
            }

            // Nettoyer la description
            $description = $this->sanitizeDescription($paymentData['description'] ?? '');

            // PayDunya utilise des paramètres spécifiques
            $payload = [
                'invoice' => [
                    'items' => [
                        [
                            'name' => $description,
                            'quantity' => 1,
                            'unit_price' => (int) $paymentData['amount'], // Montant en centimes
                            'total_price' => (int) $paymentData['amount']
                        ]
                    ],
                    'total_amount' => (int) $paymentData['amount'],
                    'description' => $description
                ],
                'store' => [
                    'name' => 'E-commerce Store',
                    'tagline' => 'Votre boutique en ligne',
                    'postal_address' => 'Dakar, Sénégal',
                    'phone' => '+221771234567',
                    'logo_url' => url('/images/logo.png')
                ],
                'custom_data' => [
                    'order_id' => $paymentData['transaction_id'],
                    'customer_name' => $paymentData['customer_name'] ?? 'Client',
                    'customer_phone' => $paymentData['customer_phone'] ?? '+221771234567',
                    'customer_email' => $paymentData['customer_email'] ?? 'client@example.com'
                ],
                'actions' => [
                    'cancel_url' => $this->cancelUrl . '/' . $paymentData['transaction_id'],
                    'return_url' => $this->returnUrl . '/' . $paymentData['transaction_id'],
                    'callback_url' => $this->notifyUrl . '/' . $paymentData['transaction_id']
                ]
            ];

            Log::info('PayDunya payment initiation request', $payload);

            // Mode simulation pour les tests (désactivé - utilise l'API réelle)
            // if ($this->environment === 'test') {
            //     // Générer un token réaliste mais simulé
            //     $token = 'test_' . substr(md5($paymentData['transaction_id'] . time()), 0, 20);
            //     
            //     return [
            //         'success' => true,
            //         'payment_url' => url('/payment/paydunya/simulate/' . $token),
            //         'token' => $token,
            //         'transaction_id' => $paymentData['transaction_id']
            //     ];
            // }

            // Appel à l'API PayDunya réelle
            $response = Http::timeout(30)
                ->withHeaders([
                    'PAYDUNYA-PRIVATE-KEY' => $this->privateKey,
                    'PAYDUNYA-MASTER-KEY' => $this->masterKey,
                    'PAYDUNYA-TOKEN' => $this->token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post('https://app.paydunya.com/api/v1/checkout-invoice/create', $payload);

            Log::info('PayDunya API Response', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['response_code']) && $data['response_code'] === '00') {
                    return [
                        'success' => true,
                        'payment_url' => $data['response_texts']['invoice_url'] ?? $data['response_texts']['url'],
                        'token' => $data['response_texts']['token'] ?? null,
                        'transaction_id' => $paymentData['transaction_id']
                    ];
                } else {
                    Log::error('PayDunya payment initiation failed', [
                        'response' => $data,
                        'payload' => $payload
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => $data['response_text'] ?? 'Erreur lors de l\'initiation du paiement'
                    ];
                }
            } else {
                Log::error('PayDunya API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Erreur de connexion avec PayDunya - Mode test activé'
                ];
            }

        } catch (\Exception $e) {
            Log::error('PayDunya service error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de l\'initiation du paiement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Nettoyer la description pour PayDunya
     */
    private function sanitizeDescription($description)
    {
        // PayDunya accepte les caractères alphanumériques et quelques caractères spéciaux
        $description = preg_replace('/[^a-zA-Z0-9\s\-_.,]/', '', $description);
        return substr($description, 0, 100); // Limiter à 100 caractères
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function checkPaymentStatus($token)
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'PAYDUNYA-PRIVATE-KEY' => $this->privateKey,
                    'PAYDUNYA-MASTER-KEY' => $this->masterKey,
                    'PAYDUNYA-TOKEN' => $this->token,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->get($this->baseUrl . '/api/v1/checkout-invoice/confirm/' . $token);

            if ($response->successful()) {
                $data = $response->json();
                
                return [
                    'success' => true,
                    'status' => $data['status'] ?? 'unknown',
                    'data' => $data
                ];
            }

            return [
                'success' => false,
                'message' => 'Impossible de vérifier le statut du paiement'
            ];

        } catch (\Exception $e) {
            Log::error('PayDunya status check error', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Erreur lors de la vérification du statut'
            ];
        }
    }
}
