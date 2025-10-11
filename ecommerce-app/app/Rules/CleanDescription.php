<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CleanDescription implements ValidationRule
{
    /**
     * Run the validation rule.
     * Vérifie que la description ne contient pas de caractères spéciaux problématiques pour CinetPay
     * Évite #,/,$,_,& selon la documentation CinetPay
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) {
            $fail('Le :attribute doit être une chaîne de caractères.');
            return;
        }
        
        // Vérifier la présence de caractères spéciaux problématiques
        $problematicChars = ['#', '/', '$', '_', '&'];
        $foundChars = [];
        
        foreach ($problematicChars as $char) {
            if (strpos($value, $char) !== false) {
                $foundChars[] = $char;
            }
        }
        
        if (!empty($foundChars)) {
            $charsList = implode(', ', $foundChars);
            $fail("Le :attribute ne doit pas contenir les caractères spéciaux suivants : {$charsList}");
        }
        
        // Vérifier la longueur
        if (strlen($value) > 255) {
            $fail('Le :attribute ne doit pas dépasser 255 caractères.');
        }
    }
}
