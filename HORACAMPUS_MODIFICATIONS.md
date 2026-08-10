# HoraCampus — Refonte complète : Gestion Étudiants & Enseignants par le Décanat

## Résumé exécutif
Conformément au cahier des charges, **les étudiants et enseignants ne créent plus eux-mêmes leurs comptes**. **Seul le Décanat** de la faculté peut les enregistrer, et **uniquement pour sa propre faculté**. La connexion étudiante se fait par **Nom + Matricule** (matricule unique), sans email obligatoire. Toute tentative d’accès hors faculté ou hors rôle retourne **403**.

---

## 1. Modifications structurelles

### Migrations

- **2026_08_10_000001_enhance_users_table.php**
  - Ajoute `postnom`, `matricule` (unique), `sexe` (M/F/Autre), `annee_academique_id` (FK), `statut_inscription` (actif/inactif), `is_active` (bool).
  - Rend `email` nullable (via doctrine/dbal, avec fallback SQLite).
  - Index unique sur `matricule` (NULL autorisé, SQLite gère la distinctivité).

- **2026_08_10_000002_create_etudiants_and_enseignants_tables.php**
  - Crée `etudiants` et `enseignants` (tables miroirs) avec FK `user_id`, matricule unique, hiérarchie LMD complète, `statut`/`is_active`.
  - Permet la phrase spec : *« Le matricule doit être UNIQUE dans la table etudiants »* tout en gardant `users` comme table d’authentification principale (principe *single source of truth* + miroir).

> **Compatibilité Laravel 8 + SQLite** : ajout de `doctrine/dbal ^3.5` dans `composer.json` pour permettre `->change()` et gestion des erreurs SQLite avec fallback.

### Modèles

- **User.php** : nouveaux fillable/casts (`postnom`, `matricule`, `sexe`, `annee_academique_id`, `is_active`, `statut_inscription`), relations `anneeAcademique`, `etudiantProfile`, `enseignantProfile`, helpers `isActive()`, `sexeLabel()`, `belongsToFaculty()`.
- **Etudiant.php** / **Enseignant.php** (nouveaux) : modèles Eloquent pour les tables miroirs, relations LMD complètes.

### Factories

- **UserFactory** : ajoute `postnom`, `matricule`, `sexe`, `is_active`, `statut_inscription`.
- **EtudiantFactory** / **EnseignantFactory** : alignés sur nouvelles tables (matricule, sexe, faculte, etc.).
- **PromotionFactory** : corrigée pour utiliser `mention_id` + `annee_academique_id` (au lieu de l’ancien `departement_id`).
- **Nouvelles factories** : `DomaineFactory`, `FiliereFactory`, `MentionFactory`, `AnneeAcademiqueFactory`.

---

## 2. Authentification

### LoginRequest
Accepte **soit** `email+password` (Admin/Décanat/Enseignant) **soit** `nom+matricule` (Étudiant) via `required_without`. Le champ `password` devient optionnel pour l’étudiant (matricule = mot de passe initial).

### AuthController
- **Suppression** de `registerForm()` / `register()` et de toute inscription publique.
- **login()** :
  - Si `nom+matricule` → recherche `User` role=etudiant, nom insensible à la casse + matricule exact, vérifie `is_active`/`status`, optionnellement vérifie `password` si fourni, puis `Auth::login()` et `last_login_at`.
  - Sinon `email+password` classique pour Admin/Décanat/Enseignant (et étudiant avec email).
  - Sécurisé via `Hash::check` et `regenerate()` session.
- Conservation des helpers `domainesParFaculte` etc. avec **filtrage faculté** pour le Décanat.

### Vue login
Remplacement total de `auth/login.blade.php` :
- 2 onglets Bootstrap Pills : **Personnel** (email/password) et **Étudiant** (Nom + Matricule + password optionnel).
- Suppression du lien *« Créer un compte étudiant »*.
- Bandeau d’info rappelant que les comptes sont créés par le Décanat.

---

## 3. Contrôle d’accès

### Middleware & Routes
- `routes/web.php` : suppression des routes `register`, regroupement **`role:decanat`** exclusif pour `etudiants` & `enseignants` (index, create, store, show, edit, update, destroy, desactiver/reactiver). Ajout d’alias `/decanat/etudiants` pour la spec *« /decanat/etudiants doit retourner 403 pour un étudiant »*.
- `RoleMiddleware` inchangé mais utilisé strictement : étudiant → 403 sur `/etudiants`.
- **UserRequest** : `role` limité à `[admin,decanat]` ; message explicite *« L’administrateur ne peut créer que des comptes Décanat »*.
- **ProfileRequest** + **ProfileController** : étudiant ne peut modifier que `nom/prenom/postnom/telephone/photo` — blocage côté serveur de `matricule`, `faculte_id`, `promotion_id`, etc.

