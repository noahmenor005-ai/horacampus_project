<?php

namespace Database\Factories;

use App\Models\Cours;
use App\Models\Enseignant;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

class CoursFactory extends Factory
{
    protected $model = Cours::class;

    public function definition()
    {
        return ['nom' => $this->faker->unique()->words(3, true), 'code' => strtoupper($this->faker->unique()->bothify('??###')), 'credit' => $this->faker->numberBetween(2, 6), 'enseignant_id' => Enseignant::factory(), 'promotion_id' => Promotion::factory()];
    }
}
