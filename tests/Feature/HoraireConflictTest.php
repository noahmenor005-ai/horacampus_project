<?php

namespace Tests\Feature;

use App\Models\Auditoire;
use App\Models\Cours;
use App\Models\Departement;
use App\Models\Enseignant;
use App\Models\Faculte;
use App\Models\Horaire;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoraireConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlapping_room_teacher_or_promotion_is_rejected()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $faculte = Faculte::create(['nom' => 'Sciences']);
        $departement = Departement::create(['nom' => 'Informatique', 'faculte_id' => $faculte->id]);
        $promotion = Promotion::create(['nom' => 'L1 Info', 'niveau' => 'L1', 'departement_id' => $departement->id]);
        $enseignant = Enseignant::create(['nom' => 'Ilunga', 'prenom' => 'Marie', 'email' => 'marie@example.com']);
        $auditoire = Auditoire::create(['nom' => 'A101', 'capacite' => 80, 'disponibilite' => true]);
        $cours = Cours::create(['nom' => 'Algorithmique', 'code' => 'ALG101', 'credit' => 4, 'enseignant_id' => $enseignant->id, 'promotion_id' => $promotion->id]);

        Horaire::create([
            'cours_id' => $cours->id,
            'auditoire_id' => $auditoire->id,
            'enseignant_id' => $enseignant->id,
            'promotion_id' => $promotion->id,
            'jour' => 'Lundi',
            'heure_debut' => '08:00',
            'heure_fin' => '10:00',
            'semestre' => 'Semestre 1',
            'annee_academique' => '2026-2027',
        ]);

        $response = $this->actingAs($user)->post(route('horaires.store'), [
            'cours_id' => $cours->id,
            'auditoire_id' => $auditoire->id,
            'enseignant_id' => $enseignant->id,
            'promotion_id' => $promotion->id,
            'jour' => 'Lundi',
            'heure_debut' => '09:00',
            'heure_fin' => '11:00',
            'semestre' => 'Semestre 1',
            'annee_academique' => '2026-2027',
        ]);

        $response->assertSessionHasErrors('heure_debut');
        $this->assertCount(1, Horaire::all());
    }
}
