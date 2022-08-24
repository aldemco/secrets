# Secrets

[![Latest Version on Packagist](https://img.shields.io/packagist/v/aldemco/laravel-query-builder.svg?style=flat-square)](https://packagist.org/packages/aldemco/secrets)
[![Licence MIT](https://img.shields.io/github/license/aldemco/secrets)](https://github.com/aldemco/secrets/blob/main/LICENSE.md)
[![Test Status](https://img.shields.io/github/workflow/status/aldemco/secrets/run-tests?label=tests)](https://github.com/aldemco/secrets/actions/workflows/run-tests.yml)

This package allows you to generate, store and verify secrets for many purposes, such as verifying a phone number during registration or authorization via SMS.

## Installation
You can install the package via composer:
```bash
composer require aldemco/secrets
```
You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="secrets-migrations"
php artisan migrate
```

if you want to change the default table name, then publish the config first

You can publish the config file with:
```bash
php artisan vendor:publish --tag="secrets-config"
```
This is the contents of the published config file:
```php
return [
    'table' => 'secrets',
    'auto_clearing' => false,
    'auto_clearing_dayly_at' => '01:00',
    'length' => 6,
    'secret_generator' => Aldemco\Secrets\SecretGenerator::class,
    'secret_hasher' => Aldemco\Secrets\SecretHasher::class,
    'is_crypt' => false,
    'attemps' => 3,
    'store_until_minutes' => 50000,
    'valid_until_minutes' => 10,
    'valid_from_minutes' => 0,
    'multiple_limit' => 10,
];
```

## Basic usage

## Create secret
```php
use Aldemco\Secrets\Secrets;

/**
 * Full
 */
    Secrets::create(
        context:'Verify',
        contextId: null,
        owner: 'User',
        ownerId: 1 )
            ->length(6)
            ->setStoreUntil(Carbon\Carbon::now()->addDay(1))
            ->setValidUntil(Carbon\Carbon::now()->addMinutes(10))
            ->setAttemps(5)
            ->withInterval(60)
            ->genSecretStr(new Aldemco\Secrets\SecretGenerator)
            ->genCustomSecret(function(){
                return \Str::UUID()->toString();
            })
            ->encrypt(new Aldemco\Secrets\SecretHasher)
            ->save();

/**
 * Minimal
 */
Secrets::create()->save();

```

## Verify secret
```php
use Aldemco\Secrets\Secrets;

/**
 * Full
 */
Secrets::check(
    inputSecret: '112544',
    context:'Verify',
    owner: 'User',
    ownerId: 1)
        ->setEncrypt(new Aldemco\Secrets\SecretHasher)
        ->setUnlimitedAttemps()
        ->allowMultiple()
        ->setDissalowSuccessTimestamp()
        ->setUnlimitedAttemps()
        ->removeOnSuccess()
        ->verify()
        ->getResult();

/**
 * Minimal
 */

Secrets::check(inputSecret: '112544')->verify();

```

## Commands
select: `all` `used` `unactive` `withoutAttemps` `expired`

```bash
php artisan secrets:clear {select} {context?}
```

## Installation
You can install the package via composer:
```
composer require aldemco/secrets
```

## Testing
```
composer test
```

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
