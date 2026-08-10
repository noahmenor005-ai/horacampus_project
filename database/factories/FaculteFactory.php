<?php

namespace Database\Factories;

use App\Models\Faculte;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaculteFactory extends Factory
{
    protected $model = Faculte::class;

    public function definition()
    {
        return ['nom' => $this->faker->unique()->company, 'description' => $this->faker->sentence];
    }
}
