<?php

namespace Aldemco\Secrets\Contracts;

interface SecretHasherContract
{
    public function encrypt(string $secret): string;

    public function check(string $secret, $hash): bool;
}
