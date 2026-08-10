<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrudEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && in_array($this->user()->role, ['admin', 'secretaire_academique'], true);
    }

    public function rules(): array
    {
        return [];
    }
}
