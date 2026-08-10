<?php

namespace Database\Factories;

use App\Models\Departement;
use App\Models\Faculte;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartementFactory extends Factory
{
    protected $model = Departement::class;

    public function definition()
    {
        return ['nom' => $this->faker->unique()->word, 'faculte_id' => Faculte::factory()];
    }
}
