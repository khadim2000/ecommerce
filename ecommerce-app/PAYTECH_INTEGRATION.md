# Intégration PayTech - Documentation

## Résumé des modifications

Cette documentation décrit la migration de CinetPay vers PayTech pour le système de paiement de l'application e-commerce.

## Changements apportés

### 1. **Nouveau service PayTech**
- ✅ `PayTechService.php` - Service personnalisé pour l'API PayTech
- ✅ Gestion complète des paiements avec PayTech
- ✅ Support des méthodes : Orange Money, Wave, Visa, Mastercard
- ✅ Gestion des webhooks et vérifications

### 2. **Configuration mise à jour**
- ✅ Ajout de la configuration PayTech dans `config/services.php`
- ✅ Variables d'environnement pour PayTech

### 3. **Contrôleurs mis à jour**
- ✅ `PaymentController` - Utilise maintenant PayTechService
- ✅ `OrderController` - Suppression de la validation des multiples de 5
- ✅ Gestion des transactions PayTech

## Configuration requise

Ajoutez ces variables dans votre fichier `.env` :

```env
# Configuration PayTech
PAYTECH_API_KEY=your_api_key_here
PAYTECH_SECRET_KEY=your_secret_key_here
PAYTECH_BASE_URL=https://paytech.sn/api
PAYTECH_NOTIFY_URL=https://your-domain.com/payment/notify
PAYTECH_RETURN_URL=https://your-domain.com/payment/verify
PAYTECH_ENVIRONMENT=sandbox
```

## Avantages de PayTech vs CinetPay

### PayTech
- ✅ Pas de restriction sur les multiples de 5
- ✅ API plus simple et flexible
- ✅ Meilleure documentation
- ✅ Support des devises XOF, XAF, CDF, GNF, USD

### CinetPay (ancien)
- ❌ Restriction sur les multiples de 5
- ❌ API plus complexe
- ❌ Documentation moins claire

## Utilisation

### Pour payer une commande avec PayTech :

```php
// Formulaire HTML
<form method="POST" action="{{ route('orders.pay', $order->id) }}">
    @csrf
    <input type="hidden" name="currency" value="XOF">
    <input type="hidden" name="payment_method" value="orange_money">
    <input type="hidden" name="description" value="Paiement commande personnalisé">
    <button type="submit">Payer avec PayTech</button>
</form>
```

### Exemple d'utilisation en JavaScript :

```javascript
// Payer une commande
fetch(`/orders/${orderId}/pay`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        currency: 'XOF',
        payment_method: 'orange_money',
        description: 'Paiement commande personnalisé'
    })
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        window.location.href = data.payment_url;
    }
});
```

## Flux de paiement PayTech

1. **Créer une commande** via `OrderController::store()`
2. **Payer la commande** via `PaymentController::payOrder()`
3. **Redirection vers PayTech** pour le paiement
4. **Webhook PayTech** met à jour le statut
5. **Retour utilisateur** vers la page de commande

## Méthodes de paiement supportées

| Méthode | Code PayTech |
|---------|--------------|
| Orange Money | ORANGE_MONEY |
| Wave | WAVE |
| Visa | VISA |
| Mastercard | MASTERCARD |

## Routes disponibles

- `POST /orders/{orderId}/pay` - Payer une commande
- `GET /orders/{id}` - Voir une commande
- `POST /payment/notify/{transaction_id}` - Webhook PayTech
- `GET /payment/verify/{transaction_id}` - Vérification de paiement

## Gestion des erreurs

Le service PayTech inclut une gestion d'erreurs complète :
- Validation des montants
- Nettoyage des descriptions
- Gestion des exceptions
- Logging détaillé

## Migration depuis CinetPay

Les changements sont transparents pour l'utilisateur final :
- Même interface utilisateur
- Mêmes routes
- Même logique métier
- Amélioration des performances

## Tests

Pour tester l'intégration PayTech :

1. Configurez les variables d'environnement
2. Créez une commande
3. Initiez un paiement
4. Vérifiez les logs pour les appels API
5. Testez les webhooks

## Support

En cas de problème avec PayTech :
- Vérifiez les logs Laravel
- Vérifiez la configuration des variables d'environnement
- Consultez la documentation PayTech officielle
- Contactez le support PayTech si nécessaire



