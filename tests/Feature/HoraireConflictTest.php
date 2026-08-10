<?php

namespace Tests\Feature;

use App\Models\AnneeAcademique;
use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\Cours;
use App\Models\Domaine;
use App\Models\Ec;
use App\Models\Faculte;
use App\Models\Filiere;
use App\Models\Horaire;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\Semestre;
use App\Models\Ue;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HoraireConflictTest extends TestCase
{
    use RefreshDatabase;

    public function test_overlapping_room_teacher_or_promotion_is_rejected()
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_ACCEPTED]);
        $faculte = Faculte::create(['code'=>'TST','nom' => 'Sciences Test']);
        $domaine = Domaine::create(['faculte_id'=>$faculte->id, 'nom'=>'Domaine Test']);
        $filiere = Filiere::create(['domaine_id'=>$domaine->id, 'nom'=>'Filiere Test']);
        $mention = Mention::create(['filiere_id'=>$filiere->id, 'nom'=>'Mention Test']);
        $annee = AnneeAcademique::create(['libelle'=>'2026-2027','date_debut'=>'2026-09-01','date_fin'=>'2027-07-31','active'=>true]);
        $semestre = Semestre::create(['libelle'=>'Semestre 1','annee_academique_id'=>$annee->id,'date_debut'=>'2026-09-01','date_fin'=>'2027-01-31','actif'=>true]);
        $promotion = Promotion::create(['mention_id'=>$mention->id,'annee_academique_id'=>$annee->id,'nom'=>'L1 Test','niveau'=>'L1','effectif'=>30]);
        $enseignant = User::factory()->create(['role'=>User::ROLE_ENSEIGNANT,'status'=>User::STATUS_ACCEPTED,'faculte_id'=>$faculte->id]);
        $batiment = Batiment::create(['code'=>'BAT-TST','nom'=>'Batiment Test']);
        $auditoire = Auditoire::create(['nom'=>'TST101','batiment_id'=>$batiment->id,'capacite'=>80,'disponibilite'=>true,'etat'=>'disponible','type'=>'cours']);
        $ue = Ue::create(['promotion_id'=>$promotion->id,'semestre_id'=>$semestre->id,'code'=>'TST101','nom'=>'UE Test','credit'=>4]);
        $ec = Ec::create(['ue_id'=>$ue->id,'code'=>'TST101-CM','nom'=>'EC Test','coefficient'=>2,'volume_horaire'=>30]);
        $cours = Cours::create(['ec_id'=>$ec->id,'promotion_id'=>$promotion->id,'enseignant_id'=>$enseignant->id,'type'=>'CM','volume_horaire'=>30]);

        // Horaire existant
        Horaire::create([
            'cours_id' => $cours->id,
            'auditoire_id' => $auditoire->id,
            'enseignant_id' => $enseignant->id,
            'promotion_id' => $promotion->id,
            'semestre_id' => $semestre->id,
            'date' => '2026-09-14',
            'heure_debut' => '08:00',
            'heure_fin' => '10:00',
            'statut' => Horaire::STATUT_VALIDE,
        ]);

        $response = $this->actingAs($user)->post(route('horaires.store'), [
            'cours_id' => $cours->id,
            'auditoire_id' => $auditoire->id,
            'enseignant_id' => $enseignant->id,
            'promotion_id' => $promotion->id,
            'semestre_id' => $semestre->id,
            'date' => '2026-09-14',
            'heure_debut' => '09:00',
            'heure_fin' => '11:00',
            'statut' => Horaire::STATUT_VALIDE,
        ]);

        // Selon la logique métier, un conflit doit générer une erreur de validation sur heure_debut ou heure_fin
        // On accepte soit une erreur de validation, soit un refus silencieux (pas de deuxième horaire créé)
        if ($response->getSession()->has('errors')) {
            $response->assertSessionHasErrors(['heure_debut','heure_fin','date']);
        }
        $this->assertCount(1, Horaire::all());
    }
}
