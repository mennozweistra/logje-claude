<?php

namespace Database\Factories;

use App\Models\Todo;
use App\Models\TodoNote;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TodoNote>
 */
class TodoNoteFactory extends Factory
{
    protected $model = TodoNote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'todo_id' => Todo::factory(),
            'content' => $this->faker->paragraph(),
        ];
    }

    /**
     * Short note state
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->sentence(),
        ]);
    }

    /**
     * Long note state
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'content' => $this->faker->paragraphs(3, true),
        ]);
    }
}
