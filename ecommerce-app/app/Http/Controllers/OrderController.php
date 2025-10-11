<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{


    public function index()
    {
        return Order::where('user_id', Auth::id())->with('orderItems.product')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'address' => 'required|string|max:255',
            'phone' => 'required|string|regex:/^\+221[0-9]{9}$/',
        ], [
            'phone.regex' => 'Le numéro de téléphone doit être au format sénégalais (+221 suivi de 9 chiffres)',
        ]);
    
        $cartItems = Cart::where('user_id', Auth::id())->with('product')->get();
    
        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Le panier est vide'], 400);
        }
        $totalPrice = $cartItems->sum(function ($item) {
            return $item->product->price * $item->quantity;
        });
        
        $order = Order::create([
            'user_id' => Auth::id(),
            'address' => $request->address,
            'phone' => $request->phone,
            'total_price' => $totalPrice, 
        ]);
    
       
    
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'price' => $item->product->price,
                'quantity' => $item->quantity,
                 "total_price" => $item->product->price * $item->quantity,
                'size' => $item->size,
                'color' => $item->color,
            ]);
        }
    
        Cart::where('user_id', Auth::id())->delete();
    
        return response()->json([
            'success' => true,
            'order' => $order->load('orderItems.product')
        ], 201);
    }
}
