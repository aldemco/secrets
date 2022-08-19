<?php

declare(strict_types=1);

namespace Aldemco\Secrets\Traits;

use Aldemco\Secrets\Contracts\SecretHasherContract;
use Aldemco\Secrets\Exceptions\SecretValidatorException;
use Aldemco\Secrets\Models\Secret;
use Carbon\Carbon;

trait Validator
{
    protected function secretTypeValidator($secretStr): void
    {
        if (! (bool) (is_string($secretStr))) {
            throw new SecretValidatorException('Incorrect secret type');
        }
    }

    protected function secretLenValidator(string $secret): void
    {
        if (strlen($secret) > 255) {
            throw new SecretValidatorException('Incorrect secret length');
        }
    }

    protected function isValidUntil(Secret $secret): bool
    {
        if (Carbon::now() > $secret->valid_until && $secret->valid_until !== null) {
            throw new SecretValidatorException('Secret expired');
        }

        return true;
    }

    protected function isValidFrom(Secret $secret): bool
    {
        if (Carbon::now() < $secret->valid_from && $secret->valid_from !== null) {
            throw new SecretValidatorException('The secret is not valid');
        }

        return true;
    }

    protected function isAllowEnter(Secret $secret): bool
    {
        if ($secret->attemps_cnt < 1) {
            throw new SecretValidatorException('The limit of attempts to enter a secret has been exhausted');
        }

        return true;
    }

    protected function isNotUsed(Secret $secret): bool
    {
        if ($secret->success_enter !== null) {
            throw new SecretValidatorException('Secret already used');
        }

        return true;
    }

    protected function isCorrectSecret(string $secret, string $inputSecret): bool
    {
        if ($secret !== $inputSecret) {
            throw new SecretValidatorException('Wrong Secret');
        }

        return true;
    }

    protected function isCorrectEncryptSecret(SecretHasherContract $hasher, string $hash, string $inputSecret): bool
    {
        if ($hasher->check($inputSecret, $hash) === false) {
            throw new SecretValidatorException('Wrong Secret');
        }

        return true;
    }

    protected function checkInterval(Secret $lastSecret, int $seconds): void
    {
        if ($lastSecret->created_at->addSeconds($seconds) >= Carbon::now()) {
            throw new SecretValidatorException('Too frequent requests');
        }
    }
}
