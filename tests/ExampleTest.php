<?php

use GetCodeDev\TestLevenshtein\Models\Home;
use GetCodeDev\TestLevenshtein\Models\User;

it('error: when pass model with table not exists in database', function () {
    $check = check_duplicates(
        model: \GetCodeDev\TestLevenshtein\Models\Test::class,
        search: [
            'first_name' => '1',
        ],
    );

    expect($check)
        ->toMatchArray([
            'success' => false,
            'message' => "Table 'test' not exists",
            'items' => [],
        ]);

});

it('success: on empty database check return empty array', function () {
    $check = check_duplicates(
        model: User::class,
        search: [
            'first_name' => '1',
        ],
    );

    expect($check)
        ->toMatchArray([
            'success' => true,
            'items' => [],
        ]);
});

it('success: skip search columns that not existss in db', function () {
    $check = check_duplicates(
        model: User::class,
        search: [
            'first_name' => '1',
            'test' => true,
        ],
    );

    expect($check)
        ->toMatchArray([
            'success' => true,
            'items' => [],
        ]);
});

it('success: find duplicate with limit 1', function () {

    User::factory()
        ->count(10)
        ->hasJobs(3)
        ->create();

    $first_name = 'Ricky12345';

    $user = User::factory()
        ->state([
            'first_name' => $first_name
        ])
        ->create();

    $check = check_duplicates(
        model: User::class,
        search: [
            'first_name' => $first_name,
        ],
        limit: 1,
    );

    expect($check)
        ->toMatchArray([
            'success' => true,
            'items' => [
                [
                    'users_id' => $user->id,
                    'users_first_name' => $first_name,
                    'sim_users_first_name' => 100
                ]
            ],
        ]);
});

it('success: default limit 5 return 5 results', function () {

    User::factory()
        ->count(10)
        ->hasJobs(3)
        ->create();

    $first_name = 'Ricky';

    $user = User::factory()
        ->state([
            'first_name' => $first_name
        ])
        ->create();

    $check = check_duplicates(
        model: User::class,
        search: [
            'first_name' => $first_name,
        ],
    );

    expect($check)
        ->toHaveKey('success', true)
        ->items->toHaveCount(5)
    ;
});

it('success: set limit 10 return 10 results', function () {

    User::factory()
        ->count(10)
        ->hasJobs(3)
        ->create();

    $first_name = 'Ricky';

    $user = User::factory()
        ->state([
            'first_name' => $first_name
        ])
        ->create();

    $check = check_duplicates(
        model: User::class,
        search: [
            'first_name' => $first_name,
        ],
        limit: 10,
    );

    expect($check)
        ->toHaveKey('success', true)
        ->items->toHaveCount(10)
    ;
});

it('success: with_similarity_min_common 50 return correct result', function () {

    $user_correct = User::factory()
        ->state([
            'first_name' => 'Ricky123',
            'last_name' => 'Smith123',
        ])
        ->create();

    $user_not_correct = User::factory()
        ->state([
            'first_name' => 'Erna',
            'last_name' => 'Kilback',
        ])
        ->create();

    $check = check_duplicates(
        model: User::class,
        search: [
            'first_name' => 'Ricky',
            'last_name' => 'Smith',
        ],
        with_similarity_min_common: 50
    );

    expect($check)
        ->toHaveKey('success', true)
        ->items->toHaveCount(1)
        ->items->toHaveKey('0.users_id', $user_correct->id)
    ;
});

it('success: search for concat column in users table return correct result', function () {

    $user_correct = User::factory()
        ->state([
            'first_name' => 'Ricky123',
            'last_name' => 'Smith123',
        ])
        ->create();

    $user_not_correct = User::factory()
        ->state([
            'first_name' => 'Erna',
            'last_name' => 'Kilback',
        ])
        ->create();

    $check = check_duplicates(
        model: User::class,
        search: [
            'full_name' => 'Ricky Smith',
        ],
        concat_search_columns: [
            'users' => [
                'full_name' => 'users.first_name users.last_name',
            ],
        ],
        with_similarity_min_common: 50
    );

    expect($check)
        ->toHaveKey('success', true)
        ->items->toHaveCount(1)
        ->items->toHaveKey('0.users_id', $user_correct->id)
    ;
});

it('success: search for concat column in users table return empty if not match', function () {

    $user_correct = User::factory()
        ->state([
            'first_name' => 'Ricky123',
            'last_name' => 'Smith123',
        ])
        ->create();

    $user_not_correct = User::factory()
        ->state([
            'first_name' => 'Erna',
            'last_name' => 'Kilback',
        ])
        ->create();

    $check = check_duplicates(
        model: User::class,
        search: [
            'full_name' => 'Ricky Smith',
        ],
        concat_search_columns: [
            'users' => [
                'full_name' => 'users.last_name users.first_name',
            ],
        ],
        with_similarity_min_common: 50
    );

    expect($check)
        ->toHaveKey('success', true)
        ->items->toHaveCount(0)
    ;
});

it('success: search for relation column in homes table return correct result', function () {

    $user_correct = User::factory()
        ->state([
            'first_name' => 'Ricky123',
            'last_name' => 'Smith123',
        ])
        ->for(Home::factory()->state([
            'street' => '61946 Elena Skyway Apt. 654',
        ]))
        ->create();

    $user_not_correct = User::factory()
        ->state([
            'first_name' => 'Erna',
            'last_name' => 'Kilback',
        ])
        ->for(Home::factory()->state([
            'street' => '222',
        ]))
        ->create();

    $check = check_duplicates(
        model: User::class,
        search: [
            'first_name' => 'Ricky123',
            'home' => [
                'street' => '61946 Elena Skyway Apt. 654',
            ],
        ],
        with_similarity_min_common: 50
    );

    expect($check)
        ->toHaveKey('success', true)
        ->items->toHaveCount(1)
        ->items->toHaveKey('0.users_id', $user_correct->id)
    ;
});

it('success: search for relation concat column in homes table return correct result', function () {

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
            'first_name' => 'Ricky123',
            'home' => [
                'address' => "$street, $city $state, $zip",
            ],
        ],
        concat_search_columns: [
            'home' => [
                'address' => 'homes.street, homes.city homes.state, homes.zip',
            ],
        ],
        with_similarity_min_common: 50
    );

    expect($check)
        ->toHaveKey('success', true)
        ->items->toHaveCount(1)
        ->items->toHaveKey('0.users_id', $user_correct->id)
    ;
});

it('success: search for relation concat column in homes table  return empty if not match', function () {

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

    expect($check)
        ->toHaveKey('success', true)
        ->items->toHaveCount(0)
    ;
});
