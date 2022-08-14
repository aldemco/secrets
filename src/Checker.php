<?php

namespace Aldemco\Secrets;

use Aldemco\Secrets\Contracts\SecretHasherContract;
use Aldemco\Secrets\Models\Secret;
use Illuminate\Database\Eloquent\Collection;
use Closure;

class Checker extends SecretsAbstract
{
    private Collection $secretCollection;

    private Collection $execptions;

    private Secret $currentSecret;

    protected SecretHasherContract $hasher;

    private string $context;

    private string $contextId;

    private string $ownerId;

    private string $inputSecretStr;

    private bool $isCorrectSecret = false;

    private bool $isMultiple = false;

    private bool $isUnlimitedAttemps = false;

    private ?Closure $onAfterSave = function(){};

    private ?Closure $onErrors = function(){};

    private ?Closure $onSuccess = function(){};

    public function __construct(string|int $inpitSecretStr, $context = '', $contextId = '', $owner = '', $ownerId = '')
    {

        $this->context = $context ?? $this->context = Secrets::getcontextClass();
        $this->contextId = $contextId;
        $this->owner = $owner;
        $this->ownerId = $ownerId;
        $this->inputSecretStr = $inpitSecretStr;
        $this->execptions = new Collection();

        if (config('secrets.is_crypt') === true) {
            $secretHasherClass = config('secrets.secret_hasher', Aldemco\Secrets\SecretHasher::class);
            $this->hasher = new $secretHasherClass;
        }

    }
    
    protected function setCurrentSecret(Secret $secret): void
    {
        $this->currentSecret = $secret;
    }

    protected function setLastEnter(Secret $secret, ?Carbon $dateTime): void
    {
        $secret->last_enter = $dateTime ?? Carbon::now();
    }

    protected function setSuccessEnter(Secret $secret, ?Carbon $dateTime): void
    {
        $secret->success_enter = $dateTime ?? Carbon::now();
    }

    public function setDissalowSuccessTimestamp(bool $value = true): self
    {
        $this->dissalowSuccessTimestamp = $value;

        return $this;
    }

    public function allowMultiple(bool $value = true): self
    {
        $this->isMultiple = $value;

        return $this;
    }

    public function setUnlimitedAttemps(bool $value = true): self
    {
        $this->isUnlimitedAttemps = $value;

        return $this;
    }

    public function setEncrypt(SecretHasherContract $hasher): self
    {
        $this->hasher = $hasher;

        return $this;
    }

    public function withRemove(): self
    {
        $this->onAfterSave = function () {
            if ($this->currentSecret->success_enter !== null) {
                $this->currentSecret->delete();
            }
        };

        return $this;
    }

    public function onSuccess(Closure $callback): self
    {
        $this->onSuccess = $callback;

        return $this;
    }

    public function onErrors(Closure $callback): self
    {
        $this->onErrors = $callback;

        return $this;
    }




}
