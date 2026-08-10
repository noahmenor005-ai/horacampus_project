<?php

namespace Database\Factories;

use App\Models\Departement;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition()
    {
        return ['nom' => 'L' . $this->faker->numberBetween(1, 3) . ' ' . $this->faker->unique()->word, 'niveau' => 'L' . $this->faker->numberBetween(1, 3), 'departement_id' => Departement::factory(), 'effectif' => $this->faker->numberBetween(20, 120)];
    }
}
