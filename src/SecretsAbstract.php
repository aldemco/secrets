<?php

namespace Aldemco\Secrets;

use Aldemco\Secrets\Models\Secret;
use Aldemco\Secrets\Contracts\SecretHasherContract;
use Aldemco\Secrets\Exceptions\SecretValidatorException;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

abstract class SecretsAbstract
{

    protected function secretTypeValidator($secret): void
    {
        if (! (bool) (is_string($secret) || is_numeric($secret))) {
            throw new SecretValidatorException('Incorrect secret type');
        }
    }

    protected function secretLenValidator($secret): void
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
        if (Carbon::now() <= $secret->valid_from && $secret->valid_from !== null) {
            throw new SecretValidatorException('The secret is not valid');
        }

        return true;
    }

    protected function isAllowEnter(Secret $secret): bool
    {
        if ($secret->attemps_cnt <= 0) {
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

    protected function isCorrectEncryptSecret(SecretHasherContract $hasher, string|int $hash, string|int $inputSecret): bool
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

    protected static function findAll(
        $context = null,
        $contextId = null,
        $owner = null,
        $ownerId = null,
        $secret = null,
        $limit = 1
    ): Collection {
        $secrets = Secret::when($context, fn ($q) => $q->where('context', $context))
                ->when($contextId, fn ($q) => $q->where('context_id', $contextId))
                ->when($owner, fn ($q) => $q->where('owner_class', $owner))
                ->when($ownerId, fn ($q) => $q->where('owner_id', $ownerId))
                ->when($secret, fn ($q) => $q->where('secret', $secret))
                ->whereNull('success_enter')
                //->where('attemps_cnt', '>', 0)
                // ->where('valid_until', '>=', Carbon::now())
                // ->where('valid_from',  '<=', Carbon::now())
                // ->orWhere('valid_from', null)
                ->limit($limit)
                ->orderBy('created_at', 'desc')
                ->get();

        return $secrets;
    }

    protected static function getContextClass(): string
    {
        $trace = debug_backtrace();
        foreach ($trace as $item) {
            if ($item['class'] !== self::class) {
                return $item['class'];
            }
        }

        return $trace[0]['class'];
    }
}
