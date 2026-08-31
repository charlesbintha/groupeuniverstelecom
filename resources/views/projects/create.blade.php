@extends('layouts.app')

@section('title', 'Créer un projet')
@section('page-title', 'Fiche de création de projet')

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
        padding: 34px;
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

    /* Uniformisation des boutons */
    .toolbar .btn,
    .toolbar button.btn,
    .actions-bar .btn,
    .actions-bar button.btn {
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
    .toolbar button.btn svg,
    .actions-bar .btn svg,
    .actions-bar button.btn svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
    }

    @media (max-width: 768px) {
        .actions-bar {
            flex-direction: column;
        }
        .actions-bar .btn,
        .actions-bar button.btn {
            width: 100%;
        }
        .toolbar .btn,
        .toolbar button.btn {
            flex: 1;
            min-width: 0;
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
        background: #f0f9ff;
        border-left: 4px solid #0094d8;
        padding: 12px 16px;
        border-radius: 8px;
        margin-top: 16px;
        font-size: 14px;
        color: #075985;
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

    /* File Upload Styling */
    .file-upload-wrapper {
        width: 100%;
    }

    .file-input-hidden {
        display: none;
    }

    .file-upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 8px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }

    .file-upload-zone:hover {
        border-color: var(--blue);
        background: #eff6ff;
    }

    .file-upload-zone.dragover {
        border-color: var(--blue);
        background: #dbeafe;
        transform: scale(1.02);
    }

    .file-upload-icon {
        font-size: 48px;
        opacity: 0.6;
    }

    .file-upload-text {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .file-upload-text strong {
        color: #1e293b;
        font-size: 14px;
    }

    .file-upload-text span {
        color: #64748b;
        font-size: 13px;
    }

    .file-upload-hint {
        color: #94a3b8;
        font-size: 12px;
    }

    .file-preview {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 16px;
        background: #ffffff;
        display: flex;
        align-items: center;
        gap: 12px;
    }



    .file-preview-info {
        flex: 1;
        min-width: 0;
    }

    .file-preview-name {
        font-weight: 600;
        color: #1e293b;
        font-size: 14px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .file-preview-size {
        color: #64748b;
        font-size: 12px;
        margin-top: 4px;
    }

    .file-preview-remove {
        background: #fee2e2;
        color: #dc2626;
        border: none;
        border-radius: 50%;
        width: 28px;
        height: 28px;
        font-size: 20px;
        cursor: pointer;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }

    .file-preview-remove:hover {
        background: #fecaca;
    }

    /* Compact file upload for deliverables */
    .file-upload-wrapper.compact {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-file-upload {
        background: #f1f5f9;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 6px 12px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
    }

    .btn-file-upload:hover {
        background: #e2e8f0;
        border-color: #94a3b8;
    }

    .file-name-display {
        color: #059669;
        font-size: 12px;
        font-weight: 500;
        display: none;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        max-width: 200px;
    }

    /* Checkbox moderne pour Réalisé */
    .realise-checkbox-wrapper {
        display: flex;
        align-items: center;
        gap: 6px;
        height: 100%;
        margin-bottom: 12px
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
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-head">
        <h2 class="title">Fiche de création de projet</h2>
        <div class="toolbar">
            <a class="btn sec" href="{{ route('projects.index') }}">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Liste des projets
            </a>
        </div>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('projects.store') }}" id="projectForm" enctype="multipart/form-data">
            @csrf
            @if(session('info'))
                <div class="info-box">{!! session('info') !!}</div>
            @endif
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
                            <input type="text" name="nom_projet" required placeholder="Nom officiel du projet" value="{{ old('nom_projet', session('nom_projet')) }}">
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Type
                            <select name="type_projet" id="typeProjet" required>
                                <option value="">— Sélectionner —</option>
                                <option value="Interne" {{ old('type_projet', session('type_projet')) == 'Interne' ? 'selected' : '' }}>Interne</option>
                                <option value="Externe" {{ old('type_projet', session('type_projet')) == 'Externe' ? 'selected' : '' }}>Externe</option>
                            </select>
                        </label>
                    </div>
                </div>

                <!-- Salesforce block (hidden by default) -->
                <div id="sfOppBlock" style="display:none">
                    <label>Opportunité Salesforce</label>
                    <input type="text" id="sfOppSearch" placeholder="Rechercher (au moins 2 caractères)…" autocomplete="off" style="margin:8px 0">
                    <select name="sf_opportunity_id" id="sfOpportunitySelect">
                        <option value="">— Saisissez un terme de recherche —</option>
                    </select>
                    <button type="button" id="sfOppMoreBtn" class="btn sec" style="margin-top:8px; display:none">Charger plus…</button>
                    <div class="text-muted" id="sfOppMeta" style="margin-top:6px"></div>
                    <input type="hidden" name="sf_opportunity_name" id="sfOppName">
                    <input type="hidden" name="sf_opportunity_stage" id="sfOppStage">
                    <input type="hidden" name="sf_opportunity_amount" id="sfOppAmount">
                </div>

                <div class="row row-2">
                    <div class="form-group">
                        <label>Nature de projet
                            <select name="nature_projet" required>
                                <option value="">— Sélectionner —</option>
                                <option value="B2B" {{ old('nature_projet', session('nature_projet')) == 'B2B' ? 'selected' : '' }}>B2B</option>
                                <option value="B2C" {{ old('nature_projet', session('nature_projet')) == 'B2C' ? 'selected' : '' }}>B2C</option>
                                <option value="GOV" {{ old('nature_projet', session('nature_projet')) == 'GOV' ? 'selected' : '' }}>GOV</option>
                                <option value="Autres" {{ old('nature_projet', session('nature_projet')) == 'Autres' ? 'selected' : '' }}>Autres</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Budget initial (FCFA)
                            <input type="text" name="budget_initial" placeholder="ex. 1 500 000" value="{{ old('budget_initial', session('budget_initial')) }}">
                        </label>
                    </div>
                </div>

                

                
            <div class="row row-3">
                <div class="form-group">
                    <label>Axe stratégique visé *
                        <select id="axe_strategique_select" name="axe_strategique_select" required>
                            <option value="">-- Sélectionner un axe --</option>
                            <option {{ old('axe_strategique', session('axe_strategique')) == 'Organisation' ? 'selected' : '' }}>Organisation</option>
                            <option {{ old('axe_strategique', session('axe_strategique')) == 'Stratégie' ? 'selected' : '' }}>Stratégie</option>
                            <option {{ old('axe_strategique', session('axe_strategique')) == 'Capital Humain' ? 'selected' : '' }}>Capital Humain</option>
                            <option {{ old('axe_strategique', session('axe_strategique')) == 'Produits & Services' ? 'selected' : '' }}>Produits & Services</option>
                            <option {{ old('axe_strategique', session('axe_strategique')) == 'Communication' ? 'selected' : '' }}>Communication</option>
                            <option {{ old('axe_strategique', session('axe_strategique')) == 'Commercialisation' ? 'selected' : '' }}>Commercialisation</option>
                            <option {{ old('axe_strategique', session('axe_strategique')) == 'Déploiement & Exploitation' ? 'selected' : '' }}>Déploiement & Exploitation</option>
                            <option {{ old('axe_strategique', session('axe_strategique')) == 'Reporting Financier' ? 'selected' : '' }}>Reporting Financier</option>
                            <option value="Autre (préciser)">Autre (préciser)</option>
                        </select>
                    </label>
                    <div id="axe_autre_wrap" style="display:none; margin-top:8px">
                        <input id="axe_autre" type="text" placeholder="Préciser l'axe stratégique…">
                    </div>
                    <input type="hidden" id="axe_strategique" name="axe_strategique" value="{{ old('axe_strategique', session('axe_strategique')) }}">
                </div>
                <div class="form-group">
                    <label>Montant encaissement (FCFA)
                        <input type="text" name="montant_encaissement" placeholder="ex. 1 000 000" value="{{ old('montant_encaissement', session('montant_encaissement')) }}">
                    </label>
                </div>
                <div class="form-group">
                    <label>Montant décaissement (FCFA)
                        <input type="text" name="montant_decaissement_2" placeholder="ex. 700 000" value="{{ old('montant_decaissement_2', session('montant_decaissement_2')) }}">
                    </label>
                </div>
            </div>
                <div class="row row-2">
                    <div class="form-group">
                        <label>Statut financier *
                            <select name="statut_financier" required>
                                <option value="">— Sélectionner —</option>
                                <option value="Non démarré" {{ old('statut_financier', session('statut_financier')) == 'Non démarré' ? 'selected' : '' }}>Non démarré</option>
                                <option value="En cours" {{ old('statut_financier', session('statut_financier')) == 'En cours' ? 'selected' : '' }}>En cours</option>
                                <option value="Terminé" {{ old('statut_financier', session('statut_financier')) == 'Terminé' ? 'selected' : '' }}>Terminé</option>
                            </select>
                        </label>
                        <small>État du financement du projet</small>
                    </div>
                    <div class="form-group">
                        <label>Ressource à mobiliser *</label>
                        <div class="radio-group">
                            <div class="radio-option">
                                <input type="radio" name="ressource_a_mobiliser" value="1" id="ressource_oui" {{ old('ressource_a_mobiliser', session('ressource_a_mobiliser')) == '1' ? 'checked' : '' }}>
                                <label for="ressource_oui" class="radio-label">
                                    <span>✅</span>
                                    <span>Oui</span>
                                </label>
                            </div>
                            <div class="radio-option">
                                <input type="radio" name="ressource_a_mobiliser" value="0" id="ressource_non" {{ old('ressource_a_mobiliser', session('ressource_a_mobiliser')) === '0' ? 'checked' : '' }}>
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
                                <option value="GUT" {{ old('resource_type', session('resource_type')) == 'GUT' ? 'selected' : '' }}>GUT (Groupe Univers Télécom)</option>
                                <option value="Banque" {{ old('resource_type', session('resource_type')) == 'Banque' ? 'selected' : '' }}>Banque</option>
                            </select>
                        </label>
                        <small>Source de financement du projet</small>
                    </div>
                    <div class="form-group" id="bank-field-group" style="display: none;">
                        <label>Banque *
                            <select name="resource_bank" id="resource_bank">
                                <option value="">— Sélectionner une banque —</option>
                                <option value="BIS" {{ old('resource_bank', session('resource_bank')) == 'BIS' ? 'selected' : '' }}>BIS (Banque Islamique du Sénégal)</option>
                                <option value="FBN Bank" {{ old('resource_bank', session('resource_bank')) == 'FBN Bank' ? 'selected' : '' }}>FBN Bank</option>
                            </select>
                        </label>
                        <small>Sélectionner la banque de financement</small>
                    </div>
                </div>

                <!-- SECTION CONTRACTANT (toujours visible) -->
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
                                    <option value="{{ $filiale->nom_filiale }}" {{ old('filiale_contractant', session('filiale_contractant')) == $filiale->nom_filiale ? 'selected' : '' }}>{{ $filiale->nom_filiale }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Direction Contractant
                            <select name="direction_contractant" id="direction_contractant" disabled>
                                <option value="">— Sélectionner une direction —</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Owner Contractant *
                            <select name="owner_contractant" id="owner_contractant" required>
                                <option value="">— Sélectionner un owner —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->prenom_nom }}" {{ old('owner_contractant', session('owner_contractant')) == $emp->prenom_nom ? 'selected' : '' }}>
                                        {{ $emp->prenom_nom }}{{ $emp->email ? ' — '.$emp->email : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Account Manager *
                            <select name="account_manager" id="account_manager" required>
                                <option value="">— Sélectionner un account manager —</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->prenom_nom }}" {{ old('account_manager', session('account_manager', session('owner_contractant'))) == $emp->prenom_nom ? 'selected' : '' }}>
                                        {{ $emp->prenom_nom }}{{ $emp->email ? ' — '.$emp->email : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                </div>

                <!-- QUESTION: Même exécutant? -->
                <div class="form-group" style="margin-top: 2rem;">
                    <label style="font-weight: 600; color: #334155; margin-bottom: 8px;">
                        L'exécutant est-il le même que le contractant ?
                    </label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="same_executant" value="oui" id="same_executant_oui"
                                   {{ old('same_executant', 'oui') == 'oui' ? 'checked' : '' }}>
                            <label for="same_executant_oui" class="radio-label">
                                <span>✅</span>
                                <span>Oui (même structure)</span>
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="same_executant" value="non" id="same_executant_non"
                                   {{ old('same_executant') == 'non' ? 'checked' : '' }}>
                            <label for="same_executant_non" class="radio-label">
                                <span>🔄</span>
                                <span>Non (structure différente)</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="info-box">
                    <span class="info-box-icon">💡</span>
                    <span>Si "Non", des tâches automatiques seront ajoutées: "Formalisation" et "Définition de la répartition des revenus"</span>
                </div>

                <!-- SECTION EXÉCUTANT (conditionnelle) -->
                <div id="executant_section" class="conditional-section" style="display: none;">
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
                                <select name="filiale_executant" id="filiale_executant">
                                    <option value="">— Sélectionner une filiale —</option>
                                    @foreach($filialesOptions as $filiale)
                                        <option value="{{ $filiale->nom_filiale }}" {{ old('filiale_executant', session('filiale_executant')) == $filiale->nom_filiale ? 'selected' : '' }}>{{ $filiale->nom_filiale }}</option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Direction Exécutant
                                <select name="direction_executant" id="direction_executant" disabled>
                                    <option value="">— Sélectionner une direction —</option>
                                </select>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Chef de Projet *
                                <select name="owner_executant" id="owner_executant">
                                    <option value="">— Sélectionner un chef —</option>
                                    @foreach($employees as $emp)
                                        <option value="{{ $emp->prenom_nom }}" {{ old('owner_executant', session('owner_executant')) == $emp->prenom_nom ? 'selected' : '' }}>
                                            {{ $emp->prenom_nom }}{{ $emp->email ? ' — '.$emp->email : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Hidden fields pour exécutant (remplis automatiquement si "Oui") -->
                <input type="hidden" name="filiale_executant_hidden" id="filiale_executant_hidden" value="{{ old('filiale_executant') }}">
                <input type="hidden" name="direction_executant_hidden" id="direction_executant_hidden" value="{{ old('direction_executant') }}">
                <input type="hidden" name="owner_executant_hidden" id="owner_executant_hidden" value="{{ old('owner_executant') }}">
                <div class="executant-divider"></div>

                <div class="row row-3">
                    <div class="form-group">
                        <label>Date de démarrage prévue *
                            <input type="date" id="date_demarrage" name="date_demarrage" required value="{{ old('date_demarrage', session('date_demarrage')) }}">
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Date de fin estimée
                            <input type="date" id="date_fin" name="date_fin" value="{{ old('date_fin', session('date_fin')) }}">
                        </label>
                    </div>
                    <div class="form-group">
                        <label>Statut initial *
                            <select name="statut_initial" required>
                                <option value="">-- Sélectionner un statut --</option>
                                <option value="Planifié" {{ old('statut_initial', session('statut_initial')) == 'Planifié' ? 'selected' : '' }}>Planifié</option>
                                <option value="En cours" {{ old('statut_initial', session('statut_initial')) == 'En cours' ? 'selected' : '' }}>En cours</option>
                                <option value="Pause" {{ old('statut_initial', session('statut_initial')) == 'Pause' ? 'selected' : '' }}>Pause</option>
                                <option value="Suspendu" {{ old('statut_initial', session('statut_initial')) == 'Suspendu' ? 'selected' : '' }}>Suspendu</option>
                                <option value="Mis en pause" {{ old('statut_initial', session('statut_initial')) == 'Mis en pause' ? 'selected' : '' }}>Mis en pause</option>
                                <option value="Retard" {{ old('statut_initial', session('statut_initial')) == 'Retard' ? 'selected' : '' }}>Retard</option>
                                <option value="Terminé" {{ old('statut_initial', session('statut_initial')) == 'Terminé' ? 'selected' : '' }}>Terminé</option>
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
                    <label>
                        <textarea name="notes" rows="6" placeholder="Notes, observations ou informations complémentaires de la semaine sur le projet...">{{ old('notes', session('notes')) }}</textarea>
                    </label>
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
                            <input type="radio" name="contractualisation" value="1" id="contractualisation_oui" {{ old('contractualisation', session('contractualisation')) == '1' ? 'checked' : '' }} onchange="toggleContractualisation()">
                            <label for="contractualisation_oui" class="radio-label">
                                <span>✅</span>
                                <span>Oui</span>
                            </label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="contractualisation" value="0" id="contractualisation_non" {{ old('contractualisation', session('contractualisation')) === '0' ? 'checked' : '' }} onchange="toggleContractualisation()">
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
                                    <option value="Bon de commande" {{ old('contractualisation_type', session('contractualisation_type')) == 'Bon de commande' ? 'selected' : '' }}>Bon de commande</option>
                                    <option value="Annexes" {{ old('contractualisation_type', session('contractualisation_type')) == 'Annexes' ? 'selected' : '' }}>Annexes</option>
                                </select>
                            </label>
                            <small>Nature du document contractuel</small>
                        </div>
                        <div class="form-group">
                            <label>Document contractuel (optionnel)</label>
                            <div class="file-upload-wrapper">
                                <input type="file" name="contractualisation_document" id="contractualisation_document"
                                       accept=".pdf,.doc,.docx,.zip,.pptx,.xlsx,.xls"
                                       onchange="handleFileSelect(this)"
                                       class="file-input-hidden">
                                <div class="file-upload-zone" onclick="document.getElementById('contractualisation_document').click()">
                                    <div class="file-upload-icon">📄</div>
                                    <div class="file-upload-text">
                                        <strong>Cliquez pour choisir un fichier</strong>
                                        <span>ou glissez-déposez ici</span>
                                    </div>
                                    <small class="file-upload-hint">PDF, DOC, DOCX, ZIP (max 50 MB)</small>
                                </div>
                                <div class="file-preview" id="contractualisation_document_preview" style="display: none;">
                                    
                                    <div class="file-preview-info">
                                        <div class="file-preview-name"></div>
                                        <div class="file-preview-size"></div>
                                    </div>
                                    <button type="button" class="file-preview-remove" onclick="clearFileInput('contractualisation_document')">&times;</button>
                                </div>
                            </div>
                        </div>
                    </div>
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
                        <input type="checkbox" name="maintenance_glpi" value="1" {{ old('maintenance_glpi', session('maintenance_glpi')) ? 'checked' : '' }}>
                        <span>Ce projet fait-il l'objet de maintenance ?</span>
                    </label>
                    <small>Si coché, un projet sera automatiquement créé sur la plateforme GLPI (<a href="https://infra.groupe-universtelecom.com" target="_blank" rel="noopener">infra.groupe-universtelecom.com</a>)</small>
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
                    <textarea name="objectif_projet" placeholder="Décrire l'objectif principal…" required>{{ old('objectif_projet', session('objectif_projet')) }}</textarea>
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
                    <textarea name="contexte" placeholder="Décrire le contexte du projet…">{{ old('contexte', session('contexte')) }}</textarea>
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
                    <textarea name="synthese" placeholder="Synthèse du projet…">{{ old('synthese', session('synthese')) }}</textarea>
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
                    <textarea name="prochaine_etape" placeholder="Ex. valider le cadrage, lancer l'AO…">{{ old('prochaine_etape', session('prochaine_etape')) }}</textarea>
                </div>
            </div>

            <!-- KPIs -->
            <div class="section">
                <h3>📊 KPIs et cibles</h3>
                @if($kpiRows->isEmpty())
                    <p class="text-muted">—</p>
                @else
                    <table class="kpi-matrix">
                        <colgroup>
                            <col class="col-famille">
                            <col class="col-mesure">
                            <col class="col-exemples">
                            <col class="col-cible">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Famille d'indicateurs</th>
                                <th>Ce qu'elle mesure</th>
                                <th>Exemples d'indicateurs</th>
                                <th>Valeur cible</th>
                            </tr>
                        </thead>
                        <tbody>
                        @php
                            $lastFam = null;
                            $kpiGroups = $kpiRows->groupBy('famille_ordre')->map(function($group) {
                                return [
                                    'count' => $group->count(),
                                    'nom' => $group->first()->nom_famille,
                                    'mesure' => $group->first()->mesure,
                                    'app' => $group->first()->applicabilite,
                                ];
                            });
                        @endphp
                        @foreach($kpiRows as $kpi)
                            <tr>
                                @if($kpi->famille_ordre !== $lastFam)
                                    <td class="cell-famille" rowspan="{{ $kpiGroups[$kpi->famille_ordre]['count'] }}">
                                        <div class="famille-title">{{ $kpiGroups[$kpi->famille_ordre]['nom'] }}</div>
                                    </td>
                                    <td class="cell-mesure" rowspan="{{ $kpiGroups[$kpi->famille_ordre]['count'] }}">
                                        <div class="mesure-text">{!! nl2br(e($kpiGroups[$kpi->famille_ordre]['mesure'])) !!}</div>
                                        @if($kpiGroups[$kpi->famille_ordre]['app'])
                                            <div class="mesure-app">{{ $kpiGroups[$kpi->famille_ordre]['app'] }}</div>
                                        @endif
                                    </td>
                                    @php $lastFam = $kpi->famille_ordre; @endphp
                                @endif
                                <td style="font-size:12px" class="cell-exemple">{{ $kpi->libelle }}</td>
                                <td style="font-size:12px" class="cell-cible">{{ $kpi->cible }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
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
                    Enregistrer le projet
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal de confirmation -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3>Confirmer la création du projet</h3>
        </div>
        <div class="modal-body">
            <p>Vous êtes sur le point de créer un nouveau projet. Veuillez confirmer que toutes les informations saisies sont correctes.</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn sec" onclick="closeConfirmModal()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Annuler
            </button>
            <button type="button" class="btn success" onclick="confirmSubmit()">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Confirmer la création
            </button>
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
    document.getElementById('wizardSubmitBtn').style.display = currentWizardStep === totalWizardSteps ? 'flex' : 'none';

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

        const response = await fetch('{{ route("projects.wizard.validate", ["step" => "__STEP__"]) }}'.replace('__STEP__', currentWizardStep), {
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

// Elements
const startInput = document.getElementById('date_demarrage');
const endInput = document.getElementById('date_fin');
const form = document.getElementById('projectForm');
const axeSel = document.getElementById('axe_strategique_select');
const axeWrap = document.getElementById('axe_autre_wrap');
const axeOther = document.getElementById('axe_autre');
const axeFinal = document.getElementById('axe_strategique');
const filialeSel = document.getElementById('filiale');
const directionSel = document.getElementById('direction');
const OTHER_LABEL = 'Autre (préciser)';

// Helper functions
function el(tag, cls) {
    const e = document.createElement(tag);
    if (cls) e.className = cls;
    return e;
}

function findEmpByEmail(email) {
    email = (email || '').toLowerCase();
    return (window.EMP_DATA || []).find(e => (e.email || '').toLowerCase() === email) || null;
}

// Axe strategique management
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

// Elements
const filialeContractant = document.getElementById('filiale_contractant');
const directionContractant = document.getElementById('direction_contractant');
const ownerContractant = document.getElementById('owner_contractant');
const accountManager = document.getElementById('account_manager');

const filialeExecutant = document.getElementById('filiale_executant');
const directionExecutant = document.getElementById('direction_executant');
const ownerExecutant = document.getElementById('owner_executant');

const sameExecutantOui = document.getElementById('same_executant_oui');
const sameExecutantNon = document.getElementById('same_executant_non');
const executantSection = document.getElementById('executant_section');

// Hidden fields for backend
const filialeExecutantHidden = document.getElementById('filiale_executant_hidden');
const directionExecutantHidden = document.getElementById('direction_executant_hidden');
const ownerExecutantHidden = document.getElementById('owner_executant_hidden');

// Filiale/Direction cascading for CONTRACTANT
function refreshDirectionsContractant(preselect = null) {
    const f = filialeContractant.value;
    const dirs = DIR_MAP[f] || [];
    directionContractant.innerHTML = '<option value="">— Sélectionner une direction —</option>';
    dirs.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d;
        directionContractant.appendChild(opt);
    });
    directionContractant.disabled = dirs.length === 0;
    if (preselect) directionContractant.value = preselect;
}
filialeContractant.addEventListener('change', () => refreshDirectionsContractant(null));
refreshDirectionsContractant('{{ old("direction_contractant") }}');

// Filiale/Direction cascading for EXÉCUTANT
function refreshDirectionsExecutant(preselect = null) {
    const f = filialeExecutant.value;
    const dirs = DIR_MAP[f] || [];
    directionExecutant.innerHTML = '<option value="">— Sélectionner une direction —</option>';
    dirs.forEach(d => {
        const opt = document.createElement('option');
        opt.value = d;
        opt.textContent = d;
        directionExecutant.appendChild(opt);
    });
    directionExecutant.disabled = dirs.length === 0;
    if (preselect) directionExecutant.value = preselect;
}
filialeExecutant.addEventListener('change', () => refreshDirectionsExecutant(null));
refreshDirectionsExecutant('{{ old("direction_executant") }}');

// Show/Hide executant section based on radio
function toggleExecutantSection() {
    if (sameExecutantNon.checked) {
        // Afficher section exécutant
        executantSection.style.display = 'block';

        // Rendre les champs required
        filialeExecutant.setAttribute('required', 'required');
        ownerExecutant.setAttribute('required', 'required');

        // Vider les hidden fields
        filialeExecutantHidden.value = '';
        directionExecutantHidden.value = '';
        ownerExecutantHidden.value = '';

    } else {
        // Cacher section exécutant
        executantSection.style.display = 'none';

        // Retirer required
        filialeExecutant.removeAttribute('required');
        ownerExecutant.removeAttribute('required');

        // Copier les valeurs du contractant vers les hidden fields
        copyContractantToExecutant();
    }
}

// Copy contractant values to executant (when "Oui")
function copyContractantToExecutant() {
    filialeExecutantHidden.value = filialeContractant.value;
    directionExecutantHidden.value = directionContractant.value;
    ownerExecutantHidden.value = ownerContractant.value;
}

// Event listeners
sameExecutantOui.addEventListener('change', toggleExecutantSection);
sameExecutantNon.addEventListener('change', toggleExecutantSection);

// Also copy when contractant values change (if "Oui" is selected)
filialeContractant.addEventListener('change', () => {
    if (sameExecutantOui.checked) copyContractantToExecutant();
});
directionContractant.addEventListener('change', () => {
    if (sameExecutantOui.checked) copyContractantToExecutant();
});
ownerContractant.addEventListener('change', () => {
    if (accountManager) accountManager.value = ownerContractant.value;
    if (sameExecutantOui.checked) copyContractantToExecutant();
});

// Initialize on page load
toggleExecutantSection();

// Form submission: ensure hidden fields are sent as real fields
form.addEventListener('submit', (e) => {
    if (sameExecutantOui.checked) {
        // Copy hidden values to real fields before submit
        const tempFiliale = document.createElement('input');
        tempFiliale.type = 'hidden';
        tempFiliale.name = 'filiale_executant';
        tempFiliale.value = filialeExecutantHidden.value;
        form.appendChild(tempFiliale);

        const tempDirection = document.createElement('input');
        tempDirection.type = 'hidden';
        tempDirection.name = 'direction_executant';
        tempDirection.value = directionExecutantHidden.value;
        form.appendChild(tempDirection);

        const tempOwner = document.createElement('input');
        tempOwner.type = 'hidden';
        tempOwner.name = 'owner_executant';
        tempOwner.value = ownerExecutantHidden.value;
        form.appendChild(tempOwner);
    }
});

// ========== END CONTRACTANT/EXÉCUTANT LOGIC ==========

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

// Handle file selection for contractualization document
function handleFileSelect(input) {
    if (!validateFileSize(input)) return;

    const file = input.files[0];
    if (!file) return;

    const previewId = input.id + '_preview';
    const uploadZone = input.nextElementSibling;
    const preview = document.getElementById(previewId);

    if (preview) {
        uploadZone.style.display = 'none';
        preview.style.display = 'flex';
        preview.querySelector('.file-preview-name').textContent = file.name;
        preview.querySelector('.file-preview-size').textContent = formatFileSize(file.size);
    }
}

// Handle file selection for deliverable documents
function handleDeliverableFileSelect(input) {
    if (!validateFileSize(input)) return;

    const file = input.files[0];
    const wrapper = input.closest('.file-upload-wrapper');
    const display = wrapper ? wrapper.querySelector('.file-name-display') : null;

    if (file && display) {
        display.textContent = file.name + ' (' + formatFileSize(file.size) + ')';
        display.style.display = 'inline';
    }
}

// Clear file input and reset UI
function clearFileInput(inputId) {
    const input = document.getElementById(inputId);
    const previewId = inputId + '_preview';
    const uploadZone = input.nextElementSibling;
    const preview = document.getElementById(previewId);

    input.value = '';
    if (preview) {
        preview.style.display = 'none';
        uploadZone.style.display = 'flex';
    }
}

// Format file size for display
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
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

// Initialize contractualisation toggle and drag-and-drop on page load
document.addEventListener('DOMContentLoaded', () => {
    toggleContractualisation();

    // Drag and drop support for contractualization document
    const uploadZone = document.querySelector('.file-upload-zone');
    if (uploadZone) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadZone.addEventListener(eventName, () => {
                uploadZone.classList.remove('dragover');
            }, false);
        });

        uploadZone.addEventListener('drop', function(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            const input = document.getElementById('contractualisation_document');

            if (files.length > 0) {
                input.files = files;
                handleFileSelect(input);
            }
        }, false);
    }

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
});

// Date validation
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
        sel.innerHTML = `<option value="">${placeholder}</option>`;
        fName.value = fStage.value = fAmount.value = '';
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

            if (!append) sel.innerHTML = '<option value="">— Sélectionner une opportunité —</option>';
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
            console.error(e);
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
        block.style.display = isExt ? '' : 'none';
        sel.required = isExt;
        if (!isExt) {
            search.value = '';
            resetSelect('— Saisissez un terme de recherche —');
            lastQuery = '';
            nextCursor = null;
        }
    }

    typeSel?.addEventListener('change', toggleSF);
    search?.addEventListener('input', onSearchInput);
    toggleSF();
    window.toggleSF = toggleSF;
})();

// Dynamic rows management
function removeRow(btn, containerId) {
    const container = document.getElementById(containerId);
    const rows = container.querySelectorAll(':scope > .dynamic-row');
    if (rows.length <= 1) {
        const inputs = rows[0].querySelectorAll('input');
        inputs.forEach(i => { if (i.type !== 'hidden') i.value = ''; });
        updateRequiredFlags();
        return;
    }
    btn.closest('.dynamic-row').remove();
    updateRequiredFlags();
}

function updateRequiredFlags() {
    const actionInputs = document.querySelectorAll('#actionsContainer input[name="actions[]"]');
    actionInputs.forEach((inp, i) => inp.required = (i === 0));

    const livInputs = document.querySelectorAll('#deliverablesContainer input[name="livrable_nom[]"]');
    livInputs.forEach((inp, i) => inp.required = (i === 0));
}

// Actions
function addAction(val = '') {
    const g = el('div', 'dynamic-row grid-2');
    g.innerHTML = `
        <div class="form-group">
            <input type="text" name="actions[]" required placeholder="Intitulé de l'action" value="${val}">
        </div>
        <button class="btn del" type="button" onclick="removeRow(this, 'actionsContainer')">Supprimer</button>`;
    document.getElementById('actionsContainer').appendChild(g);
    updateRequiredFlags();
}

// Deliverables
function addDeliverable(l = '', d = '', date = '', realise = false) {
    const g = el('div', 'dynamic-row grid-6');
    g.innerHTML = `
        <div class="form-group">
            <input type="text" name="livrable_nom[]" required placeholder="Livrable" value="${l}">
        </div>
        <div class="form-group">
            <input type="text" name="livrable_desc[]" placeholder="Description" value="${d}">
        </div>
        <div class="form-group">
            <input type="date" name="livrable_date[]" value="${date}">
        </div>
        <div class="form-group" style="margin-button:2px">
            <div class="realise-checkbox-wrapper">
                <label>Réalisé</label>
                <input type="hidden" name="livrable_realise[]" value="${realise ? '1' : '0'}" class="realise-value">
                <input type="checkbox" ${realise ? 'checked' : ''} onchange="this.previousElementSibling.value = this.checked ? '1' : '0'" title="Cocher si réalisé">
            </div>
        </div>
        <div class="form-group">
            <div class="file-upload-wrapper compact">
                <input type="file" name="livrable_document[]"
                       accept=".pdf,.doc,.docx,.zip,.pptx,.xlsx,.xls"
                       onchange="handleDeliverableFileSelect(this)"
                       class="file-input-hidden">
                <button type="button" class="btn-file-upload" onclick="this.previousElementSibling.click()">
                     Choisir fichier
                </button>
                <span class="file-name-display"></span>
            </div>
        </div>
        <button class="btn del" type="button" onclick="removeRow(this, 'deliverablesContainer')">Supprimer</button>`;
    document.getElementById('deliverablesContainer').appendChild(g);
    updateRequiredFlags();
}

// Stakeholders
function addStakeholder(role = '', empId = '', attentes = '', opts = {}) {
    const fixed = !!opts.fixed;
    const disable = !!opts.disableSelect;
    const pos = opts.position === 'start' ? 'start' : 'end';

    const g = el('div', 'dynamic-row grid-5' + (fixed ? ' fixed-stake' : ''));
    const options = (window.EMP_DATA || []).map(e =>
        `<option value="${e.id}" data-email="${e.email || ''}" data-aad="${e.aad_id || ''}">
           ${e.prenom_nom}${e.email ? ' — ' + e.email : ''}
         </option>`).join('');

    g.innerHTML = `
        <div class="form-group">
            <input type="text" name="stake_role[]" value="${role || ''}" placeholder="R, A, C, I ou rôle" ${fixed ? 'readonly' : ''}>
        </div>
        <div class="form-group">
            <select name="stake_emp_id[]" class="stake-emp" ${disable ? 'disabled' : ''}>
                <option value="">— Sélectionner un employé —</option>${options}
            </select>
        </div>
        <div class="form-group">
            <input type="text" name="stake_attentes[]" placeholder="Implication / Attentes" value="${attentes || ''}">
        </div>
        ${fixed ? '<div></div>' : '<button class="btn del" type="button" onclick="removeRow(this, \'stakeholdersContainer\')">Supprimer</button>'}
        <input type="hidden" name="stake_nom[]">
        <input type="hidden" name="stake_email[]">
        <input type="hidden" name="stake_aad_id[]">
        ${disable ? '<input type="hidden" name="stake_emp_id[]">' : ''}
    `;

    const container = document.getElementById('stakeholdersContainer');
    if (pos === 'start') container.insertBefore(g, container.firstChild);
    else container.appendChild(g);

    const sel = g.querySelector('select.stake-emp');
    if (empId) sel.value = String(empId);

    const syncHidden = () => {
        const opt = sel ? sel.options[sel.selectedIndex] : null;
        const nom = opt ? opt.textContent.replace(/ — .+$/, '').trim() : '';
        const email = opt ? (opt.getAttribute('data-email') || '') : '';
        const aad = opt ? (opt.getAttribute('data-aad') || '') : '';
        g.querySelector('input[name="stake_nom[]"]').value = nom;
        g.querySelector('input[name="stake_email[]"]').value = email;
        g.querySelector('input[name="stake_aad_id[]"]').value = aad;
        if (disable) g.querySelector('input[type="hidden"][name="stake_emp_id[]"]').value = sel ? sel.value : '';
    };
    sel && sel.addEventListener('change', syncHidden);
    syncHidden();
}

// External Stakeholders
function addExternalStakeholder(organisation = '', nomComplet = '', email = '', role = '', attentes = '') {
    const g = el('div', 'dynamic-row grid-5');
    g.innerHTML = `
        <div class="form-group">
            <input type="text" name="ext_stake_organisation[]" value="${organisation}" placeholder="Organisation">
        </div>
        <div class="form-group">
            <input type="text" name="ext_stake_nom_complet[]" value="${nomComplet}" placeholder="Prénom et nom" required>
        </div>
        <div class="form-group">
            <input type="email" name="ext_stake_email[]" value="${email}" placeholder="Email (optionnel)">
        </div>
        <div class="form-group">
            <input type="text" name="ext_stake_role[]" value="${role}" placeholder="Rôle">
        </div>
        <div class="form-group">
            <input type="text" name="ext_stake_attentes[]" value="${attentes}" placeholder="Implication / Attentes">
        </div>
        <button class="btn del" type="button" onclick="removeRow(this, 'externalStakeholdersContainer')">Supprimer</button>
    `;
    document.getElementById('externalStakeholdersContainer').appendChild(g);
}

// Issues
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

// General Documents
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
            display.style.display = 'inline';
        }
    } else {
        display.textContent = '';
        display.style.display = 'none';
    }
}

