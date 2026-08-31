@extends('layouts.app')

@section('title', 'Modifier le projet')
@section('page-title', 'Modifier le projet #' . $project->code_projet)

@section('styles')
<style>
    /* ========== WIZARD PROGRESS ========== */
    .wizard-progress {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important;
        align-items: flex-start !important;
        justify-content: center !important;
        margin-bottom: 32px;
        padding: 24px;
        background: #f8fafc;
        border-radius: 12px;
        gap: 0;
    }

    .wizard-progress > * {
        display: inline-flex !important;
    }

    .wizard-step {
        display: inline-flex !important;
        flex-direction: column !important;
        align-items: center !important;
        position: relative;
        flex-shrink: 0;
    }

    .wizard-step-number {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e5e7eb;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 16px;
        transition: all 0.3s ease;
    }

    .wizard-step.active .wizard-step-number {
        background: #0094d8;
        color: white;
    }

    .wizard-step.completed .wizard-step-number {
        background: #10b981;
        color: white;
    }

    .wizard-step-label {
        margin-top: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #64748b;
        text-align: center;
        max-width: 100px;
        white-space: nowrap;
    }

    .wizard-step.active .wizard-step-label {
        color: #0094d8;
        font-weight: 600;
    }

    .wizard-step.completed .wizard-step-label {
        color: #10b981;
    }

    .wizard-connector {
        display: inline-block !important;
        width: 60px;
        height: 3px;
        background: #e5e7eb;
        margin: 0 12px;
        margin-top: 18px;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }

    .wizard-connector.completed {
        background: #10b981;
    }

    @media (max-width: 600px) {
        .wizard-progress {
            flex-direction: column !important;
            align-items: center !important;
            gap: 8px;
        }
        .wizard-connector {
            width: 3px;
            height: 30px;
            margin: 8px 0;
        }
    }

    /* Wizard Navigation */
    .wizard-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 32px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
    }

    .wizard-navigation .spacer {
        flex: 1;
    }

    /* Wizard Steps Content */
    .wizard-step-content {
        display: none;
    }

    .wizard-step-content.active {
        display: block;
    }

    /* ========== MODERN PROFESSIONAL DESIGN ========== */

    /* Form Sections */
    .form-section {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 32px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .section-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 12px;
        padding-top: 32px;
        border-bottom: 2px solid #f1f5f9;
    }

    .section-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #0094d8, #0070a0);
        color: white;
        border-radius: 8px;
        font-size: 18px;
        flex-shrink: 0;
    }

    .section-title {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
    }

    .section-subtitle {
        margin: 0;
        font-size: 13px;
        font-weight: 400;
        color: #64748b;
    }

    /* Grid Layout */
    .row {
        display: grid;
        gap: 16px;
        margin-bottom: 16px;
    }

    .row-2 { grid-template-columns: repeat(2, 1fr); }
    .row-3 { grid-template-columns: repeat(3, 1fr); }
    .row-4 { grid-template-columns: repeat(4, 1fr); }

    @media (max-width: 768px) {
        .row-2, .row-3, .row-4 { grid-template-columns: 1fr; }
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 0;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
    }

    .form-group small {
        display: block;
        font-size: 12px;
        color: #64748b;
        margin-top: 4px;
        line-height: 1.4;
    }

    /* Radio Buttons (modern style) */
    .radio-group {
        display: flex;
        gap: 12px;
        margin-top: 8px;
    }

    .radio-option {
        position: relative;
        flex: 1;
    }

    .radio-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
    }

    .radio-label {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .radio-option input[type="radio"]:checked + .radio-label {
        border-color: #0094d8;
        background: #f0f9ff;
        color: #0094d8;
    }

    .radio-label:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }

    /* Dynamic Lists */
    .dynamic-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 12px;
    }

    .dynamic-row {
        display: grid;
        gap: 10px;
        align-items: end;
        padding: 12px;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
    }

    .dynamic-row.grid-2 { grid-template-columns: 1fr auto; }
    .dynamic-row.grid-3 { grid-template-columns: 1fr 1fr auto; }
    .dynamic-row.grid-4 { grid-template-columns: 1fr 2fr 1fr auto; }
    .dynamic-row.grid-5 { grid-template-columns: 1fr 1.2fr 1fr 1.5fr auto; }
    .dynamic-row.grid-6 { grid-template-columns: 1fr 1fr 0.8fr 0.6fr 1.2fr auto; }

    @media (max-width: 768px) {
        .dynamic-row.grid-2,
        .dynamic-row.grid-3,
        .dynamic-row.grid-4,
        .dynamic-row.grid-5,
        .dynamic-row.grid-6 {
            grid-template-columns: 1fr;
        }
        .dynamic-row .btn.del {
            width: 100%;
        }
    }

    .add-row-btn {
        margin-top: 10px;
        width: 100%;
        justify-content: center;
        background: #f8fafc;
        color: #0094d8;
        border-color: #e5e7eb;
    }

    .add-row-btn:hover {
        background: #f1f5f9;
    }

    /* Buttons */
    .btn.del {
        background: #fef2f2;
        border-color: #fecaca;
        color: #dc2626;
        box-shadow: none;
        font-size: 13px;
        padding: 8px 12px;
    }

    .btn.del:hover {
        background: #fee2e2;
        border-color: #fca5a5;
    }

    /* Actions bar */
    .actions-bar {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding-top: 24px;
        border-top: 1px solid #e5e7eb;
        margin-top: 24px;
    }
    .wizard-nav {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 20px;
    }
    .wizard-step {
        border: 1px solid #cbd5e1;
        background: #f8fafc;
        color: #475569;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 13px;
        font-weight: 700;
        text-align: center;
        cursor: pointer;
    }
    .wizard-step.is-active {
        border-color: #0094d8;
        background: #e0f2fe;
        color: #0369a1;
    }
    .wizard-actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 12px;
    }

    @media (max-width: 768px) {
        .actions-bar {
            flex-direction: column;
        }
        .actions-bar .btn {
            width: 100%;
        }
        .wizard-nav {
            grid-template-columns: 1fr;
        }
        .wizard-actions {
            flex-direction: column;
        }
        .wizard-actions .btn {
            width: 100%;
        }
    }

    /* Info box */
    .info-box {
        display: flex;
        gap: 12px;
        padding: 12px 16px;
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 8px;
        margin-top: 12px;
        margin-bottom: 15px;
        font-size: 13px;
        color: #075985;
        line-height: 1.5;
    }

    .info-box-icon {
        flex-shrink: 0;
        font-size: 16px;
    }

    /* Conditional section */
    .conditional-section {
        margin-top: 20px;
    }

    .executant-divider {
        margin-top: 10px;
        padding-top: 20px;
        border-top: 2px dashed #e5e7eb;
    }

    /* Fixed stakeholders */
    .fixed-stake .btn.del { display: none !important; }
    .fixed-stake input[readonly] { background: #f9fafb; cursor: not-allowed; }

    /* Modal */
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
        background: linear-gradient(135deg, #0094d8, #0070a0);
        color: white;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .modal-header-icon {
        font-size: 32px;
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
        margin: 0 0 20px 0;
        color: #475569;
        line-height: 1.6;
        font-size: 15px;
    }

    .modal-body .highlight {
        background: #fff7ed;
        border-left: 4px solid #f97316;
        padding: 12px 16px;
        border-radius: 8px;
        margin-top: 16px;
        font-size: 14px;
        color: #9a3412;
    }

    .modal-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        padding: 20px 28px;
        background: #f8fafc;
        border-top: 1px solid #e5e7eb;
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

    /* Checkbox moderne pour Réalisé */
    .realise-checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
        height: 100%;
    }

    .realise-checkbox-wrapper label {
        font-size: 13px;
        color: #475569;
        font-weight: 500;
        margin: 0;
        white-space: nowrap;
    }

    .realise-checkbox-wrapper input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: #10b981;
        margin: 0;
    }

    /* Documents existants */
    .existing-documents {
        margin-bottom: 1.5rem;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 1rem;
        background: #f8fafc;
    }

    .document-item-edit {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem;
        border-bottom: 1px solid #e2e8f0;
        background: white;
        border-radius: 6px;
        margin-bottom: 0.5rem;
    }

    .document-item-edit:last-child {
        margin-bottom: 0;
    }

    .doc-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        flex: 1;
    }

    .doc-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 0.95rem;
    }

    .doc-details {
        font-size: 0.875rem;
        color: #64748b;
    }

    .doc-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .doc-actions .btn {
        font-size: 13px;
        padding: 6px 12px;
        white-space: nowrap;
    }

    .deliverable-document-current {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        margin-top: 6px;
        padding: 8px 10px;
        border: 1px solid #a7f3d0;
        border-radius: 6px;
        background: #ecfdf5;
    }

    .deliverable-document-current small {
        min-width: 0;
        color: #047857;
        overflow-wrap: anywhere;
    }

    .deliverable-document-delete {
        flex: 0 0 auto;
        border: 0;
        background: transparent;
        color: #dc2626;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: underline;
    }

    .deliverable-document-delete:disabled {
        cursor: wait;
        opacity: 0.6;
    }

    /* File upload wrapper */
    .file-upload-wrapper {
        position: relative;
    }

    .file-upload-wrapper.compact {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .file-input-hidden {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }

    .btn-file-upload {
        padding: 8px 14px;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 13px;
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }

    .btn-file-upload:hover {
        background: #f1f5f9;
        border-color: #94a3b8;
    }

    .file-name-display {
        font-size: 13px;
        color: #64748b;
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-head">
        <h2 class="title">Modifier le projet {{ $project->code_projet }}</h2>
        <div class="toolbar">
            <a class="btn sec" href="{{ route('projects.show', $project) }}">← Retour au projet</a>
        </div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('projects.update', $project) }}" id="projectForm" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @if($errors->any())
                <div class="validation-errors" style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; padding: 16px; margin-bottom: 20px;">
                    <div style="display: flex; align-items: flex-start; gap: 12px;">
                        <span style="font-size: 20px;">⚠️</span>
                        <div style="flex: 1;">
                            <h4 style="color: #dc2626; margin: 0 0 8px 0; font-size: 14px; font-weight: 600;">
                                Veuillez corriger les erreurs suivantes :
                            </h4>
                            <ul style="margin: 0; padding-left: 20px; color: #b91c1c; font-size: 13px; line-height: 1.6;">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Wizard Progress -->
            <div class="wizard-progress">
                <div class="wizard-step active" data-step="1">
                    <div class="wizard-step-number">1</div>
                    <div class="wizard-step-label">Projet & Organisation</div>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="2">
                    <div class="wizard-step-number">2</div>
                    <div class="wizard-step-label">Contenu & Objectifs</div>
                </div>
                <div class="wizard-connector"></div>
                <div class="wizard-step" data-step="3">
                    <div class="wizard-step-number">3</div>
                    <div class="wizard-step-label">Équipe & Risques</div>
                </div>
            </div>

            <!-- ========== ÉTAPE 1: Projet & Organisation ========== -->
            <div class="wizard-step-content active" id="wizard-step-1">

            <!-- Informations générales -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">📋</div>
                    <div>
                        <h3 class="section-title">Informations générales</h3>
                        <p class="section-subtitle">Données de base du projet</p>
                    </div>
                </div>

                <div class="row row-2">
                    <div class="form-group">
                        <label>Nom du projet *
                            <input type="text" name="nom_projet" required placeholder="Nom officiel du projet" value="{{ old('nom_projet', $project->nom_projet) }}">
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Type *
                            <select name="type_projet" id="typeProjet" required>
                                <option value="">— Sélectionner —</option>
                                <option value="Interne" @selected(old('type_projet', $project->type_projet?->value) == 'Interne')>Interne</option>
                                <option value="Externe" @selected(old('type_projet', $project->type_projet?->value) == 'Externe')>Externe</option>
                            </select>
                        </label>
                    </div>
                </div>

                <!-- Salesforce block (shown if Externe) -->
                <div id="sfOppBlock" style="display:{{ old('type_projet', $project->type_projet?->value) == 'Externe' ? 'block' : 'none' }}">
                    <label>Opportunité Salesforce</label>
                    <div class="text-muted" style="margin:8px 0">
                        Opportunité actuelle: <strong>{{ $project->sf_opportunity_name ?? 'Non définie' }}</strong>
                    </div>
                    <input type="text" id="sfOppSearch" placeholder="Rechercher pour changer (au moins 2 caractères)…" autocomplete="off" style="margin:8px 0">
                    <select name="sf_opportunity_id" id="sfOpportunitySelect">
                        <option value="{{ old('sf_opportunity_id', $project->sf_opportunity_id) }}">— Conserver l'opportunité actuelle —</option>
                    </select>
                    <button type="button" id="sfOppMoreBtn" class="btn sec" style="margin-top:8px; display:none">Charger plus…</button>
                    <div class="text-muted" id="sfOppMeta" style="margin-top:6px"></div>
                    <input type="hidden" name="sf_opportunity_name" id="sfOppName" value="{{ old('sf_opportunity_name', $project->sf_opportunity_name) }}">
                    <input type="hidden" name="sf_opportunity_stage" id="sfOppStage" value="{{ old('sf_opportunity_stage', $project->sf_opportunity_stage) }}">
                    <input type="hidden" name="sf_opportunity_amount" id="sfOppAmount" value="{{ old('sf_opportunity_amount', $project->sf_opportunity_amount) }}">
                </div>

                <div class="row row-2">
                    <div class="form-group">
                        <label>Nature de projet *
                            <select name="nature_projet" required>
                                <option value="">— Sélectionner —</option>
                                <option value="B2B" @selected(old('nature_projet', $project->nature_projet?->value) == 'B2B')>B2B</option>
                                <option value="B2C" @selected(old('nature_projet', $project->nature_projet?->value) == 'B2C')>B2C</option>
                                <option value="GOV" @selected(old('nature_projet', $project->nature_projet?->value) == 'GOV')>GOV</option>
                                <option value="Autres" @selected(old('nature_projet', $project->nature_projet?->value) == 'Autres')>Autres</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Budget initial (FCFA)
                            <input type="text" name="budget_initial" placeholder="ex. 1 500 000" value="{{ old('budget_initial', $project->budget_initial ? number_format($project->budget_initial, 0, ',', ' ') : '') }}">
                        </label>
                    </div>
                </div>




            <div class="row row-3">
                <div class="form-group">
                    <label>Axe stratégique visé *
                        <select id="axe_strategique_select" name="axe_strategique_select" required>
                            <option value="">-- Sélectionner un axe --</option>
                            @php
                                $axes = ['Organisation', 'Stratégie', 'Capital Humain', 'Produits & Services', 'Communication', 'Commercialisation', 'Déploiement & Exploitation', 'Reporting Financier'];
                                $currentAxe = old('axe_strategique', $project->axe_strategique);
                                $isOther = !in_array($currentAxe, $axes);
                            @endphp
                            @foreach($axes as $axe)
                                <option {{ $currentAxe == $axe ? 'selected' : '' }}>{{ $axe }}</option>
                            @endforeach
                            <option value="Autre (préciser)" {{ $isOther ? 'selected' : '' }}>Autre (préciser)</option>
                        </select>
                    </label>
                    <div id="axe_autre_wrap" style="display:{{ $isOther ? 'block' : 'none' }}; margin-top:8px">
                        <input id="axe_autre" type="text" placeholder="Préciser l'axe stratégique…" value="{{ $isOther ? $currentAxe : '' }}">
                    </div>
                    <input type="hidden" id="axe_strategique" name="axe_strategique" value="{{ $currentAxe }}">
                </div>
                <div class="form-group">
                        <label>Montant encaissement (FCFA)
                            <input type="text" name="montant_encaissement" placeholder="ex. 1 000 000" value="{{ old('montant_encaissement', $project->montant_encaissement ? number_format($project->montant_encaissement, 0, ',', ' ') : '') }}">
                        </label>
                        <small>Montant reçu pour ce projet</small>
                </div>
                <div class="form-group">
                        <label>Montant décaissement (FCFA)
                            <input type="text" name="montant_decaissement_2" placeholder="ex. 700 000" value="{{ old('montant_decaissement_2', $project->montant_decaissement_2 ? number_format($project->montant_decaissement_2, 0, ',', ' ') : '') }}">
                        </label>
                        <small>Montant décaissé saisi depuis le formulaire</small>
                </div>
            </div>
                <!-- Financial Fields -->
                <div class="row row-2">
                    <div class="form-group">
                        <label>Statut financier *
                            <select name="statut_financier" required>
                                <option value="">— Sélectionner —</option>
                                <option value="Non démarré" {{ old('statut_financier', $project->statut_financier?->value) == 'Non démarré' ? 'selected' : '' }}>Non démarré</option>
                                <option value="En cours" {{ old('statut_financier', $project->statut_financier?->value) == 'En cours' ? 'selected' : '' }}>En cours</option>
                                <option value="Terminé" {{ old('statut_financier', $project->statut_financier?->value) == 'Terminé' ? 'selected' : '' }}>Terminé</option>
                            </select>
                        </label>
                        <small>État du financement du projet</small>
                    </div>
                    <div class="form-group">
                        <label>Ressource à mobiliser *</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" name="ressource_a_mobiliser" value="1" id="ressource_oui" {{ old('ressource_a_mobiliser', $project->ressource_a_mobiliser) ? 'checked' : '' }}>
                                <label for="ressource_oui" class="radio-label">
                                    <span>✅</span>
                                    <span>Oui</span>
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="ressource_a_mobiliser" value="0" id="ressource_non" {{ old('ressource_a_mobiliser', $project->ressource_a_mobiliser) === false || old('ressource_a_mobiliser', $project->ressource_a_mobiliser) === 0 ? 'checked' : '' }}>
                                <label for="ressource_non" class="radio-label">
                                    <span>❌</span>
                                    <span>Non</span>
                                </label>
                            </div>
                        </div>
                        <small>Des ressources humaines ou matérielles sont-elles nécessaires ?</small>
                    </div>
                </div>

                <!-- Champs conditionnels ressources -->
                <div class="row row-2" id="resource-fields-row" style="display: none;">
                    <div class="form-group">
                        <label>Type de ressource *
                            <select name="resource_type" id="resource_type">
                                <option value="">— Sélectionner —</option>
                                <option value="GUT" {{ old('resource_type', $project->resource_type?->value) == 'GUT' ? 'selected' : '' }}>GUT (Groupe Univers Télécom)</option>
                                <option value="Banque" {{ old('resource_type', $project->resource_type?->value) == 'Banque' ? 'selected' : '' }}>Banque</option>
                            </select>
                        </label>
                        <small>Source de financement du projet</small>
                    </div>
                    <div class="form-group" id="bank-field-group" style="display: none;">
                        <label>Banque *
                            <select name="resource_bank" id="resource_bank">
                                <option value="">— Sélectionner une banque —</option>
                                <option value="BIS" {{ old('resource_bank', $project->resource_bank?->value) == 'BIS' ? 'selected' : '' }}>BIS (Banque Islamique du Sénégal)</option>
                                <option value="FBN Bank" {{ old('resource_bank', $project->resource_bank?->value) == 'FBN Bank' ? 'selected' : '' }}>FBN Bank</option>
                            </select>
                        </label>
                        <small>Sélectionner la banque de financement</small>
                    </div>
                </div>
                <!-- ========== SECTION CONTRACTANT (always visible) ========== -->
                <div class="section-header" style="margin-top: 2rem;">
                    <div class="section-icon">💼</div>
                    <div>
                        <h3 class="section-title">Contractant</h3>
                        <p class="section-subtitle">Qui contracte/commande le projet</p>
                    </div>
                </div>

                <div class="row row-4">
                    <div class="form-group">
                        <label>Filiale Contractant *
                            <select name="filiale_contractant" id="filiale_contractant" required>
                                <option value="">— Sélectionner une filiale —</option>
                                @foreach($filialesOptions as $filiale)
                                    <option value="{{ $filiale->nom_filiale }}" {{ old('filiale_contractant', $project->filiale_contractant) == $filiale->nom_filiale ? 'selected' : '' }}>{{ $filiale->nom_filiale }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Direction Contractant
                            <select name="direction_contractant" id="direction_contractant">
                                <option value="">— Sélectionner une direction —</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Owner Contractant *
                            @php $currentOwnerContractant = old('owner_contractant', $project->owner_contractant); @endphp
                            <select name="owner_contractant" id="owner_contractant" required>
                                <option value="">— Sélectionner un owner —</option>
                                @if($currentOwnerContractant && !$employees->contains('prenom_nom', $currentOwnerContractant))
                                    <option value="{{ $currentOwnerContractant }}" selected>{{ $currentOwnerContractant }} (non trouvé dans l'annuaire)</option>
                                @endif
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->prenom_nom }}" {{ $currentOwnerContractant == $emp->prenom_nom ? 'selected' : '' }}>
                                        {{ $emp->prenom_nom }}{{ $emp->email ? ' — '.$emp->email : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Account Manager *
                            @php $currentAccountManager = old('account_manager', $project->account_manager ?? $project->owner_contractant); @endphp
                            <select name="account_manager" id="account_manager" required>
                                <option value="">— Sélectionner un account manager —</option>
                                @if($currentAccountManager && !$employees->contains('prenom_nom', $currentAccountManager))
                                    <option value="{{ $currentAccountManager }}" selected>{{ $currentAccountManager }} (non trouvé dans l'annuaire)</option>
                                @endif
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->prenom_nom }}" {{ $currentAccountManager == $emp->prenom_nom ? 'selected' : '' }}>
                                        {{ $emp->prenom_nom }}{{ $emp->email ? ' — '.$emp->email : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>

                <!-- ========== QUESTION: Même exécutant? ========== -->
                <div class="form-group" style="margin-top: 2rem;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 8px;">
                        L'exécutant est-il le même que le contractant ?
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="same_executant" value="oui" id="same_executant_oui" {{ old('filiale_executant', $project->filiale_executant) == old('filiale_contractant', $project->filiale_contractant) && old('owner_executant', $project->owner_executant) == old('owner_contractant', $project->owner_contractant) ? 'checked' : '' }}>
                            <label for="same_executant_oui" class="radio-label">
                                <span>✅</span>
                                <span>Oui (même structure)</span>
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="same_executant" value="non" id="same_executant_non" {{ old('filiale_executant', $project->filiale_executant) != old('filiale_contractant', $project->filiale_contractant) || old('owner_executant', $project->owner_executant) != old('owner_contractant', $project->owner_contractant) ? 'checked' : '' }}>
                            <label for="same_executant_non" class="radio-label">
                                <span>🔄</span>
                                <span>Non (structure différente)</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="info-box">
                    <span class="info-box-icon">💡</span>
                    <span><strong>Note:</strong> Si vous changez pour "Non", pensez à vérifier les tâches automatiques qui auraient dû être ajoutées lors de la création.</span>
                </div>

                <!-- ========== SECTION EXÉCUTANT (conditional) ========== -->
                <div id="executant_section" class="conditional-section">
                    <div class="section-header">
                        <div class="section-icon">🔧</div>
                        <div>
                            <h3 class="section-title">Exécutant</h3>
                            <p class="section-subtitle">Qui réalise le projet</p>
                        </div>
                    </div>

                    <div class="row row-3">
                        <div class="form-group">
                            <label>Filiale Exécutant *
                                <select name="filiale_executant" id="filiale_executant" required>
                                    <option value="">— Sélectionner une filiale —</option>
                                    @foreach($filialesOptions as $filiale)
                                        <option value="{{ $filiale->nom_filiale }}" {{ old('filiale_executant', $project->filiale_executant) == $filiale->nom_filiale ? 'selected' : '' }}>{{ $filiale->nom_filiale }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Direction Exécutant
                                <select name="direction_executant" id="direction_executant">
                                    <option value="">— Sélectionner une direction —</option>
                                </select>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Chef de Projet *
                                @php $currentOwnerExecutant = old('owner_executant', $project->owner_executant); @endphp
                                <select name="owner_executant" id="owner_executant" required>
                                    <option value="">— Sélectionner un chef —</option>
                                    @if($currentOwnerExecutant && !$employees->contains('prenom_nom', $currentOwnerExecutant))
                                        <option value="{{ $currentOwnerExecutant }}" selected>{{ $currentOwnerExecutant }} (non trouvé dans l'annuaire)</option>
                                    @endif
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->prenom_nom }}" {{ $currentOwnerExecutant == $emp->prenom_nom ? 'selected' : '' }}>
                                            {{ $emp->prenom_nom }}{{ $emp->email ? ' — '.$emp->email : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Hidden fields for auto-copy when "Oui" -->
                <input type="hidden" name="filiale_executant_hidden" id="filiale_executant_hidden">
                <input type="hidden" name="direction_executant_hidden" id="direction_executant_hidden">
                <input type="hidden" name="owner_executant_hidden" id="owner_executant_hidden">
                <div class="executant-divider"></div>

                <div class="row row-3">
                    <div class="form-group">
                        <label>Date de démarrage prévue *
                            <input type="date" id="date_demarrage" name="date_demarrage" required value="{{ old('date_demarrage', $project->date_demarrage ? $project->date_demarrage->format('Y-m-d') : '') }}">
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Date de fin estimée
                            <input type="date" id="date_fin" name="date_fin" value="{{ old('date_fin', $project->date_fin ? $project->date_fin->format('Y-m-d') : '') }}">
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Statut initial *
                            <select name="statut_initial" required>
                                <option value="">-- Sélectionner un statut --</option>
                                <option value="Planifié" @selected(old('statut_initial', $project->statut_initial?->value) == 'Planifié')>Planifié</option>
                                <option value="En cours" @selected(old('statut_initial', $project->statut_initial?->value) == 'En cours')>En cours</option>
                                <option value="Pause" @selected(old('statut_initial', $project->statut_initial?->value) == 'Pause')>Pause</option>
                                <option value="Suspendu" @selected(old('statut_initial', $project->statut_initial?->value) == 'Suspendu')>Suspendu</option>
                                <option value="Mis en pause" @selected(old('statut_initial', $project->statut_initial?->value) == 'Mis en pause')>Mis en pause</option>
                                <option value="Retard" @selected(old('statut_initial', $project->statut_initial?->value) == 'Retard')>Retard</option>
                                <option value="Terminé" @selected(old('statut_initial', $project->statut_initial?->value) == 'Terminé')>Terminé</option>
                            </select>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Notes et remarques de la semaine -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">📝</div>
                    <div>
                        <h3 class="section-title">Notes et remarques de la semaine</h3>
                        <p class="section-subtitle">Informations complémentaires de la semaine sur le projet</p>
                    </div>
                </div>
                <div class="form-group">
                    <label>Notes de la semaine
                        <textarea name="notes" rows="6" placeholder="Notes, observations ou informations complémentaires de la semaine sur le projet...">{{ old('notes', $project->notes) }}</textarea>
                    </label>
                    <small>Notes, observations ou informations complémentaires de la semaine (max 5000 caractères)</small>
                </div>
            </div>

            </div><!-- Fin ÉTAPE 1 -->

            <!-- ========== ÉTAPE 2: Contenu & Objectifs ========== -->
            <div class="wizard-step-content" id="wizard-step-2">

            <!-- Objectif -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">🎯</div>
                    <div>
                        <h3 class="section-title">Objectifs du projet</h3>
                        <p class="section-subtitle">Finalité et résultats attendus</p>
                    </div>
                </div>
                <div class="form-group">
                    <textarea name="objectif_projet" placeholder="Décrire l'objectif principal…" required>{{ old('objectif_projet', $project->objectif_projet) }}</textarea>
                </div>
            </div>

            <!-- Contexte -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">📖</div>
                    <div>
                        <h3 class="section-title">Contexte</h3>
                        <p class="section-subtitle">Situation et environnement du projet</p>
                    </div>
                </div>
                <div class="form-group">
                    <textarea name="contexte" placeholder="Décrire le contexte du projet…">{{ old('contexte', $project->contexte) }}</textarea>
                </div>
            </div>

            <!-- Synthèse -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">📝</div>
                    <div>
                        <h3 class="section-title">Synthèse</h3>
                        <p class="section-subtitle">Résumé exécutif du projet</p>
                    </div>
                </div>
                <div class="form-group">
                    <textarea name="synthese" placeholder="Synthèse du projet…">{{ old('synthese', $project->synthese) }}</textarea>
                </div>
            </div>

            <!-- Maintenance GLPI -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">🔧</div>
                    <div>
                        <h3 class="section-title">Maintenance GLPI</h3>
                        <p class="section-subtitle">Gestion de la maintenance sur la plateforme GLPI</p>
                    </div>
                </div>

                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                        <input type="checkbox" name="maintenance_glpi" value="1" {{ old('maintenance_glpi', $project->maintenance_glpi) ? 'checked' : '' }}>
                        <span>Ce projet fait-il l'objet de maintenance ?</span>
                    </label>
                    <small>
                        Si coché, un projet sera automatiquement créé ou mis à jour sur la plateforme GLPI
                        (<a href="https://infra.groupe-universtelecom.com" target="_blank" rel="noopener">infra.groupe-universtelecom.com</a>)
                        @if($project->glpi_project_id)
                            <br><span style="color: #059669;">✓ Projet déjà lié à GLPI (ID: {{ $project->glpi_project_id }})</span>
                        @endif
                    </small>
                </div>
            </div>

            <!-- Contractualisation -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">📄</div>
                    <div>
                        <h3 class="section-title">Contractualisation</h3>
                        <p class="section-subtitle">Formalisation contractuelle du projet</p>
                    </div>
                </div>

                <div class="form-group">
                    <label>Contractualisation requise ? *</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="contractualisation" value="1" id="contractualisation_oui" {{ old('contractualisation', $project->contractualisation) ? 'checked' : '' }} onchange="toggleContractualisation()">
                            <label for="contractualisation_oui" class="radio-label">
                                <span>✅</span>
                                <span>Oui</span>
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="contractualisation" value="0" id="contractualisation_non" {{ old('contractualisation', $project->contractualisation) === false || old('contractualisation', $project->contractualisation) === 0 ? 'checked' : '' }} onchange="toggleContractualisation()">
                            <label for="contractualisation_non" class="radio-label">
                                <span>❌</span>
                                <span>Non</span>
                            </label>
                        </div>
                    </div>
                    <small>Le projet nécessite-t-il un document contractuel formel ?</small>
                </div>

                <div id="contract_section" style="display: none;">
                    <div class="row row-2">
                        <div class="form-group">
                            <label>Type de document (optionnel)
                                <select name="contractualisation_type" id="contractualisation_type">
                                    <option value="">— Sélectionner —</option>
                                    <option value="Bon de commande" {{ old('contractualisation_type', $project->contractualisation_type?->value) == 'Bon de commande' ? 'selected' : '' }}>Bon de commande</option>
                                    <option value="Annexes" {{ old('contractualisation_type', $project->contractualisation_type?->value) == 'Annexes' ? 'selected' : '' }}>Annexes</option>
                                </select>
                            </label>
                            <small>Nature du document contractuel</small>
                        </div>
                        <div class="form-group">
                            @php
                                $contractDoc = $project->documents()->where('document_type', App\Enums\DocumentType::CONTRACTUALISATION->value)->first();
                            @endphp
                            <label>Document contractuel (optionnel)
                                <input type="file" name="contractualisation_document" id="contractualisation_document" accept=".pdf,.doc,.docx,.zip,.pptx,.xlsx,.xls" onchange="validateFileSize(this)">
                            </label>
                            @if($contractDoc)
                                <small style="display: block; margin-top: 4px; color: #059669;">
                                     Document actuel: <strong>{{ $contractDoc->original_filename }}</strong>
                                    <br>Choisir un nouveau fichier remplacera l'ancien.
                                </small>
                            @else
                                <small style="display: block; margin-top: 4px;">Aucun document actuellement. Vous pouvez en uploader un.</small>
                            @endif
                            <small style="display: block; margin-top: 4px;">Format accepté : PDF, DOC, DOCX, ZIP, XLSX, PPTX (max 50 MB)</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">✅</div>
                    <div>
                        <h3 class="section-title">Actions</h3>
                        <p class="section-subtitle">Tâches à réaliser dans le cadre du projet</p>
                    </div>
                </div>
                <div class="dynamic-list" id="actionsContainer"></div>
                <button class="btn sec add-row-btn" type="button" onclick="addAction()">+ Ajouter une action</button>
            </div>

            <!-- Livrables -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">📦</div>
                    <div>
                        <h3 class="section-title">Livrables attendus</h3>
                        <p class="section-subtitle">Résultats tangibles à produire</p>
                    </div>
                </div>
                <div class="dynamic-list" id="deliverablesContainer"></div>
                <button class="btn sec add-row-btn" type="button" onclick="addDeliverable()">+ Ajouter un livrable</button>
            </div>

            </div><!-- Fin ÉTAPE 2 -->

            <!-- ========== ÉTAPE 3: Équipe & Risques ========== -->
            <div class="wizard-step-content" id="wizard-step-3">

            <!-- Parties prenantes -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">👥</div>
                    <div>
                        <h3 class="section-title">Parties prenantes (RACI)</h3>
                        <p class="section-subtitle">Responsables, Approuveurs, Consultés, Informés</p>
                    </div>
                </div>
                <div class="dynamic-list" id="stakeholdersContainer"></div>
                <button class="btn sec add-row-btn" type="button" onclick="addStakeholder()">+ Ajouter une partie prenante</button>
            </div>

            <!-- Parties prenantes externes -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">🌐</div>
                    <div>
                        <h3 class="section-title">Parties prenantes externes</h3>
                        <p class="section-subtitle">Partenaires, clients, fournisseurs externes</p>
                    </div>
                </div>
                <div class="dynamic-list" id="externalStakeholdersContainer"></div>
                <button class="btn sec add-row-btn" type="button" onclick="addExternalStakeholder()">+ Ajouter une partie prenante externe</button>
            </div>
                        <!-- Documents généraux du projet -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">📁</div>
                    <div>
                        <h3 class="section-title">Documents du projet</h3>
                        <p class="section-subtitle">Documents généraux, supports, présentations</p>
                    </div>
                </div>

                @if($existingGeneralDocs && count($existingGeneralDocs) > 0)
                <div class="existing-documents">
                    <h4 style="margin: 0 0 1rem 0; font-size: 0.95rem; color: #475569;">Documents existants</h4>
                    @foreach($existingGeneralDocs as $doc)
                    <div class="document-item-edit" data-doc-id="{{ $doc['id'] }}">
                        <div class="doc-info">
                            <span class="doc-name">{{ $doc['name'] }}</span>
                            <span class="doc-details">{{ $doc['filename'] }} • {{ $doc['size'] }} • {{ $doc['uploaded_at'] }}</span>
                        </div>
                        <div class="doc-actions">
                            <button type="button"
                                    onclick="deleteDocument({{ $project->id }}, {{ $doc['id'] }}, this)"
                                    class="btn del">Supprimer</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif

                <h4 style="margin: 1.5rem 0 1rem 0; font-size: 0.95rem; color: #475569;">Ajouter de nouveaux documents</h4>
                <div class="dynamic-list" id="documentsGenerauxContainer"></div>
                <button class="btn sec add-row-btn" type="button" onclick="addGeneralDocument()">+ Ajouter un document</button>
            </div>


            <!-- Enjeux / Contraintes / Risques / REX -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">⚠️</div>
                    <div>
                        <h3 class="section-title">Enjeux, contraintes, risques et REX</h3>
                        <p class="section-subtitle">Points d'attention et facteurs critiques</p>
                    </div>
                </div>
                <div class="dynamic-list" id="issuesContainer"></div>
                <button class="btn sec add-row-btn" type="button" onclick="addIssue()">+ Ajouter un élément</button>
            </div>

            <!-- Prochaine étape -->
            <div class="form-section">
                <div class="section-header">
                    <div class="section-icon">➡️</div>
                    <div>
                        <h3 class="section-title">Prochaine étape</h3>
                        <p class="section-subtitle">Action immédiate à entreprendre</p>
                    </div>
                </div>
                <div class="form-group">
                    <textarea name="prochaine_etape" placeholder="Ex. valider le cadrage, lancer l'AO…">{{ old('prochaine_etape', $project->prochaine_etape) }}</textarea>
                </div>
            </div>

            </div><!-- Fin ÉTAPE 3 -->

            <!-- Wizard Navigation -->
            <div class="wizard-navigation">
                <button type="button" class="btn sec" id="wizardPrevBtn" onclick="wizardPrev()" style="display: none;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Précédent
                </button>
                <div class="spacer"></div>
                <a href="{{ route('projects.show', $project) }}" class="btn sec">Annuler</a>
                <button type="button" class="btn" id="wizardNextBtn" onclick="wizardNext()">
                    Suivant
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <button type="button" class="btn success" id="wizardSubmitBtn" onclick="openConfirmModal()" style="display: none;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width:18px;height:18px">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Confirmer la modification</h3>
        </div>
        <div class="modal-body">
            <p>Vous êtes sur le point de modifier le projet <strong>{{ $project->code_projet }}</strong>. Veuillez confirmer que toutes les modifications sont correctes.</p>
            <div class="highlight">
                <strong>⚠️ Attention :</strong> Cette action va mettre à jour toutes les informations du projet.
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn sec" onclick="closeConfirmModal()">Annuler</button>
            <button type="button" class="btn" onclick="confirmSubmit()">Confirmer les modifications</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// ========== WIZARD STATE & FUNCTIONS ==========
let currentWizardStep = 1;
const totalWizardSteps = 3;

function updateWizardUI() {
    // Update step content visibility
    document.querySelectorAll('.wizard-step-content').forEach((el, i) => {
        el.classList.toggle('active', i + 1 === currentWizardStep);
    });

    // Update progress indicator
    document.querySelectorAll('.wizard-step').forEach((el, i) => {
        const stepNum = i + 1;
        el.classList.remove('active', 'completed');
        if (stepNum === currentWizardStep) {
            el.classList.add('active');
        } else if (stepNum < currentWizardStep) {
            el.classList.add('completed');
        }
    });

    // Update connectors
    document.querySelectorAll('.wizard-connector').forEach((el, i) => {
        el.classList.toggle('completed', i + 1 < currentWizardStep);
    });

    // Update navigation buttons
    document.getElementById('wizardPrevBtn').style.display = currentWizardStep > 1 ? 'flex' : 'none';
    document.getElementById('wizardNextBtn').style.display = currentWizardStep < totalWizardSteps ? 'flex' : 'none';
    document.getElementById('wizardSubmitBtn').style.display = (currentWizardStep === 1 || currentWizardStep === totalWizardSteps) ? 'flex' : 'none';

    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function validateWizardStep(step) {
    const stepContent = document.getElementById('wizard-step-' + step);
    if (!stepContent) return true;

    // Check required fields in current step
    const requiredFields = stepContent.querySelectorAll('[required]');
    let isValid = true;

    requiredFields.forEach(field => {
        if (!field.value || field.value.trim() === '') {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });

    if (!isValid) {
        alert('Veuillez remplir tous les champs obligatoires avant de continuer.');
    }

    return isValid;
}

async function wizardNext() {
    if (!validateWizardStep(currentWizardStep)) {
        return;
    }

    // Optional: Server-side validation via AJAX
    try {
        const formData = new FormData(document.getElementById('projectForm'));
        formData.append('wizard_step', currentWizardStep);

        const response = await fetch('{{ route("projects.wizard.validate.edit", ["project" => $project->id, "step" => "__STEP__"]) }}'.replace('__STEP__', currentWizardStep), {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });

        const result = await response.json();

        if (!result.ok && result.errors) {
            let errorMsg = 'Erreurs de validation:\n';
            Object.values(result.errors).forEach(errs => {
                errs.forEach(e => errorMsg += '- ' + e + '\n');
            });
            alert(errorMsg);
            return;
        }
    } catch (e) {
        // Continue even if AJAX fails (offline mode)
        console.warn('Wizard validation skipped:', e);
    }

    if (currentWizardStep < totalWizardSteps) {
        currentWizardStep++;
        updateWizardUI();
    }
}

function wizardPrev() {
    if (currentWizardStep > 1) {
        currentWizardStep--;
        updateWizardUI();
    }
}

// Initialize wizard on page load
document.addEventListener('DOMContentLoaded', function() {
    updateWizardUI();
});

// ========== END WIZARD ==========

// Employee data for stakeholders
window.EMP_DATA = @json($employees);

// Directions mapping
const DIR_MAP = @json($directionsMap);

// Existing project data - Use old() values if validation failed, otherwise use existing data
@php
    $hasValidationErrors = $errors->any();

    // Actions
    $actionsData = $hasValidationErrors && old('actions')
        ? old('actions')
        : $existingActions;

    // Deliverables - reconstruct from old() if validation failed
    if ($hasValidationErrors && old('livrable_nom')) {
        $deliverablesData = [];
        $livrableNoms = old('livrable_nom', []);
        $livrableDescs = old('livrable_desc', []);
        $livrableDates = old('livrable_date', []);
        $livrableRealise = old('livrable_realise', []);
        foreach ($livrableNoms as $i => $nom) {
            $deliverablesData[] = [
                'nom' => $nom ?? '',
                'desc' => $livrableDescs[$i] ?? '',
                'date' => $livrableDates[$i] ?? '',
                'realise' => isset($livrableRealise[$i]),
                'document' => $existingDeliverables[$i]['document'] ?? null
            ];
        }
    } else {
        $deliverablesData = $existingDeliverables;
    }

    // Stakeholders - reconstruct from old() if validation failed
    if ($hasValidationErrors && old('stake_emp_id')) {
        $stakeholdersData = [];
        $stakeEmpIds = old('stake_emp_id', []);
        $stakeRoles = old('stake_role', []);
        $stakeAttentes = old('stake_attentes', []);
        foreach ($stakeEmpIds as $i => $empId) {
            $stakeholdersData[] = [
                'emp_id' => $empId ?? '',
                'role' => $stakeRoles[$i] ?? '',
                'attentes' => $stakeAttentes[$i] ?? ''
            ];
        }
    } else {
        $stakeholdersData = $existingStakeholders;
    }

    // External Stakeholders - reconstruct from old() if validation failed
    if ($hasValidationErrors && old('ext_stake_organisation')) {
        $extStakeholdersData = [];
        $extOrganisations = old('ext_stake_organisation', []);
        $extNomsComplets = old('ext_stake_nom_complet', []);
        $extEmails = old('ext_stake_email', []);
        $extRoles = old('ext_stake_role', []);
        $extAttentes = old('ext_stake_attentes', []);
        foreach ($extOrganisations as $i => $org) {
            $extStakeholdersData[] = [
                'organisation' => $org ?? '',
                'nom_complet' => $extNomsComplets[$i] ?? '',
                'email' => $extEmails[$i] ?? '',
                'role' => $extRoles[$i] ?? '',
                'attentes' => $extAttentes[$i] ?? ''
            ];
        }
    } else {
        $extStakeholdersData = $existingExternalStakeholders;
    }

    // Issues - reconstruct from old() if validation failed
    if ($hasValidationErrors && old('issue_cat')) {
        $issuesData = [];
        $issueCats = old('issue_cat', []);
        $issueDetails = old('issue_detail', []);
        foreach ($issueCats as $i => $cat) {
            $issuesData[] = [
                'cat' => $cat ?? '',
                'detail' => $issueDetails[$i] ?? ''
            ];
        }
    } else {
        $issuesData = $existingIssues;
    }
@endphp
const EXISTING_ACTIONS = @json($actionsData);
const EXISTING_DELIVERABLES = @json($deliverablesData);
const EXISTING_STAKEHOLDERS = @json($stakeholdersData);
const EXISTING_EXTERNAL_STAKEHOLDERS = @json($extStakeholdersData);
const EXISTING_ISSUES = @json($issuesData);
const PROJECT_ID = @json($project->id);


// Elements
const startInput = document.getElementById('date_demarrage');
const endInput = document.getElementById('date_fin');
const form = document.getElementById('projectForm');
const axeSel = document.getElementById('axe_strategique_select');
const axeWrap = document.getElementById('axe_autre_wrap');
const axeOther = document.getElementById('axe_autre');
const axeFinal = document.getElementById('axe_strategique');
const OTHER_LABEL = 'Autre (préciser)';

// Contractant/Executant elements
const filialeContractant = document.getElementById('filiale_contractant');
const directionContractant = document.getElementById('direction_contractant');
const ownerContractant = document.getElementById('owner_contractant');
const accountManager = document.getElementById('account_manager');
const filialeExecutant = document.getElementById('filiale_executant');
const directionExecutant = document.getElementById('direction_executant');
const ownerExecutant = document.getElementById('owner_executant');
const executantSection = document.getElementById('executant_section');
const sameExecutantOui = document.getElementById('same_executant_oui');
const sameExecutantNon = document.getElementById('same_executant_non');
const filialeExecutantHidden = document.getElementById('filiale_executant_hidden');
const directionExecutantHidden = document.getElementById('direction_executant_hidden');
const ownerExecutantHidden = document.getElementById('owner_executant_hidden');

// Helper functions
function el(tag, cls) {
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    return e;
}

// Same functions as create.blade.php
function syncAxeFinal() {
    const v = axeSel.value;
    if (v === OTHER_LABEL) {
        axeWrap.style.display = 'block';
        axeOther.required = true;
        axeSel.required = false;
        axeFinal.value = axeOther.value.trim();
    } else {
        axeWrap.style.display = 'none';
        axeOther.required = false;
        axeOther.value = '';
        axeSel.required = true;
        axeFinal.value = v || '';
    }
}
axeSel.addEventListener('change', syncAxeFinal);
axeOther.addEventListener('input', () => { axeFinal.value = axeOther.value.trim(); });
syncAxeFinal();

// ========== CONTRACTANT/EXÉCUTANT LOGIC ==========

// Filiale/Direction cascading for CONTRACTANT
function refreshDirectionsContractant(preselect = null) {
    const f = filialeContractant.value;
    const dirs = DIR_MAP[f] || [];
    directionContractant.innerHTML = '<option value="">— Sélectionner une direction —</option>';
    dirs.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d;
        if (preselect && d === preselect) opt.selected = true;
        directionContractant.appendChild(opt);
    });
    directionContractant.disabled = dirs.length === 0;
}

// Filiale/Direction cascading for EXÉCUTANT
function refreshDirectionsExecutant(preselect = null) {
    const f = filialeExecutant.value;
    const dirs = DIR_MAP[f] || [];
    directionExecutant.innerHTML = '<option value="">— Sélectionner une direction —</option>';
    dirs.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d;
        if (preselect && d === preselect) opt.selected = true;
        directionExecutant.appendChild(opt);
    });
    directionExecutant.disabled = dirs.length === 0;
}

// Show/Hide executant section based on radio
function toggleExecutantSection() {
    if (sameExecutantNon.checked) {
        // Non: Show executant section, enable fields
        executantSection.style.display = 'block';
        filialeExecutant.setAttribute('required', 'required');
        ownerExecutant.setAttribute('required', 'required');
    } else {
        // Oui: Hide executant section, remove required, copy values
        executantSection.style.display = 'none';
        filialeExecutant.removeAttribute('required');
        ownerExecutant.removeAttribute('required');
        copyContractantToExecutant();
    }
}

// Copy contractant values to executant hidden fields (when "Oui")
function copyContractantToExecutant() {
    filialeExecutantHidden.value = filialeContractant.value;
    directionExecutantHidden.value = directionContractant.value;
    ownerExecutantHidden.value = ownerContractant.value;
}

// Event listeners for radio change
sameExecutantOui.addEventListener('change', toggleExecutantSection);
sameExecutantNon.addEventListener('change', toggleExecutantSection);

// Event listeners for filiale changes (cascading directions)
filialeContractant.addEventListener('change', () => {
    refreshDirectionsContractant();
    if (sameExecutantOui.checked) copyContractantToExecutant();
});

filialeExecutant.addEventListener('change', () => {
    refreshDirectionsExecutant();
});

// Also copy when contractant values change (if "Oui" selected)
directionContractant.addEventListener('change', () => {
    if (sameExecutantOui.checked) copyContractantToExecutant();
});

ownerContractant.addEventListener('change', () => {
    if (accountManager) accountManager.value = ownerContractant.value;
    if (sameExecutantOui.checked) copyContractantToExecutant();
});

// Initialize directions on page load with existing values
refreshDirectionsContractant('{{ old("direction_contractant", $project->direction_contractant) }}');
refreshDirectionsExecutant('{{ old("direction_executant", $project->direction_executant) }}');

// Initialize show/hide state
toggleExecutantSection();

function validateDates() {
    endInput.min = startInput.value || '';
    startInput.max = endInput.value || '';
    if (startInput.value && endInput.value && endInput.value < startInput.value) {
        endInput.setCustomValidity('La date de fin ne peut pas être antérieure à la date de démarrage.');
    } else {
        endInput.setCustomValidity('');
    }
}
startInput.addEventListener('change', validateDates);
endInput.addEventListener('change', validateDates);

function removeRow(btn, containerId) {
    const container = document.getElementById(containerId);
    const rows = container.querySelectorAll(':scope > .dynamic-row');
    if (rows.length <= 1) {
        const inputs = rows[0].querySelectorAll('input');
        inputs.forEach(i => { if (i.type !== 'hidden') i.value = ''; });
        return;
    }
    btn.closest('.dynamic-row').remove();
}

function addAction(val = '') {
    const g = el('div', 'dynamic-row grid-2');
    g.innerHTML = `
        <div class="form-group">
            <input type="text" name="actions[]" required placeholder="Intitulé de l'action" value="${val}">
        </div>
        <button class="btn del" type="button" onclick="removeRow(this, 'actionsContainer')">Supprimer</button>`;
    document.getElementById('actionsContainer').appendChild(g);
}

function addDeliverable(l = '', d = '', date = '', realise = false, doc = null) {
    const g = el('div', 'dynamic-row grid-6');
    const docInfo = doc
        ? `<div class="deliverable-document-current" data-document-id="${Number(doc.id)}">
                <small>Document actuel : <strong>${escapeHtml(doc.filename)}</strong> (${escapeHtml(doc.size)})</small>
                <button type="button"
                        class="deliverable-document-delete"
                        onclick="deleteDocument(PROJECT_ID, ${Number(doc.id)}, this)">
                    Supprimer le document
                </button>
            </div>`
        : '';
    const emptyDocInfo = `<small class="deliverable-document-empty" style="display: block; margin-top: 4px;" ${doc ? 'hidden' : ''}>Format accepté : PDF, DOC, DOCX, ZIP (max 50 MB)</small>`;

    g.innerHTML = `
        <div class="form-group">
            <input type="text" name="livrable_nom[]" required placeholder="Livrable" value="${l || '' }">
        </div>
        <div class="form-group">
            <input type="text" name="livrable_desc[]" placeholder="Description" value="${d || ''}">
        </div>
        <div class="form-group">
            <input type="date" name="livrable_date[]" value="${date || ''}">
        </div>
        <div class="form-group">
            <div class="realise-checkbox-wrapper">
                <label>Effectué</label>
                <input type="hidden" name="livrable_realise[]" value="${realise ? '1' : '0'}" class="realise-value">
                <input type="checkbox" ${realise ? 'checked' : ''} onchange="this.previousElementSibling.value = this.checked ? '1' : '0'" title="Cocher si réalisé">
            </div>
        </div>
        <div class="form-group">
            <input type="file" name="livrable_document[]" accept=".pdf,.doc,.docx,.zip,.pptx,.xlsx,.xls" onchange="validateFileSize(this)">
            ${docInfo}
            ${emptyDocInfo}
        </div>
        <button class="btn del" type="button" onclick="removeRow(this, 'deliverablesContainer')">Supprimer</button>`;
    document.getElementById('deliverablesContainer').appendChild(g);
}

function addStakeholder(role = '', empId = '', attentes = '') {
    const g = el('div', 'dynamic-row grid-5');
    const options = (window.EMP_DATA || []).map(e =>
        `<option value="${e.id}">${e.prenom_nom}${e.email ? ' — ' + e.email : ''}</option>`).join('');

    g.innerHTML = `
        <div class="form-group">
            <input type="text" name="stake_role[]" value="${role || ''}" placeholder="R, A, C, I ou rôle">
        </div>
        <div class="form-group">
            <select name="stake_emp_id[]" class="stake-emp">
                <option value="">— Sélectionner un employé —</option>${options}
            </select>
        </div>
        <div class="form-group">
            <input type="text" name="stake_attentes[]" placeholder="Implication / Attentes" value="${attentes || ''}">
        </div>
        <button class="btn del" type="button" onclick="removeRow(this, 'stakeholdersContainer')">Supprimer</button>
        <input type="hidden" name="stake_nom[]">
        <input type="hidden" name="stake_email[]">
        <input type="hidden" name="stake_aad_id[]">
    `;

    const container = document.getElementById('stakeholdersContainer');
    container.appendChild(g);

    const sel = g.querySelector('select.stake-emp');
    if (empId) sel.value = String(empId);

    const syncHidden = () => {
        const opt = sel ? sel.options[sel.selectedIndex] : null;
        const nom = opt && sel.value ? opt.textContent.replace(/ — .+$/, '').trim() : '';
        const empData = (window.EMP_DATA || []).find(e => String(e.id) === String(sel.value));
        g.querySelector('input[name="stake_nom[]"]').value = nom;
        g.querySelector('input[name="stake_email[]"]').value = empData ? (empData.email || '') : '';
        g.querySelector('input[name="stake_aad_id[]"]').value = empData ? (empData.aad_id || '') : '';
    };
    sel && sel.addEventListener('change', syncHidden);
    syncHidden();
}

function addExternalStakeholder(organisation = '', nomComplet = '', email = '', role = '', attentes = '') {
    const g = el('div', 'dynamic-row grid-5');
    g.innerHTML = `
        <div class="form-group">
            <input type="text" name="ext_stake_organisation[]" value="${organisation || ''}" placeholder="Organisation">
        </div>
        <div class="form-group">
            <input type="text" name="ext_stake_nom_complet[]" value="${nomComplet || ''}" placeholder="Prénom et nom" required>
        </div>
        <div class="form-group">
            <input type="email" name="ext_stake_email[]" value="${email || ''}" placeholder="Email (optionnel)">
        </div>
        <div class="form-group">
            <input type="text" name="ext_stake_role[]" value="${role || ''}" placeholder="Rôle">
        </div>
        <div class="form-group">
            <input type="text" name="ext_stake_attentes[]" value="${attentes || ''}" placeholder="Implication / Attentes">
        </div>
        <button class="btn del" type="button" onclick="removeRow(this, 'externalStakeholdersContainer')">Supprimer</button>
    `;
    document.getElementById('externalStakeholdersContainer').appendChild(g);
}

function addIssue(cat = 'Enjeux', detail = '') {
    const g = el('div', 'dynamic-row grid-3');
    g.innerHTML = `
        <div class="form-group">
            <select name="issue_cat[]">
                <option ${cat === 'Enjeux' ? 'selected' : ''}>Enjeux</option>
                <option ${cat === 'Contraintes' ? 'selected' : ''}>Contraintes</option>
                <option ${cat === 'Risques' ? 'selected' : ''}>Risques</option>
                <option ${cat === 'REX' ? 'selected' : ''}>REX</option>
            </select>
        </div>
        <div class="form-group">
            <input type="text" name="issue_detail[]" placeholder="Détail" value="${detail}">
        </div>
        <button class="btn del" type="button" onclick="removeRow(this, 'issuesContainer')">Supprimer</button>`;
    document.getElementById('issuesContainer').appendChild(g);
}

function addGeneralDocument(name = '', filename = '') {
    const g = el('div', 'dynamic-row grid-3');
    g.innerHTML = `
        <div class="form-group">
            <input type="text" name="doc_name[]" placeholder="Nom du document" value="${name}">
        </div>
        <div class="form-group">
            <div class="file-upload-wrapper compact">
                <input type="file" name="doc_file[]"
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip"
                       onchange="handleGeneralDocFileSelect(this)"
                       class="file-input-hidden">
                <button type="button" class="btn-file-upload" onclick="this.previousElementSibling.click()">
                    📎 Choisir fichier
                </button>
                <span class="file-name-display">${filename}</span>
            </div>
        </div>
        <button class="btn del" type="button" onclick="removeRow(this, 'documentsGenerauxContainer')">Supprimer</button>`;
    document.getElementById('documentsGenerauxContainer').appendChild(g);
}

function handleGeneralDocFileSelect(input) {
    const display = input.parentElement.querySelector('.file-name-display');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (validateFileSize(input)) {
            display.textContent = file.name;
            display.style.color = '#059669';
        }
    } else {
        display.textContent = '';
    }
}

function escapeHtml(value) {
    const element = document.createElement('div');
    element.textContent = value ?? '';

    return element.innerHTML;
}

function deleteDocument(projectId, documentId, button) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer ce document ?')) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        alert('Erreur: Token CSRF introuvable. Veuillez recharger la page.');
        return;
    }

    button.disabled = true;

    fetch(`/projects/${projectId}/documents/${documentId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken.content,
            'Accept': 'application/json',
        }
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Erreur lors de la suppression du document');
        }

        return data;
    })
    .then(data => {
        const deliverableDocument = button.closest('.deliverable-document-current');

        if (deliverableDocument) {
            const formGroup = deliverableDocument.closest('.form-group');
            deliverableDocument.remove();

            const emptyDocInfo = formGroup?.querySelector('.deliverable-document-empty');
            if (emptyDocInfo) emptyDocInfo.hidden = false;
        } else {
            button.closest('.document-item-edit')?.remove();
        }

        alert(data.message || 'Document supprimé avec succès');
    })
    .catch(error => {
        alert(error.message || 'Erreur lors de la suppression du document');
    })
    .finally(() => {
        if (button.isConnected) button.disabled = false;
    });
}

function openConfirmModal() {
    // Validate form first
    syncAxeFinal();
    validateDates();

    if (!form.reportValidity()) {
        return; // Don't open modal if form is invalid
    }

    document.getElementById("confirmModal").classList.add("show");
}

function closeConfirmModal() {
    document.getElementById("confirmModal").classList.remove("show");
}

function confirmSubmit() {
    closeConfirmModal();
    submitForm();
}

function submitForm() {
    syncAxeFinal();
    validateDates();

    // If "Oui" is selected, create hidden inputs with copied values
    if (sameExecutantOui.checked) {
        // Remove any previously created temp fields
        const tempFields = form.querySelectorAll('.temp-executant-field');
        tempFields.forEach(f => f.remove());

        // Create hidden inputs with executant data from hidden fields
        const tempFiliale = document.createElement('input');
        tempFiliale.type = 'hidden';
        tempFiliale.name = 'filiale_executant';
        tempFiliale.value = filialeExecutantHidden.value;
        tempFiliale.className = 'temp-executant-field';
        form.appendChild(tempFiliale);

        const tempDirection = document.createElement('input');
        tempDirection.type = 'hidden';
        tempDirection.name = 'direction_executant';
        tempDirection.value = directionExecutantHidden.value;
        tempDirection.className = 'temp-executant-field';
        form.appendChild(tempDirection);

        const tempOwner = document.createElement('input');
        tempOwner.type = 'hidden';
        tempOwner.name = 'owner_executant';
        tempOwner.value = ownerExecutantHidden.value;
        tempOwner.className = 'temp-executant-field';
        form.appendChild(tempOwner);
    }

    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
    } else {
        if (form.reportValidity()) form.submit();
    }
}

// Salesforce opportunity search
(function() {
    const typeSel = document.querySelector('select[name="type_projet"]');
    const block = document.getElementById('sfOppBlock');
    const sel = document.getElementById('sfOpportunitySelect');
    const search = document.getElementById('sfOppSearch');
    const btnMore = document.getElementById('sfOppMoreBtn');
    const meta = document.getElementById('sfOppMeta');
    const fName = document.getElementById('sfOppName');
    const fStage = document.getElementById('sfOppStage');
    const fAmount = document.getElementById('sfOppAmount');

    let nextCursor = null;
    let lastQuery = '';
    let debounceId = null;

    function resetSelect(placeholder) {
        const currentOppId = '{{ old("sf_opportunity_id", $project->sf_opportunity_id) }}';
        sel.innerHTML = `<option value="${currentOppId}">${placeholder}</option>`;
        // Restaurer les valeurs actuelles du projet
        fName.value = '{{ old("sf_opportunity_name", $project->sf_opportunity_name) }}';
        fStage.value = '{{ old("sf_opportunity_stage", $project->sf_opportunity_stage) }}';
        fAmount.value = '{{ old("sf_opportunity_amount", $project->sf_opportunity_amount) }}';
        meta.textContent = '';
        btnMore.style.display = 'none';
    }

    async function fetchOpps(q, cursor = null, append = false) {
        try {
            const url = cursor
                ? `/api/salesforce/opportunities?cursor=${encodeURIComponent(cursor)}`
                : `/api/salesforce/opportunities?q=${encodeURIComponent(q)}&limit=100`;
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const j = await res.json();
            if (!j.ok) throw new Error(j.error || 'Erreur Salesforce');

            if (!append) {
                const currentOppId = '{{ old("sf_opportunity_id", $project->sf_opportunity_id) }}';
                sel.innerHTML = `<option value="${currentOppId}">— Conserver l'opportunité actuelle —</option>`;
            }
            let added = 0;
            for (const o of (j.items || [])) {
                const opt = document.createElement('option');
                opt.value = o.id;
                opt.textContent = `${o.name || ''} — ${o.stage || 'Stage ?'}${o.amount != null ? ' — ' + new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(o.amount) : ''}`;
                opt.dataset.name = o.name || '';
                opt.dataset.stage = o.stage || '';
                opt.dataset.amount = (o.amount != null) ? o.amount : '';
                sel.appendChild(opt);
                added++;
            }

            nextCursor = j.next_cursor || null;
            btnMore.style.display = nextCursor ? '' : 'none';
            meta.textContent = added
                ? `${append ? '… + ' : ''}${added} résultat(s)` + (nextCursor ? ' — plus disponibles' : '')
                : (append ? 'Aucun autre résultat.' : 'Aucun résultat.');

        } catch (e) {
            resetSelect('Erreur de chargement');

        }
    }

    function onSearchInput() {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => {
            const q = (search.value || '').trim();
            if (q.length < 2) {
                resetSelect('Saisissez au moins 2 caractères…');
                lastQuery = '';
                return;
            }
            lastQuery = q;
            sel.innerHTML = '<option value="">Chargement…</option>';
            meta.textContent = '';
            nextCursor = null;
            fetchOpps(q, null, false);
        }, 250);
    }

    btnMore.addEventListener('click', () => {
        if (!nextCursor) return;
        meta.textContent = 'Chargement…';
        fetchOpps(lastQuery, nextCursor, true);
    });

    sel.addEventListener('change', () => {
        const o = sel.options[sel.selectedIndex];
        const currentOppId = '{{ old("sf_opportunity_id", $project->sf_opportunity_id) }}';

        // Si l'option sélectionnée est l'opportunité actuelle, restaurer les valeurs du projet
        if (sel.value === currentOppId) {
            fName.value = '{{ old("sf_opportunity_name", $project->sf_opportunity_name) }}';
            fStage.value = '{{ old("sf_opportunity_stage", $project->sf_opportunity_stage) }}';
            fAmount.value = '{{ old("sf_opportunity_amount", $project->sf_opportunity_amount) }}';
            return;
        }

        if (!o || !sel.value) {
            fName.value = fStage.value = fAmount.value = '';
            return;
        }
        fName.value = o.dataset.name || '';
        fStage.value = o.dataset.stage || '';
        fAmount.value = o.dataset.amount || '';
    });

    function toggleSF() {
        const isExt = (typeSel && (typeSel.value || '').toLowerCase() === 'externe');
        block.style.display = isExt ? 'block' : 'none';
        sel.required = isExt;
        if (!isExt) {
            search.value = '';
            resetSelect('— Conserver l\'opportunité actuelle —');
            lastQuery = '';
            nextCursor = null;
        }
    }

    typeSel?.addEventListener('change', toggleSF);
    search?.addEventListener('input', onSearchInput);
    toggleSF();
})();

