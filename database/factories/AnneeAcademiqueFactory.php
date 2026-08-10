<?php

namespace Database\Factories;

use App\Models\AnneeAcademique;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnneeAcademiqueFactory extends Factory
{
    protected $model = AnneeAcademique::class;

    public function definition()
    {
        $year = $this->faker->numberBetween(2024, 2030);
        return [
            'libelle' => $year . '-' . ($year+1),
            'date_debut' => $year . '-09-01',
            'date_fin' => ($year+1) . '-07-31',
            'active' => false,
        ];
    }
}
