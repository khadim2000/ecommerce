<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
// use App\Http\Controllers\PaymentStatusController;
use Illuminate\Foundation\Application;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Page d'accueil - accessible à tous
Route::controller(ProductController::class)->group(function () {
    Route::get('/', 'index')->name('home');           // page d'accueil
    Route::get('/products/{id}', 'show')->name('product.detail'); // détails produit
});

// Routes protégées (authentifiées)
Route::middleware(['auth'])->controller(ProductController::class)->group(function () {
    Route::get('/dashboard', 'dashboard')->name('dashboard'); // dashboard
    // tu peux ajouter ici d'autres routes réservées aux utilisateurs connectés
});

// Route de test pour l'authentification
Route::get('/test-auth', function () {
    $user = auth()->user();
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => $user ? [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role
        ] : null
    ]);
})->name('test.auth');

// Route pour se connecter automatiquement en tant qu'admin (TEST UNIQUEMENT)
Route::get('/login-admin', function () {
    $admin = \App\Models\User::where('role', 'admin')->first();
    if ($admin) {
        auth()->login($admin);
        return redirect('/dashboard')->with('success', 'Connecté en tant qu\'admin');
    }
    return redirect('/')->with('error', 'Aucun admin trouvé');
})->name('login.admin');

// Route pour vérifier l'authentification
Route::get('/check-auth', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->user() ? [
            'id' => auth()->user()->id,
            'name' => auth()->user()->name,
            'email' => auth()->user()->email,
            'role' => auth()->user()->role
        ] : null
    ]);
})->name('check.auth');

// Route pour tester l'API des couleurs
Route::get('/test-colors', function () {
    return response()->json(\App\Models\Color::all());
})->name('test.colors');

// Détail d'un produit
Route::get('/products/{id}', function ($id) {
    return Inertia::render('ProductDetail', ['productId' => $id]);
})->name('product.detail');

// Routes d'authentification gérées par auth.php

// Afficher le formulaire d'inscription
Route::get('/register', function () {
    return Inertia::render('Auth/Register'); 
})->name('register.form');
Route::post('/register', [AuthController::class, 'register']);

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


//Route::any('/api/payments/initiate', [PaymentController::class, 'initiatePayment'])->name('payments.initiate');


// Routes nécessitant d'être connecté
Route::middleware('auth')->group(function () {
    // Voir ses commandes, profil, etc.
    Route::get('/profile', function () {
        return Inertia::render('Profile');
    })->name('profile');
    
    // Panier
    Route::get('/cart', function () {
        return Inertia::render('Cart');
    })->name('cart');
    
    // Checkout
    Route::get('/checkout', function () {
        return Inertia::render('Checkout');
    })->name('checkout');
    
    // Démonstration PayTech
    Route::get('/paytech-demo', function () {
        return Inertia::render('PayTechDemo');
    })->name('paytech.demo');
    
    // Historique des commandes
    Route::get('/orders', function () {
        return Inertia::render('Orders');
    })->name('orders');
    
    // Statut de paiement
    Route::get('/payment/status', function () {
        return Inertia::render('PaymentStatus');
    })->name('payment.status');
    
    // Routes pour le panier
    Route::post('/api/cart/add', [CartController::class, 'store'])->name('cart.add');
    Route::put('/api/cart/{id}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/api/cart/{id}', [CartController::class, 'destroy'])->name('cart.remove');
    
    // Routes pour le checkout et paiement (web routes pour Inertia.js)
    Route::post('/checkout/order', [OrderController::class, 'store'])->name('checkout.order');
    Route::post('/checkout/paytech/initiate', [PaymentController::class, 'initiatePayTechPayment'])->name('checkout.paytech.initiate');
    Route::get('/api/cart', [CartController::class, 'index'])->name('cart.get');
    
    // API Routes pour les commandes
    Route::post('/api/orders', [OrderController::class, 'store'])->name('orders.create');
    Route::get('/api/orders', [OrderController::class, 'index'])->name('orders.get');
    
    // API Routes pour les paiements
    Route::any('/api/payments/initiate', [PaymentController::class, 'initiatePayDunyaPayment'])->name('payments.initiate');
    Route::get('/api/payments/status', [PaymentController::class, 'checkPaymentStatus'])->name('payments.status');
    
    // Routes pour les paiements de commandes
    Route::post('/orders/{orderId}/pay', [PaymentController::class, 'payOrder'])->name('orders.pay');
    Route::get('/orders/{id}', [PaymentController::class, 'show'])->name('orders.show');
    
});

// Route publique pour renouveler le token CSRF
Route::get('/api/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->name('csrf.token');

// Routes publiques pour les callbacks de paiement
Route::prefix('payment')->group(function () {
    // Orange Money callbacks
    Route::get('/orange/success', [PaymentController::class, 'orangeMoneySuccess'])->name('payment.orange.success');
    Route::get('/orange/cancel', [PaymentController::class, 'orangeMoneyCancel'])->name('payment.orange.cancel');
    Route::post('/orange/notify', [PaymentController::class, 'orangeMoneyNotify'])->name('payment.orange.notify');
    
    // Wave callbacks
    Route::get('/wave/success', [PaymentController::class, 'waveSuccess'])->name('payment.wave.success');
    Route::get('/wave/cancel', [PaymentController::class, 'waveCancel'])->name('payment.wave.cancel');
    Route::post('/wave/callback', [PaymentController::class, 'waveCallback'])->name('payment.wave.callback');
    
    // CinetPay callbacks
    Route::get('/cinetpay/return', [PaymentController::class, 'cinetPayReturn'])->name('payment.cinetpay.return');
    
    // Routes publiques pour les notifications et vérifications de paiement
    Route::post('/payment/notify/{transaction_id}', [PaymentController::class, 'notification'])->name('payment.notify');
    Route::get('/payment/verify/{transaction_id}', [PaymentController::class, 'verification'])->name('payment.verify');
});

// Payment status pages - Using PaymentController instead
Route::get('/payments/success', function() {
    return Inertia::render('PaymentSuccess');
})->name('payment.success');
Route::get('/payments/failed', function() {
    return Inertia::render('PaymentFailed');
})->name('payment.failed');
Route::get('/payment/status', function() {
    return Inertia::render('PaymentStatus');
})->name('payment.status');

// Middleware admin pour routes admin (gestion produits, commandes,...)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/products', [ProductController::class, 'adminIndex'])->name('admin.products');
    Route::get('/admin/products/{id}', [ProductController::class, 'show'])->name('admin.products.show');
    Route::post('/admin/products', [ProductController::class, 'store'])->name('admin.products.store');
    Route::put('/admin/products/{id}', [ProductController::class, 'update'])->name('admin.products.update');
    Route::delete('/admin/products/{id}', [ProductController::class, 'destroy'])->name('admin.products.destroy');
    // ... autres routes admin
});

// Page de simulation PayDunya pour les tests
Route::get('/payment/paydunya/simulate/{token}', function($token) {
    return view('payments.paydunya-simulate', compact('token'));
})->name('payment.paydunya.simulate');

