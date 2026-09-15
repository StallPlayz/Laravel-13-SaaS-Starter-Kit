<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $locations = [
            ['country' => 'Indonesia', 'province' => 'East Java', 'city' => 'Surabaya'],
            ['country' => 'Indonesia', 'province' => 'Jakarta', 'city' => 'Central Jakarta'],
            ['country' => 'Indonesia', 'province' => 'Bali', 'city' => 'Denpasar'],
            ['country' => 'United States', 'province' => 'California', 'city' => 'Los Angeles'],
            ['country' => 'United States', 'province' => 'New York', 'city' => 'New York City'],
            ['country' => 'Japan', 'province' => 'Tokyo', 'city' => 'Tokyo'],
            ['country' => 'Australia', 'province' => 'New South Wales', 'city' => 'Sydney'],
        ];

        $location = fake()->randomElement($locations);

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'phone_number' => fake()->phoneNumber(),
            'country' => $location['country'],
            'province' => $location['province'],
            'city' => $location['city'],
            'district' => fake()->streetName(),
            'address' => fake()->streetAddress(),
            'terms' => true,
            'account_tier' => fake()->randomElement(['free', 'pro', 'enterprise']),
            'max_workspaces' => fake()->numberBetween(1, 5),
            'platform_role' => 'user',
            'remember_token' => Str::random(10),
        ];
    }
}