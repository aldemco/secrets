<?php

namespace Aldemco\Secrets;

use Aldemco\Secrets\Contracts\SecretHasherContract;
use Illuminate\Support\Facades\Hash;

class SecretHasher implements SecretHasherContract
{
    private static $count = 0;

    public function encrypt($secret): string
    {
        self::$count++;
        return Hash::make($secret);
    }

    public function check($secret, $hash): bool
    {
        return Hash::check($secret, $hash);
    }
}