### Policies
- **EtudiantPolicy** / **EnseignantPolicy** : `viewAny`, `view`, `create`, `update`, `delete` vérifient `faculte_id` du Décanat. Admin ne bypass plus pour ces ressources.
- **AuthServiceProvider** : `Gate::before` détecte si la cible est un `User` étudiant/enseignant et **ne bypass pas** l’admin dans ce cas. L’admin reste super-admin pour `Horaire`, `Demande`, etc.
- **ScopesFaculty** trait utilisé partout pour filtrer `Domaine/Filiere/Mention/Promotion` selon `faculte_id`.

---

## 4. Gestion Décanat

### EtudiantController (refonte totale)
- `index` : pagination + recherche (`q` sur nom/postnom/prenom/matricule/email/tel), filtres `promotion_id`, `sexe`, `is_active`, scoping faculté.
- `show` : consultation seule, abort 403 si autre faculté.
- `create/store` :
  - FormRequest `EtudiantRequest` (validation hiérarchique + `matricule` unique).
  - Force `faculte_id = auth()->user()->faculte_id` (ne jamais faire confiance au navigateur).
  - Vérifie **Domaine→Filière→Mention→Promotion** cohérence.
  - Génère compte : `password = Hash::make(matricule)`, `role=etudiant`, `status=accepted`, `is_active=true`.
  - Email facultatif → `null` ou placeholder `matricule@etudiant.horacampus.local` si contrainte NOT NULL.
  - Crée miroir `Etudiant` dans table `etudiants`.
  - Message flash avec matricule + mot de passe à communiquer.
- `edit/update` : même vérifs, bloque modification inter-faculté.
- `desactiver/reactiver/destroy` : utilise `is_active` + `status`.

### EnseignantController (refonte)
- Même principe : `matricule` auto-généré si vide, password = matricule, `email` obligatoire, `faculte_id` forcé, création miroir `Enseignant`, sync `ec_ids` filtrés par faculté.

### Formulaires (Bootstrap 5)
- **etudiants/form.blade.php** : champs Nom/Postnom/Prénom, Matricule unique, Sexe, Téléphone, Email facultatif, Statut, Faculté (désactivée pour Décanat), Domaine→Filière→Mention→Promotion→Année académique en **listes déroulantes dépendantes** (JS `fetch` sur `/api/...` filtrés côté serveur).
- **enseignants/form.blade.php** : Nom/Postnom/Prénom, Matricule, Sexe, Téléphone, Grade, Email, Faculté, Spécialité, EC multiples.
- **index** : tables avec matricule badge, recherche, pagination, boutons *Consulter/Modifier/Désactiver/Supprimer*, messages explicites.

### Vues show
- **etudiants/show.blade.php** et **enseignants/show.blade.php** : fiches complètes, parcours LMD, horaires, disponibilités.

---

## 5. Tableaux de bord

- **layouts/app.blade.php** : sidebar dynamique par rôle :
  - **Décanat** : Tableau de bord, Étudiants, Enseignants, Promotions, Cours, Disponibilités, Demandes, Organisation LMD (Domaines, Filières, Mentions, Années acad., Semestres, UE, EC), Horaires, Historique.
  - **Étudiant** (simple) : Horaires, Cours, Enseignants, Promotion, Salles — *aucun bouton de modification*.
  - **Enseignant** : Disponibilités, Emploi du temps, UE/EC, Cours, Salles.
  - **Admin** : Facultés, Bâtiments, Auditoires, Utilisateurs/Décanats, Rapports, supervision globale.
- **dashboard/etudiant.blade.php** : refonte très simple, consultation seule, infos matricule/promotion/faculté, prochain cours, emploi du temps semaine, liste cours, enseignants, salles — bandeau *« Vous ne pouvez modifier aucune donnée académique »*.
- **dashboard/enseignant.blade.php** et **decanat.blade.php** : enrichis, stats filtrées par faculté.

---

## 6. Base de données & Seeders

