<?php

use Aldemco\Secrets\Models\Secret;
use Aldemco\Secrets\Secrets;
use Aldemco\Secrets\Tests\TestCase;
use Carbon\Carbon;

class ConsoleTest extends TestCase
{
    public function testClearSecretsAll()
    {
        for ($i = 0; $i < 10; $i++) {
            Secrets::create('Register')->setAttemps(1)->save();
        }

        $this->artisan('secrets:clear all Register');

        $secret = Secret::find(1);

        $this->assertNull($secret);
    }

    public function testClearSecretsUnactive()
    {
        for ($i = 0; $i < 10; $i++) {
            Secrets::create('Register')->setAttemps(1)
                ->setValidFrom(Carbon::now()->addMinute())
                ->setValidUntil(Carbon::now()->subMinute())
                ->save();
        }

        $this->artisan('secrets:clear unactive Register');

        $secret = Secret::find(1);

        $this->assertNull($secret);
    }

    public function testClearSecretsExpired()
    {
        for ($i = 0; $i < 10; $i++) {
            Secrets::create('Register')->setAttemps(1)
                ->setStoreUntil(Carbon::now()->addMinute())
                ->save();
        }

        $this->artisan('secrets:clear expired Register');

        $secret = Secret::find(1);

        $this->assertNull($secret);
    }

    public function testClearWithoutAttemps()
    {
        for ($i = 0; $i < 10; $i++) {
            Secrets::create('Register')->setAttemps(1)
                ->setAttemps(0)
                ->save();
        }

        $this->artisan('secrets:clear witoutAttemps Register');

        $secret = Secret::find(1);

        $this->assertNull($secret);
    }

    public function testClearUsed()
    {
        $secrets = [];
        for ($i = 0; $i < 10; $i++) {
            $secrets[] = Secrets::create(
                context:'Verify',
                owner: 'User',
                ownerId: 1)->setAttemps(1)->save()->secretStr;
        }

        foreach ($secrets as $secret) {
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

        $this->artisan('secrets:clear used Verify');

        $secret = Secret::find(1);

        $this->assertNull($secret);
    }
}
