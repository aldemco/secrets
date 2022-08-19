<?php

namespace Aldemco\Secrets\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Aldemco\Secrets\Secrets
 */
class Secrets extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Aldemco\Secrets\Secrets::class;
    }
}
