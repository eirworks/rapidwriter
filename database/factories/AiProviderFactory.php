<?php

namespace Database\Factories;

use App\Models\AiProvider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiProvider>
 */
class AiProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->colorName(),
            'url' => fake()->url(),
            'model' => fake()->randomElement(['gpt-3.5-turbo', 'gpt-4']),
            'api_key' => fake()->uuid(),
        ];
    }
}
