<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayTechService
{
    private $apiKey;
    private $secretKey;
    private $baseUrl;
    private $notifyUrl;
    private $returnUrl;
    private $environment;

    public function __construct()
    {
        $this->apiKey = config('services.paytech.api_key');
        $this->secretKey = config('services.paytech.secret_key');
        $this->environment = config('services.paytech.environment', 'test');
        $this->baseUrl = $this->environment === 'prod' 
            ? 'https://paytech.sn/api' 
            : 'https://paytech.sn/api';
        $this->notifyUrl = config('services.paytech.notify_url', url('/payment/notify'));
        $this->returnUrl = config('services.paytech.return_url', url('/payment/verify'));
    }

    /**
     * Initiate a payment with PayTech
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

            // PayTech utilise des paramètres spécifiques - les infos client seront saisies sur PayTech
            $payload = [
                'item_name' => $description,
                'item_price' => (int) $paymentData['amount'], // Montant en centimes
                'currency' => $paymentData['currency'] ?? 'XOF',
                'ref_command' => $paymentData['transaction_id'],
                'success_url' => $this->returnUrl . '/' . $paymentData['transaction_id'],
                'cancel_url' => config('services.paytech.cancel_url') . '/' . $paymentData['transaction_id'],
                'ipn_url' => $this->notifyUrl . '/' . $paymentData['transaction_id'],
                'command_name' => 'Commande e-commerce',
                // Les informations client seront collectées directement sur PayTech
            ];

            Log::info('PayTech payment initiation request', $payload);

            // Ajouter l'environnement au payload
            $payload['env'] = $this->environment;
            
            // Mode simulation pour les tests (avec vrai format PayTech)
            if ($this->environment === 'test') {
                // Générer un token réaliste mais simulé
                $token = substr(md5($paymentData['transaction_id'] . time()), 0, 15);
                
                return [
                    'success' => true,
                    'payment_url' => 'https://paytech.sn/payment/checkout/' . $token,
                    'token' => $token,
                    'transaction_id' => $paymentData['transaction_id']
                ];
            }
            
            // Appel à l'API PayTech réelle avec authentification
            $response = Http::timeout(30)
                ->withHeaders([
                    'API_KEY' => $this->apiKey,
                    'API_SECRET' => $this->secretKey,
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->post($this->baseUrl . '/payment/request-payment', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['success']) && ($data['success'] === true || $data['success'] === 1)) {
                    // PayTech retourne un token, nous construisons l'URL de paiement
                    $token = $data['token'] ?? null;
                    if ($token) {
                        // Utiliser l'URL directe de PayTech
                        $paymentUrl = $data['redirect_url'] ?? $data['redirectUrl'] ?? 'https://paytech.sn/payment/checkout/' . $token;
                    } else {
                        // Fallback vers l'URL directe si disponible
                        $paymentUrl = $data['redirect_url'] ?? $data['payment_url'] ?? null;
                    }
                    
                    return [
                        'success' => true,
                        'payment_url' => $paymentUrl,
                        'token' => $token,
                        'transaction_id' => $paymentData['transaction_id']
                    ];
                } else {
                    Log::error('PayTech payment initiation failed', [
                        'response' => $data,
                        'payload' => $payload
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => $data['message'] ?? 'Erreur lors de l\'initiation du paiement'
                    ];
                }
            } else {
                Log::error('PayTech API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Erreur de connexion avec PayTech - Mode test activé'
                ];
            }
        } catch (\Exception $e) {
            Log::error('PayTech service exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur interne du service de paiement'
            ];
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment($transactionId)
    {
        try {
            $payload = [
                'ref_command' => $transactionId
            ];

            Log::info('PayTech verification request', [
                'transaction_id' => $transactionId,
                'payload' => $payload
            ]);

            $response = Http::timeout(30)->post($this->baseUrl . '/check', $payload);

            Log::info('PayTech verification response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['success']) && $data['success'] === true) {
                    return [
                        'success' => true,
                        'status' => $data['status'] === 'SUCCESS' ? 'SUCCESS' : 'FAILED',
                        'amount' => $data['amount'] ?? null,
                        'currency' => $data['currency'] ?? null,
                        'payment_method' => $data['payment_method'] ?? null,
                        'payment_date' => $data['payment_date'] ?? null,
                        'customer_name' => $data['customer_name'] ?? null,
                        'customer_phone' => $data['customer_phone'] ?? null,
                        'raw_response' => $data
                    ];
                } else {
                    Log::warning('PayTech verification returned error', [
                        'message' => $data['message'] ?? 'Unknown error',
                        'transaction_id' => $transactionId
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => $data['message'] ?? 'Erreur lors de la vérification du paiement'
                    ];
                }
            } else {
                Log::error('PayTech verification HTTP error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'transaction_id' => $transactionId
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Erreur HTTP lors de la vérification du paiement'
                ];
            }
        } catch (\Exception $e) {
            Log::error('PayTech verification exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'transaction_id' => $transactionId
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur interne lors de la vérification'
            ];
        }
    }

    /**
     * Handle webhook notification
     */
    public function handleWebhook(array $webhookData)
    {
        try {
            $transactionId = $webhookData['transaction_id'] ?? null;
            $status = $webhookData['status'] ?? null;
            $amount = $webhookData['amount'] ?? null;
            $currency = $webhookData['currency'] ?? null;
            $paymentMethod = $webhookData['payment_method'] ?? null;
            $customerPhone = $webhookData['customer_phone'] ?? null;
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'status' => $status,
                'amount' => $amount,
                'currency' => $currency,
                'payment_method' => $paymentMethod,
                'customer_phone' => $customerPhone,
                'raw_data' => $webhookData
            ];
        } catch (\Exception $e) {
            Log::error('PayTech webhook handling exception', [
                'message' => $e->getMessage(),
                'webhook_data' => $webhookData
            ]);
            
            return [
                'success' => false,
                'message' => 'Erreur lors du traitement du webhook'
            ];
        }
    }

    /**
     * Nettoyer la description
     */
    private function sanitizeDescription($description)
    {
        // Supprimer les caractères spéciaux problématiques
        $description = str_replace(['#', '/', '$', '_', '&'], '', $description);
        
        // Remplacer les espaces multiples par un seul espace
        $description = preg_replace('/\s+/', ' ', $description);
        
        // Supprimer les espaces en début et fin
        $description = trim($description);
        
        // Limiter la longueur à 255 caractères
        return substr($description, 0, 255);
    }

    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods()
    {
        return [
            'orange_money' => [
                'name' => 'Orange Money',
                'logo' => '/images/payment/orange-money.svg',
                'description' => 'Mobile Money Orange',
                'method' => 'ORANGE_MONEY'
            ],
            'wave' => [
                'name' => 'Wave',
                'logo' => '/images/payment/wave.svg',
                'description' => 'Wave Mobile Money',
                'method' => 'WAVE'
            ],
            'visa' => [
                'name' => 'Visa',
                'logo' => '/images/payment/visa.svg',
                'description' => 'Carte Visa',
                'method' => 'VISA'
            ],
            'mastercard' => [
                'name' => 'Mastercard',
                'logo' => '/images/payment/mastercard.svg',
                'description' => 'Carte Mastercard',
                'method' => 'MASTERCARD'
            ]
        ];
    }
}
