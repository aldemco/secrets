<?php

use Aldemco\Secrets\Contracts\SecretHasherContract;
use Aldemco\Secrets\Models\Secret;
use Aldemco\Secrets\Secrets;
use Aldemco\Secrets\Tests\TestCase;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class CheckerSecretTest extends TestCase
{
    public function testCheckEncryptSecret()
    {
        $secret = Secrets::create()
        ->encrypt(app(SecretHasherContract::class))
        ->setAttemps(5)
        ->save();

        Secrets::check($secret->secretStr)
        ->setEncrypt(app(SecretHasherContract::class))
        ->verify();

        $this->assertTrue(true);
    }

    public function testCheckIncorrect()
    {
        $this->expectExceptionMessage('Wrong Secret');

        $secret = Secrets::create()
        ->encrypt(app(SecretHasherContract::class))
        ->setAttemps(5)
        ->save();

        Secrets::check($secret->secretStr.'incorrect')
        ->setEncrypt(app(SecretHasherContract::class))
        ->verify();
    }

    public function testCheckAttemps()
    {
        $this->expectExceptionMessage('The limit of attempts to enter a secret has been exhausted');

        $secret = Secrets::create()
        ->setAttemps(2)
        ->save();

        try {
            Secrets::check($secret->secretStr.'incorrect')
            ->verify();
        } catch (Exception $e) {
        }

        try {
            Secrets::check($secret->secretStr.'incorrect')
            ->verify();
        } catch (Exception $e) {
        }

        Secrets::check($secret->secretStr)->verify();
    }

    public function testCheckUnlimitedAttemps()
    {
        $secret = Secrets::create()
        ->setAttemps(2)
        ->save();

        try {
            Secrets::check($secret->secretStr.'incorrect')
            ->setUnlimitedAttemps()
            ->allowMultiple()
            ->verify();
        } catch (Exception $e) {
        }

        try {
            Secrets::check($secret->secretStr.'incorrect')
            ->setUnlimitedAttemps()
            ->allowMultiple()
            ->verify();
        } catch (Exception $e) {
        }

        Secrets::check($secret->secretStr)->verify();

        $this->assertTrue(true);
    }

    public function testCheckremoveOnSuccess()
    {
        $secret = Secrets::create(
            context: 'test'
        )
        ->setAttemps(1)
        ->save();

        Secrets::check($secret->secretStr, 'test')->removeOnSuccess()->verify();

        $secret = Secret::where('context', 'test')
        ->first();

        $this->assertNull($secret);
    }

    public function testCheckWithRemoveIncorrect()
    {
        $secret = Secrets::create(
            context: 'test'
        )
        ->setAttemps(1)
        ->save();

        try {
            Secrets::check($secret->secretStr.'Incorrect')->removeOnSuccess()->verify();
        } catch (Exception $e) {
        }

        $secret = Secret::where('context', 'test')
        ->first();

        $this->assertInstanceOf(Secret::class, $secret);
    }

    public function testCheckSetDissalowSuccessTimestamp()
    {
        $secret = Secrets::create()
        ->setAttemps(1)
        ->save();

        try {
            Secrets::check($secret->secretStr)
            ->setUnlimitedAttemps()
            ->allowMultiple()
            ->removeOnSuccess()
            ->verify();
        } catch (Exception $e) {
        }

        $this->assertTrue(true);
    }

    public function testExamplesFull()
    {
        $secretStr = Secrets::create(
            context:'Verify',
            owner: 'User',
            ownerId: 1)
                ->length(6)
                ->setStoreUntil(Carbon\Carbon::now()->addDay())
                ->setValidFrom(Carbon\Carbon::now())
                ->setValidUntil(Carbon\Carbon::now()->addMinutes(10))
                ->setAttemps(5)
                ->withInterval(60)
                ->genSecretStr(new Aldemco\Secrets\SecretGenerator)
                ->genCustomSecret(function () {
                    return Str::UUID()->toString();
                })
                ->encrypt(new Aldemco\Secrets\SecretHasher)
                ->save()->secretStr;

        $secret = Secrets::check(
            inputSecret: $secretStr,
            context:'Verify',
            owner: 'User',
            ownerId: 1
        )
        ->setEncrypt(new Aldemco\Secrets\SecretHasher)
        ->removeOnSuccess()
        ->setUnlimitedAttemps()
        ->onSuccess(function () {
        })
        ->verify();

        $this->assertTrue(true);
    }

    public function testExamplesMinimal()
    {
        $secret = Secrets::create()->setAttemps(1)->save();
        Secrets::check(inputSecret: $secret->secretStr)->verify();
        $this->assertTrue(true);
    }

    public function testMultipleAttemps()
    {
        $this->expectExceptionMessage('The limit of attempts to enter a secret has been exhausted');

        $secrets = [];
        for ($i = 0; $i < 2; $i++) {
            $secrets[] = Secrets::create()->setAttemps(1)->save()->secretStr;
        }

        foreach (Arr::shuffle($secrets) as $secret) {
            try {
                Secrets::check(
                    inputSecret: $secret.'err',
                )
                ->allowMultiple()
                ->verify();
            } catch (Exception $e) {
            }

            $res = Secrets::check(
                inputSecret: $secret,
            )
            ->allowMultiple()
            ->verify()
            ->getResult();
        }

        $this->assertTrue(true);
    }

    public function testMultipleremoveOnSuccess()
    {
        $secrets = [];
        for ($i = 0; $i < 10; $i++) {
            $secrets[] = Secrets::create()->setAttemps(3)->save()->secretStr;
        }

        foreach (array_slice(Arr::shuffle($secrets), 0, 5) as $secret) {
            $res = Secrets::check(
                inputSecret: $secret,
            )
            ->allowMultiple()
            ->removeOnSuccess()
            ->verify()
            ->getResult();
        }

        $this->assertCount(5, Secret::all(), );
    }

    public function testCheckMultiple()
    {
        $secrets = [];
        for ($i = 0; $i < 10; $i++) {
            $secrets[] = Secrets::create(
                context:'Verify',
                owner: 'User',
                ownerId: 1)->setAttemps(1)->save()->secretStr;
        }

        foreach (array_slice(Arr::shuffle($secrets), 0, 5) as $secret) {
            $res = Secrets::check(
                inputSecret: $secret,
                context:'Verify',
                owner: 'User',
                ownerId: 1
            )
            ->allowMultiple()
            ->verify()
            ->getResult();
        }

        $this->assertTrue(true);
    }
}
