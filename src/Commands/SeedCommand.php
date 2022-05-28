<?php

namespace GetCodeDev\TestLevenshtein\Commands;

use GetCodeDev\TestLevenshtein\Models\User;
use Illuminate\Console\Command;

class SeedCommand extends Command
{
    public $signature = 'seed';

    public $description = 'My command';

    public function handle(): int
    {
        //$this->runTestSeeder();

        $first_name = 'Rogers';
        $last_name = 'Rogers';

        $street = '61946 Elena Skyway Apt. 654';
        $city = 'East Gwen';
        $state = 'ND';
        $zip = 46786;

        $concat_search_columns = [
            'users' => [
                'full_name' => 'users.last_name users.first_name',
            ],
        ];

        $a = check_duplicates(
            model: User::class,
            search: [
                'first_name' => $first_name,
                'last_name'  => $last_name,
                'full_name'  => $first_name.' '.$last_name,
                'home' => [
                    'street' => $street,
                ],
                'jobs' => [
                    'street' => $street,
                ],
                //'test' => true
            ],
            //concat_search_columns: $concat_search_columns,
            priority_columns: [
                'full_name',
                'first_name',
                'last_name',
                'homes.street',
                'jobs.street',
            ],
            limit: 5,
            //with_similarity_min_common: 50
        );

        dd($a);

        $this->comment('All done');

        return self::SUCCESS;
    }

    protected function runTestSeeder(): void
    {
        User::factory()
            ->count(100)
            ->hasJobs(3)
            ->create();
    }
}
