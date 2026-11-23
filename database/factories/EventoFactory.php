<?php

namespace Database\Factories;

use App\Models\Evento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Evento>
 */
class EventoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->state(['role' => User::ROLE_ORGANIZADOR]),
            'titulo' => fake()->sentence(4),
            'descripcion' => fake()->paragraph(),
            'cupo_maximo' => fake()->numberBetween(20, 500),
        ];
    }
}
