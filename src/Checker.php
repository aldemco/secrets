<?php

namespace Aldemco\Secrets;

use Aldemco\Secrets\Contracts\SecretHasherContract;
use Aldemco\Secrets\Exceptions\SecretValidatorException;
use Aldemco\Secrets\Models\Secret;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
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

    protected function decrypt(): void
    {
        $this->hasher->check($this->inputSecretStr, $this->currentSecret->secret);
    }

    protected function findSecrets(int $limit = 1, string $secretStr = ''): void
    {
        $this->secretCollection = Secrets::findAll(
            $context = $this->context,
            $contextId = $this->contextId,
            $owner = $this->owner,
            $ownerId = $this->ownerId,
            $secret = $secretStr,
            $limit = $limit,
        );
    }

    protected function isValid(Secret $secret): void
    {
        $this->isValidUntil($secret);
        $this->isValidFrom($secret);
        if ($this->isUnlimitedAttemps == false) {
            $this->isAllowEnter($secret);
        }

        $this->isNotUsed($secret);
    }

    protected function saveCurrentSecret(): void
    {
        $this->currentSecret->save();
        if ($this->onAfterSave) {
            $this->onAfterSave->call($this);
        }
    }

    public function verify()
    {
        if ($this->isMultiple) {
            $this->findSecrets($limit = config('secrets.multiple_limit', 10));
        } else {
            $this->findSecrets($limit = 1);
        }

        $this->verifyAll();

        if ($this->execptions->count()) {
            $this->onErrors->call($this, $this->execptions);
        }

        return $this;
    }

    protected function verifyAll()
    {
        $this->secretCollection->each(function (Secret $secret) {
            $this->setCurrentSecret($secret);

            try {
                $this->setLastEnter($secret);
                $this->isValid($secret);

                $secretStr = $this->currentSecret->secret;

                if ($this->currentSecret->is_crypt) {
                    $this->isCorrectSecret = $this->isCorrectEncryptSecret($this->hasher, $secretStr, $this->inputSecretStr);
                } else {
                    $this->isCorrectSecret = $this->isCorrectSecret($secretStr, $this->inputSecretStr);
                }

                if ($this->isCorrectSecret) {
                    $this->dissalowSuccessTimestamp ?? $this->setSuccessEnter($secret);

                    if ($this->onSuccess) {
                        $this->onSuccess->call($this);
                    }

                }
            } catch (SecretValidatorException $e) {
                $this->execptions->add($e);
            }

            if ($this->isUnlimitedAttemps === false && $this->isCorrectSecret === false) {
                $this->currentSecret->attemps_cnt--;
            }

            $this->saveCurrentSecret();

            if ($this->isCorrectSecret === true) {
                return false;
            }

        });
    }


}
