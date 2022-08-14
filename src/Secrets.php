<?php

namespace Aldemco\Secrets;

use Aldemco\Secrets\Contracts\SecretGeneratorContract;
use Aldemco\Secrets\Contracts\SecretHasherContract;
use Aldemco\Secrets\Models\Secret;
use Carbon\Carbon;

class Secrets extends SecretsAbstract
{
    public string $secretStr;

    private Secret $model;

    private string $encryptedSecretStr;

    public function __construct($context = null, $contextId = null, $owner = null, $ownerId = null)
    {

        $this->initModel();

        $this->model->owner = $owner;
        $this->model->owner_id = $ownerId;

        if ($context === null) {
            $context = self::getContextClass();
        }

        $this->model->context = $context;
        $this->model->context_id = $contextId;
        
        $secretGeneratorClass = config('secrets.secret_generator', Aldemco\Secrets\SecretGenerator::class);
        $this->genSecretStr(new $secretGeneratorClass);

        if (config('secrets.is_crypt') === true) {
            $hasherClass = config('secrets.secret_hasher', Aldemco\Secrets\SecretHasher::class);
            $this->encrypt(new $hasherClass);
        }
    }

    public function __toString(): string
    {
        return (string) $this->secretStr;
    }

    public function genSecretStr(SecretGeneratorContract $generator): self
    {
        $this->secretStr = $generator->generate(config('secrets.length', 6));

        return $this;
    }

    public function encrypt(SecretHasherContract $hasher): self
    {
        $this->encryptedSecretStr = $hasher->encrypt($this->secretStr);
        $this->model->is_crypt = true;

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

    protected function initModel(): void
    {
        $this->model = new Secret();
    }

}
