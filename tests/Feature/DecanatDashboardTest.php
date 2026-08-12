<?php

namespace Tests\Feature;

use App\Models\Domaine;
use App\Models\Faculte;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DecanatDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function decanat(): User
    {
        $faculte = Faculte::create([
            'code' => 'FSI',
            'nom' => 'Faculté des Sciences Informatiques',
        ]);

        return User::factory()->create([
            'nom' => 'FSI',
            'prenom' => 'Décanat',
            'email' => 'decanat@fsi.cd',
            'password' => bcrypt('098765'),
            'role' => User::ROLE_DECANAT,
            'status' => User::STATUS_ACCEPTED,
            'is_active' => true,
            'faculte_id' => $faculte->id,
        ]);
    }

    public function test_decanat_can_login_with_nom_and_password(): void
    {
        $this->decanat();

        $response = $this->post('/login', [
            'identifiant' => 'FSI',
            'password' => '098765',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
    }

    public function test_decanat_can_create_and_update_domaine_of_own_faculty(): void
    {
        $user = $this->decanat();

        $this->actingAs($user)->post(route('decanat.domaines.store'), [
            'nom' => 'Sciences du Numérique',
            'description' => 'Domaine test',
            'actif' => 1,
        ])->assertRedirect(route('decanat.domaines.index'));

        $this->assertDatabaseHas('domaines', [
            'nom' => 'Sciences du Numérique',
            'faculte_id' => $user->faculte_id,
        ]);

        $domaine = Domaine::where('nom', 'Sciences du Numérique')->first();

        $this->actingAs($user)->put(route('decanat.domaines.update', $domaine), [
            'nom' => 'Sciences du Numérique 2',
            'description' => 'Modifié',
            'actif' => 1,
        ])->assertRedirect(route('decanat.domaines.index'));

        $this->assertDatabaseHas('domaines', ['nom' => 'Sciences du Numérique 2']);
    }

    public function test_decanat_cannot_see_other_faculty_domaine(): void
    {
        $user = $this->decanat();
        $other = Faculte::create(['code' => 'FST', 'nom' => 'Autre faculté']);
        $domaine = Domaine::create(['faculte_id' => $other->id, 'nom' => 'Secret', 'actif' => true]);

        $this->actingAs($user)->get(route('decanat.domaines.edit', $domaine))->assertStatus(403);
        $this->actingAs($user)->get(route('decanat.domaines.index'))->assertDontSee('Secret');
    }

    public function test_student_cannot_access_decanat_routes(): void
    {
        $faculte = Faculte::create(['code' => 'X', 'nom' => 'X']);
        $student = User::factory()->create([
            'role' => User::ROLE_ETUDIANT,
            'status' => User::STATUS_ACCEPTED,
            'is_active' => true,
            'faculte_id' => $faculte->id,
        ]);

        $this->actingAs($student)->get('/decanat/etudiants')->assertStatus(403);
        $this->actingAs($student)->get('/decanat/domaines')->assertStatus(403);
    }
}
