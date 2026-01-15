<?php

declare(strict_types=1);

namespace He4rt\Location\Database\Factories;

use He4rt\Location\Address;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Str;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'full_address' => fake()->address(),
            'street_number' => fake()->streetName(),
            'route' => fake()->word(),
            'city' => fake()->city(),
            'sub_locality' => fake()->word(),
            'state' => fake()->word(),
            'country' => fake()->country(),
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'created_at' => Date::now(),
            'updated_at' => Date::now(),

            'addressable_id' => User::factory(),
            'addressable_type' => User::class,
        ];
    }
}