// Contractualisation toggle
function toggleContractualisation() {
    const contractualisation = document.querySelector('input[name="contractualisation"]:checked')?.value === '1';
    const contractSection = document.getElementById('contract_section');

    if (contractSection) {
        contractSection.style.display = contractualisation ? 'block' : 'none';

        const typeSelect = document.getElementById('contractualisation_type');
        const fileInput = document.getElementById('contractualisation_document');

        if (typeSelect) typeSelect.required = false;
        if (fileInput) fileInput.required = false;
    }
}

// File size validation
function validateFileSize(input) {
    if (!input.files || !input.files[0]) return true;

    const maxSize = 50 * 1024 * 1024; // 50MB
    const file = input.files[0];

    if (file.size > maxSize) {
        alert(`❌ Fichier trop volumineux!\n\nTaille: ${(file.size / 1024 / 1024).toFixed(2)} MB\nMaximum autorisé: 50 MB\n\nVeuillez choisir un fichier plus petit.`);
        input.value = '';
        return false;
    }

    return true;
}

// Resource fields conditional visibility
function toggleResourceFields() {
    const ressourceOui = document.getElementById('ressource_oui');
    const resourceFieldsRow = document.getElementById('resource-fields-row');
    const resourceTypeSelect = document.getElementById('resource_type');
    const bankFieldGroup = document.getElementById('bank-field-group');
    const resourceBankSelect = document.getElementById('resource_bank');

    if (!ressourceOui || !resourceFieldsRow) return;

    // Show/hide resource type field based on "Ressource à mobiliser"
    const showResourceFields = ressourceOui.checked;
    resourceFieldsRow.style.display = showResourceFields ? '' : 'none';

    if (!showResourceFields) {
        resourceTypeSelect.value = '';
        resourceBankSelect.value = '';
        bankFieldGroup.style.display = 'none';
        resourceTypeSelect.removeAttribute('required');
        resourceBankSelect.removeAttribute('required');
    } else {
        resourceTypeSelect.setAttribute('required', 'required');
        toggleBankField();
    }
}

