# Configuration CinetPay

## Variables d'environnement requises

Ajoutez ces variables à votre fichier `.env` :

```env
# Configuration CinetPay
CINETPAY_API_KEY=your_cinetpay_api_key_here
CINETPAY_SITE_ID=your_cinetpay_site_id_here
CINETPAY_BASE_URL=https://api-checkout.cinetpay.com/v2
CINETPAY_NOTIFY_URL=https://yourdomain.com/api/payments/cinetpay/notify
CINETPAY_RETURN_URL=https://yourdomain.com/payment/cinetpay/return
CINETPAY_ENVIRONMENT=sandbox
```

## Obtenir vos identifiants CinetPay

1. Créez un compte sur [CinetPay](https://www.cinetpay.com/)
2. Connectez-vous à votre tableau de bord
3. Récupérez votre `API_KEY` et `SITE_ID`
4. Configurez les URLs de notification et de retour

## URLs de callback

### URL de notification (Webhook)
- **URL** : `https://yourdomain.com/api/payments/cinetpay/notify`
- **Méthode** : POST
- **Description** : CinetPay appelle cette URL pour notifier le statut du paiement

### URL de retour
- **URL** : `https://yourdomain.com/payment/cinetpay/return`
- **Méthode** : GET
- **Description** : URL où l'utilisateur est redirigé après le paiement

## Méthodes de paiement supportées

- **Orange Money** : Mobile money Orange
- **Wave** : Mobile money Wave
- **Visa** : Cartes Visa
- **Mastercard** : Cartes Mastercard

## Flux de paiement

1. L'utilisateur sélectionne une méthode de paiement
2. Une commande est créée avec le statut "unpaid"
3. Un paiement CinetPay est initié
4. L'utilisateur est redirigé vers la page de paiement CinetPay
5. Après paiement, CinetPay redirige vers l'URL de retour
6. Le webhook CinetPay confirme le statut du paiement
7. La commande est mise à jour avec le statut final

## Test en mode sandbox

1. Configurez `CINETPAY_ENVIRONMENT=sandbox`
2. Utilisez les identifiants de test CinetPay
3. Testez avec les numéros de téléphone de test

## Logs et débogage

Les logs CinetPay sont disponibles dans `storage/logs/laravel.log`. Recherchez les entrées avec "CinetPay" pour déboguer les problèmes.

## Problèmes courants

### Erreur "Missing required parameters"
- Vérifiez que toutes les variables d'environnement sont définies
- Assurez-vous que les URLs sont accessibles publiquement

### Erreur "Order not found"
- Vérifiez que la transaction ID correspond à une commande existante
- Vérifiez les logs pour voir les données reçues

### Erreur "Amount mismatch"
- Vérifiez que le montant envoyé correspond au montant de la commande
- Assurez-vous que les décimales sont gérées correctement

## Support

Pour toute question ou problème, consultez :
- [Documentation CinetPay](https://doc.cinetpay.com/)
- Les logs de l'application
- Le support CinetPay

