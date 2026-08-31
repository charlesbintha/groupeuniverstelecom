@extends('layouts.app')

@section('title', 'Projet #' . $project->code_projet)
@section('page-title', $project->nom_projet)

@section('styles')
<style>
    /* Toolbar */
    .toolbar {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    /* Uniformisation des boutons */
    .toolbar .btn,
    .toolbar button.btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.2s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .toolbar .btn svg,
    .toolbar button.btn svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    /* Modal de suppression */
    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.6);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        backdrop-filter: blur(4px);
    }

    .modal-overlay.show {
        display: flex;
        animation: fadeIn 0.2s ease;
    }

    .modal-card {
        background: #ffffff;
        border-radius: 16px;
        max-width: 500px;
        width: 90%;
        padding: 0;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: slideUp 0.3s ease;
        overflow: hidden;
    }

    .modal-header {
        padding: 24px 28px;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .modal-header h3 {
        margin: 0;
        font-size: 20px;
        font-weight: 700;
    }

    .modal-body {
        padding: 28px;
    }

    .modal-body p {
        margin: 0 0 16px 0;
        color: #475569;
        line-height: 1.6;
        font-size: 15px;
    }

    .modal-body .warning-box {
        background: #fef2f2;
        border-left: 4px solid #ef4444;
        padding: 12px 16px;
        border-radius: 8px;
        margin-top: 16px;
        font-size: 14px;
        color: #991b1b;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 28px;
        background: #f8fafc;
        border-top: 1px solid #e5e7eb;
    }

    .modal-actions .btn,
    .modal-actions button.btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.2s ease;
        cursor: pointer;
        white-space: nowrap;
    }

    .modal-actions .btn svg,
    .modal-actions button.btn svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            transform: translateY(30px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @media (max-width: 768px) {
        .toolbar {
            justify-content: stretch;
        }
        .toolbar .btn,
        .toolbar button.btn {
            flex: 1;
            min-width: 0;
        }
        .modal-card {
            max-width: 95%;
        }
        .modal-actions {
            flex-direction: column;
        }
        .modal-actions .btn {
            width: 100%;
        }
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
    .meta .item.item-full {
        grid-column: 1 / -1;
    }
    .financial-amounts-row {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
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
    .financial-amounts-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
    }
    .financial-amount {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 12px;
    }
    @media (max-width: 900px) {
        .financial-amounts-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .financial-amounts-row {
            grid-template-columns: 1fr;
        }
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
    .section tr:last-child td {
        border-bottom: none;
    }
    .section th {
        background: #f8fafc;
        font-size: 12px;
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
</style>
@endsection

@section('content')
<div class="toolbar">
    <a class="btn sec" href="{{ route('projects.index') }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Liste
    </a>
    <a class="btn" href="{{ route('projects.edit', $project) }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Modifier
    </a>
    <a class="btn" href="{{ route('projects.duplicate', $project) }}" style="background: linear-gradient(135deg, #10b981, #059669); color: white; border-color: #10b981;" title="Créer une copie de ce projet">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        Dupliquer
    </a>
    <button type="button" class="btn" style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; border-color: #ef4444;" onclick="openDeleteModal()">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
        </svg>
        Supprimer
    </button>
    <a class="btn orange" href="{{ route('projects.create') }}">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Nouveau
    </a>
    <a class="btn sec" href="#" onclick="window.print();return false;">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Imprimer
    </a>
</div>

<div class="card">
    <div class="card-head">
        <h2 class="title">
            {{ $project->nom_projet }}
            <span class="pill blue">#{{ $project->code_projet }}</span>
        </h2>
    </div>
        @php
            // Vérifier si les informations contractant sont différentes de l'executant
            $showContractant = (
                ($project->filiale_contractant && $project->filiale_contractant !== $project->filiale_executant) ||
                ($project->direction_contractant && $project->direction_contractant !== $project->direction_executant) ||
                ($project->owner_contractant && $project->owner_contractant !== $project->owner_executant) ||
                ($project->account_manager && $project->account_manager !== $project->owner_contractant)
            );

            $contractantEntite = $project->filiale_contractant ?? '—';
            if ($project->direction_contractant) {
                $contractantEntite .= ' (' . $project->direction_contractant . ')';
            }

            $executantEntite = $project->filiale_executant ?? '—';
            if ($project->direction_executant) {
                //$executantEntite .= ' (' . $project->direction_executant . ')';
            }

            // Afficher les blocs entité une seule fois si filiale + direction sont identiques
            $showSeparateEntites = (
                ($project->filiale_contractant ?? null) !== ($project->filiale_executant ?? null) ||
                ($project->direction_contractant ?? null) !== ($project->direction_executant ?? null)
            );
        @endphp
    <!-- META -->
    <div class="meta">
        <div class="item">
            <div class="label">Type de projet</div>
            <div class="val">{{ $project->type_projet->value ?? '—' }}</div>
        </div>
        <div class="item">
            <div class="label">Nature du projet</div>
            <div class="val">{{ $project->nature_projet->value ?? '—' }}</div>
        </div>

        @if($project->type_projet->value === 'Externe' && $project->sf_opportunity_name)

            <div class="item">
                <div class="label">Opportunité Salesforce</div>
                <div class="val">
                    @if(isset($sfData) && isset($sfData['link']))
                        <a href="{{ $sfData['link'] }}" target="_blank" rel="noopener">
                            {{ $project->sf_opportunity_name }}
                        </a>
                    @else
                        {{ $project->sf_opportunity_name }}
                    @endif
                </div>
            </div>

            @if(isset($sfData) && !empty($sfData['account_name']))
                <div class="item">
                    <div class="label">Compte Client</div>
                    <div class="val">{{ $sfData['account_name'] }}</div>
                </div>
            @endif
        @endif

        <div class="item">
            <div class="label">Axe stratégique</div>
            <div class="val">{{ $project->axe_strategique ?? '—' }}</div>
        </div>
        <div class="item">
            <div class="label">Statut initial</div>
            <div class="val">
                <span class="pill @switch($project->statut_initial->value)
                    @case('Planifié') yellow @break
                    @case('En cours') blue @break
                    @case('Pause') orange @break
                    @case('Suspendu') gray @break
                    @case('Mis en pause') orange @break
                    @case('Retard') red @break
                    @case('Terminé') green @break
                @endswitch">
                    {{ $project->statut_initial->value ?? '—' }}
                </span>
            </div>
        </div>
            <div class="item">
                <div class="label">Période</div>
                <div class="val">
                    {{ $project->date_demarrage ? $project->date_demarrage->format('d/m/Y') : '—' }}
                    →
                    {{ $project->date_fin ? $project->date_fin->format('d/m/Y') : '—' }}
                </div>
            </div>
            @if($showSeparateEntites)
                <div class="item">
                    <div class="label">Entité Contractante</div>
                    <div class="val">{{ $contractantEntite }}</div>
                </div>
            @endif
            <div class="item">
                <div class="label">Entité {{ $showSeparateEntites ? 'Executante' : '' }}</div>
                <div class="val">{{ $showSeparateEntites ? $executantEntite : $contractantEntite }}</div>
            </div>
            <div class="item">
                <div class="label">Chef de projet Contractant</div>
                <div class="val">{{ $project->owner_contractant ?? '—' }}</div>
            </div>
            <div class="item">
                <div class="label">Account Manager</div>
                <div class="val">{{ $project->account_manager ?? $project->owner_contractant ?? '—' }}</div>
            </div>
            <div class="item">
                <div class="label">Chef de Projet Executant</div>
                <div class="val">{{ $project->owner_executant ?? '—' }}</div>
            </div>
            @if(!$showSeparateEntites)
                <div class="item" style="grid-column: 1 / -1; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border: 1px solid #bae6fd; border-left: 4px solid #0ea5e9;">
                    <div style="display: flex; align-items: center; gap: 10px; color: #0c4a6e;">
                        <svg style="width: 20px; height: 20px; flex-shrink: 0;" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <span style="font-size: 13px; font-weight: 600; line-height: 1.5;">
                        Le contractant et l'exécutant de ce projet sont identiques
                    </span>
                    </div>
                </div>
            @endif


    </div>

    <!-- Sous-bloc Informations financières -->
    <div class="section" style="padding-top: 0; border-top: none;">
        <h3 style="font-size: 16px; margin-bottom: 20px;">💰 Informations financières</h3>
        <div class="meta" style="margin: 0; padding: 20px; background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);">
            <div class="item">
                <div class="label">Statut financier</div>
                <div class="val">
                    <span class="pill @switch($project->statut_financier?->value)
                        @case('Non démarré') orange @break
                        @case('En cours') blue @break
                        @case('Terminé') green @break
                    @endswitch">
                        {{ $project->statut_financier?->value ?? '—' }}
                    </span>
                </div>
            </div>
            <div class="item">
                <div class="label">Budget Initial</div>
                <div class="val" style="color: #166534; font-weight: 700;">
                    @if($project->budget_initial)
                        {{ number_format($project->budget_initial, 0, ',', ' ') }} FCFA
                    @else
                        —
                    @endif
                </div>
            </div>
        <div class="item">
            <div class="label">Ressource à mobiliser</div>
            <div class="val">
                @if($project->ressource_a_mobiliser)
                    @php
                        $resourceDetail = null;
                        if ($project->resource_type) {
                            $resourceDetail = $project->resource_type->value === 'Banque' && $project->resource_bank
                                ? $project->resource_bank->label()
                                : $project->resource_type->label();
                        }
                    @endphp
                    <span class="pill green">✅ Oui{{ $resourceDetail ? ' (' . $resourceDetail . ')' : '' }}</span>
                @else
                    <span class="pill gray">❌ Non</span>
                @endif
            </div>
        </div>
            <div class="item item-full">
                @php
                    $budgetInitial = (float) ($project->budget_initial ?? 0);
                    $montantDecaissement = (float) ($project->montant_decaissement_2 ?? 0);
                    $tauxExecutionBudgetaire = $budgetInitial > 0
                        ? ($montantDecaissement / $budgetInitial) * 100
                        : null;
                @endphp
                <div class="financial-amounts-grid">
                    <div class="financial-amount">
                        <div class="label">Montant Encaissement</div>
                        <div class="val" style="color: #166534;">
                            @if($project->montant_encaissement)
                                {{ number_format($project->montant_encaissement, 0, ',', ' ') }} FCFA
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="financial-amount">
                        <div class="label">Montant Décaissement</div>
                        <div class="val" style="color: #0f766e;">
                            @if($project->montant_decaissement_2)
                                {{ number_format($project->montant_decaissement_2, 0, ',', ' ') }} FCFA
                            @else
                                —
                            @endif
                        </div>
                    </div>
                    <div class="financial-amount">
                        <div class="label">Montant engagement</div>
                        <div class="val" style="color: #991b1b;">
                            <a href="#" onclick="openDemandesAchat('{{ $project->code_projet }}'); return false;" style="color: #991b1b; text-decoration: underline; cursor: pointer;" title="Voir les demandes d'achat">
                                <span id="montantEngagementPage">Chargement…</span>
                            </a>
                        </div>
                    </div>
                    <div class="financial-amount">
                        <div class="label">Montant Recouvrement</div>
                        <div class="val" style="color: #7c3aed;">
                            <span id="montantRecouvrementPage">{{ number_format($project->montant_recouvrement ?? 0, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                    <div class="financial-amount">
                        <div class="label">Montant recouvré</div>
                        <div class="val" style="color: #15803d;">
                            <span id="montantRecouvrePage">{{ number_format($project->montant_recouvre ?? 0, 0, ',', ' ') }} FCFA</span>
                        </div>
                    </div>
                    <div class="financial-amount">
                        <div class="label">Exécution budgétaire</div>
                        <div class="val" style="color: #0369a1;">
                            @if($tauxExecutionBudgetaire !== null)
                                {{ number_format($tauxExecutionBudgetaire, 1, ',', ' ') }} %
                            @else
                                —
                            @endif
                        </div>
                        <small style="display: block; margin-top: 6px; color: #64748b;">
                            Décaissement / Budget initial
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Objectif -->
    <div class="section">
        <h3>🎯 Objectif du projet</h3>
        <div class="notes-content">
            @if($project->objectif_projet)
                {!! nl2br(e($project->objectif_projet)) !!}
            @else
                <span class="text-muted">—</span>
            @endif
        </div>
    </div>

    <!-- Contexte -->
    <div class="section">
        <h3>📖 Contexte</h3>
        <div class="notes-content">
            @if($project->contexte)
                {!! nl2br(e($project->contexte)) !!}
            @else
                <span class="text-muted">—</span>
            @endif
        </div>
    </div>

    <!-- Synthèse -->
    <div class="section">
        <h3>📝 Synthèse</h3>
        <div class="notes-content">
            @if($project->synthese)
                {!! nl2br(e($project->synthese)) !!}
            @else
                <span class="text-muted">—</span>
            @endif
        </div>
    </div>

        <!-- Notes -->
    @if($project->notes)
    <div class="section">
        <h3>📝 Notes et remarques de la semaine</h3>
        <div class="notes-content">{!! nl2br(e($project->notes)) !!}</div>
    </div>
    @endif
    <!-- Contractualisation -->
    @if($project->contractualisation)
        <div class="section">
            <h3>📄 Contractualisation</h3>
            <div class="meta" style="margin-top: 12px;">
                <div class="item">
                    <div class="label">Contractualisation requise</div>
                    <div class="val"><span class="pill green">✅ Oui</span></div>
                </div>
                @if($project->contractualisation_type)
                    <div class="item">
                        <div class="label">Type de document</div>
                        <div class="val">{{ $project->contractualisation_type->value }}</div>
                    </div>
                @endif
                @php
                    $contractDoc = $project->documents()->where('document_type', App\Enums\DocumentType::CONTRACTUALISATION->value)->first();
                @endphp
                @if($contractDoc)
                    <div class="item">
                        <div class="label">Document contractuel</div>
                        <div class="val">
                            <a href="{{ route('projects.documents.download', ['project' => $project->id, 'document' => $contractDoc->id]) }}"
                               style="color: #0ea5e9; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
                                <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                {{ $contractDoc->original_filename }}
                            </a>
                            <small style="display: block; margin-top: 4px; color: #64748b;">
                                Ajouté le {{ $contractDoc->created_at->format('d/m/Y à H:i') }}
                            </small>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        <div class="section">
            <h3>📄 Contractualisation</h3>
            <div class="wraptext text-muted">Aucune contractualisation requise pour ce projet</div>
        </div>
    @endif

    <!-- Prochaine étape -->
    @if($project->prochaine_etape)
        <div class="section">
            <h3>➡️ Prochaine étape</h3>
            <div class="notes-content">{!! nl2br(e($project->prochaine_etape)) !!}</div>
        </div>
    @endif

    <!-- Actions -->
    <div class="section">
        <h3>✅ Actions</h3>
        @if($project->actions->isEmpty())
            <div class="wraptext text-muted">—</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:80px">Ordre</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->actions->sortBy('ordre') as $action)
                        <tr>
                            <td>{{ $action->ordre }}</td>
                            <td class="wraptext">{{ $action->libelle }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Livrables -->
    <div class="section">
        <h3>📦 Livrables attendus</h3>
        @if($project->deliverables->isEmpty())
            <div class="wraptext text-muted">—</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Livrable</th>
                        <th>Description</th>
                        <th style="width:140px">Date prévue</th>
                        <th style="width:120px">Statut</th>
                        <th style="width:180px">Document</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->deliverables as $deliverable)
                        @php
                            $deliverableDoc = $project->documents()
                                ->where('document_type', App\Enums\DocumentType::LIVRABLE->value)
                                ->where('deliverable_id', $deliverable->id)
                                ->first();
                        @endphp
                        <tr>
                            <td class="wraptext">{{ $deliverable->livrable }}</td>
                            <td class="wraptext">{{ $deliverable->description }}</td>
                            <td>{{ $deliverable->date_prevue ? $deliverable->date_prevue->format('d/m/Y') : '' }}</td>
                            <td>
                                @if($deliverable->realise)
                                    <span style="display: inline-flex; align-items: center; padding: 4px 10px; font-size: 12px; font-weight: 600; border-radius: 12px; background-color: #d1fae5; color: #065f46;">
                                        Réalisé
                                    </span>
                                @else
                                    <span style="display: inline-flex; align-items: center; padding: 4px 10px; font-size: 12px; font-weight: 600; border-radius: 12px; background-color: #f1f5f9; color: #475569;">
                                        Non réalisé
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($deliverableDoc)
                                    <a href="{{ route('projects.documents.download', ['project' => $project->id, 'document' => $deliverableDoc->id]) }}"
                                       style="color: #0ea5e9; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; font-size: 13px;">
                                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        {{ $deliverableDoc->original_filename }}
                                    </a>
                                @else
                                    <span style="color: #94a3b8; font-size: 13px;">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Parties prenantes -->
    <div class="section">
        <h3>👥 Parties prenantes (RACI)</h3>
        @if($project->stakeholders->isEmpty())
            <div class="wraptext text-muted">—</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:120px">Rôle</th>
                        <th>Prénom Nom</th>
                        <th>Implication / Attentes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->stakeholders as $stakeholder)
                        <tr>
                            <td>{{ $stakeholder->role }}</td>
                            <td>{{ $stakeholder->prenom_nom }}</td>
                            <td class="wraptext">{{ $stakeholder->attentes }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Parties prenantes externes -->
    <div class="section">
        <h3>🌐 Parties prenantes externes</h3>
        @if($project->externalStakeholders->isEmpty())
            <div class="wraptext text-muted">—</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Organisation</th>
                        <th>Prénom et nom</th>
                        <th>Email</th>
                        <th style="width:120px">Rôle</th>
                        <th>Implication / Attentes</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->externalStakeholders as $stakeholder)
                        <tr>
                            <td>{{ $stakeholder->organisation ?? '—' }}</td>
                            <td>{{ $stakeholder->nom_complet }}</td>
                            <td>{{ $stakeholder->email ?? '—' }}</td>
                            <td>{{ $stakeholder->role ?? '—' }}</td>
                            <td class="wraptext">{{ $stakeholder->attentes ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Documents généraux -->
    @php
        $generalDocs = $project->documents()
            ->where('document_type', App\Enums\DocumentType::GENERAL->value)
            ->orderBy('created_at', 'desc')
            ->get();
    @endphp

    @if($generalDocs->count() > 0)
    <div class="section">
        <h3>📁 Documents du projet</h3>
        <div class="documents-list">
            @foreach($generalDocs as $doc)
            <div class="document-item">
                <div class="doc-content">
                    <div class="doc-title">{{ $doc->name }}</div>
                    <div class="doc-details">
                        <span class="doc-filename">{{ $doc->original_filename }}</span>
                        <span class="doc-separator">•</span>
                        <span class="doc-size">{{ $doc->getFileSizeFormatted() }}</span>
                        <span class="doc-separator">•</span>
                        <span class="doc-date">{{ $doc->created_at->format('d/m/Y à H:i') }}</span>
                    </div>
                </div>
                <a href="{{ route('projects.documents.download', ['project' => $project->id, 'document' => $doc->id]) }}" class="btn-download-modern">
                    Télécharger
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif



    <!-- Enjeux / Contraintes / Risques / REX -->
    <div class="section">
        <h3>⚠️ Enjeux, contraintes, risques et REX</h3>
        @if($project->issues->isEmpty())
            <div class="wraptext text-muted">—</div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:160px">Catégorie</th>
                        <th>Détail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($project->issues as $issue)
                        <tr>
                            <td>{{ $issue->categorie }}</td>
                            <td class="wraptext">{{ $issue->detail }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <!-- Microsoft Planner integration -->
    @if($project->ms_plan_id)
        <div class="section">
            <h3>📅 Microsoft Planner</h3>
            <div class="meta" style="margin: 0; padding: 16px; background: #f8fafc;">
                <div class="item">
                    <div class="label">Plan ID</div>
                    <div class="val">{{ $project->ms_plan_id }}</div>
                </div>
                @if($project->ms_bucket_id)
                    <div class="item">
                        <div class="label">Bucket ID</div>
                        <div class="val">{{ $project->ms_bucket_id }}</div>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Footer -->
    <div class="section" style="color:#94a3b8; font-size:13px; border-top: 2px dashed #e5e7eb; padding: 16px 24px;">
        <div style="display: flex; gap: 24px; flex-wrap: wrap;">
            <div>
                <strong style="color: #64748b;">Créé:</strong> {{ $project->created_at->format('d/m/Y à H:i') }}
            </div>
            @if($project->updated_at && $project->updated_at != $project->created_at)
                <div>
                    <strong style="color: #64748b;">Modifié:</strong> {{ $project->updated_at->format('d/m/Y à H:i') }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal détail engagement -->
<div class="modal-overlay" id="engagementModal">
    <div class="modal-card">
        <div class="modal-header" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
            <h3>Détail du montant engagement</h3>
        </div>
        <div class="modal-body">
            <p>
                Montant engagement:
                <strong>
                    @if(($project->montant_decaissement ?? 0) > 0)
                        {{ number_format($project->montant_decaissement, 0, ',', ' ') }} FCFA
                    @else
                        0 F CFA
                    @endif
                </strong>
            </p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn sec" onclick="closeEngagementModal()">Fermer</button>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal-card">
        <div class="modal-header">
            <svg style="width: 32px; height: 32px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3>Confirmer la suppression</h3>
        </div>
        <div class="modal-body">
            <p><strong>Attention !</strong> Vous êtes sur le point de supprimer le projet :</p>
            <p style="font-weight: 700; color: #1e293b; font-size: 16px;">{{ $project->nom_projet }}</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn sec" onclick="closeDeleteModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Annuler
            </button>
            <button type="button" class="btn" style="background: #ef4444; border-color: #ef4444; color: white;" onclick="confirmDelete()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Confirmer la suppression
            </button>
        </div>
    </div>
</div>

<!-- Formulaire de suppression caché -->
<form method="POST" action="{{ route('projects.destroy', $project) }}" id="deleteForm" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Modal des demandes d'achat -->
<div class="modal-overlay" id="demandesAchatModal">
    <div class="modal-card" style="max-width: 900px;">
        <div class="modal-header" style="background: linear-gradient(135deg, #0094d8, #0070a0);">
            <svg style="width: 28px; height: 28px; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3>Demandes d'achat</h3>
        </div>
        <div class="modal-body" style="max-height: 500px; overflow-y: auto; padding: 0;">
            <div id="demandesAchatContent" style="padding: 20px;">
                <div style="text-align: center; padding: 40px;">
                    <div class="spinner" style="display: inline-block; width: 40px; height: 40px; border: 3px solid #e5e7eb; border-top-color: #0094d8; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                    <p style="margin-top: 16px; color: #6b7280;">Chargement des demandes d'achat...</p>
                </div>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn sec" onclick="closeDemandesAchatModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Fermer
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    #demandesAchatModal .da-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    #demandesAchatModal .da-table th {
        background: #f8fafc;
        padding: 12px 16px;
        text-align: left;
        font-weight: 600;
        color: #475569;
        border-bottom: 2px solid #e5e7eb;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    #demandesAchatModal .da-table td {
        padding: 12px 16px;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
    }
    #demandesAchatModal .da-table tr:hover {
        background: #f9fafb;
    }
    #demandesAchatModal .da-table tr:last-child td {
        border-bottom: none;
    }
    #demandesAchatModal .da-empty {
        text-align: center;
        padding: 40px;
        color: #6b7280;
    }
    #demandesAchatModal .da-empty svg {
        width: 48px;
        height: 48px;
        margin-bottom: 16px;
        color: #d1d5db;
    }
    #demandesAchatModal .da-total {
        background: #f0f9ff;
        padding: 16px 20px;
        border-top: 2px solid #0094d8;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
    }
    #demandesAchatModal .da-total .amount {
        font-size: 18px;
        color: #0094d8;
    }
</style>
@endsection

@section('scripts')
<script>
// Gestion du modal de suppression
function openDeleteModal() {
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

function confirmDelete() {
    closeDeleteModal();
    document.getElementById('deleteForm').submit();
}

// Modal détail engagement
function openEngagementModal() {
    document.getElementById('engagementModal').classList.add('show');
}

function closeEngagementModal() {
    document.getElementById('engagementModal').classList.remove('show');
}

// Fermer le modal avec Escape
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeDeleteModal();
        closeEngagementModal();
    }
});

// Fermer le modal en cliquant sur l'overlay
document.getElementById('deleteModal').addEventListener('click', (e) => {
    if (e.target.id === 'deleteModal') {
        closeDeleteModal();
    }
});

document.getElementById('engagementModal').addEventListener('click', (e) => {
    if (e.target.id === 'engagementModal') {
        closeEngagementModal();
    }
});

// Ensure modal is hidden on page load
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deleteModal');
    if (modal) {
        modal.classList.remove('show');
    }
    const engagementModal = document.getElementById('engagementModal');
    if (engagementModal) {
        engagementModal.classList.remove('show');
    }
});

