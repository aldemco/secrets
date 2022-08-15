<?php

use Aldemco\Secrets\Models\Secret;
use Aldemco\Secrets\SecretGenerator;
use Aldemco\Secrets\SecretHasher;
use Aldemco\Secrets\Secrets;
use Aldemco\Secrets\Tests\TestCase;
use Carbon\Carbon;

class CreateSecretTest extends TestCase
{
    public function testCreateEncryptSecret()
    {
        $secret = Secrets::create()
        ->encrypt(new SecretHasher)
        ->setAttemps(5)
        ->save();

        $secret = Secret::find(1);
        $this->assertInstanceOf(Secret::class, $secret);
    }

    public function testCreateSecretWintAttemps()
    {
        $secret = Secrets::create()
        ->setAttemps(4)
        ->save();

        $secret = Secret::where('secret', $secret->secretStr)
            ->where('attemps_cnt', 4)
            ->first();

        $this->assertInstanceOf(Secret::class, $secret);
    }

    /**
     * @expectedExceptionMessage Too frequent requests
     */
    public function testCreateSecretWithInterval()
    {
        $this->expectExceptionMessage('Too frequent requests');

        $secret = Secrets::create()
        ->withInterval(1)
        ->save();

        $secret = Secrets::create()
        ->withInterval(1)
        ->save();
    }

    public function testCreateSecretSetters()
    {
        $setStoreUntil = Carbon::now()->addMinutes(60);
        $setValidFrom = Carbon::now()->addMinutes(1);
        $setValidUntil = Carbon::now()->addMinutes(10);

        $secret = Secrets::create()
        ->setStoreUntil($setStoreUntil)
        ->setValidFrom($setValidFrom)
        ->setValidUntil($setValidUntil)
        ->save();

        $secret = Secret::where('store_until', $setStoreUntil)
            ->where('valid_from', $setValidFrom)
            ->where('valid_until', $setValidUntil)
            ->first();

        $this->assertInstanceOf(Secret::class, $secret);
    }

    public function testCreateSecretCustom()
    {
        $custom = 'PromoCode123';

        $secret = Secrets::create()
        ->genCustomSecret(function () use ($custom) {
            return $custom;
        })
        ->save();

        $secret = Secret::where('secret', $custom)
            ->first();

        $this->assertInstanceOf(Secret::class, $secret);
    }

    public function testCreateSecretEncrypt()
    {
        $custom = 'PromoCode123';

        $secret = Secrets::create()
        ->encrypt(new SecretHasher)
        ->genCustomSecret(function () use ($custom) {
            return $custom;
        })
        ->save();

        $secret = Secret::where('id', 1)
            ->first();

        if ($secret->secret === $custom) {
            throw Exception('encrypt not working');
        }

        $this->assertInstanceOf(Secret::class, $secret);
    }

    public function testCreateSecretMeta()
    {
        $context = 'register';
        $contextId = '+79000000000';
        $owner = 'test';
        $ownerId = '2';

        $secret = Secrets::create(
            context:$context,
            contextId: $contextId,
            owner: $owner,
            ownerId: $ownerId
        )
        ->save();

        $secret = Secret::where('context', $context)
            ->where('context_id', $contextId)
            ->where('owner', $owner)
            ->where('owner_id', $ownerId)
            ->first();

        $this->assertInstanceOf(Secret::class, $secret);
    }

    public function testCreateSecretLen()
    {
        $len = 25;

        $secret = Secrets::create()
        ->length($len)
        ->save();

        $model = Secret::where('secret', $secret->secretStr)
            ->first();

        if (strlen($secret->secretStr) !== $len) {
            throw  new \Exception('Len not working'.$secret->secretStr);
        }

        $this->assertInstanceOf(Secret::class, $model);
    }
}