// Initialize fixed stakeholders
function initFixedStakeholders() {
    const EQ = 'awagueye.sall@universtelecom.net';
    const EM = 'asta.fall@uta.sn';
    const ACC = 'germaine.mendy@universtelecom.net';
    const eQ = findEmpByEmail(EQ);
    const acc = findEmpByEmail(ACC);
    const eM = findEmpByEmail(EM);
    addStakeholder('Responsable Marketing', eM ? eM.id : '', '', { fixed: true, position: 'start' });
    addStakeholder('Responsable qualité', eQ ? eQ.id : '', '', { fixed: true, position: 'start' });
    addStakeholder('Audit contrôle et conformité', acc ? acc.id : '', 'optimisation des coûts, évaluation rapport financier',{ fixed: true, position: 'start' });
}

// Modal functions
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
    if (typeof window.toggleSF === 'function') window.toggleSF();

    if (typeof form.requestSubmit === 'function') {
        form.requestSubmit();
    } else {
        if (form.reportValidity()) form.submit();
    }
}

// Form validation
form.addEventListener('submit', (e) => {
    syncAxeFinal();

    const hasAction = Array.from(document.querySelectorAll('#actionsContainer input[name="actions[]"]'))
        .some(i => i.value.trim() !== '');
    if (!hasAction) {
        e.preventDefault();
        alert('Ajoutez au moins une action.');
        return;
    }

    const hasLivrable = Array.from(document.querySelectorAll('#deliverablesContainer input[name="livrable_nom[]"]'))
        .some(i => i.value.trim() !== '');
    if (!hasLivrable) {
        e.preventDefault();
        alert('Ajoutez au moins un livrable.');
        return;
    }

    document.querySelectorAll('#stakeholdersContainer .stake-emp')
        .forEach(sel => sel.dispatchEvent(new Event('change')));
});

