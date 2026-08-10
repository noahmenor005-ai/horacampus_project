<?php

namespace Database\Factories;

use App\Models\Domaine;
use App\Models\Faculte;
use Illuminate\Database\Eloquent\Factories\Factory;

class DomaineFactory extends Factory
{
    protected $model = Domaine::class;

    public function definition()
    {
        return [
            'faculte_id' => Faculte::factory(),
            'nom' => $this->faker->unique()->word . ' Domaine',
            'description' => $this->faker->sentence,
        ];
    }
}
