<?php

// config for Aldemco/Secrets
return [
    'table' => 'secrets',
    'auto_clearing' => false,
    'auto_clearing_dayly_at' => '01:00',
    'length' => 6,
    'secret_generator' => Aldemco\Secrets\SecretGenerator::class,
    'secret_hasher' => Aldemco\Secrets\SecretHasher::class,
    'is_crypt' => false,
    'attemps' => 3,
    'store_until_minutes' => 50000,
    'valid_until_minutes' => 10,
    'valid_from_minutes' => 0,
    'multiple_limit' => 10,
];
