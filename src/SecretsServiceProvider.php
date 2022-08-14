<?php

namespace Aldemco\Secrets;

use Aldemco\Secrets\Commands\SecretsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SecretsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('secrets')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_secrets_table')
            ->hasCommand(SecretsCommand::class);
    }

    public function boot()
    {
        parent::boot();
    }
}
