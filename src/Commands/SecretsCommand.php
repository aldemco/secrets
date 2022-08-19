<?php

namespace Aldemco\Secrets\Commands;

use Aldemco\Secrets\Models\Secret;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SecretsCommand extends Command
{
    public $signature = 'secrets:clear {select} {context?}';

    public $description = 'Clear secrets {select = all used unactive withoutAttemps expired} {context}';

    protected $select = ['all', 'used', 'unactive', 'withoutAttemps', 'expired'];

    public function handle(): int
    {
        $args = $this->arguments();

        $collection = Secret::when($args['context'], fn ($q) => $q->where('context', $args['context']))
            ->when($args['select'] === 'withoutAttemps', fn ($q) => $q->where('attemps_cnt', '<', 1))
            ->when($args['select'] === 'used', fn ($q) => $q->whereNotNull('success_enter'))
            ->when($args['select'] === 'unactive', fn ($q) => $q->where('valid_until', '<=', Carbon::now())->where('valid_from', '>=', Carbon::now()))
            ->when($args['select'] === 'expired', fn ($q) => $q->where('store_until', '>=', Carbon::now()))
            ->when($args['select'] === 'all', fn ($q) => $q->where('id', '>', 0))
            ->get();

        $this->table(
            ['id', 'context', 'secret'],
            $collection
        );

        $collection->each(function ($secret) {
            $secret->delete();
        });

        $this->comment("Clear {$collection->count()} items");

        return self::SUCCESS;
    }
}