- **DatabaseSeeder** : crée 2 facultés (FST + FSEG) pour tester l’isolation, 2 Décanats, 1 enseignant, 2 étudiants avec matricules `24XYZ123` (FST) et `FSEG2024001` (isolation), hiérarchie LMD complète, mises à jour des `etudiants`/`enseignants` miroirs, horaires, disponibilités, demandes.
- **HoraireConflictTest** : réécrit pour utiliser Domaine/Filiere/Mention/Promotion/Ue/Ec/Batiment et nouveaux champs Horaire.

---

## 7. Sécurité (points clés)

- Toutes les autorisations vérifiées **côté serveur** (`EtudiantRequest::withValidator`, `isScoped()`, `abort(403)`).
- `faculte_id` forcé depuis `auth()->user()->faculte_id` — IDs du navigateur ignorés.
- `matricule` unique validé par `Rule::unique` + index DB.
- Double protection : FormRequest + Policy + Middleware `role`.
- Tests de la hiérarchie LMD dans `EtudiantRequest`.

---

## 8. Interface

- Formulaires Bootstrap 5 professionnels, `form-control-lg`, `form-select`, badges, alerts, `surface` cards, ombres, animations.
- Listes dépendantes : `Faculté → Domaine → Filière → Mention → Promotion` avec fetch JS et filtrage serveur.

---

## 9. Fichiers modifiés / créés

**Migrations**
- `database/migrations/2026_08_10_000001_enhance_users_table.php`
- `database/migrations/2026_08_10_000002_create_etudiants_and_enseignants_tables.php`

**Modèles**
- `app/Models/User.php` (modifié)
- `app/Models/Etudiant.php` (nouveau)
- `app/Models/Enseignant.php` (nouveau)

**Requests**
- `app/Http/Requests/LoginRequest.php` (modifié)
- `app/Http/Requests/EtudiantRequest.php` (nouveau)
- `app/Http/Requests/EnseignantRequest.php` (nouveau)
- `app/Http/Requests/ProfileRequest.php` (modifié)
- `app/Http/Requests/UserRequest.php` (modifié)

**Policies & Provider**
- `app/Policies/EtudiantPolicy.php` (nouveau)
- `app/Policies/EnseignantPolicy.php` (nouveau)
- `app/Providers/AuthServiceProvider.php` (modifié)

**Controllers**
- `app/Http/Controllers/AuthController.php` (modifié)
- `app/Http/Controllers/EtudiantController.php` (modifié)
- `app/Http/Controllers/EnseignantController.php` (modifié)
- `app/Http/Controllers/DashboardController.php` (modifié)
- `app/Http/Controllers/ProfileController.php` (modifié)

**Routes**
- `routes/web.php` (modifié)

**Vues**
- `resources/views/auth/login.blade.php` (modifié)
- `resources/views/layouts/app.blade.php` (modifié)
- `resources/views/etudiants/index.blade.php` (modifié)
- `resources/views/etudiants/form.blade.php` (modifié)
- `resources/views/etudiants/show.blade.php` (nouveau)
- `resources/views/enseignants/index.blade.php` (modifié)
- `resources/views/enseignants/form.blade.php` (modifié)
- `resources/views/enseignants/show.blade.php` (nouveau)
- `resources/views/dashboard/etudiant.blade.php` (modifié)
- `resources/views/dashboard/enseignant.blade.php` (modifié)
- `resources/views/dashboard/decanat.blade.php` (modifié)

**Seeders & Factories**
- `database/seeders/DatabaseSeeder.php` (modifié)
- `database/factories/UserFactory.php` (modifié)
- `database/factories/EtudiantFactory.php` (modifié)
- `database/factories/EnseignantFactory.php` (modifié)
- `database/factories/PromotionFactory.php` (modifié)
- `database/factories/DomaineFactory.php` (nouveau)
- `database/factories/FiliereFactory.php` (nouveau)
- `database/factories/MentionFactory.php` (nouveau)
- `database/factories/AnneeAcademiqueFactory.php` (nouveau)

**Config**
- `composer.json` (doctrine/dbal)
- `phpunit.xml` (sqlite :memory:)
- `.env.example` (sqlite par défaut)
- `tests/Feature/HoraireConflictTest.php` (corrigé)

---

## 9. Commandes Laravel à exécuter

### Installation initiale
```bash
git clone <repo> horacampus_project
cd horacampus_project

# Dépendances (ajout doctrine/dbal pour SQLite)
composer install

# Environnement
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
```

