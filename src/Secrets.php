<?php

declare(strict_types=1);

namespace Aldemco\Secrets;

use Aldemco\Secrets\Contracts\SecretGeneratorContract;
use Aldemco\Secrets\Contracts\SecretHasherContract;
use Aldemco\Secrets\Models\Secret;
use Aldemco\Secrets\Traits\Helper;
use Aldemco\Secrets\Traits\Validator;
use Carbon\Carbon;

class Secrets
{
    use Helper, Validator;

    public string $secretStr;

    private Secret $model;

    private string $encryptedSecretStr;

    private int $secretStrLength = 6;

    private SecretGeneratorContract $secretGenerator;

    private SecretHasherContract $hasher;

    public function __construct(
        string $context = '',
        string $contextId = '',
        string $owner = '',
        string $ownerId = '',
        ?SecretHasherContract $hasher,
        ?SecretGeneratorContract $secretGenerator
    ) {
        $this->initModel();
        $this->model->owner = $owner;
        $this->model->owner_id = $ownerId;
        $this->model->context = $context;
        $this->model->context_id = $contextId;
        $this->secretGenerator = $secretGenerator;
        $this->hasher = $hasher;
        $this->genSecretStr(null);

        if (config('secrets.is_crypt') === true) {
            $this->encrypt(null);
        }
    }

    public function __toString(): string
    {
        return (string) $this->secretStr;
    }

    public function genSecretStr(?SecretGeneratorContract $generator): self
    {
        if (! $generator instanceof SecretGeneratorContract) {
            $generator = $this->secretGenerator;
        }
        $this->secretStr = $generator->generate($this->secretStrLength ?? config('secrets.length', 6));

        return $this;
    }

    public function genCustomSecret(callable $generator): self
    {
        $secretStr = $generator();
        $this->secretLenValidator($secretStr);
        $this->secretTypeValidator($secretStr);
        $this->secretStr = $secretStr;

        return $this;
    }

    public function encrypt(?SecretHasherContract $hasher): self
    {
        if ($hasher) {
            $this->encryptedSecretStr = $hasher->encrypt($this->secretStr);
        } else {
            $this->encryptedSecretStr = $this->hasher;
        }

        $this->model->is_crypt = true;

        return $this;
    }

    public function length(int $length): self
    {
        $this->secretStrLength = $length;
        $this->genSecretStr(null);

        return $this;
    }

    protected function setSecret(string $secret): self
    {
        $this->model->secret = $secret;

        return $this;
    }

    public function setValidFrom(Carbon $from): self
    {
        $this->model->valid_from = $from;

        return $this;
    }

    public function setValidUntil(Carbon $until): self
    {
        $this->model->valid_until = $until;

        return $this;
    }

    public function setStoreUntil(Carbon $until): self
    {
        $this->model->store_until = $until;

        return $this;
    }

    public function setAttemps(int $attemps): self
    {
        $this->model->attemps_cnt = $attemps;

        return $this;
    }

    public function withInterval(int $seconds): self
    {
        $seconds = $seconds ?? config('secrets.interval', 60);
        /**
         * @var Secret $lastSecret
         */
        $lastSecret = self::findAll(
            context: (string) $this->model->context,
            contextId: (string) $this->model->context_id,
            owner: (string) $this->model->owner_class,
            ownerId: (string) $this->model->owner_id,
            limit:1
        )->first();

        if ($lastSecret !== null) {
            $this->checkInterval($lastSecret, $seconds);
        }

        return $this;
    }

    public function save(): self
    {
        $this->setSecret($this->encryptedSecretStr ?? $this->secretStr);

        $defaultStoreUntilMinutes = config('secrets.store_until_minutes', 0);
        if ($this->model->store_until === null and $defaultStoreUntilMinutes !== 0) {
            $this->model->store_until = Carbon::now()->addMinutes($defaultStoreUntilMinutes);
        }

        $defaultValidUntilMinutes = config('secrets.valid_until_minutes', 0);
        if ($this->model->valid_until === null and $defaultValidUntilMinutes !== 0) {
            $this->model->valid_until = Carbon::now()->addMinutes($defaultValidUntilMinutes);
        }

        $defaultAttemps = config('secrets.attemps', 0);
        if ($this->model->attemps_cnt === null and $defaultAttemps !== 0) {
            $this->model->attemps_cnt = $defaultAttemps;
        }

        $defaultValidFromMinutes = config('secrets.valid_from_minutes', 0);
        if ($this->model->valid_from === null and $defaultValidFromMinutes !== 0) {
            $this->model->valid_from = Carbon::now()->addMinutes($defaultValidFromMinutes);
        }

        $this->model->save();

        return $this;
    }

    protected function initModel(): void
    {
        $this->model = new Secret();
    }

    public static function create(
        string $context = '',
        string $contextId  = '',
        string $owner  = '',
        string $ownerId  = ''): self
    {
        if ($context === '') {
            $context = self::getContextClass();
        }

        return app(self::class, [
            'context' => $context,
            'contextId' => $contextId,
            'owner' => $owner,
            'ownerId' => $ownerId,
        ]);
    }

    public static function check(
        string $inputSecret,
        string $context = '',
        string $contextId = '',
        string $owner = '',
        string $ownerId = ''): Checker
    {
        if ($context === '') {
            $context = self::getContextClass();
        }

        return app(Checker::class, [
            'inputSecretStr' => $inputSecret,
            'context' => $context,
            'contextId' => $contextId,
            'owner' => $owner,
            'ownerId' => $ownerId,
        ]);
    }
}