// ===============================
// Demandes d'achat Modal
// ===============================
function openDemandesAchatModal() {
    document.getElementById('demandesAchatModal').classList.add('show');
}

function closeDemandesAchatModal() {
    document.getElementById('demandesAchatModal').classList.remove('show');
}

function formatMontant(montant, devise = 'FCFA') {
    if (!montant && montant !== 0) return '—';
    return new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(montant) + ' ' + (devise || 'FCFA');
}

function parseMontant(value) {
    if (typeof value === 'number') return Number.isFinite(value) ? value : 0;
    if (typeof value !== 'string') return 0;

    const normalizedValue = value.trim().replace(/\s/g, '').replace(',', '.');
    const parsedValue = Number(normalizedValue);

    return Number.isFinite(parsedValue) ? parsedValue : 0;
}

function montantEngagementEnFcfa(item) {
    const montant = parseMontant(item.montant_engagement);
    const devise = (item.devise || 'FCFA').toUpperCase();
    const tauxChange = ['FCFA', 'XOF'].includes(devise) ? 1 : parseMontant(item.taux_change);

    return montant * tauxChange;
}

let demandesAchatPromise = null;

function chargerDemandesAchat(codeProjet) {
    if (!demandesAchatPromise) {
        const apiUrl = `https://ged579032964b0a-m2tp93zrxyw6ducz.adb.me-dubai-1.oraclecloudapps.com/ords/gut_corp/da/achat/${encodeURIComponent(codeProjet)}`;

        demandesAchatPromise = fetch(apiUrl).then(response => {
            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }

            return response.json();
        });
    }

    return demandesAchatPromise;
}

