<?php

namespace Aldemco\Secrets\Commands;

use Illuminate\Console\Command;

class SecretsCommand extends Command
{
    public $signature = 'secrets:clear {used?} {expired?} {unactive?} {withoutAttemps?} {all?}';

    public $description = 'Clear secrets';

    public function handle(): int
    {
        $this->arguments();

        $this->comment('All done');

        return self::SUCCESS;
    }
}
