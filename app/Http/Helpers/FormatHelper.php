<?php

if (!function_exists('formatFcfa')) {
    /**
     * Formate un montant en FCFA (ex. 1234567 → "1 234 567 fcfa")
     *
     * @param float|int|null $number Montant à formater (null/0 → "0 fcfa")
     * @return string Montant formaté
     */
    function formatFcfa($number) {
        if ($number === null || $number === '') {
            return '0 fcfa';
        }
        $value = (float) $number;
        $formatted = number_format($value, 0, ',', ' ');  // Espace pour milliers (français), 0 décimale
        return $formatted . ' fcfa';
    }
}
