<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class PaymentController extends Controller
{
    public function payOrder($orderId)
    {
        $order = Order::with('orderItems.product')
            ->where('user_id', Auth::id())
            ->findOrFail($orderId);


        $items = $order->orderItems->map(function ($item) {
            return [
                "name"        => $item->product->name,
                "quantity"    => $item->quantity,
                "unit_price"  => $item->price,
                "total_price" => $item->total_price,
            ];
        })->toArray();

        $payload = [
            "invoice" => [
                "items"        => $items,
                "total_amount" => $order->total_price,
                "description"  => "Commande #" . $order->id
            ],
            "store" => [
                "name" => "Magasin le Choco"
            ]
        ];

        $response = Http::withHeaders([
            "Content-Type"          => "application/json",
            "PAYDUNYA-MASTER-KEY"   => env("PAYDUNYA_MASTER_KEY"),
            "PAYDUNYA-PRIVATE-KEY"  => env("PAYDUNYA_PRIVATE_KEY"),
            "PAYDUNYA-TOKEN"        => env("PAYDUNYA_TOKEN"),
        ])->post("https://app.paydunya.com/sandbox-api/v1/checkout-invoice/create", $payload);

        if ($response->successful()) {
            $body = $response->json(); // 🔥 on récupère le JSON
    
            return response()->json([
                "success"     => $body["response_code"] === "00",
                "payment_url" => $body["response_text"] ?? null, // 🔥 ici est ton lien
                "order"       => $order
            ]);
        }

        return response()->json([
            "success" => false,
            "error"   => $response->json()
        ], 500);
    }
}