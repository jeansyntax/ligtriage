<?php

return [
    # one-time setup link for hosts without a terminal. leave empty to switch it off
    'setup_key' => env('SETUP_KEY', ''),

    # password given to the seeded staff accounts
    'seed_password' => env('SEED_PASSWORD', 'password'),

    # waiting tickets from the QR flow are cancelled after this many hours
    'expire_hours' => 4,

    # used for wait estimates until enough patients are done today
    'default_handling_minutes' => 8,

    'age_groups' => [
        'infant' => 'Under 1 year',
        'child' => '1 to 12 years',
        'adult' => '13 to 59 years',
        'senior' => '60 years and above',
    ],

    'symptoms' => [
        'fever' => 'Fever',
        'cough' => 'Cough',
        'colds' => 'Colds',
        'sore_throat' => 'Sore throat',
        'headache' => 'Headache',
        'body_pain' => 'Body pain',
        'vomiting' => 'Vomiting',
        'diarrhea' => 'Diarrhea',
        'rash' => 'Skin rash',
        'abdominal_pain' => 'Stomach pain',
        'injury' => 'Wound or injury',
        'eye_ear' => 'Eye or ear problem',
        'other' => 'Something else',
    ],

    'durations' => [
        'today' => 'Started today',
        '1_2_days' => '1 to 2 days',
        '3_plus_days' => '3 days or more',
    ],

    'danger' => [
        'breathing' => 'Difficulty breathing',
        'chest_pain' => 'Chest pain',
        'seizure' => 'Seizure or convulsions',
        'unresponsive' => 'Fainting or cannot be woken up',
        'bleeding' => 'Severe bleeding',
        'cannot_drink' => 'Vomiting everything, cannot keep fluids down',
        'dehydration' => 'Very weak, sunken eyes, or no urine today',
    ],
];
