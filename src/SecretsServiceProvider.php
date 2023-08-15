<?php

namespace Aldemco\Secrets;

use Aldemco\Secrets\Commands\SecretsCommand;
use Aldemco\Secrets\Contracts\SecretGeneratorContract;
use Aldemco\Secrets\Contracts\SecretHasherContract;
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
            ->hasMigration('create_secrets_table')
            ->hasCommand(SecretsCommand::class);
    }

    public function boot()
    {
        parent::boot();
        $this->app->booted(function () {
            if (config('secrets.auto_clearing', false)) {
                $schedule = app(Schedule::class);
                $schedule->command('secrets:clear expired')->dailyAt(config('secrets.auto_clearing_dayly_at', '01:00'));
            }
        });
    }

    public function packageBooted()
    {
        $this->app->singleton(SecretHasherContract::class, function ($app) {
            $hasherClass = config('secrets.secret_hasher', SecretHasher::class);

            return new $hasherClass;
        });

        $this->app->singleton(SecretGeneratorContract::class, function ($app) {
            $generatorClass = config('secrets.secret_generator', SecretGenerator::class);

            return new $generatorClass;
        });
    }
}
