@extends('layouts.app')

@section('title', 'Créer un utilisateur')
@section('page-title', 'Administration - Créer un utilisateur')

@section('content')
<div class="card">
    <div class="card-head">
        <h2 class="title">Créer un nouveau compte</h2>
        <div class="toolbar">
            <a class="btn sec" href="{{ route('admin.users.index') }}">← Retour à la liste</a>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf

            <div class="form-group">
                <label>Employé *
                    <select name="employe_id" id="employeSelect" required>
                        <option value="">— Sélectionner un employé —</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}"
                                    data-email="{{ $emp->email ?? '' }}"
                                    {{ old('employe_id') == $emp->id ? 'selected' : '' }}>
                                {{ $emp->prenom_nom }}{{ $emp->email ? ' — '.$emp->email : '' }}
                            </option>
                        @endforeach
                    </select>
                </label>
            </div>

            <div class="form-group">
                <label>Email *
                    <input type="email" name="email" id="emailInput" required placeholder="email@example.com" value="{{ old('email') }}">
                </label>
            </div>

            <div style="margin: 16px 0; padding: 12px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; font-size: 14px; color: #1e40af;">
                <strong>📧 Configuration du mot de passe</strong><br>
                Un email sera automatiquement envoyé à l'utilisateur avec un lien sécurisé pour créer son mot de passe.
            </div>

            <div class="form-group">
                <label>Rôle *
                    <select name="spatie_role" id="roleSelect" required>
                        <option value="">— Sélectionner un rôle —</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('spatie_role') == $role->name ? 'selected' : '' }}>
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
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                    Compte actif
                </label>
            </div>

            <div class="actions-inline" style="justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('admin.users.index') }}" class="btn sec">Annuler</a>
                <button type="submit" class="btn">Créer le compte</button>
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

// Auto-fill email when employee is selected
document.getElementById('employeSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const email = selectedOption.getAttribute('data-email');
    const emailInput = document.getElementById('emailInput');

    if (email && email.trim() !== '') {
        emailInput.value = email;
        emailInput.style.background = '#f0f9ff';
        emailInput.style.borderColor = '#0ea5e9';

        // Reset styles after 1 second
        setTimeout(() => {
            emailInput.style.background = '';
            emailInput.style.borderColor = '';
        }, 1000);
    }
});
</script>
@endsection
@endsection
