<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;



class CartController extends Controller
{
    /**
     * Afficher le panier de l'utilisateur connecté
     */
    public function index()
    {
        $userId = Auth::id();
        
        if (!$userId) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }
        
        return response()->json(
            Cart::with('product')
                ->where('user_id', $userId)
                ->get()
        );
    }
public function store(Request $request)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity'   => 'integer|min:1',
        'color'      => 'nullable|string|max:255',
        'size'       => 'nullable|string|max:255',
    ]);

    // Valeur par défaut pour la quantité
    $validated['quantity'] = $validated['quantity'] ?? 1;

    $userId = Auth::id();

    if (!$userId) {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }
        return redirect()->route('login');
    }

    $validated['user_id'] = $userId;

    // Vérifier si l'article existe déjà avec la même couleur et taille
    $existingItem = Cart::where('user_id', $userId)
        ->where('product_id', $validated['product_id'])
        ->where('color', $validated['color'])
        ->where('size', $validated['size'])
        ->first();

    if ($existingItem) {
        // Mettre à jour la quantité si l'article existe déjà
        $existingItem->quantity += $validated['quantity'];
        $existingItem->save();
        
        if ($request->expectsJson()) {
            return response()->json($existingItem->load('product'), 200);
        }
        
        return back()->with('success', 'Quantité mise à jour dans le panier !');
    }

    $cartItem = Cart::create($validated);

    if ($request->expectsJson()) {
        return response()->json($cartItem->load('product'), 201);
    }
    
    return back()->with('success', 'Produit ajouté au panier !');
}

/**
 * Mettre à jour la quantité d'un produit dans le panier
 */
public function update(Request $request, $id)
{
    $validated = $request->validate([
        'quantity' => 'required|integer|min:1',
    ]);

    $cartItem = Cart::where('id', $id)
        ->where('user_id', Auth::id())
        ->first();

    if (!$cartItem) {
        return response()->json(['error' => 'Article non trouvé'], 404);
    }

    $cartItem->update($validated);

    return response()->json($cartItem->load('product'));
}

/**
 * Supprimer un produit du panier
 */
    public function destroy($id)
    {
        $deleted = Cart::where('id', $id)
            ->where('user_id', Auth::id())
            ->delete();

        if ($deleted) {
            return response()->json(['message' => 'Article supprimé'], 200);
        }

        return response()->json(['error' => 'Article non trouvé'], 404);
    }
}
