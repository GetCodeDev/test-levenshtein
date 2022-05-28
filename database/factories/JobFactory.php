<?php

namespace GetCodeDev\TestLevenshtein\Database\Factories;

use Faker\Generator;
use GetCodeDev\TestLevenshtein\Models\Job;
use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Factories\Factory;


class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition()
    {
        return [
            'street'       => $this->faker->streetAddress(),
            'city'         => $this->faker->city(),
            'state'        => $this->faker->stateAbbr(),
            'zip'          => $this->faker->regexify('[1-9]{1}[0-9]{4}'),
            'full_address' => $this->faker->name(),
        ];
    }
}

