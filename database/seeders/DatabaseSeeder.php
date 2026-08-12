<?php

namespace Database\Seeders;

use App\Models\AnneeAcademique;
use App\Models\Auditoire;
use App\Models\Batiment;
use App\Models\Cours;
use App\Models\DemandeAuditoire;
use App\Models\Disponibilite;
use App\Models\Domaine;
use App\Models\Ec;
use App\Models\Enseignant;
use App\Models\Etudiant;
use App\Models\Faculte;
use App\Models\Filiere;
use App\Models\Horaire;
use App\Models\Mention;
use App\Models\Promotion;
use App\Models\Semestre;
use App\Models\Ue;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->seedUsers();
        $this->seedInfrastructure();
        $this->seedLmd();
    }

    private function seedUsers(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'noahmenor005@gmail.com'],
            [
                'nom' => 'MENOR',
                'postnom' => 'NOAH',
                'prenom' => 'Admin',
                'matricule' => null,
                'sexe' => 'M',
                'password' => Hash::make('#noah005'),
                'telephone' => '0999000000',
                'role' => User::ROLE_ADMIN,
                'status' => User::STATUS_ACCEPTED,
                'is_active' => true,
                'statut_inscription' => 'actif',
            ]
        );

        $faculte = Faculte::updateOrCreate(
            ['code' => 'FST'],
            ['nom' => 'Faculté des Sciences et Technologies', 'description' => 'Faculté de référence de la plateforme HoraCampus']
        );

        // Deuxième faculté pour tester l'isolation Décanat
        $faculte2 = Faculte::updateOrCreate(
            ['code' => 'FSEG'],
            ['nom' => 'Faculté des Sciences Économiques et de Gestion', 'description' => 'Faculté de test isolation']
        );

        // Faculté FSI — faculté par défaut du compte Décanat FSI
        $faculteFsi = Faculte::updateOrCreate(
            ['code' => 'FSI'],
            ['nom' => 'Faculté des Sciences Informatiques', 'description' => 'Faculté du compte Décanat par défaut FSI']
        );

        // Décanat FST
        $decanatFst = User::updateOrCreate(
            ['email' => 'decanat@fst.cd'],
            [
                'nom' => 'KABUNDI',
                'postnom' => 'MUKENDI',
                'prenom' => 'Jean-Paul',
                'matricule' => 'DEC-FST-001',
                'sexe' => 'M',
                'password' => Hash::make('password'),
                'telephone' => '0991000000',
                'role' => User::ROLE_DECANAT,
                'status' => User::STATUS_ACCEPTED,
                'is_active' => true,
                'statut_inscription' => 'actif',
                'faculte_id' => $faculte->id,
            ]
        );

        User::updateOrCreate(
            ['email' => 'decanat@fseg.cd'],
            [
                'nom' => 'KASONGO',
                'postnom' => '',
                'prenom' => 'Marie',
                'matricule' => 'DEC-FSEG-001',
                'sexe' => 'F',
                'password' => Hash::make('password'),
                'telephone' => '0991000001',
                'role' => User::ROLE_DECANAT,
                'status' => User::STATUS_ACCEPTED,
                'is_active' => true,
                'statut_inscription' => 'actif',
                'faculte_id' => $faculte2->id,
            ]
        );

        // Décanat FSI — compte Décanat créé par défaut (Nom : FSI, mot de passe : 098765)
        User::updateOrCreate(
            ['email' => 'decanat@fsi.cd'],
            [
                'nom' => 'FSI',
                'postnom' => null,
                'prenom' => 'Décanat',
                'matricule' => 'DEC-FSI-001',
                'sexe' => null,
                'password' => Hash::make('098765'),
                'telephone' => '0991000002',
                'role' => User::ROLE_DECANAT,
                'status' => User::STATUS_ACCEPTED,
                'is_active' => true,
                'statut_inscription' => 'actif',
                'faculte_id' => $faculteFsi->id,
            ]
        );

        $enseignant = User::updateOrCreate(
            ['email' => 'enseignant@fst.cd'],
            [
                'nom' => 'KABASELE',
                'postnom' => 'ILUNGA',
                'prenom' => 'Jean',
                'matricule' => 'ENS-FST-001',
                'sexe' => 'M',
                'password' => Hash::make('password'),
                'telephone' => '0992000000',
                'role' => User::ROLE_ENSEIGNANT,
                'status' => User::STATUS_ACCEPTED,
                'is_active' => true,
                'statut_inscription' => 'actif',
                'faculte_id' => $faculte->id,
            ]
        );

        // Étudiant avec matricule unique pour test connexion Nom + Matricule
        User::updateOrCreate(
            ['matricule' => '24XYZ123'],
            [
                'nom' => 'MENOR',
                'postnom' => 'TEST',
                'prenom' => 'Noah',
                'sexe' => 'M',
                'email' => 'etudiant@fst.cd',
                'password' => Hash::make('24XYZ123'), // mot de passe initial = matricule
                'telephone' => '0993000000',
                'role' => User::ROLE_ETUDIANT,
                'status' => User::STATUS_ACCEPTED,
                'is_active' => true,
                'statut_inscription' => 'actif',
                'faculte_id' => $faculte->id,
            ]
        );

        // Étudiant isolation (autre faculté)
        User::updateOrCreate(
            ['matricule' => 'FSEG2024001'],
            [
                'nom' => 'NGOY',
                'postnom' => 'KABEYA',
                'prenom' => 'Patrick',
                'sexe' => 'M',
                'email' => 'etudiant.fseg@fst.cd',
                'password' => Hash::make('FSEG2024001'),
                'telephone' => '0993000001',
                'role' => User::ROLE_ETUDIANT,
                'status' => User::STATUS_ACCEPTED,
                'is_active' => true,
                'statut_inscription' => 'actif',
                'faculte_id' => $faculte2->id,
            ]
        );

        $admin->update(['faculte_id' => $faculte->id]);

        // Miroir dans tables etudiants/enseignants
        try {
            $u = User::where('matricule','24XYZ123')->first();
            if ($u) {
                Etudiant::updateOrCreate(['matricule' => '24XYZ123'], [
                    'user_id' => $u->id,
                    'nom' => $u->nom,
                    'postnom' => $u->postnom,
                    'prenom' => $u->prenom,
                    'sexe' => $u->sexe,
                    'telephone' => $u->telephone,
                    'email' => $u->email,
                    'faculte_id' => $u->faculte_id,
                    'statut' => 'actif',
                    'is_active' => true,
                ]);
            }
            $e = User::where('email','enseignant@fst.cd')->first();
            if ($e) {
                Enseignant::updateOrCreate(['matricule' => 'ENS-FST-001'], [
                    'user_id' => $e->id,
                    'nom' => $e->nom,
                    'postnom' => $e->postnom,
                    'prenom' => $e->prenom,
                    'sexe' => $e->sexe,
                    'telephone' => $e->telephone,
                    'email' => $e->email,
                    'faculte_id' => $e->faculte_id,
                    'statut' => 'actif',
                    'is_active' => true,
                ]);
            }
        } catch (\Throwable $ex) {}
    }

    private function seedLmd(): void
    {
        $annee = AnneeAcademique::updateOrCreate(
            ['libelle' => '2026-2027'],
            ['date_debut' => '2026-09-01', 'date_fin' => '2027-07-31', 'active' => true]
        );

        Semestre::updateOrCreate(['libelle' => 'Semestre 1'], ['annee_academique_id' => $annee->id, 'date_debut' => '2026-09-01', 'date_fin' => '2027-01-31', 'actif' => true]);
        Semestre::updateOrCreate(['libelle' => 'Semestre 2'], ['annee_academique_id' => $annee->id, 'date_debut' => '2027-02-01', 'date_fin' => '2027-07-31', 'actif' => false]);

        $faculte = Faculte::where('code', 'FST')->first();
        $domaine = Domaine::updateOrCreate(['nom' => 'Sciences Exactes'], ['faculte_id' => $faculte->id, 'description' => 'Mathématiques, informatique et sciences de l\'ingénieur']);
        $filiere = Filiere::updateOrCreate(['nom' => 'Informatique'], ['domaine_id' => $domaine->id, 'description' => 'Informatique générale et génie logiciel']);

        $mention = Mention::updateOrCreate(['nom' => 'Licence Informatique'], ['filiere_id' => $filiere->id, 'description' => 'Cycle de licence en informatique']);

        $promotions = [];
        foreach (['L1', 'L2', 'L3'] as $niveau) {
            $promotions[$niveau] = Promotion::updateOrCreate(
                ['mention_id' => $mention->id, 'nom' => "{$niveau} Informatique"],
                ['annee_academique_id' => $annee->id, 'niveau' => $niveau, 'effectif' => 60]
            );
        }

        $enseignant = User::where('email', 'enseignant@fst.cd')->first();
        $etudiant = User::where('matricule', '24XYZ123')->first();

        $etudiant->update([
            'domaine_id' => $domaine->id,
            'filiere_id' => $filiere->id,
            'mention_id' => $mention->id,
            'promotion_id' => $promotions['L1']->id,
            'annee_academique_id' => $annee->id,
        ]);

        try {
            Etudiant::where('matricule','24XYZ123')->update([
                'domaine_id' => $domaine->id,
                'filiere_id' => $filiere->id,
                'mention_id' => $mention->id,
                'promotion_id' => $promotions['L1']->id,
                'annee_academique_id' => $annee->id,
                'faculte_id' => $faculte->id,
            ]);
            // Etudiant FSEG
            $domaine2 = Domaine::updateOrCreate(['nom' => 'Sciences Économiques'], ['faculte_id' => Faculte::where('code','FSEG')->first()->id]);
            $filiere2 = Filiere::updateOrCreate(['nom' => 'Gestion'], ['domaine_id' => $domaine2->id]);
            $mention2 = Mention::updateOrCreate(['nom' => 'Licence Gestion'], ['filiere_id' => $filiere2->id]);
            $prom2 = Promotion::updateOrCreate(['mention_id' => $mention2->id, 'nom' => 'L1 Gestion'], ['annee_academique_id' => $annee->id, 'niveau'=>'L1', 'effectif'=>50]);
            $u2 = User::where('matricule','FSEG2024001')->first();
            if ($u2) {
                $u2->update(['domaine_id'=>$domaine2->id,'filiere_id'=>$filiere2->id,'mention_id'=>$mention2->id,'promotion_id'=>$prom2->id,'annee_academique_id'=>$annee->id]);
                Etudiant::where('matricule','FSEG2024001')->update(['domaine_id'=>$domaine2->id,'filiere_id'=>$filiere2->id,'mention_id'=>$mention2->id,'promotion_id'=>$prom2->id,'annee_academique_id'=>$annee->id,'faculte_id'=>$u2->faculte_id]);
            }
        } catch (\Throwable $e) {}

        $semestre1 = Semestre::where('libelle', 'Semestre 1')->first();

        $ues = [
            ['code' => 'INFO1110', 'nom' => 'Algorithmique et programmation', 'credit' => 8],
            ['code' => 'INFO1120', 'nom' => 'Architecture des ordinateurs', 'credit' => 6],
            ['code' => 'MATH1130', 'nom' => 'Mathématiques discrètes', 'credit' => 5],
            ['code' => 'COM1140', 'nom' => 'Communication académique', 'credit' => 3],
        ];

        $ecs = [];
        foreach ($ues as $index => $ueData) {
            $ue = Ue::updateOrCreate(
                ['code' => $ueData['code'], 'promotion_id' => $promotions['L1']->id],
                ['semestre_id' => $semestre1->id, 'nom' => $ueData['nom'], 'credit' => $ueData['credit']]
            );

            foreach ([['CM', 30], ['TD', 15]] as [$type, $volume]) {
                $ec = Ec::updateOrCreate(
                    ['code' => $ueData['code'] . '-' . $type],
                    ['ue_id' => $ue->id, 'nom' => $ueData['nom'] . ' (' . $type . ')', 'coefficient' => 2, 'volume_horaire' => $volume]
                );
                $ec->enseignants()->syncWithoutDetaching([$enseignant->id]);
                $ecs[$ueData['code'] . '-' . $type] = $ec;
            }
        }

        foreach ([['INFO1110-CM', 'INFO1110-TD'], ['INFO1120-CM', 'INFO1120-TD']] as $i => $pair) {
            [$cm, $td] = $pair;
            Cours::updateOrCreate(
                ['ec_id' => $ecs[$cm]->id, 'promotion_id' => $promotions['L1']->id, 'type' => 'CM'],
                ['enseignant_id' => $enseignant->id, 'volume_horaire' => 30]
            );
            Cours::updateOrCreate(
                ['ec_id' => $ecs[$td]->id, 'promotion_id' => $promotions['L1']->id, 'type' => 'TD'],
                ['enseignant_id' => $enseignant->id, 'volume_horaire' => 15]
            );
        }

        $this->seedDisponibilites($enseignant, $semestre1);
        $this->seedDemandes($promotions['L1'], $enseignant, $semestre1, $ecs, $annee);
        $this->seedHoraires($promotions['L1'], $enseignant, $semestre1, $ecs);
    }

    private function seedInfrastructure(): void
    {
        $batiments = [];
        foreach (['A', 'B'] as $bloc) {
            $batiments[$bloc] = Batiment::updateOrCreate(
                ['code' => "BAT-$bloc"],
                ['nom' => "Bâtiment $bloc — Campus principal", 'adresse' => 'Campus universitaire']
            );

            foreach (range(1, 4) as $i) {
                Auditoire::updateOrCreate(
                    ['nom' => "{$bloc}{$i}01"],
                    ['batiment_id' => $batiments[$bloc]->id, 'capacite' => 40 + $i * 30, 'type' => 'cours', 'equipements' => 'Tableau, projecteur, prise réseau', 'disponibilite' => true, 'etat' => 'disponible']
                );
            }
        }
    }

    private function seedDisponibilites(User $enseignant, Semestre $semestre): void
    {
        $creneaux = [
            ['Lundi', '08:00', '10:00'],
            ['Lundi', '10:00', '12:00'],
            ['Mardi', '08:00', '12:00'],
            ['Mercredi', '10:00', '12:00'],
            ['Vendredi', '08:00', '10:00'],
        ];

        foreach ($creneaux as [$jour, $debut, $fin]) {
            Disponibilite::updateOrCreate(
                ['user_id' => $enseignant->id, 'jour' => $jour, 'heure_debut' => $debut],
                ['semestre_id' => $semestre->id, 'heure_fin' => $fin, 'statut' => Disponibilite::STATUT_VALIDEE]
            );
        }
    }

    private function seedDemandes(Promotion $promotion, User $enseignant, Semestre $semestre, array $ecs, AnneeAcademique $annee): void
    {
        $decanat = User::where('email', 'decanat@fst.cd')->first();
        $auditoires = Auditoire::orderBy('capacite')->get();

        DemandeAuditoire::updateOrCreate(
            ['created_by' => $decanat->id, 'date' => '2026-09-14', 'heure_debut' => '08:00', 'heure_fin' => '10:00'],
            [
                'cours_id' => Cours::where('type', 'CM')->firstOrFail()->id,
                'enseignant_id' => $enseignant->id,
                'promotion_id' => $promotion->id,
                'auditoire_id' => $auditoires->first()?->id,
                'semestre_id' => $semestre->id,
                'effectif_attendu' => 60,
                'statut' => DemandeAuditoire::STATUT_ACCEPTEE,
            ]
        );

        DemandeAuditoire::updateOrCreate(
            ['created_by' => $decanat->id, 'date' => '2026-09-15', 'heure_debut' => '14:00', 'heure_fin' => '16:00'],
            [
                'cours_id' => Cours::where('type', 'TD')->firstOrFail()->id,
                'enseignant_id' => $enseignant->id,
                'promotion_id' => $promotion->id,
                'semestre_id' => $semestre->id,
                'effectif_attendu' => 60,
                'statut' => DemandeAuditoire::STATUT_EN_ATTENTE,
            ]
        );

        DemandeAuditoire::updateOrCreate(
            ['created_by' => $decanat->id, 'date' => '2026-09-18', 'heure_debut' => '08:00', 'heure_fin' => '10:00'],
            [
                'cours_id' => Cours::where('type', 'CM')->firstOrFail()->id,
                'enseignant_id' => $enseignant->id,
                'promotion_id' => $promotion->id,
                'semestre_id' => $semestre->id,
                'effectif_attendu' => 60,
                'statut' => DemandeAuditoire::STATUT_REFUSEE,
                'motif_refus' => 'Auditoire indisponible sur ce créneau (maintenance).',
            ]
        );
    }

    private function seedHoraires(Promotion $promotion, User $enseignant, Semestre $semestre, array $ecs): void
    {
        $auditoires = Auditoire::orderBy('capacite')->get();
        $planning = [
            ['2026-09-14', '08:00', '10:00', 'INFO1110-CM', 'A101'],
            ['2026-09-15', '10:00', '12:00', 'INFO1110-TD', 'A101'],
            ['2026-09-16', '08:00', '10:00', 'INFO1120-CM', 'A201'],
            ['2026-09-17', '14:00', '16:00', 'INFO1120-TD', 'B101'],
            ['2026-09-18', '08:00', '10:00', 'INFO1110-CM', 'A301'],
        ];

        foreach ($planning as [$date, $debut, $fin, $ecCode, $auditoireNom]) {
            $auditoire = $auditoires->first(fn ($a) => $a->nom === $auditoireNom) ?? $auditoires->first();
            $ec = $ecs[$ecCode] ?? null;
            $cours = $ec ? Cours::where('ec_id', $ec->id)->where('promotion_id', $promotion->id)->first() : null;
            if (!$cours) {
                continue;
            }

            Horaire::updateOrCreate(
                ['date' => $date, 'heure_debut' => $debut, 'promotion_id' => $promotion->id],
                [
                    'cours_id' => $cours->id,
                    'auditoire_id' => $auditoire->id,
                    'enseignant_id' => $enseignant->id,
                    'semestre_id' => $semestre->id,
                    'heure_fin' => $fin,
                    'statut' => Horaire::STATUT_VALIDE,
                ]
            );
        }
    }
}
