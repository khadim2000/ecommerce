<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class SimulatePayTechSMS extends Command
{
    protected $signature = 'paytech:simulate-sms {phone}';
    protected $description = 'Simule le processus PayTech avec SMS pour les tests';

    public function handle()
    {
        $phone = $this->argument('phone');
        
        $this->info('🧪 SIMULATION PAYTECH AVEC SMS');
        $this->line('📱 Numéro: ' . $phone);
        $this->line('⚠️  MODE TEST - Pas de vrai SMS envoyé');
        $this->line('');

        // 1. Créer une commande de test
        $user = User::first();
        Auth::login($user);

        $product = Product::first();
        Cart::where('user_id', $user->id)->delete();

        $cart = Cart::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'size' => 'M',
            'color' => 'Rouge'
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'total' => $product->price,
            'address' => '123 Rue Test, Dakar',
            'phone' => $phone,
            'payment_method' => 'orange_money',
            'payment_status' => 'pending'
        ]);

        $transactionId = 'PT_' . time() . '_' . $order->id;
        $order->update([
            'cinetpay_transaction_id' => $transactionId,
            'cinetpay_token' => 'test_token_' . $transactionId,
            'cinetpay_payment_url' => 'https://paytech.sn/payment/test/' . $transactionId,
            'cinetpay_payment_method' => 'orange_money',
        ]);

        $this->info('✅ Commande créée: ' . $order->id);
        $this->info('💰 Montant: ' . $order->total . ' XOF');
        $this->info('🆔 Transaction: ' . $transactionId);
        $this->line('');

        // 2. Simuler le processus PayTech
        $this->info('🔄 SIMULATION DU PROCESSUS PAYTECH...');
        $this->line('');
        
        $this->info('1️⃣ Redirection vers PayTech...');
        $this->line('   URL: https://paytech.sn/payment/test/' . $transactionId);
        $this->line('   ✅ Simulation: Utilisateur redirigé');
        $this->line('');

        $this->info('2️⃣ Sélection de l\'opérateur...');
        $this->line('   ✅ Simulation: Orange Money sélectionné');
        $this->line('');

        // 3. Générer un code SMS simulé
        $smsCode = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->info('3️⃣ ENVOI SMS SIMULÉ...');
        $this->line('   📱 Numéro destinataire: ' . $phone);
        $this->line('   📨 Message simulé: "Votre code PayTech: ' . $smsCode . '"');
        $this->line('   ⚠️  Note: En mode test, aucun vrai SMS n\'est envoyé');
        $this->line('');

        $this->info('4️⃣ CODE DE CONFIRMATION GÉNÉRÉ:');
        $this->line('   🔐 Code SMS: ' . $smsCode);
        $this->line('   ⏰ Valide pendant 10 minutes');
        $this->line('');

        // 4. Simuler la confirmation
        $this->info('5️⃣ SIMULATION DE LA CONFIRMATION...');
        $this->line('   ✅ Code ' . $smsCode . ' entré');
        $this->line('   ✅ Paiement confirmé');
        $this->line('   ✅ Transaction réussie');
        $this->line('');

        // Mettre à jour la commande
        $order->update([
            'payment_status' => 'paid'
        ]);

        $this->info('🎉 TRANSACTION SIMULÉE TERMINÉE !');
        $this->line('');
        $this->info('📋 Résumé:');
        $this->line('   • Commande ID: ' . $order->id);
        $this->line('   • Montant: ' . $order->total . ' XOF');
        $this->line('   • Statut: Payé');
        $this->line('   • Méthode: Orange Money');
        $this->line('   • Code SMS: ' . $smsCode);
        $this->line('');

        $this->warn('⚠️  IMPORTANT:');
        $this->line('   • Ceci est une simulation pour les tests');
        $this->line('   • Aucun vrai paiement n\'a été effectué');
        $this->line('   • Aucun vrai SMS n\'a été envoyé');
        $this->line('   • Pour de vrais SMS, configurez le mode production PayTech');
        $this->line('');

        // Nettoyer
        Cart::where('user_id', $user->id)->delete();
        Auth::logout();
    }
}
