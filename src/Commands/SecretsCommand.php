<?php

namespace Aldemco\Secrets\Commands;

use Aldemco\Secrets\Models\Secret;
use Illuminate\Console\Command;

class SecretsCommand extends Command
{
    public $signature = 'secrets:clear {select} {context?}';

    public $description = 'Clear secrets {select = all used unactive withoutAttemps expired} {context}';

    protected $select = ['all', 'used', 'unactive', 'withoutAttemps', 'expired'];

    protected array $show = ['id', 'context', 'secret', 'success_enter', 'attemps_cnt'];

    public function handle(): int
    {
        $args = $this->arguments();

        $secrets = Secret::when($args['context'], fn ($q) => $q->where('context', $args['context']))
            ->when($args['select'] === 'withoutAttemps', fn ($q) => $q->withoutAttemps())
            ->when($args['select'] === 'used', fn ($q) => $q->used())
            ->when($args['select'] === 'unactive', fn ($q) => $q->unactive())
            ->when($args['select'] === 'expired', fn ($q) => $q->expired())
            ->when($args['select'] === 'all', fn ($q) => $q->where('id', '>', 0))
            ->get();

        $secrets->each(function ($secret) {
            $secret->delete();
        });

        $collection = $secrets->map(function ($secret) {
            return $secret->only($this->show);
        });

        $this->table(
            $this->show,
            $collection
        );

        $this->comment("Clear {$collection->count()} items");

        return self::SUCCESS;
    }
}