// ===========================================================================
// PRÉ-REMPLISSAGE POUR DUPLICATION ET RESTAURATION APRÈS ERREURS
// ===========================================================================
function populateDuplicatedData(data = null) {
    // Priority: data (from cache) > old() (after validation error) > session (duplication)

    // Main form fields - old() values already handled by Blade templates

    // Executant/Contractant fields
    const filialeExecutant = data?.filiale_executant ?? @json(old('filiale_executant', session('filiale_executant')));
    const filialeContractant = data?.filiale_contractant ?? @json(old('filiale_contractant', session('filiale_contractant')));
    const directionExecutant = data?.direction_executant ?? @json(old('direction_executant', session('direction_executant')));
    const directionContractant = data?.direction_contractant ?? @json(old('direction_contractant', session('direction_contractant')));
    const ownerExecutant = data?.owner_executant ?? @json(old('owner_executant', session('owner_executant')));
    const ownerContractant = data?.owner_contractant ?? @json(old('owner_contractant', session('owner_contractant')));
    const accountManager = data?.account_manager ?? @json(old('account_manager', session('account_manager', session('owner_contractant'))));

    // Dynamic arrays - Priority: old() > session()
    const duplicatedActions = data?.actions ?? @json(old('actions', session('actions', [])));
    const duplicatedLivrableNom = data?.livrable_nom ?? @json(old('livrable_nom', session('livrable_nom', [])));
    const duplicatedLivrableDesc = data?.livrable_desc ?? @json(old('livrable_desc', session('livrable_desc', [])));
    const duplicatedLivrableDate = data?.livrable_date ?? @json(old('livrable_date', session('livrable_date', [])));
    const duplicatedStakeEmpId = data?.stake_emp_id ?? @json(old('stake_emp_id', session('stake_emp_id', [])));
    const duplicatedStakeRole = data?.stake_role ?? @json(old('stake_role', session('stake_role', [])));
    const duplicatedStakeAttentes = data?.stake_attentes ?? @json(old('stake_attentes', session('stake_attentes', [])));
    const duplicatedExtStakeOrganisation = data?.ext_stake_organisation ?? @json(old('ext_stake_organisation', session('ext_stake_organisation', [])));
    const duplicatedExtStakeNomComplet = data?.ext_stake_nom_complet ?? @json(old('ext_stake_nom_complet', session('ext_stake_nom_complet', [])));
    const duplicatedExtStakeEmail = data?.ext_stake_email ?? @json(old('ext_stake_email', session('ext_stake_email', [])));
    const duplicatedExtStakeRole = data?.ext_stake_role ?? @json(old('ext_stake_role', session('ext_stake_role', [])));
    const duplicatedExtStakeAttentes = data?.ext_stake_attentes ?? @json(old('ext_stake_attentes', session('ext_stake_attentes', [])));
    const duplicatedIssueCat = data?.issue_cat ?? @json(old('issue_cat', session('issue_cat', [])));
    const duplicatedIssueDetail = data?.issue_detail ?? @json(old('issue_detail', session('issue_detail', [])));
    const duplicatedDocName = data?.doc_name ?? @json(old('doc_name', session('doc_name', [])));

    // Note: doc_file cannot be restored (browser security prevents pre-filling file inputs)

    // Fill main form fields
    if (nomProjet) {
        const nomProjetField = document.querySelector('input[name="nom_projet"]');
        if (nomProjetField) nomProjetField.value = nomProjet;
    }

    if (typeProjet) {
        const typeProjetField = document.querySelector('select[name="type_projet"]');
        if (typeProjetField) {
            typeProjetField.value = typeProjet;
            typeProjetField.dispatchEvent(new Event('change')); // Trigger change for Salesforce logic
        }
    }

    if (natureProjet) {
        const natureProjetField = document.querySelector('select[name="nature_projet"]');
        if (natureProjetField) natureProjetField.value = natureProjet;
    }

    if (axeStrategique) {
        const axeField = document.querySelector('textarea[name="axe_strategique"]');
        if (axeField) axeField.value = axeStrategique;
    }

    if (objectifProjet) {
        const objectifField = document.querySelector('textarea[name="objectif_projet"]');
        if (objectifField) objectifField.value = objectifProjet;
    }

    if (budgetInitial) {
        const budgetField = document.querySelector('input[name="budget_initial"]');
        if (budgetField) budgetField.value = budgetInitial;
    }

    if (statutInitial) {
        const statutField = document.querySelector('select[name="statut_initial"]');
        if (statutField) statutField.value = statutInitial;
    }

    if (prochaineEtape) {
        const etapeField = document.querySelector('textarea[name="prochaine_etape"]');
        if (etapeField) etapeField.value = prochaineEtape;
    }

    // Fill Executant/Contractant fields
    if (filialeExecutant) {
        const filialeExecField = document.querySelector('select[name="filiale_executant"]');
        if (filialeExecField) {
            filialeExecField.value = filialeExecutant;
            filialeExecField.dispatchEvent(new Event('change')); // Trigger cascade
        }
    }

    if (filialeContractant) {
        const filialeContField = document.querySelector('select[name="filiale_contractant"]');
        if (filialeContField) {
            filialeContField.value = filialeContractant;
            filialeContField.dispatchEvent(new Event('change')); // Trigger cascade
        }
    }

    if (ownerExecutant) {
        const ownerExecField = document.querySelector('select[name="owner_executant"]');
        if (ownerExecField) ownerExecField.value = ownerExecutant;
    }

    if (ownerContractant) {
        const ownerContField = document.querySelector('select[name="owner_contractant"]');
        if (ownerContField) ownerContField.value = ownerContractant;
    }

    if (accountManager) {
        const accountManagerField = document.querySelector('select[name="account_manager"]');
        if (accountManagerField) accountManagerField.value = accountManager;
    }

    // ACTIONS
    if (duplicatedActions && duplicatedActions.length > 0) {
        duplicatedActions.forEach((libelle, index) => {
            if (libelle && libelle.trim()) {
                addAction(libelle);
            }
        });
    } else {
        addAction(); // Add one empty action
    }

    // LIVRABLES
    if (duplicatedLivrableNom && duplicatedLivrableNom.length > 0) {
        duplicatedLivrableNom.forEach((nom, index) => {
            const desc = duplicatedLivrableDesc[index] || '';
            const date = duplicatedLivrableDate[index] || '';
            if (nom && nom.trim()) {
                addDeliverable(nom, desc, date);
            }
        });
    } else {
        addDeliverable(); // Add one empty deliverable
    }

    // STAKEHOLDERS (after fixed stakeholders)
    if (duplicatedStakeEmpId && duplicatedStakeEmpId.length > 0) {
        duplicatedStakeEmpId.forEach((empId, index) => {
            const role = duplicatedStakeRole[index] || '';
            const attentes = duplicatedStakeAttentes[index] || '';
            if (empId) {
                addStakeholder(role, empId, attentes);
            }
        });
    } else {
        addStakeholder(); // Add one empty stakeholder
    }

    // EXTERNAL STAKEHOLDERS
    if (duplicatedExtStakeOrganisation && duplicatedExtStakeOrganisation.length > 0) {
        duplicatedExtStakeOrganisation.forEach((organisation, index) => {
            const nomComplet = duplicatedExtStakeNomComplet[index] || '';
            const email = duplicatedExtStakeEmail[index] || '';
            const role = duplicatedExtStakeRole[index] || '';
            const attentes = duplicatedExtStakeAttentes[index] || '';
            if (organisation || nomComplet) {
                addExternalStakeholder(organisation, nomComplet, email, role, attentes);
            }
        });
    }

    // DOCUMENTS (names only - files cannot be restored due to browser security)
    if (duplicatedDocName && duplicatedDocName.length > 0) {
        duplicatedDocName.forEach((docName, index) => {
            if (docName && docName.trim()) {
                addGeneralDocument(docName);
            }
        });
    }

    // ISSUES
    if (duplicatedIssueCat && duplicatedIssueCat.length > 0) {
        duplicatedIssueCat.forEach((cat, index) => {
            const detail = duplicatedIssueDetail[index] || '';
            if (detail && detail.trim()) {
                addIssue(cat, detail);
            }
        });
    } else {
        addIssue(); // Add one empty issue
    }

    // CASCADE DIRECTIONS (pour contractant et exécutant si duplication)
    // Use setTimeout to ensure selects are populated after cascade events
    if (directionContractant) {
        setTimeout(() => {
            const directionContField = document.querySelector('select[name="direction_contractant"]');
            if (directionContField) {
                directionContField.value = directionContractant;
            }
        }, 200);
    }

    if (directionExecutant) {
        setTimeout(() => {
            const directionExecField = document.querySelector('select[name="direction_executant"]');
            if (directionExecField) {
                directionExecField.value = directionExecutant;
            }
        }, 200);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', () => {
    // Ensure modal is hidden on load
    const modal = document.getElementById('confirmModal');
    if (modal) {
        modal.classList.remove('show');
    }

    initFixedStakeholders();

    // Check if this is a duplication (session has duplicate_source_id or duplicate_cache_key)
    const isDuplication = {{ session('duplicate_source_id') ? 'true' : 'false' }};
    const cacheKey = @json(session('duplicate_cache_key'));

    if (isDuplication) {
        // Check if data is in cache (large project) or in session (normal project)
        if (cacheKey) {
            // Large project: Fetch data from cache via API
            console.log('Fetching duplication data from cache:', cacheKey);
            fetch(`/api/duplicate-data/${cacheKey}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.success && result.data) {
                        console.log('Cache data retrieved successfully, size:', JSON.stringify(result.data).length);
                        populateDuplicatedData(result.data);
                    } else {
                        throw new Error(result.error || 'Unknown error');
                    }
                })
                .catch(error => {
                    alert('⚠️ Erreur lors du chargement des données de duplication. Le cache a peut-être expiré. Veuillez recommencer la duplication.');
                    // Fallback: add empty rows
                    addAction();
                    addDeliverable();
                    addStakeholder();
                    addIssue();
                });
        } else {
            // Normal project: PRÉ-REMPLIR avec données de session
            populateDuplicatedData();
        }
    } else {
        // NORMAL: Ajouter lignes vides par défaut
        addAction();
        addDeliverable();
        addStakeholder();
        addIssue();
    }

    // Manager filiale validation - Frontend warning
    @if(auth()->user()->can('projects.view-filiale'))
        const userFiliale = "{{ auth()->user()->getFiliale() ?? '' }}";

        if (!userFiliale) {
            alert('⚠️ ATTENTION\n\nVotre compte n\'a pas de filiale associée.\nVous ne pourrez pas créer de projets.\n\nContactez un administrateur.');
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
                    alert('⚠️ ATTENTION - Projet Invisible\n\n' +
                        'Au moins une filiale (Exécutant ou Contractant) doit correspondre à votre filiale : ' + userFiliale + '\n\n' +
                        'Actuellement :\n' +
                        '• Exécutant : ' + (executant || '(vide)') + '\n' +
                        '• Contractant : ' + (contractant || '(vide)') + '\n\n' +
                        'Si vous continuez, vous ne pourrez PAS voir ce projet après sa création.');
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
