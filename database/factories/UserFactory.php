<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    public function definition()
    {
        return [
            'nom' => $this->faker->lastName(),
            'postnom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'matricule' => strtoupper($this->faker->unique()->bothify('??###??##')),
            'sexe' => $this->faker->randomElement(['M','F']),
            'email' => $this->faker->unique()->safeEmail(),
            'telephone' => $this->faker->numerify('09#########'),
            'role' => User::ROLE_ETUDIANT,
            'status' => User::STATUS_ACCEPTED,
            'statut_inscription' => 'actif',
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }

    public function admin(): Factory
    {
        return $this->state(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACCEPTED, 'is_active'=>true]);
    }

    public function decanat(): Factory
    {
        return $this->state(function(array $attrs){
            return [
                'role' => User::ROLE_DECANAT,
                'status' => User::STATUS_ACCEPTED,
                'is_active'=>true,
                'faculte_id' => \App\Models\Faculte::factory(),
            ];
        });
    }

    public function enseignant(): Factory
    {
        return $this->state(['role' => User::ROLE_ENSEIGNANT, 'status' => User::STATUS_ACCEPTED, 'is_active'=>true]);
    }

    public function etudiant(): Factory
    {
        return $this->state(['role' => User::ROLE_ETUDIANT, 'status' => User::STATUS_ACCEPTED, 'is_active'=>true]);
    }
}
