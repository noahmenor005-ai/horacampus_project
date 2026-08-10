<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CrudController;
use App\Models\Faculte;

class FaculteController extends CrudController
{
    protected $model = Faculte::class;
    protected $title = 'Faculté';
    protected $route = 'facultes';
    protected $relations = ['domaines'];
    protected $fields = [
        'code' => 'required|string|max:20|unique:facultes,code',
        'nom' => 'required|string|max:150|unique:facultes,nom',
        'description' => 'nullable|string',
    ];
}
