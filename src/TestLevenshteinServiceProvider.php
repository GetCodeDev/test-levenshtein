<?php

namespace GetCodeDev\TestLevenshtein;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use GetCodeDev\TestLevenshtein\Commands\TestLevenshteinCommand;

class TestLevenshteinServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('test-levenshtein')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_test-levenshtein_table')
            ->hasCommand(TestLevenshteinCommand::class);
    }
}
