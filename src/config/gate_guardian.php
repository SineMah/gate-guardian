<?php

use GateGuardian\Creator\GuardType;

return [
    'key_identifier' => [
        'azp',
        'resource_access'
    ],
    'guards' => [
        'zitadel',
        'default'
    ],
    'factory' => GuardType::class,
];
