<?php

namespace App\Support;

# simple screening rules. a suggestion only, staff always confirm
class TriageRules
{
    public static function suggest(array $a): array
    {
        $danger = $a['danger'] ?? [];

        if ($danger) {
            $reasons = array_map(
                fn ($d) => 'Danger sign: '.config("triage.danger.$d", $d),
                $danger
            );

            return ['emergency', $reasons];
        }

        $symptoms = $a['symptoms'] ?? [];
        $has = fn ($key) => in_array($key, $symptoms, true);
        $long = ($a['duration'] ?? '') === '3_plus_days';
        $age = $a['age_group'] ?? '';
        $pregnant = ! empty($a['pregnant']);
        $reasons = [];

        if ($age === 'infant' && $has('fever')) {
            $reasons[] = 'Fever in an infant under 1 year';
        }
        if ($age === 'senior' && $has('fever')) {
            $reasons[] = 'Fever in a senior citizen';
        }
        if ($has('fever') && $long) {
            $reasons[] = 'Fever for 3 days or more';
        }
        if (($has('vomiting') || $has('diarrhea')) && $long) {
            $reasons[] = 'Vomiting or diarrhea for 3 days or more';
        }
        if ($has('fever') && $has('rash')) {
            $reasons[] = 'Fever with skin rash';
        }
        if ($pregnant && ($has('abdominal_pain') || $has('fever') || $has('vomiting'))) {
            $reasons[] = 'Pregnant with stomach pain, fever or vomiting';
        }
        if ($has('injury')) {
            $reasons[] = 'Wound or injury needs checking';
        }

        if ($reasons) {
            return ['urgent', $reasons];
        }

        return ['routine', ['No danger signs and no urgent pattern']];
    }
}
