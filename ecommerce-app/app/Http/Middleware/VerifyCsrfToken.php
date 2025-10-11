<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Routes API (exclues de la vérification CSRF)
        'api/*',
        
        // Routes web pour Inertia.js (exclues de la vérification CSRF)
        'checkout/order',
        'checkout/paytech/initiate',
        
        // Routes de callback de paiement (Orange Money, Wave, PayTech)
        'payment/orange-money/success',
        'payment/orange-money/cancel',
        'payment/orange-money/notify',
        'payment/wave/success',
        'payment/wave/cancel',
        'payment/wave/callback',
        'api/payments/cinetpay/notify',
        'api/payments/orange-money/notify',
        'api/payments/wave/callback',
        'api/payments/paytech/notify/*',
        'api/payments/paytech/verify/*',
        'api/payments/paytech/cancel/*',
    ];
}
