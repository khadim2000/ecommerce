<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CinetPayService
{
    private $apiKey;
    private $siteId;
    private $baseUrl;
    private $notifyUrl;
    private $returnUrl;

    public function __construct()
    {
        $this->apiKey = config('services.cinetpay.api_key');
        $this->siteId = config('services.cinetpay.site_id');
        $this->baseUrl = config('services.cinetpay.base_url', 'https://api-checkout.cinetpay.com/v2');
        $this->notifyUrl = config('services.cinetpay.notify_url');
        $this->returnUrl = config('services.cinetpay.return_url');
    }

    /**
     * Initiate a payment with CinetPay
     * Utilise les paramètres exacts de la documentation API CinetPay
     */
    public function initiatePayment(array $paymentData)
    {
        try {
            // Validation du montant - doit être un multiple de 5
            if ($paymentData['amount'] % 5 !== 0) {
                return [
                    'success' => false,
                    'message' => 'Le montant doit être un multiple de 5'
                ];
            }

            // Nettoyer la description pour éviter les caractères spéciaux
            $description = $this->sanitizeDescription($paymentData['description']);

            $payload = [
                'apikey' => $this->apiKey, // OBLIGATOIRE - votre apikey fournie par CinetPay
                'site_id' => $this->siteId, // OBLIGATOIRE - votre site_id fourni par CinetPay
                'transaction_id' => $paymentData['transaction_id'], // OBLIGATOIRE - identifiant unique de la transaction
                'amount' => (int) $paymentData['amount'], // OBLIGATOIRE - montant en multiples de 5
                'currency' => $paymentData['currency'] ?? 'XOF', // OBLIGATOIRE - devise (XOF, XAF, CDF, GNF, USD)
                'description' => $description, // OBLIGATOIRE - description sans caractères spéciaux
                'notify_url' => $this->notifyUrl, // OBLIGATOIRE - URL de notification
                'return_url' => $this->returnUrl, // OBLIGATOIRE - URL de retour
                'channels' => $paymentData['channels'] ?? 'ALL', // OBLIGATOIRE - canaux de paiement (ALL, MOBILE_MONEY, CREDIT_CARD, WALLET)
            ];

            $response = Http::timeout(30)->post($this->baseUrl . '/payment', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['code'] === '201') {
                    return [
                        'success' => true,
                        'payment_url' => $data['data']['payment_url'],
                        'token' => $data['data']['token'],
                        'transaction_id' => $paymentData['transaction_id']
                    ];
                } else {
                    Log::error('CinetPay payment initiation failed', [
                        'code' => $data['code'],
                        'message' => $data['message'] ?? 'Unknown error',
                        'payload' => $payload
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => $data['message'] ?? 'Erreur lors de l\'initiation du paiement'
                    ];
                }
            } else {
                Log::error('CinetPay API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'payload' => $payload
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Erreur de connexion avec CinetPay'
                ];
            }
        } catch (\Exception $e) {
            Log::error('CinetPay service exception', [
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
                'apikey' => $this->apiKey,
                'site_id' => $this->siteId,
                'transaction_id' => $transactionId
            ];

            Log::info('CinetPay verification request', [
                'transaction_id' => $transactionId,
                'payload' => $payload
            ]);

            $response = Http::timeout(30)->post($this->baseUrl . '/payment/check', $payload);

            Log::info('CinetPay verification response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['code']) && $data['code'] === '00') {
                    $paymentData = $data['data'] ?? [];
                    
                    return [
                        'success' => true,
                        'status' => $paymentData['status'] ?? 'UNKNOWN',
                        'amount' => $paymentData['amount'] ?? null,
                        'currency' => $paymentData['currency'] ?? null,
                        'payment_method' => $paymentData['payment_method'] ?? null,
                        'payment_date' => $paymentData['payment_date'] ?? null,
                        'customer_name' => $paymentData['customer_name'] ?? null,
                        'customer_phone' => $paymentData['customer_phone'] ?? null,
                        'raw_response' => $data
                    ];
                } else {
                    Log::warning('CinetPay verification returned error code', [
                        'code' => $data['code'] ?? 'unknown',
                        'message' => $data['message'] ?? 'Unknown error',
                        'transaction_id' => $transactionId
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => $data['message'] ?? 'Erreur lors de la vérification du paiement',
                        'code' => $data['code'] ?? 'unknown'
                    ];
                }
            } else {
                Log::error('CinetPay verification HTTP error', [
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
            Log::error('CinetPay verification exception', [
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
            // Verify the webhook signature if needed
            $transactionId = $webhookData['cpm_trans_id'] ?? null;
            $status = $webhookData['cpm_result'] ?? null;
            $amount = $webhookData['cpm_amount'] ?? null;
            $currency = $webhookData['cpm_currency'] ?? null;
            $paymentMethod = $webhookData['payment_method'] ?? null;
            $customerPhone = $webhookData['cel_phone_num'] ?? null;
            
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
            Log::error('CinetPay webhook handling exception', [
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
     * Nettoyer la description pour éviter les caractères spéciaux
     * Selon la documentation CinetPay, éviter #,/,$,_,&
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
     * Valider que le montant est un multiple de 5
     */
    public function validateAmount($amount)
    {
        return $amount % 5 === 0;
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
                'channel' => 'MOBILE_MONEY'
            ],
            'wave' => [
                'name' => 'Wave',
                'logo' => '/images/payment/wave.svg',
                'description' => 'Wave Mobile Money',
                'channel' => 'MOBILE_MONEY'
            ],
            'visa' => [
                'name' => 'Visa',
                'logo' => '/images/payment/visa.svg',
                'description' => 'Carte Visa',
                'channel' => 'CREDIT_CARD'
            ],
            'mastercard' => [
                'name' => 'Mastercard',
                'logo' => '/images/payment/mastercard.svg',
                'description' => 'Carte Mastercard',
                'channel' => 'CREDIT_CARD'
            ]
        ];
    }
}
