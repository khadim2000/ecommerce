# Intégration CinetPay - Documentation

## Résumé des modifications

Cette documentation décrit les modifications apportées à l'intégration CinetPay pour respecter exactement les paramètres de l'API officielle.

## Paramètres obligatoires selon la documentation CinetPay

| Paramètre | Type | Obligatoire | Description |
|-----------|------|-------------|-------------|
| `apikey` | String | Oui | Votre apikey fournie par CinetPay |
| `site_id` | String | Oui | Votre site_id fourni par CinetPay |
| `transaction_id` | String | Oui | Identification unique de la transaction |
| `amount` | Integer | Oui | Montant de la transaction (multiple de 5) |
| `currency` | String | Oui | Devise (XOF, XAF, CDF, GNF, USD) |
| `description` | String | Oui | Description sans caractères spéciaux |
| `notify_url` | Url | Oui | Lien de notification du paiement |
| `return_url` | Url | Oui | Lien de retour après paiement |
| `channels` | String | Oui | Canaux de paiement (ALL, MOBILE_MONEY, CREDIT_CARD, WALLET) |

## Modifications apportées

### 1. CinetPayService.php

- **Validation du montant** : Vérification que le montant est un multiple de 5
- **Nettoyage de la description** : Suppression des caractères spéciaux (#,/,$,_,&)
- **Paramètres simplifiés** : Utilisation uniquement des paramètres obligatoires
- **Commentaires explicatifs** : Documentation des paramètres obligatoires

### 2. PaymentController.php

- **Validation des devises** : Support des devises XOF, XAF, CDF, GNF, USD
- **Validation personnalisée** : Utilisation des règles CleanDescription
- **Channels mis à jour** : Utilisation des valeurs correctes (MOBILE_MONEY, CREDIT_CARD)
- **Gestion des descriptions** : Support des descriptions personnalisées

### 3. Nouvelles règles de validation

#### MultipleOfFive.php
```php
// Vérifie que le montant est un multiple de 5
$numericValue = is_numeric($value) ? (int) $value : $value;
if (!is_numeric($value) || $numericValue % 5 !== 0) {
    $fail('Le :attribute doit être un multiple de 5 pour utiliser CinetPay.');
}
```

#### CleanDescription.php
```php
// Supprime les caractères spéciaux problématiques
$problematicChars = ['#', '/', '$', '_', '&'];
foreach ($problematicChars as $char) {
    if (strpos($value, $char) !== false) {
        $foundChars[] = $char;
    }
}
```

### 4. OrderController.php

- **Validation des montants** : Vérification lors de la création des commandes
- **Gestion des paiements en ligne** : Validation pour les méthodes CinetPay uniquement

## Mapping des canaux de paiement

| Méthode de paiement | Canal CinetPay |
|-------------------|----------------|
| orange_money | MOBILE_MONEY |
| wave | MOBILE_MONEY |
| visa | CREDIT_CARD |
| mastercard | CREDIT_CARD |
| Autres | ALL |

## Configuration requise

Assurez-vous que les variables d'environnement suivantes sont configurées :

```env
CINETPAY_API_KEY=your_api_key_here
CINETPAY_SITE_ID=your_site_id_here
CINETPAY_BASE_URL=https://api-checkout.cinetpay.com/v2
CINETPAY_NOTIFY_URL=https://your-domain.com/api/payments/cinetpay/notify
CINETPAY_RETURN_URL=https://your-domain.com/payments/cinetpay/return
CINETPAY_ENVIRONMENT=sandbox
```

## Tests

Des tests d'intégration ont été créés pour vérifier :

- ✅ Validation des montants multiples de 5
- ✅ Nettoyage des descriptions
- ✅ Mapping correct des canaux
- ✅ Envoi des bons paramètres à l'API CinetPay
- ✅ Gestion des paiements à la livraison pour les montants non multiples de 5

## Exemple d'utilisation

```php
// Initier un paiement CinetPay
$response = $this->postJson('/api/payments/cinetpay/initiate', [
    'order_id' => 1,
    'payment_method' => 'orange_money',
    'currency' => 'XOF',
    'description' => 'Commande 123 - E-commerce'
]);
```

## Notes importantes

1. **Montants** : Tous les montants doivent être des multiples de 5 pour les paiements en ligne
2. **Descriptions** : Éviter les caractères spéciaux (#,/,$,_,&)
3. **Devises** : Seules XOF, XAF, CDF, GNF, USD sont supportées
4. **Canaux** : Utiliser MOBILE_MONEY pour Orange Money et Wave, CREDIT_CARD pour Visa et Mastercard