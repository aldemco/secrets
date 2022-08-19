<?php

declare(strict_types=1);

namespace Aldemco\Secrets;

use Aldemco\Secrets\Contracts\SecretHasherContract;
use Aldemco\Secrets\Exceptions\SecretValidatorException;
use Aldemco\Secrets\Models\Secret;
use Aldemco\Secrets\Traits\Helper;
use Aldemco\Secrets\Traits\Validator;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property Collection<int, Secret> $secretCollection
 * @property Collection<int, SecretValidatorException> $execptions
 */
class Checker
{
    use Helper, Validator;

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

    private ?Closure $onAfterSave = null;

    private ?Closure $onSuccess = null;

    const STATUS_NOT_VERIFY = 0;

    const STATUS_VERIFY = 1;

    const STATUS_INCORRECT = 2;

    public function __construct(string $inpitSecretStr, $context = '', $contextId = '', $owner = '', $ownerId = '')
    {
        $this->context = (string) $context ?? $this->context = self::getcontextClass();
        $this->contextId = (string) $contextId;
        $this->owner = (string) $owner;
        $this->ownerId = (string) $ownerId;
        $this->inputSecretStr = $inpitSecretStr;
        $this->execptions = new Collection();

        if (config('secrets.is_crypt') === true) {
            $this->hasher = app(SecretHasherContract::class);
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

    protected function findSecrets(int $limit = 1, string $secretStr = ''): void
    {
        $this->secretCollection = self::findAll(
            $context = $this->context,
            $contextId = $this->contextId,
            $owner = $this->owner,
            $ownerId = $this->ownerId,
            $secret = $secretStr,
            $limit = $limit,
        );

        if ($this->secretCollection->count() < 1) {
            throw new SecretValidatorException('Wrong Secret');
        }
    }

    protected function isValid(Secret $secret): void
    {
        $this->isValidUntil($secret);
        $this->isValidFrom($secret);
        if ($this->isUnlimitedAttemps === false) {
            $this->isAllowEnter($secret);
        }

        $this->isNotUsed($secret);
    }

    public function getResult(): bool
    {
        return $this->isCorrectSecret;
    }

    public function verify()
    {
        if ($this->isMultiple) {
            $this->findSecrets($limit = config('secrets.multiple_limit', 10));
            $this->verifyAll();

            if ($this->secretCollection->contains('status', '==', self::STATUS_VERIFY)) {
                $this->secretCollection->each(function ($secret) {
                    if ($secret->status === self::STATUS_VERIFY) {
                        $secret->save();
                    }
                });
            } elseif ($this->secretCollection->contains('status', '==', self::STATUS_INCORRECT)) {
                $this->secretCollection->each(function ($secret) {
                    $secret->save();
                });
            }
        } else {
            $this->findSecrets($limit = 1);
            $this->verifyAll();
            $this->secretCollection->each(function ($secret) {
                $secret->save();
            });
        }

        if ($this->onAfterSave) {
            $this->onAfterSave->call($this);
        }

        if ($this->execptions->count() > 0 && $this->isCorrectSecret === false) {
            throw $this->execptions->last();
        }

        return $this;
    }

    protected function verifyAll()
    {
        foreach ($this->secretCollection as $secret) {
            $this->setCurrentSecret($secret);
            try {
                $this->setLastEnter($secret, null);
                $this->isValid($secret);

                $secretStr = $secret->secret;

                if ($secret->is_crypt) {
                    $this->isCorrectSecret = $this->isCorrectEncryptSecret($this->hasher, $secretStr, $this->inputSecretStr);
                } else {
                    $this->isCorrectSecret = $this->isCorrectSecret($secretStr, $this->inputSecretStr);
                }

                if ($this->isCorrectSecret) {
                    $this->dissalowSuccessTimestamp ?? $this->setSuccessEnter($secret, null);
                    $secret->status = self::STATUS_VERIFY;
                    if ($this->onSuccess) {
                        $this->onSuccess->call($this);
                    }
                }
            } catch (SecretValidatorException $e) {
                $this->execptions->add($e);
                $secret->status = self::STATUS_INCORRECT;
            }

            if ($this->isCorrectSecret === true) {
                break;
            }

            if ($this->isUnlimitedAttemps === false) {
                $secret->attemps_cnt--;
            }
        }
    }
}
