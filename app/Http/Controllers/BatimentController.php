<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CrudController;
use App\Models\Batiment;

class BatimentController extends CrudController
{
    protected $model = Batiment::class;
    protected $title = 'Bâtiment';
    protected $route = 'batiments';
    protected $relations = ['auditoires'];
    protected $fields = [
        'code' => 'required|string|max:30|unique:batiments,code',
        'nom' => 'required|string|max:150',
        'adresse' => 'nullable|string|max:255',
    ];
}
