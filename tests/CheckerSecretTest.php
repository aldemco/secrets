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
            ->setEncrypt(new SecretHasher)
            ->verify();
        } catch (Exception $e) {
        }

        try {
            Secrets::check($secret->secretStr.'incorrect')
            ->setEncrypt(new SecretHasher)
            ->verify();
        } catch (Exception $e) {
        }

        Secrets::check($secret->secretStr.'incorrect')
        ->setEncrypt(new SecretHasher)
        ->verify();
    }
}