async function afficherMontantEngagementPage(codeProjet) {
    const montantElement = document.getElementById('montantEngagementPage');
    if (!montantElement) return;

    try {
        const data = await chargerDemandesAchat(codeProjet);
        const items = data.items || [];
        const totalMontantFcfa = items.reduce((sum, item) => sum + montantEngagementEnFcfa(item), 0);

        montantElement.textContent = formatMontant(totalMontantFcfa, 'F CFA');
    } catch (error) {
        console.error('Erreur lors du calcul du montant engagement:', error);
        montantElement.textContent = 'Indisponible';
    }
}

async function afficherMontantsRecouvrement() {
    const montantRecouvrementElement = document.getElementById('montantRecouvrementPage');
    const montantRecouvreElement = document.getElementById('montantRecouvrePage');
    if (!montantRecouvrementElement || !montantRecouvreElement) return;

    try {
        const response = await fetch(@json(route('projects.recouvrement', $project)));

        if (!response.ok) {
            throw new Error(`Erreur HTTP: ${response.status}`);
        }

        const recouvrement = await response.json();
        const montantRecouvrement = parseMontant(recouvrement.montant_recouvrement);
        const montantRecouvre = parseMontant(recouvrement.montant_recouvre);

        montantRecouvrementElement.textContent = formatMontant(montantRecouvrement, 'FCFA');
        montantRecouvreElement.textContent = formatMontant(montantRecouvre, 'FCFA');
    } catch (error) {
        console.error('Erreur lors du chargement des montants de recouvrement:', error);
    }
}

