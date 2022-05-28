<?php

namespace GetCodeDev\TestLevenshtein;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use GetCodeDev\TestLevenshtein\Commands\SeedCommand;
use GetCodeDev\TestLevenshtein\TestLevenshtein;

class TestLevenshteinServiceProvider extends PackageServiceProvider
{
    public function bootingPackage()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

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
            ->hasCommand(SeedCommand::class);

        require_once($package->basePath('helpers.php'));

        $this->app->bind('test-levenshtein', function($app) {
            return new TestLevenshtein();
        });
    }
}
