@extends('layouts.app')

@section('title', 'Liste des projets')
@section('page-title', 'Liste des projets')


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
     /* Toolbar */
    .toolbar {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    /* Meta Grid */
    .meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 16px;
        padding: 24px;
        background: #f9fafb;
        border-radius: 12px;
        margin-bottom: 32px;
    }
    .meta .item {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 14px 16px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .meta .item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    .label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: .6px;
        font-weight: 600;
        margin-bottom: 6px;
    }
    .val {
        margin-top: 6px;
        font-weight: 600;
        color: #1e293b;
        font-size: 15px;
    }

    /* Sections */
    .section {
        padding: 28px 24px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
    }

    .section:first-of-type {
        border-top: none;
    }

    .section h3 {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 0 16px 0;
        font-size: 17px;
        font-weight: 700;
        color: #1e293b;
        padding-bottom: 12px;
        border-bottom: 2px solid #f1f5f9;
    }

    .section h3::before {
        content: '';
        width: 4px;
        height: 24px;
        background: linear-gradient(135deg, #0094d8, #0070a0);
        border-radius: 2px;
    }

    .section table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        table-layout: fixed;
        font-size:12px;
        overflow: hidden;
    }
    .section th,
    .section td {
        background: #fff;
        padding: 12px 16px;
        text-align: left;
        vertical-align: top;
        border-bottom: 1px solid #e5e7eb;
    }
    table{
        font-size:12px;
        table-layout: fixed;
  width: 100%;
    }
   th:nth-child(2),
    td:nth-child(2) {
      width: 370px;       /* largeur souhaitée */
      max-width: 370px;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap; /* évite le retour à la ligne */
    }

    /* Le texte est limité dans un élément interne afin que le td reste
       une vraie cellule de tableau (important pour le fond des retards). */
    td:nth-child(2) {
      white-space: normal;
    }
    .project-name {
      display: -webkit-box;
      -webkit-box-orient: vertical;
      -webkit-line-clamp: 3;
      overflow: hidden;
      line-height: 1.35;
    }

    .section tr:last-child td {
        border-bottom: none;
    }
    .section th {
        background: #f8fafc;
        letter-spacing: .6px;
        text-transform: uppercase;
        color: #475569;
        font-weight: 700;
        border-bottom: 2px solid #e5e7eb;
    }
    .wraptext {
        white-space: normal;
        word-break: break-word;
        line-height: 1.6;
    }

    /* Empty state */
    .text-muted {
        color: #94a3b8;
        font-style: italic;
        padding: 12px 0;
    }

    @media (max-width: 920px) {
        .meta {
            grid-template-columns: 1fr;
            padding: 16px;
        }
        .section {
            padding: 16px;
        }
    }

    @media print {
        .toolbar {
            display: none;
        }
        .section {
            page-break-inside: avoid;
        }
        body {
            background: #fff;
        }
        .site-header {
            border: none;
        }
        .meta {
            background: #fff;
        }
    }
    .page {
        max-width:1600px;
        margin: 24px auto;
        padding: 0 18px 40px;
        }

    tr.project-overdue td {
        background: #fff1f2;
        border-top: 1px solid #fecaca;
        border-bottom: 1px solid #fecaca;
    }
    tr.project-overdue td:first-child {
        border-left: 4px solid #dc2626;
    }
    .overdue-note {
        display: block;
        margin-top: 6px;
        color: #b91c1c;
        font-size: 11px;
        font-weight: 700;
    }


</style>
@endsection

