<?php

namespace Database\Factories;

use App\Models\Etudiant;
use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;

class EtudiantFactory extends Factory
{
    protected $model = Etudiant::class;

    public function definition()
    {
        return ['promotion_id' => Promotion::factory(), 'matricule' => strtoupper($this->faker->unique()->bothify('ETU-####')), 'nom' => $this->faker->lastName, 'prenom' => $this->faker->firstName, 'email' => $this->faker->unique()->safeEmail, 'telephone' => $this->faker->phoneNumber];
    }
}
