<?php

namespace GetCodeDev\TestLevenshtein\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase as Orchestra;
use GetCodeDev\TestLevenshtein\TestLevenshteinServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'GetCodeDev\\TestLevenshtein\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            TestLevenshteinServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {

    }

    protected function migrateFreshUsing()
    {
        return ['--schema-path' => 'database/schema/mysql-schema.dump'];
    }
}