function formatDate(dateStr) {
    if (!dateStr) return '—';
    const date = new Date(dateStr);
    return date.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

async function openDemandesAchat(codeProjet) {
    const modal = document.getElementById('demandesAchatModal');
    const content = document.getElementById('demandesAchatContent');

    // Show modal with loading state
    openDemandesAchatModal();
    content.innerHTML = `
        <div style="text-align: center; padding: 40px;">
            <div class="spinner" style="display: inline-block; width: 40px; height: 40px; border: 3px solid #e5e7eb; border-top-color: #0094d8; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="margin-top: 16px; color: #6b7280;">Chargement des demandes d'achat...</p>
        </div>
    `;

    try {
        const data = await chargerDemandesAchat(codeProjet);
        const items = data.items || [];

        if (items.length === 0) {
            content.innerHTML = `
                <div class="da-empty">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p style="font-weight: 600; margin-bottom: 8px;">Aucune demande d'achat</p>
                    <p>Aucune demande d'achat n'est associée à ce projet.</p>
                </div>
            `;
            return;
        }

        // Convert every purchase request to FCFA before calculating the total.
        // The ORDS API must expose the exchange rate as `taux_change` for each item.
        const totalMontantFcfa = items.reduce((sum, item) => sum + montantEngagementEnFcfa(item), 0);

        // Build table
        let tableHtml = `
            <table class="da-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Titre</th>
                        <th>Code Processus</th>
                        <th>Date Création</th>
                        <th style="text-align: right;">Montant Engagement</th>
                        <th style="text-align: right;">Taux de change</th>
                        <th style="text-align: right;">Montant en FCFA</th>
                    </tr>
                </thead>
                <tbody>
        `;

        items.forEach(item => {
            const itemDevise = item.devise || 'FCFA';
            const tauxChange = ['FCFA', 'XOF'].includes(itemDevise.toUpperCase()) ? 1 : parseMontant(item.taux_change);
            const montantFcfa = montantEngagementEnFcfa(item);
            tableHtml += `
                <tr>
                    <td style="font-weight: 600; color: #0094d8;">${item.demande_achat_id || '—'}</td>
                    <td>${item.titre || '—'}</td>
                    <td><code style="background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-size: 12px;">${item.code_processus || '—'}</code></td>
                    <td>${formatDate(item.date_creation)}</td>
                    <td style="text-align: right; font-weight: 600;">${formatMontant(item.montant_engagement, itemDevise)}</td>
                    <td style="text-align: right;">${new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 6 }).format(tauxChange)}</td>
                    <td style="text-align: right; font-weight: 600;">${formatMontant(montantFcfa, 'FCFA')}</td>
                </tr>
            `;
        });

        tableHtml += `
                </tbody>
            </table>
            <div class="da-total">
                <span>Total (${items.length} demande${items.length > 1 ? 's' : ''})</span>
                <span class="amount">${formatMontant(totalMontantFcfa, 'FCFA')}</span>
            </div>
        `;

        content.innerHTML = tableHtml;

    } catch (error) {
        console.error('Erreur lors du chargement des demandes d\'achat:', error);
        content.innerHTML = `
            <div class="da-empty">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color: #ef4444;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p style="font-weight: 600; margin-bottom: 8px; color: #ef4444;">Erreur de chargement</p>
                <p>Impossible de charger les demandes d'achat. Veuillez réessayer plus tard.</p>
                <p style="font-size: 12px; color: #9ca3af; margin-top: 8px;">${error.message}</p>
            </div>
        `;
    }
}

// Fermer le modal avec Escape (mis à jour)
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeDeleteModal();
        closeDemandesAchatModal();
    }
});

// Fermer le modal en cliquant sur l'overlay
document.getElementById('demandesAchatModal')?.addEventListener('click', (e) => {
    if (e.target.id === 'demandesAchatModal') {
        closeDemandesAchatModal();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    afficherMontantEngagementPage(@json($project->code_projet));
    afficherMontantsRecouvrement();
});
</script>
@endsection
