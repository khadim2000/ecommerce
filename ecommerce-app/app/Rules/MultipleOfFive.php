<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MultipleOfFive implements ValidationRule
{
    /**
     * Run the validation rule.
     * Vérifie que la valeur est un multiple de 5 (requis pour CinetPay)
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Convertir en entier si c'est une chaîne numérique
        $numericValue = is_numeric($value) ? (int) $value : $value;
        
        // Vérifier que c'est un nombre et qu'il est un multiple de 5
        if (!is_numeric($value) || $numericValue % 5 !== 0) {
            $fail('Le :attribute doit être un multiple de 5 pour utiliser CinetPay.');
        }
    }
}
