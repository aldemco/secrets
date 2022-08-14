<?php

namespace Aldemco\Secrets;

use Aldemco\Secrets\Contracts\SecretGeneratorContract;

class SecretGenerator implements SecretGeneratorContract
{
    public function generate(int $len = 6): string
    {
        $keyspace = '0123456789';
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $len; $i++) {
            $pieces[] = $keyspace[random_int(0, $max)];
        }

        return implode('', $pieces);
    }

    public function validate(string $secret): bool
    {
        return true;
    }
}
