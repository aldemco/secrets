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

}
