<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PayDunya - Simulation de Paiement</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .paydunya-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .paydunya-header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        .payment-option {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .payment-option:hover {
            border-color: #ff6b6b;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .payment-option.selected {
            border-color: #ff6b6b;
            background-color: #fff5f5;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="paydunya-card overflow-hidden">
            <!-- Header -->
            <div class="paydunya-header p-6 text-center">
                <h1 class="text-2xl font-bold mb-2">💰 PayDunya</h1>
                <p class="text-lg opacity-90">Simulation de Paiement</p>
                <p class="text-sm opacity-75 mt-2">Mode Test - Token: {{ substr($token, 0, 15) }}...</p>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="text-center mb-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-2">Sélectionnez votre méthode de paiement</h2>
                    <p class="text-gray-600">Montant: <span class="font-bold text-lg text-green-600" id="amount">123 XOF</span></p>
                </div>

                <!-- Payment Methods -->
                <div class="space-y-3 mb-6">
                    <div class="payment-option p-4" data-method="orange">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-orange-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                O
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Orange Money</h3>
                                <p class="text-sm text-gray-600">Paiement mobile</p>
                            </div>
                        </div>
                    </div>

                    <div class="payment-option p-4" data-method="mtn">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                M
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">MTN Money</h3>
                                <p class="text-sm text-gray-600">Paiement mobile</p>
                            </div>
                        </div>
                    </div>

                    <div class="payment-option p-4" data-method="moov">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                M
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">Moov Money</h3>
                                <p class="text-sm text-gray-600">Paiement mobile</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="mb-6">
                    <h3 class="font-semibold text-gray-800 mb-3">Informations Client</h3>
                    <div class="space-y-3">
                        <input type="text" placeholder="Nom complet" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500" id="customerName">
                        <input type="tel" placeholder="Numéro de téléphone" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500" id="customerPhone">
                        <input type="email" placeholder="Email" class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-red-500" id="customerEmail">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex space-x-3">
                    <button onclick="simulatePayment()" class="flex-1 bg-green-500 hover:bg-green-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                        ✅ Payer Maintenant
                    </button>
                    <button onclick="cancelPayment()" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-lg transition duration-200">
                        ❌ Annuler
                    </button>
                </div>

                <div class="mt-4 text-center">
                    <p class="text-xs text-gray-500">Cette page simule PayDunya pour les tests</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let selectedMethod = null;

        // Sélection de la méthode de paiement
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.payment-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                selectedMethod = this.dataset.method;
            });
        });

        function simulatePayment() {
            if (!selectedMethod) {
                alert('Veuillez sélectionner une méthode de paiement');
                return;
            }

            const customerName = document.getElementById('customerName').value || 'Client Test';
            const customerPhone = document.getElementById('customerPhone').value || '+221771234567';
            const customerEmail = document.getElementById('customerEmail').value || 'client@test.com';

            // Simuler un délai de traitement
            document.querySelector('button[onclick="simulatePayment()"]').innerHTML = '⏳ Traitement...';
            document.querySelector('button[onclick="simulatePayment()"]').disabled = true;

            setTimeout(() => {
                // Simuler un succès de paiement (80% de chance)
                const isSuccess = Math.random() > 0.2;
                
                if (isSuccess) {
                    // Rediriger vers la page de succès
                    window.location.href = `/api/payments/paydunya/success/{{ $token }}`;
                } else {
                    // Rediriger vers la page d'annulation
                    window.location.href = `/api/payments/paydunya/cancel/{{ $token }}`;
                }
            }, 2000);
        }

        function cancelPayment() {
            if (confirm('Êtes-vous sûr de vouloir annuler ce paiement ?')) {
                window.location.href = `/api/payments/paydunya/cancel/{{ $token }}`;
            }
        }

        // Auto-sélectionner Orange Money
        document.querySelector('.payment-option[data-method="orange"]').click();
    </script>
</body>
</html>