@section('content')
<div class="card">
    <div class="card-head">
        <h2 class="title">Projets</h2>
        <div class="toolbar">
            <a href="{{ route('projects.create') }}" class="btn">+ Nouveau projet</a>
            <a href="{{ route('dashboard') }}" class="btn sec">Dashboard</a>
            @can('users.view')
                <a href="{{ route('admin.users.index') }}" class="btn sec">Administration</a>
            @endcan
            @role('Admin')
                <a href="{{ route('admin.activity.index') }}" class="btn sec">Journal d’activité</a>
            @endrole
            <form method="POST" action="{{ route('logout') }}" style="display: inline;">
                @csrf
            </form>
        </div>
    </div>


    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="{{ route('projects.index') }}" style="display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 20px;">
            <input type="text"
                   name="q"
                   placeholder="Rechercher (nom, axe, chef...)"
                   value="{{ request('q') }}"
                   style="flex: 1; min-width: 200px;">

            <select name="filiale" style="min-width: 180px;">
                <option value="">Toutes les filiales</option>
                @foreach($filialesOptions as $fil)
                    <option value="{{ $fil }}" {{ request('filiale') == $fil ? 'selected' : '' }}>
                        {{ $fil }}
                    </option>
                @endforeach
            </select>

            <select name="direction" id="direction-filter" style="min-width: 220px;">
                <option value="">Toutes les directions</option>
            </select>

            <select name="statut" style="min-width: 150px;">
                <option value="">Tous les statuts</option>
                @foreach($statutsOptions as $st)
                    <option value="{{ $st }}" {{ request('statut') == $st ? 'selected' : '' }}>
                        {{ $st }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn sec">Filtrer</button>
            <a href="{{ route('projects.index') }}" class="btn sec">Réinitialiser</a>

        </form>

        <!-- Results count -->
        <p class="text-muted" style="margin-bottom: 16px;">
            {{ $projects->total() }} projet(s) trouvé(s)
            @if(request('q') || request('filiale') || request('direction') || request('statut'))
                — <a href="{{ route('projects.index') }}" style="color: var(--blue);">Voir tous</a>
            @endif
        </p>

        <!-- Projects table -->
        @if($projects->count() > 0)
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr class="row">
                            <th>Code</th>
                            <th>Nom du projet</th>
                            <th>Type</th>
                            <th>Entité</th>
                            <th>Chef de projet </th>
                            <th>Statut</th>
                            <th>Date fin</th>
                            <th>Budget</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projects as $project)
                            <tr class="row {{ $project->isOverdue() ? 'project-overdue' : '' }}">
                                <td><strong>{{ $project->code_projet }}</strong></td>
                                <td><span class="project-name">{{ $project->nom_projet }}</span></td>
                                <td>
                                    <span class="pill {{ $project->type_projet->value === 'Externe' ? 'green' : 'blue' }}">
                                        {{ $project->type_projet->value }}
                                    </span>
                                </td>
                                <td>{{ $project->filiale_contractant }}</td>
                                <td>{{ $project->owner_executant }}</td>
                                <td>
                                    <span class="pill @switch($project->statut_initial->value)
                                        @case('Planifié') yellow @break
                                        @case('En cours') blue @break
                                        @case('Pause') orange @break
                                        @case('Suspendu') gray @break
                                        @case('Mis en pause') orange @break
                                        @case('Retard') red @break
                                        @case('Terminé') green @break
                                    @endswitch">
                                        {{ $project->statut_initial->value }}
                                    </span>
                                    @if($project->isOverdue())
                                        <span class="overdue-note">⚠ En retard de {{ $project->overdueDays() }} jour(s)</span>
                                    @endif
                                </td>
                                <td>{{ $project->date_fin ? $project->date_fin->format('d/m/Y') : '—' }}</td>
                                <td>{{ $project->budget_initial ? number_format($project->budget_initial, 0, ',', ' ') . ' FCFA' : '—' }}</td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <a href="{{ route('projects.show', $project) }}" class="btn sec" style="padding: 6px 12px; font-size: 12px;">
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 4px;">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            
                                        </a>
                                         @if($project->ms_plan_id)
                                            <a href="{{ route('projects.report', $project) }}" class="btn sec" style="padding: 6px 12px; font-size: 12px;" title="Voir le rapport Microsoft Planner">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 16px; height: 16px; margin-right: 4px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                                </svg>
                                                
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($projects->hasPages())
                <div style="margin-top: 20px; display: flex; justify-content: center; gap: 8px;">
                    @if($projects->onFirstPage())
                        <span class="btn sec" style="opacity: 0.5; cursor: not-allowed;">← Précédent</span>
                    @else
                        <a href="{{ $projects->previousPageUrl() }}" class="btn sec">← Précédent</a>
                    @endif

                    <span class="btn sec" style="background: var(--blue); color: white;">
                        Page {{ $projects->currentPage() }} / {{ $projects->lastPage() }}
                    </span>

                    @if($projects->hasMorePages())
                        <a href="{{ $projects->nextPageUrl() }}" class="btn sec">Suivant →</a>
                    @else
                        <span class="btn sec" style="opacity: 0.5; cursor: not-allowed;">Suivant →</span>
                    @endif
                </div>
            @endif
        @else
            <p class="text-center text-muted" style="padding: 40px 0;">
                Aucun projet trouvé. <a href="{{ route('projects.create') }}" style="color: var(--blue);">Créer un nouveau projet</a>
            </p>
        @endif
    </div>
</div>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const filialeSelect = document.querySelector('select[name="filiale"]');
    const directionSelect = document.getElementById('direction-filter');
    const directionsMap = @json($directionsMap);
    const selectedDirection = @json(request('direction'));

    if (!filialeSelect || !directionSelect) {
        return;
    }

    function renderDirections() {
        const filiale = filialeSelect.value;
        const directions = filiale && directionsMap[filiale] ? directionsMap[filiale] : [];

        directionSelect.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Toutes les directions';
        directionSelect.appendChild(defaultOption);

        directions.forEach((direction) => {
            const option = document.createElement('option');
            option.value = direction;
            option.textContent = direction;
            if (selectedDirection === direction) {
                option.selected = true;
            }
            directionSelect.appendChild(option);
        });
    }

    filialeSelect.addEventListener('change', () => {
        renderDirections();
        directionSelect.value = '';
    });

    renderDirections();
});
</script>
@endsection
@endsection
