<?php

namespace Database\Factories;

use App\Models\Enseignant;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnseignantFactory extends Factory
{
    protected $model = Enseignant::class;

    public function definition()
    {
        return ['nom' => $this->faker->lastName, 'prenom' => $this->faker->firstName, 'email' => $this->faker->unique()->safeEmail, 'telephone' => $this->faker->phoneNumber, 'specialite' => $this->faker->jobTitle];
    }
}
