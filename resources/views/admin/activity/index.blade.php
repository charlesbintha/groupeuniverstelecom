@extends('layouts.app')

@section('title', 'Journal d’activité')

@section('styles')
<style>
    .page { max-width: 1450px; }
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }
    .stat-card {
        padding: 18px;
        background: #fff;
        border: 1px solid var(--border);
        border-radius: 14px;
        box-shadow: var(--shadow);
    }
    .stat-label { color: var(--muted); font-size: 12px; font-weight: 700; text-transform: uppercase; }
    .stat-value { margin-top: 8px; font-size: 28px; font-weight: 800; color: var(--blue-600); }
    .stat-help { margin-top: 5px; color: var(--muted); font-size: 11px; line-height: 1.4; }
    .filters {
        display: grid;
        grid-template-columns: repeat(3, minmax(160px, 1fr)) auto auto;
        gap: 12px;
        align-items: end;
    }
    .filters .form-group { margin: 0; }
    .table-scroll { overflow-x: auto; }
    .activity-table { font-size: 12px; }
    .activity-table td { vertical-align: top; }
    .activity-name { min-width: 180px; font-weight: 600; }
    .nowrap { white-space: nowrap; }
    .pagination { display: flex; justify-content: center; align-items: center; gap: 10px; margin: 18px 0 4px; }
    .rate-bar { display: flex; align-items: center; gap: 8px; min-width: 125px; }
    .rate-track { width: 72px; height: 7px; overflow: hidden; background: #e5e7eb; border-radius: 999px; }
    .rate-fill { height: 100%; background: var(--blue); border-radius: 999px; }
    .section-note { color: var(--muted); font-size: 12px; margin: 0; }
    @media (max-width: 900px) {
        .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .filters { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 560px) {
        .stats-grid, .filters { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-head">
        <div>
            <h2 class="title">Journal d’activité des utilisateurs</h2>
            <p class="section-note">Période du {{ \Carbon\Carbon::parse($dateStart)->format('d/m/Y') }} au {{ \Carbon\Carbon::parse($dateEnd)->format('d/m/Y') }}</p>
        </div>
        <div class="toolbar">
            <a class="btn sec" href="{{ route('admin.users.index') }}">Utilisateurs</a>
            <a class="btn sec" href="{{ route('projects.index') }}">Projets</a>
        </div>
    </div>
    <div class="card-body">
        <form class="filters" method="GET" action="{{ route('admin.activity.index') }}">
            <div class="form-group">
                <label for="date_debut">Date début</label>
                <input id="date_debut" name="date_debut" type="date" value="{{ $dateStart }}" required>
            </div>
            <div class="form-group">
                <label for="date_fin">Date fin</label>
                <input id="date_fin" name="date_fin" type="date" value="{{ $dateEnd }}" required>
            </div>
            <div class="form-group">
                <label for="user_id">Utilisateur</label>
                <select id="user_id" name="user_id">
                    <option value="">Tous les utilisateurs</option>
                    @foreach($filterUsers as $filterUser)
                        <option value="{{ $filterUser->id }}" @selected($selectedUserId === $filterUser->id)>
                            {{ $filterUser->name ?: $filterUser->email }} — {{ $filterUser->email }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="btn" type="submit">Filtrer</button>
            <a class="btn sec" href="{{ route('admin.activity.index') }}">Réinitialiser</a>
        </form>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-label">Taux d’utilisation global</div>
        <div class="stat-value">{{ number_format($globalUsageRate, 1, ',', ' ') }} %</div>
        <div class="stat-help">Utilisateurs ayant réalisé au moins une action / comptes actifs.</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Utilisateurs actifs sur la période</div>
        <div class="stat-value">{{ $activeUserCount }}</div>
        <div class="stat-help">Sur {{ $enabledUserCount }} compte(s) actuellement activé(s).</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Actions enregistrées</div>
        <div class="stat-value">{{ number_format($totalActivities, 0, ',', ' ') }}</div>
        <div class="stat-help">Toutes les consultations et opérations tracées.</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Période analysée</div>
        <div class="stat-value" style="font-size: 19px;">{{ \Carbon\Carbon::parse($dateStart)->format('d/m/Y') }}<br>{{ \Carbon\Carbon::parse($dateEnd)->format('d/m/Y') }}</div>
        <div class="stat-help">Les deux dates sont incluses dans le calcul.</div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h3 class="title" style="font-size: 18px;">Taux d’utilisation par utilisateur</h3>
            <p class="section-note">La part représente les actions de l’utilisateur rapportées à toutes les actions de la période.</p>
        </div>
        <a class="btn success" href="{{ route('admin.activity.export', ['date_debut' => $dateStart, 'date_fin' => $dateEnd, 'user_id' => $selectedUserId]) }}">
            Exporter Excel (.xlsx)
        </a>
    </div>
    <div class="card-body table-scroll">
        <table class="activity-table">
            <thead>
                <tr class="row">
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th>Actions</th>
                    <th>Part des actions</th>
                    <th>Jours actifs</th>
                    <th>Dernière activité</th>
                </tr>
            </thead>
            <tbody>
                @forelse($userStatistics as $statUser)
                    <tr class="row">
                        <td><strong>{{ $statUser->name ?: '—' }}</strong><br><span class="text-muted">{{ $statUser->email }}</span></td>
                        <td>{{ $statUser->getRoleNames()->first() ?: ucfirst($statUser->role) }}</td>
                        <td><span class="pill {{ $statUser->is_active ? 'green' : 'red' }}">{{ $statUser->is_active ? 'Actif' : 'Inactif' }}</span></td>
                        <td><strong>{{ $statUser->activity_count }}</strong></td>
                        <td>
                            <div class="rate-bar">
                                <div class="rate-track"><div class="rate-fill" style="width: {{ min(100, $statUser->usage_rate) }}%;"></div></div>
                                <span>{{ number_format($statUser->usage_rate, 2, ',', ' ') }} %</span>
                            </div>
                        </td>
                        <td>{{ $statUser->active_days }}</td>
                        <td class="nowrap">{{ $statUser->last_activity_at ? \Carbon\Carbon::parse($statUser->last_activity_at)->format('d/m/Y H:i') : 'Jamais' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted">Aucun utilisateur.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h3 class="title" style="font-size: 18px;">Détail du journal</h3>
            <p class="section-note">{{ $activities->total() }} activité(s) correspondant aux filtres.</p>
        </div>
    </div>
    <div class="card-body table-scroll">
        <table class="activity-table">
            <thead>
                <tr class="row">
                    <th>Date et heure</th>
                    <th>Utilisateur</th>
                    <th>Action</th>
                    <th>Route</th>
                    <th>IP</th>
                    <th>Résultat</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activities as $activity)
                    <tr class="row">
                        <td class="nowrap">{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                        <td class="activity-name">{{ $activity->user_name ?: '—' }}<br><span class="text-muted">{{ $activity->user_email }}</span></td>
                        <td>{{ $activity->action }} <span class="pill gray">{{ $activity->method }}</span></td>
                        <td>{{ $activity->route_name ?: '—' }}</td>
                        <td class="nowrap">{{ $activity->ip_address ?: '—' }}</td>
                        <td><span class="pill {{ $activity->status_code < 400 ? 'green' : 'red' }}">HTTP {{ $activity->status_code }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted" style="padding: 28px;">Aucune activité enregistrée pour cette période.</td></tr>
                @endforelse
            </tbody>
        </table>

        @if($activities->hasPages())
            <div class="pagination">
                @if($activities->onFirstPage())
                    <span class="btn sec" style="opacity: .5;">← Précédent</span>
                @else
                    <a class="btn sec" href="{{ $activities->previousPageUrl() }}">← Précédent</a>
                @endif
                <span>Page {{ $activities->currentPage() }} / {{ $activities->lastPage() }}</span>
                @if($activities->hasMorePages())
                    <a class="btn sec" href="{{ $activities->nextPageUrl() }}">Suivant →</a>
                @else
                    <span class="btn sec" style="opacity: .5;">Suivant →</span>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
