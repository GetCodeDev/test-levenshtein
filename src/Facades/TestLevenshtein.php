<?php

namespace GetCodeDev\TestLevenshtein\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \GetCodeDev\TestLevenshtein\TestLevenshtein
 */
class TestLevenshtein extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'test-levenshtein';
    }
}
