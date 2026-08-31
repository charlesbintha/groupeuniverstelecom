@extends('layouts.app')

@section('title', 'Administration - Utilisateurs')
@section('page-title', 'Administration - Gestion des comptes')

@section('styles')
<style>
 .site-header .wrap {
            max-width: 1300px;
            margin: 0 auto;
            min-height: 72px;
            padding: 10px 18px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        table{
            font-size:12px;
            width: 100%;
        }
    .alerts {
        margin: 12px 0;
    }
    .ok {
        background: #ecfdf5;
        border: 1px solid #bbf7d0;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 8px;
        color: #166534;
    }
    .err {
        background: #fef2f2;
        border: 1px solid #fecaca;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 8px;
        color: #991b1b;
    }
    form.inline {
        display: inline;
        margin: 0 2px;
    }
    .table-wrap {
        padding: 12px;
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        padding: 0;
        margin: 0 2px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s;
        background: #f3f4f6;
        color: #6b7280;
    }
    .action-btn:hover {
        background: #e5e7eb;
        color: #374151;
        transform: translateY(-1px);
    }
    .action-btn.edit {
        background: #dbeafe;
        color: #2563eb;
    }
    .action-btn.edit:hover {
        background: #bfdbfe;
        color: #1e40af;
    }
    .action-btn.toggle {
        background: #fef3c7;
        color: #d97706;
    }
    .action-btn.toggle:hover {
        background: #fde68a;
        color: #b45309;
    }
    .action-btn.reset {
        background: #e0e7ff;
        color: #4f46e5;
    }
    .action-btn.reset:hover {
        background: #c7d2fe;
        color: #4338ca;
    }
    .action-btn.delete {
        background: #fee2e2;
        color: #dc2626;
    }
    .action-btn.delete:hover {
        background: #fecaca;
        color: #b91c1c;
    }
    .action-btn svg {
        width: 16px;
        height: 16px;
    }

    /* Delete Modal */
    .delete-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
        animation: fadeIn 0.2s ease;
    }
    .delete-modal.active {
        display: flex;
    }
    .delete-modal-content {
        background: white;
        border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 450px;
        width: 90%;
        animation: slideUp 0.3s ease;
    }
    .delete-modal-header {
        padding: 24px 24px 16px;
        border-bottom: 1px solid #e5e7eb;
    }
    .delete-modal-header h3 {
        margin: 0;
        color: #dc2626;
        font-size: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .delete-modal-body {
        padding: 24px;
    }
    .delete-modal-user {
        background: #fef2f2;
        border: 1px solid #fecaca;
        padding: 12px 16px;
        border-radius: 8px;
        margin: 16px 0;
    }
    .delete-modal-user strong {
        color: #991b1b;
        display: block;
        margin-bottom: 4px;
    }
    .delete-modal-user .user-detail {
        color: #7f1d1d;
        font-size: 14px;
    }
    .delete-modal-footer {
        padding: 16px 24px;
        background: #f9fafb;
        border-top: 1px solid #e5e7eb;
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        border-radius: 0 0 16px 16px;
    }
    .delete-modal-footer .btn {
        margin: 0;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-head">
        <h2 class="title">Comptes autorisés</h2>
        <div class="toolbar">
            <a class="btn sec" href="{{ route('projects.index') }}">← Liste des projets</a>
            <a class="btn sec" href="{{ route('admin.activity.index') }}">Journal d’activité</a>
            <a class="btn" href="{{ route('admin.users.create') }}">+ Ajouter un admin</a>
        </div>
    </div>

    <div class="table-wrap">
        <!-- Alerts -->
        @if(session('success') || session('error') || $errors->any())
            <div class="alerts">
                @if(session('success'))
                    <div class="ok">{!! session('success') !!}</div>
                @endif
                @if(session('error'))
                    <div class="err">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="err">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endif

        <!-- Users table -->
        @if($users->isEmpty())
            <p class="text-center text-muted" style="padding: 40px 0;">
                Aucun compte utilisateur.
            </p>
        @else
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr class="row">
                            
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Actif</th>
                            <th>Créé</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr class="row">
                                
                                <td>{{ $user->employee->prenom_nom ?? '—' }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php
                                        $spatieRole = $user->getRoleNames()->first() ?? 'Aucun';
                                        $badgeColor = match($spatieRole) {
                                            'Admin' => 'blue',
                                            'Project Admin' => 'green',
                                            'Manager' => 'orange',
                                            'User' => 'default',
                                            default => 'default'
                                        };
                                    @endphp
                                    <span class="pill {{ $badgeColor }}">
                                        {{ $spatieRole }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->is_active)
                                        <span class="pill green">actif</span>
                                    @else
                                        <span class="pill red">inactif</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d/m/Y') }}</td>
                                <td style="white-space: nowrap;">
                                    <!-- Edit -->
                                    @can('update', $user)
                                        @if($user->id !== auth()->id())
                                            <a href="{{ route('admin.users.edit', $user) }}" class="action-btn edit" title="Modifier">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </a>
                                        @endif
                                    @endcan

                                    <!-- Toggle active -->
                                    @if($user->id !== auth()->id())
                                        <form class="inline" method="POST" action="{{ route('admin.users.toggle', $user) }}" onsubmit="return confirm('Confirmer ?');">
                                            @csrf
                                            <button type="submit" class="action-btn toggle" title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                                @if($user->is_active)
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @else
                                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Reset password -->
                                    <form class="inline" method="POST" action="{{ route('admin.users.resetPassword', $user) }}" onsubmit="return confirm('Réinitialiser le mot de passe ?');">
                                        @csrf
                                        <button type="submit" class="action-btn reset" title="Réinitialiser le mot de passe">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                        </button>
                                    </form>

                                    <!-- Delete -->
                                    @if($user->id !== auth()->id())
                                        <form class="inline delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" data-user-id="{{ $user->id }}" data-user-name="{{ $user->employee->prenom_nom ?? $user->name }}" data-user-email="{{ $user->email }}" data-user-role="{{ $user->getRoleNames()->first() ?? 'Aucun' }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="action-btn delete delete-user-btn" title="Supprimer">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div style="margin-top: 20px; display: flex; justify-content: center; gap: 8px;">
                    @if($users->onFirstPage())
                        <span class="btn sec" style="opacity: 0.5; cursor: not-allowed;">← Précédent</span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="btn sec">← Précédent</a>
                    @endif

                    <span class="btn sec" style="background: var(--blue); color: white;">
                        Page {{ $users->currentPage() }} / {{ $users->lastPage() }}
                    </span>

                    @if($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="btn sec">Suivant →</a>
                    @else
                        <span class="btn sec" style="opacity: 0.5; cursor: not-allowed;">Suivant →</span>
                    @endif
                </div>
            @endif
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="delete-modal" id="deleteModal">
    <div class="delete-modal-content">
        <div class="delete-modal-header">
            <h3>
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 28px; height: 28px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                Confirmation de suppression
            </h3>
        </div>
        <div class="delete-modal-body">
            <p style="margin: 0 0 8px; color: #374151; font-size: 15px;">
                Vous êtes sur le point de supprimer définitivement ce compte utilisateur :
            </p>
            <div class="delete-modal-user">
                <strong id="modalUserName">—</strong>
                <div class="user-detail" id="modalUserEmail">—</div>
                <div class="user-detail" style="margin-top: 4px;">
                    <span class="pill" id="modalUserRole" style="font-size: 12px;">—</span>
                </div>
            </div>
            <p style="margin: 16px 0 0; color: #991b1b; font-weight: 600; font-size: 14px;">
                ⚠️ Cette action est irréversible. Toutes les données associées seront perdues.
            </p>
        </div>
        <div class="delete-modal-footer">
            <button type="button" class="btn sec" onclick="closeDeleteModal()">Annuler</button>
            <button type="button" class="btn" style="background: #dc2626;" onclick="confirmDelete()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 6px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Supprimer définitivement
            </button>
        </div>
    </div>
</div>

@section('scripts')
<script>
let currentDeleteForm = null;

// Open delete modal
document.querySelectorAll('.delete-user-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        currentDeleteForm = this.closest('form');

        const userName = currentDeleteForm.getAttribute('data-user-name');
        const userEmail = currentDeleteForm.getAttribute('data-user-email');
        const userRole = currentDeleteForm.getAttribute('data-user-role');

        document.getElementById('modalUserName').textContent = userName;
        document.getElementById('modalUserEmail').textContent = userEmail;

        const roleSpan = document.getElementById('modalUserRole');
        roleSpan.textContent = userRole;
        roleSpan.className = 'pill ' + getRoleColor(userRole);

        document.getElementById('deleteModal').classList.add('active');
    });
});

// Close modal
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
    currentDeleteForm = null;
}

// Confirm delete
function confirmDelete() {
    if (currentDeleteForm) {
        currentDeleteForm.submit();
    }
}

// Get role color
function getRoleColor(role) {
    const colors = {
        'Admin': 'blue',
        'Project Admin': 'green',
        'Manager': 'orange',
        'User': 'default'
    };
    return colors[role] || 'default';
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeleteModal();
    }
});

// Close modal on backdrop click
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
@endsection

@endsection
