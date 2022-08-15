<?php

use Aldemco\Secrets\SecretHasher;
use Aldemco\Secrets\Secrets;
use Aldemco\Secrets\Tests\TestCase;

class CheckerSecretTest extends TestCase
{
    public function testCheckEncryptSecret()
    {
        $secret = Secrets::create()
        ->encrypt(new SecretHasher)
        ->setAttemps(5)
        ->save();

        Secrets::check($secret->secretStr)
        ->setEncrypt(new SecretHasher)
        ->verify();

        $this->assertTrue(true);
    }

    public function testCheckIncorrect()
    {
        $this->expectExceptionMessage('Wrong Secret');

        $secret = Secrets::create()
        ->encrypt(new SecretHasher)
        ->setAttemps(5)
        ->save();

        Secrets::check($secret->secretStr.'incorrect')
        ->setEncrypt(new SecretHasher)
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

    public function testExamplesFull(){
        Secrets::create(
            context:'Register',
            contextId: '+70000000000',
            owner: null,
            ownerId: null )
                ->encrypt(new SecretHasher)
                ->genSecretStr(new Aldemco\Secrets\SecretGenerator)
                ->length(6)
                ->setStoreUntil(Carbon\Carbon::now()->addDay(1))
                ->setValidFrom(Carbon\Carbon::now()->addSecond(1))
                ->setValidUntil(Carbon\Carbon::now()->addMinutes(10))
                ->setAttemps(5)
                ->withInterval(60)
                ->genCustomSecret(function(){
                    return \Str::UUID()->toString();
                })
                ->save();

        $secret = Secrets::check(
            inputSecret: '112544',
            context:'Verify',
            owner: 'User',
            ownerId: 1
        )
        ->setEncrypt(new SecretHasher)
        ->withRemove()
        ->setUnlimitedAttemps()
        ->onErrors(function($execptions){})
        ->onSuccess(function() {})
        ->verify();

        $this->assertTrue(true);
    }

    public function testExamplesMinimal() {
        $secret = Secrets::create()->setAttemps(1)->save();
        Secrets::check(inputSecret: $secret->secretStr)->verify();
        $this->assertTrue(true);
    }
}
