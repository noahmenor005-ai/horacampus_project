<?php

namespace Database\Factories;

use App\Models\Domaine;
use App\Models\Filiere;
use Illuminate\Database\Eloquent\Factories\Factory;

class FiliereFactory extends Factory
{
    protected $model = Filiere::class;

    public function definition()
    {
        return [
            'domaine_id' => Domaine::factory(),
            'nom' => $this->faker->unique()->word . ' Filiere',
            'description' => $this->faker->sentence,
        ];
    }
}