### Base de données & Seed
```bash
# Vérifier que .env contient :
# DB_CONNECTION=sqlite
# DB_DATABASE=database/database.sqlite
# DB_FOREIGN_KEYS=true

php artisan migrate:fresh --seed
# Attendu : toutes les migrations passent sans erreur, seed crée :
# - Admin : noahmenor005@gmail.com / #noah005
# - Décanat FST : decanat@fst.cd / password (faculté FST)
# - Décanat FSEG : decanat@fseg.cd / password (isolation)
# - Enseignant : enseignant@fst.cd / password
# - Étudiant : MENOR / 24XYZ123 (matricule = password initial) — email etudiant@fst.cd / password
# - Promotions, UE, EC, Cours, Auditoires, Horaires, Demandes, Disponibilités
```

### Lancement
```bash
php artisan serve --host=0.0.0.0 --port=8000
# Ouvrir http://localhost:8000
```

### Tests automatisés
```bash
php artisan test
# ou
vendor/bin/phpunit
# Les tests utilisent sqlite :memory: (phpunit.xml)
```

### Vérifications manuelles des 10 scénarios
```bash
# 1. Décanat crée un étudiant → SUCCESS
# Connexion : decanat@fst.cd / password
# Aller dans Gestion des étudiants → Ajouter, remplir :
# Nom=MENOR Postnom=KABILA Prénom=Jean Matricule=24ABC999 Sexe=M Tel=0990000001
# Faculté=FST (forcée) Domaine=Sciences Exactes Filière=Informatique Mention=Licence Info Promotion=L1 Informatique Année=2026-2027 Statut=actif
# Enregistrer → message avec mot de passe = matricule

# 2. Décanat crée un enseignant → SUCCESS
# Gestion des enseignants → Ajouter, Nom=TSHIBANDA Prenom=Paul Email=paul.tshib@fst.cd Faculté=FST → Enregistrer → identifiants affichés

# 3. Deuxième étudiant même matricule → REFUSÉ
# Réessayer avec Matricule=24XYZ123 → erreur « Ce matricule est déjà utilisé. »

# 4. Étudiant essaie de créer un autre étudiant → REFUSÉ
# Connexion étudiant : Nom=MENOR Matricule=24XYZ123 (onglet Étudiant)
# GET /etudiants → 403
# GET /etudiants/creer → 403
# POST /etudiants → 403

# 5. Étudiant essaie d'accéder au dashboard Décanat → REFUSÉ
# GET /decanat/etudiants → 403

# 6. Étudiant se connecte avec Nom + Matricule → SUCCESS
# Login onglet Étudiant : Nom=MENOR Matricule=24XYZ123 → redirection /dashboard (vue étudiant simple)

# 7. Étudiant mauvais matricule → REFUSÉ
# Nom=MENOR Matricule=FAUX123 → « Aucun étudiant trouvé... »

# 8. Étudiant consulte son emploi du temps → SUCCESS
# GET /horaires → 200, voir les 5 horaires de L1 Informatique

# 9. Décanat essaie de consulter étudiant d'une autre faculté → REFUSÉ
# Connexion decanat@fst.cd → GET /etudiants/{id de FSEG2024001} → 403 (abort)

# 10. Administrateur peut gérer les Décanats et les salles → SUCCESS
# Connexion noahmenor005@gmail.com / #noah005
# GET /utilisateurs → 200, création Décanat uniquement
# GET /batiments, /auditoires → 200
# GET /etudiants → 403 (admin ne gère pas direct)
```

### Commandes utiles
```bash
php artisan route:list | grep -E "etudiant|enseignant|login"
php artisan migrate:status
php artisan config:clear
php artisan view:clear
```

---

## 10. Notes de compatibilité

- **PHP 8 / Laravel 8 / SQLite** validé : migrations utilisent `constrained()->nullOnDelete()` et `unique()` compatibles SQLite.
- **Migrate:fresh** : détruit et reconstruit tout, **aucune colonne en double** grâce aux `hasColumn`/`hasTable` checks.
- **Relations Eloquent** : User ↔ Faculte/Domaine/Filiere/Mention/Promotion/AnneeAcademique + HasOne Etudiant/Enseignant.
- Aucune **architecture parallèle** : réutilisation des modèles `User`, `Promotion`, `Faculte`, etc., seules les tables miroirs ont été ajoutées pour conformité stricte du cahier des charges.

Le système final est **propre, sécurisé, cohérent** et prêt pour `php artisan migrate:fresh --seed`.
