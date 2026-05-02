<?php

return [
    'authenticator' => [
        'issuer' => env('TWO_FACTOR_ISSUER', env('APP_NAME', 'DentalCare')),
        'digits' => (int) env('TWO_FACTOR_DIGITS', 6),
        'period' => (int) env('TWO_FACTOR_PERIOD', 30),
        'window' => (int) env('TWO_FACTOR_WINDOW', 1),
        'challenge_expires_minutes' => (int) env('TWO_FACTOR_CHALLENGE_EXPIRES_MINUTES', 10),
        'max_attempts' => (int) env('TWO_FACTOR_MAX_ATTEMPTS', 5),
        'recovery_codes' => (int) env('TWO_FACTOR_RECOVERY_CODES', 8),
    ],
];
