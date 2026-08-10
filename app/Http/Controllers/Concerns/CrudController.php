<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class CrudController extends Controller
{
    protected $model;
    protected $title;
    protected $route;
    protected $fields = [];
    protected $relations = [];

    public function index(Request $request)
    {
        $query = ($this->model)::with($this->relations);

        if ($request->filled('q')) {
            $query->where(function ($builder) use ($request) {
                foreach ($this->fields as $field => $rules) {
                    if (str_contains($rules, 'string')) {
                        $builder->orWhere($field, 'like', '%' . $request->q . '%');
                    }
                }
            });
        }

        $items = $query->latest()->paginate(10)->withQueryString();

        return view('crud.index', $this->viewData(compact('items')));
    }

    public function create()
    {
        return view('crud.form', $this->viewData(['item' => new $this->model]));
    }

    public function store(Request $request)
    {
        ($this->model)::create($request->validate($this->rules()));

        return redirect()->route($this->routeName() . '.index')->with('success', $this->title . ' créé(e) avec succès.');
    }

    public function edit($id)
    {
        return view('crud.form', $this->viewData(['item' => ($this->model)::findOrFail($id)]));
    }

    public function update(Request $request, $id)
    {
        $item = ($this->model)::findOrFail($id);
        $item->update($request->validate($this->rules($item)));

        return redirect()->route($this->routeName() . '.index')->with('success', $this->title . ' mis(e) à jour.');
    }

    public function destroy($id)
    {
        ($this->model)::findOrFail($id)->delete();

        return back()->with('success', $this->title . ' supprimé(e).');
    }

    protected function rules($item = null): array
    {
        $rules = $this->fields;

        if ($item) {
            foreach ($rules as $field => $rule) {
                $rules[$field] = preg_replace('/unique:([^,]+),([^|]+)/', 'unique:$1,$2,' . $item->id, $rule);
            }
        }

        return $rules;
    }

    protected function routeName(): string
    {
        return $this->route ?: strtolower(class_basename($this->model));
    }

    protected function viewData(array $data = []): array
    {
        return array_merge($data, [
            'title' => $this->title,
            'fields' => $this->fields,
            'route' => $this->routeName(),
            'selects' => $this->selects(),
        ]);
    }

    protected function selects(): array
    {
        return [];
    }
}
