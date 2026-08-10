<?php

namespace Database\Factories;

use App\Models\Filiere;
use App\Models\Mention;
use Illuminate\Database\Eloquent\Factories\Factory;

class MentionFactory extends Factory
{
    protected $model = Mention::class;

    public function definition()
    {
        return [
            'filiere_id' => Filiere::factory(),
            'nom' => $this->faker->unique()->word . ' Mention',
            'description' => $this->faker->sentence,
        ];
    }
}
