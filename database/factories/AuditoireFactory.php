<?php

namespace Database\Factories;

use App\Models\Auditoire;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditoireFactory extends Factory
{
    protected $model = Auditoire::class;

    public function definition()
    {
        return ['nom' => 'A' . $this->faker->unique()->numberBetween(100, 999), 'capacite' => $this->faker->numberBetween(30, 250), 'bloc' => 'Bloc ' . $this->faker->randomLetter, 'disponibilite' => true];
    }
}
