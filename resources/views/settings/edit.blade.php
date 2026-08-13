@extends('layouts.app')

@section('title', 'Paramètres')

@section('content')
<div class="surface p-4">
    <h1 class="h4 mb-3">Paramètres de la plateforme</h1>
    <form method="POST" action="{{ route('settings.update') }}">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nom de l’université</label>
                <input name="nom_universite" value="{{ old('nom_universite', $settings['nom_universite']) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email de contact</label>
                <input type="email" name="email_contact" value="{{ old('email_contact', $settings['email_contact']) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Téléphone</label>
                <input name="telephone" value="{{ old('telephone', $settings['telephone']) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Adresse</label>
                <input name="adresse" value="{{ old('adresse', $settings['adresse']) }}" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label">Début de journée</label>
                <input type="time" name="heure_debut_journee" value="{{ old('heure_debut_journee', $settings['heure_debut_journee']) }}" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fin de journée</label>
                <input type="time" name="heure_fin_journee" value="{{ old('heure_fin_journee', $settings['heure_fin_journee']) }}" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label">Message d’accueil</label>
                <input name="message_accueil" value="{{ old('message_accueil', $settings['message_accueil']) }}" class="form-control">
            </div>
        </div>
        <button class="btn btn-primary mt-4">Enregistrer</button>
    </form>
</div>
@endsection
