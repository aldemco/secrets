# Secrets
## 
[![Latest Version on Packagist](https://img.shields.io/packagist/v/aldemco/laravel-query-builder.svg?style=flat-square)](https://packagist.org/packages/aldemco/secrets)
[![Licence MIT](https://img.shields.io/github/license/aldemco/secrets)](https://github.com/aldemco/secrets/blob/main/LICENSE.md)
[![Test Status](https://img.shields.io/github/workflow/status/aldemco/secrets/run-tests?label=tests)](https://github.com/aldemco/secrets/actions/workflows/run-tests.yml)

This package allows you to generate, store and verify secrets for many purposes, such as verifying a phone number during registration or authorization via SMS.

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
        ->withRemove()
        ->setEncrypt(new SecretHasher)
        ->setUnlimitedAttemps()
        ->onSuccess(function() {})
        ->verify();

/**
 * Minimal
 */

Secrets::check(inputSecret: '112544')->verify();


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