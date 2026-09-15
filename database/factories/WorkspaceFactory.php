<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WorkspaceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company();
        
        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . Str::random(4),
            'tier' => fake()->randomElement(['free', 'pro']),
            'settings' => json_encode(['description' => fake()->catchPhrase()]),
            'is_suspended' => fake()->boolean(10),
            'suspended_at' => function (array $attributes) {
                return $attributes['is_suspended'] ? now() : null;
            },
        ];
    }
}