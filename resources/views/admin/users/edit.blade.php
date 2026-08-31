@extends('layouts.app')

@section('title', 'Modifier un utilisateur')
@section('page-title', 'Administration - Modifier un utilisateur')

@section('content')
<div class="card">
    <div class="card-head">
        <h2 class="title">Modifier le compte</h2>
        <div class="toolbar">
            <a class="btn sec" href="{{ route('admin.users.index') }}">← Retour à la liste</a>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label>Employé *
                    <select name="employe_id" required>
                        <option value="">— Sélectionner un employé —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ old('employe_id', $user->employe_id) == $emp->id ? 'selected' : '' }}>
                                {{ $emp->prenom_nom }}{{ $emp->email ? ' — '.$emp->email : '' }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="form-group">
                <label>Email *
                    <input type="email" name="email" required placeholder="email@example.com" value="{{ old('email', $user->email) }}">
                </label>
            </div>

            <div class="form-group">
                <label>Rôle *
                    <select name="spatie_role" id="roleSelect" required>
                        <option value="">— Sélectionner un rôle —</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('spatie_role', $user->getRoleNames()->first()) == $role->name ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div id="roleInfo" style="display:none; margin: 16px 0; padding: 16px; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px;">
                <strong style="color: #0369a1;">📋 Permissions de ce rôle:</strong>
                <ul id="permissionsList" style="margin: 8px 0 0 0; padding-left: 20px; color: #0c4a6e; line-height: 1.8;"></ul>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 8px;">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                    Compte actif
                </label>
            </div>

            <div style="margin: 20px 0; padding: 12px; background: #fef3c7; border: 1px solid #fbbf24; border-radius: 8px; color: #92400e;">
                <strong>Note:</strong> Pour changer le mot de passe, utilisez le bouton "Réinitialiser le mot de passe" dans la liste des utilisateurs.
            </div>

            <div class="actions-inline" style="justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('admin.users.index') }}" class="btn sec">Annuler</a>
                <button type="submit" class="btn">Enregistrer les modifications</button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
const rolePermissions = {
    'Admin': [
        'Accès complet à tous les projets',
        'Gestion des utilisateurs (créer, modifier, supprimer)',
        'Synchronisation MS Planner et Salesforce',
        'Suppression de projets',
        'Accès à tous les documents'
    ],
    'Project Admin': [
        'Accès complet aux projets',
        'Synchronisation MS Planner et Salesforce',
        'Suppression de projets',
        'Pas de gestion des utilisateurs'
    ],
    'Manager': [
        'Voir les projets de sa filiale',
        'Créer et modifier ses propres projets',
        'Télécharger des documents',
        'Synchronisation MS Planner et Salesforce',
        'Duplication de projets'
    ],
    'User': [
        'Voir ses propres projets uniquement',
        'Créer et modifier ses propres projets',
        'Télécharger des documents',
        'Recherche Salesforce'
    ]
};

document.getElementById('roleSelect').addEventListener('change', function() {
    const role = this.value;
    const infoBox = document.getElementById('roleInfo');
    const permList = document.getElementById('permissionsList');

    if (role && rolePermissions[role]) {
        permList.innerHTML = rolePermissions[role]
            .map(p => `<li>${p}</li>`)
            .join('');
        infoBox.style.display = 'block';
    } else {
        infoBox.style.display = 'none';
    }
});

if (document.getElementById('roleSelect').value) {
    document.getElementById('roleSelect').dispatchEvent(new Event('change'));
}
</script>
@endsection
@endsection
