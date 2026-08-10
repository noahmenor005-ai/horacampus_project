<?php

namespace App\Http\Controllers;

use App\Models\AnneeAcademique;
use App\Models\Domaine;
use App\Models\Ec;
use App\Models\Faculte;
use App\Models\Filiere;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\Semestre;
use App\Models\Ue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LmdCrudController extends Controller
{
    private function config(string $resource): array
    {
        $configs = [
            'domaines' => [
                'model' => Domaine::class,
                'title' => 'Domaines',
                'rules' => [
                    'faculte_id' => 'required|exists:facultes,id',
                    'nom' => 'required|string|max:120',
                    'description' => 'nullable|string',
                ],
                'selects' => fn () => ['faculte_id' => Faculte::orderBy('nom')->pluck('nom', 'id')],
            ],
            'filieres' => [
                'model' => Filiere::class,
                'title' => 'Filières',
                'rules' => [
                    'domaine_id' => 'required|exists:domaines,id',
                    'nom' => 'required|string|max:120',
                    'description' => 'nullable|string',
                ],
                'selects' => fn () => ['domaine_id' => $this->scopedDomaines()->pluck('nom', 'id')],
            ],
            'mentions' => [
                'model' => Mention::class,
                'title' => 'Mentions',
                'rules' => [
                    'filiere_id' => 'required|exists:filieres,id',
                    'nom' => 'required|string|max:120',
                    'description' => 'nullable|string',
                ],
                'selects' => fn () => ['filiere_id' => $this->scopedFilieres()->get()->mapWithKeys(fn ($f) => [$f->id => $f->nom . ' (' . optional($f->domaine)->nom . ')'])],
            ],
            'promotions' => [
                'model' => Promotion::class,
                'title' => 'Promotions',
                'rules' => [
                    'mention_id' => 'required|exists:mentions,id',
                    'annee_academique_id' => 'nullable|exists:annees_academiques,id',
                    'nom' => 'required|string|max:100',
                    'niveau' => 'required|string|max:50',
                    'effectif' => 'required|integer|min:0',
                ],
                'selects' => fn () => [
                    'mention_id' => $this->scopedMentions()->get()->mapWithKeys(fn ($m) => [$m->id => $m->nom . ' — ' . optional($m->filiere)->nom]),
                    'annee_academique_id' => AnneeAcademique::orderByDesc('id')->pluck('libelle', 'id'),
                    'niveau' => ['L1' => 'L1', 'L2' => 'L2', 'L3' => 'L3', 'M1' => 'M1', 'M2' => 'M2'],
                ],
            ],
            'annees' => [
                'model' => AnneeAcademique::class,
                'title' => 'Années académiques',
                'rules' => [
                    'libelle' => 'required|string|max:20',
                    'date_debut' => 'required|date',
                    'date_fin' => 'required|date|after:date_debut',
                    'active' => 'nullable|boolean',
                ],
                'selects' => fn () => ['active' => [1 => 'Oui', 0 => 'Non']],
            ],
            'semestres' => [
                'model' => Semestre::class,
                'title' => 'Semestres',
                'rules' => [
                    'annee_academique_id' => 'required|exists:annees_academiques,id',
                    'libelle' => 'required|string|max:100',
                    'date_debut' => 'nullable|date',
                    'date_fin' => 'nullable|date|after:date_debut',
                    'actif' => 'nullable|boolean',
                ],
                'selects' => fn () => [
                    'annee_academique_id' => AnneeAcademique::orderByDesc('id')->pluck('libelle', 'id'),
                    'actif' => [1 => 'Oui', 0 => 'Non'],
                ],
            ],
            'ues' => [
                'model' => Ue::class,
                'title' => 'Unités d\'enseignement',
                'rules' => [
                    'promotion_id' => 'required|exists:promotions,id',
                    'semestre_id' => 'nullable|exists:semestres,id',
                    'code' => 'required|string|max:30',
                    'nom' => 'required|string|max:150',
                    'credit' => 'required|integer|min:0|max:30',
                ],
                'selects' => fn () => [
                    'promotion_id' => $this->scopedPromotions()->pluck('nom', 'id'),
                    'semestre_id' => Semestre::orderBy('libelle')->pluck('libelle', 'id'),
                ],
            ],
            'ecs' => [
                'model' => Ec::class,
                'title' => 'Éléments constitutifs',
                'rules' => [
                    'ue_id' => 'required|exists:ues,id',
                    'code' => 'required|string|max:30',
                    'nom' => 'required|string|max:150',
                    'coefficient' => 'required|integer|min:0',
                    'volume_horaire' => 'required|integer|min:1',
                ],
                'selects' => fn () => ['ue_id' => $this->scopedUes()->pluck('nom', 'id')],
            ],
        ];

        abort_unless(isset($configs[$resource]), 404);

        return $configs[$resource];
    }

    public function index(Request $request, string $resource)
    {
        $config = $this->config($resource);
        $query = $this->scope($config['model']::query(), $resource);

        if ($request->filled('q')) {
            $term = '%' . $request->input('q') . '%';
            $query->where(function ($q) use ($term, $config) {
                foreach (array_keys($config['rules']) as $field) {
                    if (str_contains($config['rules'][$field], 'string') && !in_array($field, ['description'], true)) {
                        $q->orWhere($field, 'like', $term);
                    }
                }
            });
        }

        $items = $query->with($this->with($resource))->latest()->paginate(12)->withQueryString();

        return view('lmd.index', $this->viewData($resource, compact('items')));
    }

    public function create(string $resource)
    {
        $config = $this->config($resource);

        return view('lmd.form', $this->viewData($resource, ['item' => new $config['model']]));
    }

    public function store(Request $request, string $resource)
    {
        $config = $this->config($resource);
        $config['model']::create($request->validate($config['rules']));

        return redirect()->route('lmd.index', $resource)->with('success', 'Enregistrement créé avec succès.');
    }

    public function edit(string $resource, $id)
    {
        $config = $this->config($resource);

        return view('lmd.form', $this->viewData($resource, ['item' => $config['model']::findOrFail($id)]));
    }

    public function update(Request $request, string $resource, $id)
    {
        $config = $this->config($resource);
        $item = $config['model']::findOrFail($id);

        $rules = $config['rules'];
        $rules = $this->makeUniqueRules($rules, $item->getTable(), $item->getKey());

        $item->update($request->validate($rules));

        return redirect()->route('lmd.index', $resource)->with('success', 'Enregistrement mis à jour.');
    }

    public function destroy(string $resource, $id)
    {
        $config = $this->config($resource);
        $config['model']::findOrFail($id)->delete();

        return back()->with('success', 'Enregistrement supprimé.');
    }

    private function makeUniqueRules(array $rules, string $table, $key): array
    {
        $uniques = ['facultes,code', 'facultes,nom', 'batiments,code', 'auditoires,nom', 'domaines,nom', 'filieres,nom', 'mentions,nom', 'annees_academiques,libelle', 'ues,code', 'ecs,code'];

        foreach ($rules as $field => $rule) {
            $parts = explode('|', $rule);
            foreach ($parts as $i => $part) {
                if (str_starts_with($part, 'unique:')) {
                    $target = substr($part, 7);
                    if (in_array($target, $uniques, true)) {
                        $parts[$i] = 'unique:' . $target . ',' . $key;
                    }
                }
            }
            $rules[$field] = implode('|', $parts);
        }

        return $rules;
    }

    private function with(string $resource): array
    {
        return [
            'domaines' => ['faculte'],
            'filieres' => ['domaine'],
            'mentions' => ['filiere.domaine'],
            'promotions' => ['mention.filiere.domaine', 'anneeAcademique'],
            'annees' => [],
            'semestres' => ['anneeAcademique'],
            'ues' => ['promotion', 'semestre'],
            'ecs' => ['ue.promotion'],
        ][$resource];
    }

    private function viewData(string $resource, array $data = []): array
    {
        $config = $this->config($resource);

        return array_merge($data, [
            'resource' => $resource,
            'title' => $config['title'],
            'fields' => $config['rules'],
            'choices' => $config['selects'](),
        ]);
    }

    private function scope($query, string $resource)
    {
        $user = auth()->user();
        if (!$user->isDecanat()) {
            return $query;
        }

        return match ($resource) {
            'domaines' => $query->where('faculte_id', $user->faculte_id),
            'filieres' => $query->whereHas('domaine', fn ($q) => $q->where('faculte_id', $user->faculte_id)),
            'mentions' => $query->whereHas('filiere.domaine', fn ($q) => $q->where('faculte_id', $user->faculte_id)),
            'promotions' => $query->whereHas('mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $user->faculte_id)),
            'ues' => $query->whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $user->faculte_id)),
            'ecs' => $query->whereHas('ue.promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', $user->faculte_id)),
            default => $query,
        };
    }

    private function scopedDomaines()
    {
        return auth()->user()->isDecanat()
            ? Domaine::where('faculte_id', auth()->user()->faculte_id)
            : Domaine::query();
    }

    private function scopedFilieres()
    {
        return auth()->user()->isDecanat()
            ? Filiere::whereHas('domaine', fn ($q) => $q->where('faculte_id', auth()->user()->faculte_id))
            : Filiere::query();
    }

    private function scopedMentions()
    {
        return auth()->user()->isDecanat()
            ? Mention::whereHas('filiere.domaine', fn ($q) => $q->where('faculte_id', auth()->user()->faculte_id))
            : Mention::query();
    }

    private function scopedPromotions()
    {
        return auth()->user()->isDecanat()
            ? Promotion::whereHas('mention.filiere.domaine', fn ($q) => $q->where('faculte_id', auth()->user()->faculte_id))
            : Promotion::query();
    }

    private function scopedUes()
    {
        return auth()->user()->isDecanat()
            ? Ue::whereHas('promotion.mention.filiere.domaine', fn ($q) => $q->where('faculte_id', auth()->user()->faculte_id))
            : Ue::query();
    }
}
