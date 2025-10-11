<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\PaymentController;
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
   
   return $request->user();
   
});



Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
  //  Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
});




// 📦 Produits - routes publiques (lecture)
Route::get('/products', [ProductController::class, 'apiIndex']);     // Liste de tous les produits
Route::get('/products/{id}', [ProductController::class, 'show']); // Voir un produit
Route::get('/category', [CategoryController::class, 'index']);    // Liste des catégories
Route::get('/color', [ColorController::class, 'index']);    
// Produits CRUD (admin/dev) - Protégés par middleware admin
Route::middleware(['auth:sanctum', 'is.admin'])->group(function () {
    Route::get('/product', [ProductController::class, 'index']);    // Liste complète
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});

    // Catégories (admin/dev)
    Route::post('/category', [CategoryController::class, 'store']);
      // Liste des couleurs

// 🛒 Panier - Routes protégées par l'authentification web
Route::middleware(['web'])->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'store']);
    Route::put('/cart/{id}', [CartController::class, 'update']);
    Route::delete('/cart/{id}', [CartController::class, 'destroy']);
    Route::post('/orders/{orderId}/pay', [PaymentController::class, 'payOrder']);
});

// 🔐 Routes protégées par Sanctum
Route::middleware('auth:sanctum')->group(function () {
    
    // Déconnexion
    Route::post('/logout', [AuthController::class, 'logout']);

   
    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist', [WishlistController::class, 'store']);
    Route::delete('/wishlist/{id}', [WishlistController::class, 'destroy']);

    // Commandes
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);

    // Avis sur produit
    Route::post('/products/{id}/review', [ReviewController::class, 'store']);

});

// Paiements PayDunya (authentification web requise)
Route::middleware(['web'])->group(function () {
    Route::post('/payments/paydunya/initiate', [PaymentController::class, 'initiatePayDunyaPayment']);
    Route::get('/payments/paydunya/status', [PaymentController::class, 'checkPaymentStatus']);
});

// Webhooks PayDunya (public routes - no auth required)
Route::post('/payments/paydunya/notify/{transaction_id}', [PaymentController::class, 'handlePayDunyaWebhook']);
Route::get('/payments/paydunya/success/{transaction_id}', [PaymentController::class, 'payDunyaPaymentSuccess']);
Route::get('/payments/paydunya/cancel/{transaction_id}', [PaymentController::class, 'payDunyaPaymentCancel']);

// Paiements PayTech (authentification web requise) - Ancien système
Route::middleware(['web'])->group(function () {
    Route::post('/payments/paytech/initiate', [PaymentController::class, 'initiatePayTechPayment']);
    Route::get('/payments/status', [PaymentController::class, 'checkPaymentStatus']);
    Route::get('/payments/methods', [PaymentController::class, 'getPaymentMethods']);
});

// Webhooks PayTech (public routes - no auth required) - Ancien système
Route::post('/payments/paytech/notify/{transaction_id}', [PaymentController::class, 'handleWebhook']);
Route::get('/payments/paytech/verify/{transaction_id}', [PaymentController::class, 'paymentSuccess']);
Route::get('/payments/paytech/cancel/{transaction_id}', [PaymentController::class, 'paymentCancel']);

require __DIR__.'/auth.php';