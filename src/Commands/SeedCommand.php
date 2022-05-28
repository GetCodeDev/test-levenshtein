<?php

namespace GetCodeDev\TestLevenshtein\Commands;

use GetCodeDev\TestLevenshtein\Models\Home;
use GetCodeDev\TestLevenshtein\Models\Test;
use GetCodeDev\TestLevenshtein\Models\User;
use Illuminate\Console\Command;

class SeedCommand extends Command
{
    public $signature = 'seed';

    public $description = 'My command';

    public function handle(): int
    {
        //$this->runTestSeeder();
        //$this->testCheck();

        $street = '61946 Elena Skyway Apt. 654';
        $city = 'East Gwen';
        $state = 'ND';
        $zip = 46786;

        $user_correct = User::factory()
            ->state([
                'first_name' => 'Ricky123',
                'last_name' => 'Smith123',
            ])
            ->for(Home::factory()->state([
                'street' => $street,
                'city'   => $city,
                'state'  => $state,
                'zip'    => $zip,
            ]))
            ->create();

        $user_not_correct = User::factory()
            ->state([
                'first_name' => 'Erna',
                'last_name' => 'Kilback',
            ])
            ->for(Home::factory()->state([
                'street' => '1111',
                'city'   => '222',
                'state'  => 'AA',
                'zip'    => 44444,
            ]))
            ->create();

        $check = check_duplicates(
            model: User::class,
            search: [
                'home' => [
                    'address' => "bbbbb, rrr BB, 77777",
                ],
            ],
            concat_search_columns: [
                'home' => [
                    'address' => 'homes.street, homes.city homes.state, homes.zip',
                ],
            ],
            priority_columns: [
                'homes.address',
            ],
            with_similarity_min_common: 50
        );

        dd($check);

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

    protected function testCheck()
    {
        $first_name = 'Ricky';
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
    }
}
