<?php

declare(strict_types=1);

namespace Aldemco\Secrets\Traits;

use Aldemco\Secrets\Models\Secret;
use Illuminate\Support\Collection;

trait Helper
{
    protected static function findAll(
        string $context = null,
        string $contextId = null,
        string $owner = null,
        string $ownerId = null,
        string $secret = null,
        $limit = 1
    ): Collection {
        $secrets = Secret::when($context, fn ($q) => $q->where('context', $context))
            ->when($contextId, fn ($q) => $q->where('context_id', $contextId))
            ->when($owner, fn ($q) => $q->where('owner', $owner))
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