function toggleBankField() {
    const resourceTypeSelect = document.getElementById('resource_type');
    const bankFieldGroup = document.getElementById('bank-field-group');
    const resourceBankSelect = document.getElementById('resource_bank');

    if (!resourceTypeSelect || !bankFieldGroup) return;

    // Show/hide bank field based on resource type
    const showBankField = resourceTypeSelect.value === 'Banque';
    bankFieldGroup.style.display = showBankField ? '' : 'none';

    if (showBankField) {
        resourceBankSelect.setAttribute('required', 'required');
    } else {
        resourceBankSelect.value = '';
        resourceBankSelect.removeAttribute('required');
    }
}

// Initialize with existing data
document.addEventListener('DOMContentLoaded', () => {
    // Initialize contractualisation toggle
    toggleContractualisation();
    // Load existing actions
    EXISTING_ACTIONS.forEach(action => addAction(action));
    if (EXISTING_ACTIONS.length === 0) addAction();

    // Load existing deliverables
    EXISTING_DELIVERABLES.forEach(d => addDeliverable(d.nom, d.desc, d.date, d.realise, d.document));
    if (EXISTING_DELIVERABLES.length === 0) addDeliverable();

    // Load existing stakeholders
    EXISTING_STAKEHOLDERS.forEach(s => addStakeholder(s.role, s.emp_id, s.attentes));
    if (EXISTING_STAKEHOLDERS.length === 0) addStakeholder();

    // Load existing external stakeholders
    EXISTING_EXTERNAL_STAKEHOLDERS.forEach(s => addExternalStakeholder(s.organisation, s.nom_complet, s.email, s.role, s.attentes));

    // Load existing issues
    EXISTING_ISSUES.forEach(i => addIssue(i.cat, i.detail));
    if (EXISTING_ISSUES.length === 0) addIssue();

    // Initialize resource fields visibility
    const ressourceOuiRadio = document.getElementById('ressource_oui');
    const ressourceNonRadio = document.getElementById('ressource_non');
    const resourceTypeSelect = document.getElementById('resource_type');

    if (ressourceOuiRadio && ressourceNonRadio) {
        ressourceOuiRadio.addEventListener('change', toggleResourceFields);
        ressourceNonRadio.addEventListener('change', toggleResourceFields);
        toggleResourceFields(); // Initial state
    }

    if (resourceTypeSelect) {
        resourceTypeSelect.addEventListener('change', toggleBankField);
        toggleBankField(); // Initial state
    }

    // Manager filiale validation - Frontend warning
    @if(auth()->user()->can('projects.view-filiale'))
        const userFiliale = "{{ auth()->user()->getFiliale() ?? '' }}";

        if (!userFiliale) {
            alert('⚠️ ATTENTION\n\nVotre compte n\'a pas de filiale associée.\nVous ne pourrez pas modifier les filiales du projet.\n\nContactez un administrateur.');
        } else {
            const form = document.querySelector('form');
            const executantSelect = document.querySelector('select[name="filiale_executant"]');
            const contractantInput = document.querySelector('input[name="filiale_contractant"]');

            function normalizeFiliale(str) {
                if (!str) return '';
                return str.toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .trim();
            }

            form?.addEventListener('submit', function(e) {
                const executant = executantSelect?.value || '';
                const contractant = contractantInput?.value || '';

                const userNorm = normalizeFiliale(userFiliale);
                const execNorm = normalizeFiliale(executant);
                const contrNorm = normalizeFiliale(contractant);

                // At least ONE must match
                const executantMatches = execNorm === userNorm;
                const contractantMatches = contrNorm === userNorm;

                if (!executantMatches && !contractantMatches && contractant !== '') {
                    e.preventDefault();
                    alert('⚠️ ATTENTION - Projet Deviendra Invisible\n\n' +
                        'Au moins une filiale (Exécutant ou Contractant) doit correspondre à votre filiale : ' + userFiliale + '\n\n' +
                        'Actuellement :\n' +
                        '• Exécutant : ' + (executant || '(vide)') + '\n' +
                        '• Contractant : ' + (contractant || '(vide)') + '\n\n' +
                        'Si vous continuez, vous ne pourrez PLUS voir ce projet après modification.');
                    return false;
                }
            });
        }
    @endif

    // Gestion des champs de montant : suppression des espaces à la saisie + formatage
    const montantFields = document.querySelectorAll(
        'input[name="budget_initial"], input[name="montant_encaissement"], input[name="montant_decaissement_2"]'
    );

    montantFields.forEach(function(field) {
        // Supprimer les espaces à chaque frappe
        field.addEventListener('input', function() {
            const cursorPos = this.selectionStart;
            const before = this.value;
            const cleaned = before.replace(/\s/g, '');
            if (cleaned !== before) {
                this.value = cleaned;
                // Ajuster la position du curseur
                const spacesRemoved = before.slice(0, cursorPos).replace(/[^\s]/g, '').length;
                this.setSelectionRange(cursorPos - spacesRemoved, cursorPos - spacesRemoved);
            }
        });

        // À la perte du focus : formater avec séparateurs de milliers
        field.addEventListener('blur', function() {
            const raw = this.value.replace(/\s/g, '').replace(',', '.');
            const num = parseFloat(raw);
            if (!isNaN(num) && raw !== '') {
                this.value = new Intl.NumberFormat('fr-FR', { maximumFractionDigits: 0 }).format(num);
            }
        });

        // Au focus : afficher la valeur brute sans espaces pour faciliter l'édition
        field.addEventListener('focus', function() {
            this.value = this.value.replace(/\s/g, '');
        });
    });

    // Avant la soumission du formulaire : s'assurer que les espaces sont retirés
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            montantFields.forEach(function(field) {
                field.value = field.value.replace(/\s/g, '');
            });
        }, true);
    }
});
</script>
@endsection
