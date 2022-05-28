<?php

namespace GetCodeDev\TestLevenshtein\Database\Factories;

use GetCodeDev\TestLevenshtein\Models\Home;
use GetCodeDev\TestLevenshtein\Models\Job;
use GetCodeDev\TestLevenshtein\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;


class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'name'       => $this->faker->name(),
            'email'      => $this->faker->unique()->safeEmail(),
            'first_name' => $this->faker->firstName(),
            'last_name'  => $this->faker->lastName(),
            'phone'      => $this->faker->e164PhoneNumber(),
            'password'   => bcrypt('test'),
            'home_id'    => Home::factory(),
        ];
    }
}

