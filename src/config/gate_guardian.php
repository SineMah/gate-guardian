<?php

use GateGuardian\Creator\GuardType;

return [
    'key_identifier' => [
        'azp',
        'urn:zitadel:iam:org:project:roles'
    ],
    'guards' => [
        'zitadel',
        'default'
    ],
    'leeway' => (int) env('GATE_GUARDIAN_LEEWAY', 0),
    'factory' => GuardType::class,
    'jwk_uri' => env('GATE_GUARDIAN_URL_JWK'),
    'validate_jwt_url' => env('GATE_GUARDIAN_URL_VALIDATE_JWT'),
    'cache_ttl' => (int) env('GATE_GUARDIAN_CACHE_TTL', 300),
];
