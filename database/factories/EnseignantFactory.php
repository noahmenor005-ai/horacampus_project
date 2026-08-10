<?php

namespace Database\Factories;

use App\Models\Enseignant;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnseignantFactory extends Factory
{
    protected $model = Enseignant::class;

    public function definition()
    {
        return [
            'nom' => $this->faker->lastName,
            'postnom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'sexe' => $this->faker->randomElement(['M','F']),
            'matricule' => strtoupper($this->faker->unique()->bothify('ENS-####')),
            'email' => $this->faker->unique()->safeEmail,
            'telephone' => $this->faker->phoneNumber,
            'specialite' => $this->faker->jobTitle,
            'faculte_id' => \App\Models\Faculte::factory(),
            'statut' => 'actif',
            'is_active' => true,
        ];
    }
}
