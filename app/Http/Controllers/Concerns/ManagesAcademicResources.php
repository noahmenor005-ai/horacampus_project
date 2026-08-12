<?php

namespace App\Http\Controllers\Concerns;

use App\Support\FacultyGuard;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

trait ManagesAcademicResources
{
    use ScopesFaculty;

    protected function assertOwned(?Model $model): void
    {
        FacultyGuard::assert($model);
    }

    protected function applySearch(Builder $query, Request $request, array $fields): Builder
    {
        if (!$request->filled('q')) {
            return $query;
        }

        $term = '%' . trim($request->input('q')) . '%';

        return $query->where(function (Builder $builder) use ($term, $fields) {
            foreach ($fields as $field) {
                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field, 2);
                    $builder->orWhereHas($relation, function (Builder $relationQuery) use ($column, $term) {
                        $relationQuery->where($column, 'like', $term);
                    });
                } else {
                    $builder->orWhere($field, 'like', $term);
                }
            }
        });
    }

    protected function toggleFlag(Model $model, string $field = 'actif')
    {
        $this->assertOwned($model);
        $model->update([$field => !$model->{$field}]);

        $label = $model->{$field} ? 'activé' : 'désactivé';

        return back()->with('success', 'Enregistrement ' . $label . ' avec succès.');
    }

    protected function denyDelete(string $message)
    {
        return back()->with('error', $message);
    }
}
