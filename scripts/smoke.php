<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DemandeAuditoire;
use App\Models\Domaine;
use App\Models\Horaire;
use App\Models\User;
use App\Services\HoraireService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

$failed = 0;
function check(string $label, $ok): void
{
    global $failed;
    echo ($ok ? '[OK]  ' : '[FAIL] ') . $label . PHP_EOL;
    if (!$ok) {
        $failed++;
    }
}

$admin = User::where('email', 'noahmenor005@gmail.com')->first();
check('Admin NOAH MENOR exists', $admin && $admin->prenom === 'NOAH' && $admin->nom === 'MENOR');
check('Admin password hashed and valid', $admin && Hash::check('#noah005', $admin->password));
check('Admin status accepted', $admin && $admin->status === 'accepted' && $admin->role === 'admin');

$fsi = User::where('nom', 'FSI')->where('role', 'decanat')->first();
check('Decanat FSI exists', (bool) $fsi);
check('Decanat FSI password 098765', $fsi && Hash::check('098765', $fsi->password));
check('Decanat FSI accepted and linked to faculty', $fsi && $fsi->status === 'accepted' && $fsi->faculte_id);

$student = User::where('matricule', 'FSI2024001')->first();
check('Student FSI exists', (bool) $student);

$kernelHttp = $app->make(Illuminate\Contracts\Http\Kernel::class);

function hit($kernelHttp, $method, $uri, $user = null, $data = [])
{
    $request = Illuminate\Http\Request::create($uri, $method, $data);
    if ($user) {
        Auth::login($user);
        $request->setUserResolver(fn () => $user);
        $request->setLaravelSession(app('session.store'));
        app('session')->start();
    } else {
        Auth::logout();
    }
    $response = $kernelHttp->handle($request);
    Auth::logout();
    return $response->getStatusCode();
}

check('Home page 200', hit($kernelHttp, 'GET', '/') === 200);
check('Login page 200', hit($kernelHttp, 'GET', '/login') === 200);

$adminReq = function ($method, $uri) use ($kernelHttp, $admin) {
    return hit($kernelHttp, $method, $uri, $admin);
};

check('Admin dashboard 200', $adminReq('GET', '/dashboard') === 200);
check('Admin facultes 200', $adminReq('GET', '/facultes') === 200);
check('Admin batiments 200', $adminReq('GET', '/batiments') === 200);
check('Admin auditoires 200', $adminReq('GET', '/auditoires') === 200);
check('Admin decanats 200', $adminReq('GET', '/decanats') === 200);
check('Admin demandes 200', $adminReq('GET', '/demandes') === 200);
check('Admin attributions 200', $adminReq('GET', '/attributions') === 200);
check('Admin users 200', $adminReq('GET', '/utilisateurs') === 200);
check('Admin rapports 200', $adminReq('GET', '/rapports') === 200);
check('Admin settings 200', $adminReq('GET', '/parametres') === 200);
check('Admin cannot open etudiants', in_array(hit($kernelHttp, 'GET', '/etudiants', $admin), [403, 404], true));

check('Decanat dashboard 200', hit($kernelHttp, 'GET', '/decanat/dashboard', $fsi) === 200);
check('Decanat domaines 200', hit($kernelHttp, 'GET', '/decanat/domaines', $fsi) === 200);
check('Decanat filieres 200', hit($kernelHttp, 'GET', '/decanat/filieres', $fsi) === 200);
check('Decanat mentions 200', hit($kernelHttp, 'GET', '/decanat/mentions', $fsi) === 200);
check('Decanat promotions 200', hit($kernelHttp, 'GET', '/decanat/promotions', $fsi) === 200);
check('Decanat ues 200', hit($kernelHttp, 'GET', '/decanat/ues', $fsi) === 200);
check('Decanat ecs 200', hit($kernelHttp, 'GET', '/decanat/ecs', $fsi) === 200);
check('Decanat etudiants 200', hit($kernelHttp, 'GET', '/decanat/etudiants', $fsi) === 200);
check('Decanat enseignants 200', hit($kernelHttp, 'GET', '/decanat/enseignants', $fsi) === 200);
check('Decanat horaires 200', hit($kernelHttp, 'GET', '/decanat/horaires', $fsi) === 200);
check('Decanat demandes 200', hit($kernelHttp, 'GET', '/decanat/demandes-salles', $fsi) === 200);

check('Student blocked on decanat etudiants', hit($kernelHttp, 'GET', '/decanat/etudiants', $student) === 403);
check('Student dashboard 200', hit($kernelHttp, 'GET', '/dashboard', $student) === 200);
check('Student horaires 200', hit($kernelHttp, 'GET', '/horaires', $student) === 200);

$enseignant = User::where('email', 'enseignant@fsi.cd')->first();
check('Teacher dashboard 200', $enseignant && hit($kernelHttp, 'GET', '/dashboard', $enseignant) === 200);

$existing = Horaire::where('enseignant_id', $enseignant->id)->first();
$conflicts = app(HoraireService::class)->conflictsFor([
    'date' => optional($existing->date)->format('Y-m-d'),
    'heure_debut' => $existing->heure_debut,
    'heure_fin' => $existing->heure_fin,
    'enseignant_id' => $existing->enseignant_id,
    'promotion_id' => $existing->promotion_id,
    'ec_id' => $existing->ec_id,
    'auditoire_id' => $existing->auditoire_id,
]);
check('Conflict detection returns messages', !empty($conflicts));
check('Conflict message mentions occupé', collect($conflicts)->contains(fn ($m) => str_contains($m, 'occup')));

$otherDomaine = Domaine::whereHas('faculte', fn ($q) => $q->where('code', '!=', 'FSI'))->first();
$index = hit($kernelHttp, 'GET', '/decanat/domaines', $fsi);
check('Isolation: FSI domaines page loads', $index === 200);

echo PHP_EOL . ($failed === 0 ? 'ALL CHECKS PASSED' : "$failed CHECK(S) FAILED") . PHP_EOL;
exit($failed ? 1 : 0);
