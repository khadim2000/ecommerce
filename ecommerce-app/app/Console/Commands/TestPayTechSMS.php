<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use App\Http\Controllers\PaymentController;
use App\Services\PayTechService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TestPayTechSMS extends Command
{
    protected $signature = 'paytech:test-sms {phone?}';
    protected $description = 'Test PayTech avec SMS de confirmation';

    public function handle()
    {
        $phone = $this->argument('phone') ?? '+221771234567';
        
        $this->info('🧪 Test PayTech avec SMS de confirmation');
        $this->line('📱 Numéro de téléphone: ' . $phone);
        $this->line('');

        // 1. Créer un utilisateur de test
        $user = User::first();
        if (!$user) {
            $this->error('❌ Aucun utilisateur trouvé');
            return;
        }

        Auth::login($user);
        $this->info('✅ Utilisateur connecté: ' . $user->name);

        // 2. Créer un panier de test
        $product = Product::first();
        if (!$product) {
            $this->error('❌ Aucun produit trouvé');
            return;
        }

        // Supprimer l'ancien panier
        Cart::where('user_id', $user->id)->delete();

        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => 'M',
            'color' => 'Rouge'
        ]);

        $this->info('✅ Panier créé avec produit: ' . $product->name);

        // 3. Créer une commande
        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => $product->price,
            'address' => '123 Rue Test, Dakar',
            'phone' => $phone, // Utiliser le numéro fourni
            'payment_method' => 'orange_money',
            'payment_status' => 'unpaid'
        ]);

        $this->info('✅ Commande créée avec ID: ' . $order->id);
        $this->info('💰 Montant: ' . $order->total . ' XOF');

        // 4. Tester l'initiation PayTech
        $this->info('');
        $this->info('🔄 Initiation du paiement PayTech...');

        $payTechService = new PayTechService();
        $paymentData = [
            'transaction_id' => 'PT_' . time() . '_' . $order->id,
            'amount' => (int) $order->total,
            'currency' => 'XOF',
            'description' => "Commande {$order->id} - Test SMS",
            'payment_method' => 'orange_money',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $phone, // Numéro pour SMS
        ];

        $result = $payTechService->initiatePayment($paymentData);

        if ($result['success']) {
            $this->info('✅ PayTech initialisé avec succès !');
            $this->info('🔗 URL de paiement: ' . $result['payment_url']);
            $this->info('🆔 Transaction ID: ' . $result['transaction_id']);
            
            // Mettre à jour la commande
            $order->update([
                'payment_status' => 'pending',
                'cinetpay_transaction_id' => $result['transaction_id'],
                'cinetpay_token' => $result['token'],
                'cinetpay_payment_url' => $result['payment_url'],
                'cinetpay_payment_method' => 'orange_money',
            ]);

            $this->info('');
            $this->info('📱 PROCHAINES ÉTAPES POUR RECEVOIR LE SMS:');
            $this->info('1. Ouvrez l\'URL de paiement dans votre navigateur');
            $this->info('2. Vous serez redirigé vers PayTech');
            $this->info('3. Sélectionnez Orange Money');
            $this->info('4. Entrez votre numéro: ' . $phone);
            $this->info('5. Vous recevrez un SMS de confirmation sur ce numéro');
            $this->info('6. Entrez le code reçu par SMS');
            $this->info('7. Confirmez le paiement');
            
            $this->info('');
            $this->info('🔗 URL à ouvrir: ' . $result['payment_url']);
            
        } else {
            $this->error('❌ Erreur PayTech: ' . $result['message']);
        }

        // Nettoyer
        Cart::where('user_id', $user->id)->delete();
        Auth::logout();
    }
}


