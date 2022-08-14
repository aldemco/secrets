<?php

namespace Aldemco\Secrets\Contracts;

interface SecretGeneratorContract
{
    public function generate(int $len): string;

    public function validate(string $secret): bool;
}
