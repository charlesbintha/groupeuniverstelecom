<?php

namespace App\Http\Controllers\Web;

use App\Enums\ContractualisationType;
use App\Enums\DocumentType;
use App\Enums\ProjectNature;
use App\Enums\ProjectStatus;
use App\Enums\ProjectType;
use App\Enums\StatutFinancier;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Direction;
use App\Models\Employee;
use App\Models\Filiale;
use App\Models\KpiFamily;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\User;
use App\Services\External\EmployeeApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects with filters and pagination.
     */
    public function index(Request $request)
    {
        $perPage = 20;

        $query = Project::query()
            ->select('projects.*')
            ->withCount(['actions as nb_actions', 'deliverables as nb_livrables']);

        /** @var User $user */
        $user = Auth::user();

        // Use permissions instead of hardcoded roles for better maintainability
        if ($user->can('projects.view-any')) {
            // Admin/Project Admin: see all projects
        } elseif ($user->can('projects.view-filiale')) {
            // Manager: see projects from their filiale (executant OR contractant)
            // Using COLLATE for accent-insensitive comparison (e.g., "Télécom" = "Telecom")
            $filiale = $user->getFiliale();
            if ($filiale) {
                $query->where(function($q) use ($filiale) {
                    $q->whereRaw('LOWER(filiale_executant) COLLATE utf8mb4_unicode_ci = LOWER(?)', [$filiale])
                      ->orWhereRaw('LOWER(filiale_contractant) COLLATE utf8mb4_unicode_ci = LOWER(?)', [$filiale]);
                });
            } else {
                // Manager without filiale: see nothing (security)
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->can('projects.view-own')) {
            // Regular user: only see their own projects
            $query->where('user_id', '=', $user->id);
        } else {
            // No permissions: see nothing (security)
            $query->whereRaw('1 = 0');
        }

        // Search filter
        if ($search = $request->input('q')) {
            $query->where(function($q) use ($search) {
                $q->where('nom_projet', 'like', "%{$search}%")
                  ->orWhere('axe_strategique', 'like', "%{$search}%")
                  ->orWhere('owner_executant', 'like', "%{$search}%")
                  ->orWhere('filiale_executant', 'like', "%{$search}%")
                  ->orWhere('type_projet', 'like', "%{$search}%")
                  ->orWhere('nature_projet', 'like', "%{$search}%")
                  ->orWhere('direction_executant', 'like', "%{$search}%");
            });
        }

        // Filiale filter
        if ($filiale = $request->input('filiale')) {
            $query->where('filiale_contractant', $filiale);
        }

        // Direction filter (linked to selected filiale in UI)
        if ($direction = $request->input('direction')) {
            $query->where('direction_contractant', $direction);
        }

        // Status filter
        if ($statut = $request->input('statut')) {
            $query->where('statut_initial', $statut);
        }

        $query->orderBy('created_at', 'desc');



        $projects = $query->paginate($perPage)->withQueryString();

        // Filter options
        $filialesOptions = Filiale::orderBy('nom_filiale')
            ->pluck('nom_filiale')
            ->toArray();

        $directionsMap = Direction::orderBy('filiale')
            ->orderBy('nom_direction')
            ->get()
            ->groupBy('filiale')
            ->map(fn ($dirs) => $dirs->pluck('nom_direction')->toArray())
            ->toArray();

        $statutsOptions = ["Planifié", "En cours", "Pause", "Suspendu", "Mis en pause", "Retard", "Terminé"];

        return view('projects.index', compact(
            'projects',
            'filialesOptions',
            'directionsMap',
            'statutsOptions'
        ));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(EmployeeApiService $employeeApi)
    {
        // Get filiales
        $filialesOptions = Filiale::orderBy('nom_filiale')->get();

        // Get directions grouped by filiale
        $directions = Direction::orderBy('filiale')
            ->orderBy('nom_direction')
            ->get()
            ->groupBy('filiale');

        // Transform directions to a simple array for JavaScript
        $directionsMap = $directions->map(function($dirs) {
            return $dirs->pluck('nom_direction')->toArray();
        })->toArray();

        // Get active employees from API
        $employeesData = $this->loadEmployeesFromApi($employeeApi);
        if ($employeesData['error'] !== null) {
            session()->flash('error', $employeesData['error']);
        }
        $employees = $employeesData['employees'];

        // Get KPIs from view
        $kpiRows = DB::table('v_kpi')
            ->orderBy('famille_ordre')
            ->orderBy('indicateur_ordre')
            ->get();

        // Group KPIs by family
        $kpiGroups = [];
        foreach ($kpiRows as $row) {
            $key = $row->famille_ordre;
            if (!isset($kpiGroups[$key])) {
                $kpiGroups[$key] = [
                    'count' => 0,
                    'nom' => $row->nom_famille,
                    'mesure' => $row->mesure,
                    'applicabilite' => $row->applicabilite,
                    'indicators' => []
                ];
            }
            if (!empty($row->libelle)) {
                $kpiGroups[$key]['count']++;
                $kpiGroups[$key]['indicators'][] = [
                    'libelle' => $row->libelle,
                    'cible' => $row->cible
                ];
            }
        }

        return view('projects.create', compact(
            'filialesOptions',
            'directionsMap',
            'employees',
            'kpiGroups',
            'kpiRows'
        ));
    }

    /**
     * Store a newly created project in storage.
     */
    public function store(StoreProjectRequest $request, EmployeeApiService $employeeApi)
    {
        // Validation is now handled by StoreProjectRequest
        // All fields (including dynamic arrays) are validated server-side

        // Authorization: Check manager filiale constraint (defense in depth)
        Gate::authorize('validateManagerFiliale', [Project::class, $request->validated()]);

        // Normalize budget
        $budget = $this->normalizeBudget($request->input('budget_initial'));
        $sfAmount = $this->normalizeBudget($request->input('sf_opportunity_amount'));
        $encaissement = $this->normalizeBudget($request->input('montant_encaissement'));
        $decaissementSaisi = $this->normalizeBudget($request->input('montant_decaissement_2'));
        $engagement = 0;

        $employeeMapResult = $this->loadEmployeeMapFromApi($employeeApi, $request->input('stake_emp_id', []));
        if (!$employeeMapResult['ok']) {
            return back()
                ->withErrors(['stake_emp_id' => $employeeMapResult['error']])
                ->withInput();
        }
        $employeeMap = $employeeMapResult['map'];

        DB::beginTransaction();

        try {
            // LOG: Check maintenance_glpi value from request
            Log::info('ProjectController::store - Creating project', [
                'maintenance_glpi_raw' => $request->input('maintenance_glpi'),
                'maintenance_glpi_boolean' => $request->boolean('maintenance_glpi'),
                'nom_projet' => $request->input('nom_projet'),
            ]);

            // Create project (code_projet will be auto-generated by Observer)
            $project = Project::create([
                'user_id' => Auth::id(), // Save the creator's user ID
                'axe_strategique' => $request->input('axe_strategique'),
                'nom_projet' => $request->input('nom_projet'),
                'type_projet' => $request->input('type_projet'),
                'filiale_executant' => $request->input('filiale_executant'),
                'filiale_contractant' => $request->input('filiale_contractant'),
                'direction_executant' => $request->input('direction_executant'),
                'direction_contractant' => $request->input('direction_contractant'),
                'owner_executant' => $request->input('owner_executant'),
                'owner_contractant' => $request->input('owner_contractant'),
                'account_manager' => $request->input('account_manager') ?: $request->input('owner_contractant'),
                'date_demarrage' => $request->input('date_demarrage'),
                'date_fin' => $request->input('date_fin'),
                'statut_initial' => $request->input('statut_initial'),
                'statut_financier' => $request->input('statut_financier'),
                'ressource_a_mobiliser' => $request->boolean('ressource_a_mobiliser'),
                'resource_type' => $request->input('resource_type'),
                'resource_bank' => $request->input('resource_bank'),
                'contractualisation' => $request->boolean('contractualisation'),
                'contractualisation_type' => $request->input('contractualisation_type'),
                'objectif_projet' => $request->input('objectif_projet'),
                'contexte' => $request->input('contexte'),
                'synthese' => $request->input('synthese'),
                'nature_projet' => $request->input('nature_projet'),
                'budget_initial' => $budget,
                'montant_encaissement' => $encaissement,
                'montant_decaissement_2' => $decaissementSaisi,
                'montant_decaissement' => $engagement,
                'sf_opportunity_id' => $request->input('type_projet') === 'Externe' ? $request->input('sf_opportunity_id') : null,
                'sf_opportunity_name' => $request->input('type_projet') === 'Externe' ? $request->input('sf_opportunity_name') : null,
                'sf_opportunity_stage' => $request->input('type_projet') === 'Externe' ? $request->input('sf_opportunity_stage') : null,
                'sf_opportunity_amount' => $request->input('type_projet') === 'Externe' ? $sfAmount : null,
                'prochaine_etape' => $request->input('prochaine_etape'),
                'notes' => $request->input('notes'),
                'maintenance_glpi' => $request->boolean('maintenance_glpi'),
            ]);

            // LOG: Confirm project created with maintenance_glpi value
            Log::info('ProjectController::store - Project created', [
                'project_id' => $project->id,
                'maintenance_glpi' => $project->maintenance_glpi,
                'nom_projet' => $project->nom_projet,
            ]);

            // Handle contractualisation document
            if ($request->hasFile('contractualisation_document') && $request->boolean('contractualisation')) {
                $file = $request->file('contractualisation_document');
                $storedFilename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('contracts/' . $project->id, $storedFilename, 'private');

                $project->documents()->create([
                    'document_type' => DocumentType::CONTRACTUALISATION->value,
                    'name' => 'Contrat - ' . $request->input('contractualisation_type'),
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename' => $storedFilename,
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'contract_type' => $request->input('contractualisation_type'),
                    'uploaded_by' => Auth::id(),
                ]);
            }

            // Create actions
            if ($actions = $request->input('actions', [])) {
                foreach ($actions as $index => $libelle) {
                    if (!empty($libelle)) {
                        $project->actions()->create([
                            'libelle' => $libelle,
                            'ordre' => $index + 1,
                        ]);
                    }
                }
            }

            // ----- Deliverables (tableau associatif) -----
            $livrableNoms  = $request->input('livrable_nom', []);
            $livrableDescs = $request->input('livrable_desc', []);
            $livrableDates = $request->input('livrable_date', []);
            $livrableRealises = $request->input('livrable_realise', []);
            $livrableFiles = $request->file('livrable_document', []);

            foreach ($livrableNoms as $i => $livrable) {
                $livrable = trim($livrable);
                if (!empty($livrable)) {
                    $deliverable = $project->deliverables()->create([
                        'livrable'    => $livrable,
                        'description' => $livrableDescs[$i] ?? null,
                        'date_prevue' => $livrableDates[$i] ?? null,
                        'realise' => $livrableRealises[$i] ?? false,
                    ]);

                    // Handle deliverable document
                    if (isset($livrableFiles[$i]) && $livrableFiles[$i]->isValid()) {
                        $file = $livrableFiles[$i];
                        $storedFilename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('deliverables/' . $project->id, $storedFilename, 'private');

                        $project->documents()->create([
                            'document_type' => DocumentType::LIVRABLE->value,
                            'deliverable_id' => $deliverable->id,
                            'name' => 'Document - ' . $livrable,
                            'original_filename' => $file->getClientOriginalName(),
                            'stored_filename' => $storedFilename,
                            'path' => $path,
                            'mime_type' => $file->getMimeType(),
                            'size' => $file->getSize(),
                            'uploaded_by' => Auth::id(),
                        ]);
                    }
                }
            }

            // ----- Stakeholders (with deduplication to prevent MS Graph errors) -----
            $empIds = $request->input('stake_emp_id', []);
            $roles = $request->input('stake_role', []);
            $attentes = $request->input('stake_attentes', []);

            $seenEmployeeIds = []; // Track seen employee IDs to prevent duplicates

            foreach ($empIds as $i => $empId) {
                if ($empId !== null && $empId !== '') {
                    $empId = (int) $empId;
                    // Check for duplicate employee ID
                    if (in_array($empId, $seenEmployeeIds, true)) {
                        Log::warning('Duplicate stakeholder skipped during project creation', [
                            'project_id' => $project->id,
                            'employe_id' => $empId,
                            'index' => $i,
                            'role' => $roles[$i] ?? null
                        ]);
                        continue; // Skip this duplicate stakeholder
                    }

                    // Add to seen list
                    $seenEmployeeIds[] = $empId;

                    $employeeData = $employeeMap[$empId] ?? null;
                    $employeeRecord = $employeeData ? $this->syncEmployeeRecord($empId, $employeeData) : null;

                    $project->stakeholders()->create([
                        'employe_id' => $employeeRecord?->id ?? $empId, // id synced with API via CASCADE
                        'role' => $roles[$i] ?? null,
                        'prenom_nom' => $employeeRecord?->prenom_nom ?? ($employeeData['prenom_nom'] ?? null),
                        'email' => $employeeRecord?->email ?? ($employeeData['email'] ?? null),
                        'attentes' => $attentes[$i] ?? null,
                        'aad_id' => $employeeRecord?->aad_id ?? ($employeeData['aad_id'] ?? null),
                    ]);
                }
            }

            // ----- External Stakeholders -----
            $extOrganisations = $request->input('ext_stake_organisation', []);
            $extNomsComplets = $request->input('ext_stake_nom_complet', []);
            $extEmails = $request->input('ext_stake_email', []);
            $extTelephones = $request->input('ext_stake_telephone', []);
            $extRoles = $request->input('ext_stake_role', []);
            $extAttentes = $request->input('ext_stake_attentes', []);

            foreach ($extNomsComplets as $i => $nomComplet) {
                $nomComplet = trim($nomComplet);
                $organisation = trim($extOrganisations[$i] ?? '');

                if (!empty($nomComplet)) {
                    $project->externalStakeholders()->create([
                        'organisation' => $organisation ?: null,
                        'nom_complet' => $nomComplet,
                        'email' => $extEmails[$i] ?? null,
                        'telephone' => $extTelephones[$i] ?? null,
                        'role' => $extRoles[$i] ?? null,
                        'attentes' => $extAttentes[$i] ?? null,
                    ]);
                }
            }

            // ----- General Documents -----
            $docNames = $request->input('doc_name', []);
            $docFiles = $request->file('doc_file', []);

            foreach ($docNames as $i => $docName) {
                $docName = trim($docName);
                if (!empty($docName) && isset($docFiles[$i]) && $docFiles[$i]->isValid()) {
                    $file = $docFiles[$i];
                    $storedFilename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('documents/' . $project->id, $storedFilename, 'private');

                    $project->documents()->create([
                        'document_type' => \App\Enums\DocumentType::GENERAL->value,
                        'name' => $docName,
                        'original_filename' => $file->getClientOriginalName(),
                        'stored_filename' => $storedFilename,
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            // ----- Issues (tableau associatif) -----
            $issueCats    = $request->input('issue_cat', []);
            $issueDetails = $request->input('issue_detail', []);

            foreach ($issueDetails as $i => $detail) {
                $detail = trim($detail);
                if (!empty($detail)) {
                    $cat = $issueCats[$i] ?? 'Enjeux';
                    $project->issues()->create([
                        'categorie' => $cat,
                        'detail'    => $detail,
                    ]);
                }
            }


            DB::commit();

            // Flash session data (from duplication) is automatically cleared after this redirect
            // No need to manually forget() - Laravel handles it automatically (Recommendation B)

            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'Projet créé avec succès');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur création projet', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Erreur lors de la création du projet. Contactez l\'administrateur.']);
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project)
    {
        $this->authorize('view', $project);

        try {
            $project->load([
                'actions' => function ($q) {
                    $q->orderBy('ordre');
                },

                'deliverables' => function ($q) {
                    // Tri : d'abord les livrables sans date, puis par date croissante
                    $q->orderByRaw('date_prevue IS NULL, date_prevue ASC');
                },

                'stakeholders.employee',

                'issues' => function ($q) {
                    // Appliquer l'ordre sur la colonne disponible (sécurité contre les différences de schéma)
                    if (Schema::hasColumn('project_issues', 'categorie')) {
                        $q->orderByRaw("FIELD(categorie, 'Enjeux', 'Contraintes', 'Risques', 'REX')");
                    } elseif (Schema::hasColumn('project_issues', 'type')) {
                        $q->orderByRaw("FIELD(type, 'Enjeux', 'Contraintes', 'Risques', 'REX')");
                    } elseif (Schema::hasColumn('project_issues', 'issue_type')) {
                        $q->orderByRaw("FIELD(issue_type, 'Enjeux', 'Contraintes', 'Risques', 'REX')");
                    } else {
                        $q->orderBy('id');
                    }
                },
            ]);
        } catch (\Throwable $e) {
            // Log l'erreur pour debugging et return une page propre (ou abort si tu préfères)
            Log::error('Error loading project relations for show', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);

            // Option 1 : rediriger avec message d'erreur utilisateur
            return redirect()->route('projects.index')->with('error', 'Impossible de charger le projet (erreur technique).');

            // Option 2 : /!\ si tu préfères renvoyer 500 pour debugging -> throw $e;
        }

        // Salesforce data if applicable
        $sfData = null;
        if ($project->type_projet->value === 'Externe' && $project->sf_opportunity_id) {
            try {
                $salesforceService = app(\App\Services\External\SalesforceService::class);
                $sfOpportunity = $salesforceService->getOpportunity($project->sf_opportunity_id);

                if ($sfOpportunity) {
                    $sfData = [
                        'link' => 'https://universtelecom2022.my.salesforce.com/lightning/r/Opportunity/' . $project->sf_opportunity_id . '/view',
                        'account_name' => $sfOpportunity['account_name'] ?? null,
                        'amount' => $sfOpportunity['amount'] ?? null,
                        'close_date' => $sfOpportunity['close_date'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('Failed to fetch Salesforce opportunity details', [
                    'project_id' => $project->id,
                    'opportunity_id' => $project->sf_opportunity_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('projects.show', compact('project', 'sfData'));
    }

    /**
     * Retrieve the project's recovery amounts from the APEX invoicing module.
     */
    public function recouvrement(Project $project)
    {
        $this->authorize('view', $project);

        $emptyAmounts = [
            'montant_recouvrement' => 0,
            'montant_recouvre' => 0,
        ];

        try {
            $url = 'https://ged579032964b0a-m2tp93zrxyw6ducz.adb.me-dubai-1.oraclecloudapps.com'
                . '/ords/gut_corp/api/recouvrement/projet/'
                . rawurlencode((string) $project->code_projet);
            $response = Http::acceptJson()->timeout(15)->get($url);

            if (!$response->successful()) {
                Log::warning('API recouvrement indisponible', [
                    'project_id' => $project->id,
                    'status' => $response->status(),
                ]);

                return response()->json($emptyAmounts);
            }

            $item = $response->json('items.0');
            if (!is_array($item)) {
                return response()->json($emptyAmounts);
            }

            return response()->json([
                'montant_recouvrement' => (float) ($item['montant_facture'] ?? 0),
                'montant_recouvre' => (float) ($item['montant_recouvre'] ?? 0),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Erreur de connexion à l’API recouvrement', [
                'project_id' => $project->id,
                'error' => $exception->getMessage(),
            ]);

            return response()->json($emptyAmounts);
        }
    }

    /**
     * Show the form for editing the specified project.
     */
    public function edit(Project $project, EmployeeApiService $employeeApi)
    {
        $this->authorize('view', $project);

        // Charger relations nécessaires
        $project->load(['actions', 'deliverables', 'stakeholders', 'externalStakeholders', 'issues']);

        // Préparer arrays simples pour la vue (évite le code JS/PHP complexe dans Blade)
        $existingActions = $project->actions->pluck('libelle')->values();

        $existingDeliverables = $project->deliverables->map(function ($d) {
            $document = $d->documents()->where('document_type', DocumentType::LIVRABLE->value)->first();
            return [
                'nom'  => $d->livrable ?? null,
                'desc' => $d->description ?? null,
                'date' => $d->date_prevue ? $d->date_prevue->format('Y-m-d') : '',
                'realise' => $d->realise ?? false,
                'document' => $document ? [
                    'id' => $document->id,
                    'filename' => $document->original_filename,
                    'size' => $document->getFileSizeFormatted(),
                ] : null,
            ];
        })->values();

        $existingStakeholders = $project->stakeholders->map(function ($s) {
            return [
                'role'    => $s->role ?? null,
                'emp_id'  => $s->employe_id ?? null,
                'nom'     => $s->prenom_nom ?? null,
                'attentes'=> $s->attentes ?? null,
            ];
        })->values();

        $existingExternalStakeholders = $project->externalStakeholders->map(function ($s) {
            return [
                'organisation' => $s->organisation ?? null,
                'nom_complet'  => $s->nom_complet ?? null,
                'email'        => $s->email ?? null,
                'telephone' => $s->telephone ?? null,
                'role'         => $s->role ?? null,
                'attentes' => $s->attentes ?? null,
            ];
        })->values();

        $existingIssues = $project->issues->map(function ($i) {
            return [
                'cat'    => $i->categorie ?? null,
                'detail' => $i->detail ?? null,
            ];
        })->values();

        $existingGeneralDocs = $project->documents()
            ->where('document_type', \App\Enums\DocumentType::GENERAL->value)
            ->get()
            ->map(function($doc) {
                return [
                    'id' => $doc->id,
                    'name' => $doc->name,
                    'filename' => $doc->original_filename,
                    'size' => $doc->getFileSizeFormatted(),
                    'uploaded_at' => $doc->created_at->format('d/m/Y H:i'),
                ];
            })->toArray();

        // Get filiales
        $filialesOptions = Filiale::orderBy('nom_filiale')->get();

        // Get directions grouped by filiale
        $directions = Direction::orderBy('filiale')
            ->orderBy('nom_direction')
            ->get()
            ->groupBy('filiale');

        // Transform directions to a simple array for JavaScript
        $directionsMap = $directions->map(function($dirs) {
            return $dirs->pluck('nom_direction')->toArray();
        })->toArray();

        // Get active employees from API
        $includeEmployeeIds = $project->stakeholders->pluck('employe_id')->filter()->values()->all();
        $employeesData = $this->loadEmployeesFromApi($employeeApi, $includeEmployeeIds);
        if ($employeesData['error'] !== null) {
            session()->flash('error', $employeesData['error']);
        }
        $employees = $employeesData['employees'];

        return view('projects.edit', compact(
            'project',
            'filialesOptions',
            'directionsMap',
            'employees',
            'existingActions',
            'existingDeliverables',
            'existingStakeholders',
            'existingExternalStakeholders',
            'existingGeneralDocs',
            'existingIssues'
        ));
    }

    /**
     * Update the specified project in storage.
     */
    public function update(UpdateProjectRequest $request, Project $project, EmployeeApiService $employeeApi)
    {
        $this->authorize('update', $project);

        // Validation is now handled by UpdateProjectRequest
        // All fields (including dynamic arrays) are validated server-side

        // Authorization: Check manager filiale constraint (defense in depth)
        Gate::authorize('validateManagerFiliale', [Project::class, $request->validated()]);

        // Normalize budget
        $budget = $this->normalizeBudget($request->input('budget_initial'));
        $sfAmount = $this->normalizeBudget($request->input('sf_opportunity_amount'));
        $encaissement = $this->normalizeBudget($request->input('montant_encaissement'));
        $decaissementSaisi = $this->normalizeBudget($request->input('montant_decaissement_2'));

        $employeeMapResult = $this->loadEmployeeMapFromApi($employeeApi, $request->input('stake_emp_id', []));
        if (!$employeeMapResult['ok']) {
            return back()
                ->withErrors(['stake_emp_id' => $employeeMapResult['error']])
                ->withInput();
        }
        $employeeMap = $employeeMapResult['map'];

        DB::beginTransaction();

        try {
            // Update project
            // Salesforce: Keep existing values if not changed (empty select means "keep current")
            $isExternal = $request->input('type_projet') === 'Externe';
            $newSfOppId = $request->input('sf_opportunity_id');

            $project->fill([
                'axe_strategique' => $request->input('axe_strategique'),
                'nom_projet' => $request->input('nom_projet'),
                'type_projet' => $request->input('type_projet'),
                'filiale_executant' => $request->input('filiale_executant'),
                'filiale_contractant' => $request->input('filiale_contractant'),
                'direction_executant' => $request->input('direction_executant'),
                'direction_contractant' => $request->input('direction_contractant'),
                'owner_executant' => $request->input('owner_executant'),
                'owner_contractant' => $request->input('owner_contractant'),
                'account_manager' => $request->input('account_manager') ?: $request->input('owner_contractant'),
                'date_demarrage' => $request->input('date_demarrage'),
                'date_fin' => $request->input('date_fin'),
                'statut_initial' => $request->input('statut_initial'),
                'statut_financier' => $request->input('statut_financier'),
                'ressource_a_mobiliser' => $request->boolean('ressource_a_mobiliser'),
                'resource_type' => $request->input('resource_type'),
                'resource_bank' => $request->input('resource_bank'),
                'contractualisation' => $request->boolean('contractualisation'),
                'contractualisation_type' => $request->input('contractualisation_type'),
                'objectif_projet' => $request->input('objectif_projet'),
                'contexte' => $request->input('contexte'),
                'synthese' => $request->input('synthese'),
                'nature_projet' => $request->input('nature_projet'),
                'budget_initial' => $budget,
                'montant_encaissement' => $encaissement,
                'montant_decaissement_2' => $decaissementSaisi,
                // Salesforce: If External and new opportunity selected, update; else keep current or set null
                'sf_opportunity_id' => $isExternal && !empty($newSfOppId) ? $newSfOppId : ($isExternal ? $project->sf_opportunity_id : null),
                'sf_opportunity_name' => $isExternal && !empty($newSfOppId) ? $request->input('sf_opportunity_name') : ($isExternal ? $project->sf_opportunity_name : null),
                'sf_opportunity_stage' => $isExternal && !empty($newSfOppId) ? $request->input('sf_opportunity_stage') : ($isExternal ? $project->sf_opportunity_stage : null),
                'sf_opportunity_amount' => $isExternal && !empty($newSfOppId) ? $sfAmount : ($isExternal ? $project->sf_opportunity_amount : null),
                'prochaine_etape' => $request->input('prochaine_etape'),
                'notes' => $request->input('notes'),
                'maintenance_glpi' => $request->boolean('maintenance_glpi'),
            ]);
            // Related entities are synchronized below. Save quietly now, then emit
            // a single update event with touch() once the complete project is ready.
            $project->saveQuietly();

            // Handle contractualisation document update
            if ($request->hasFile('contractualisation_document')) {
                // Delete old contractualisation document
                $oldDocument = $project->documents()
                    ->where('document_type', DocumentType::CONTRACTUALISATION->value)
                    ->first();

                if ($oldDocument) {
                    Storage::disk('private')->delete($oldDocument->path);
                    $oldDocument->delete();
                }

                // Upload new document
                $file = $request->file('contractualisation_document');
                $storedFilename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('contracts/' . $project->id, $storedFilename, 'private');

                $project->documents()->create([
                    'document_type' => DocumentType::CONTRACTUALISATION->value,
                    'name' => 'Contrat - ' . $request->input('contractualisation_type'),
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_filename' => $storedFilename,
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'contract_type' => $request->input('contractualisation_type'),
                    'uploaded_by' => Auth::id(),
                ]);
            }

            // ------------------
            // Livrables - Sync intelligente pour préserver les documents existants
            // ------------------
            $existingDeliverables = $project->deliverables()->orderBy('id')->get();
            $livrableNoms  = $request->input('livrable_nom', []);
            $livrableDescs = $request->input('livrable_desc', []);
            $livrableDates = $request->input('livrable_date', []);
            $livrableRealises = $request->input('livrable_realise', []);
            $livrableFiles = $request->file('livrable_document', []);

            $processedDeliverableIds = [];

            foreach ($livrableNoms as $i => $livrable) {
                $livrable = trim($livrable);
                if (!empty($livrable)) {
                    // Check if deliverable at this index exists
                    $existingDeliverable = $existingDeliverables->get($i);

                    if ($existingDeliverable) {
                        // Update existing deliverable
                        $existingDeliverable->update([
                            'livrable'    => $livrable,
                            'description' => $livrableDescs[$i] ?? null,
                            'date_prevue' => $livrableDates[$i] ?? null,
                            'realise' => $livrableRealises[$i] ?? false,
                        ]);
                        $deliverable = $existingDeliverable;
                        $processedDeliverableIds[] = $existingDeliverable->id;

                        // Handle document replacement ONLY if new file uploaded
                        if (isset($livrableFiles[$i]) && $livrableFiles[$i]->isValid()) {
                            // Delete old document
                            $oldDocument = $project->documents()
                                ->where('deliverable_id', $deliverable->id)
                                ->where('document_type', DocumentType::LIVRABLE->value)
                                ->first();

                            if ($oldDocument) {
                                Storage::disk('private')->delete($oldDocument->path);
                                $oldDocument->delete();
                            }

                            // Upload new document
                            $file = $livrableFiles[$i];
                            $storedFilename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $path = $file->storeAs('deliverables/' . $project->id, $storedFilename, 'private');

                            $project->documents()->create([
                                'document_type' => DocumentType::LIVRABLE->value,
                                'deliverable_id' => $deliverable->id,
                                'name' => 'Document - ' . $livrable,
                                'original_filename' => $file->getClientOriginalName(),
                                'stored_filename' => $storedFilename,
                                'path' => $path,
                                'mime_type' => $file->getMimeType(),
                                'size' => $file->getSize(),
                                'uploaded_by' => Auth::id(),
                            ]);
                        }
                        // Else: Keep existing document (no change)
                    } else {
                        // Create new deliverable
                        $deliverable = $project->deliverables()->create([
                            'livrable'    => $livrable,
                            'description' => $livrableDescs[$i] ?? null,
                            'date_prevue' => $livrableDates[$i] ?? null,
                            'realise' => $livrableRealises[$i] ?? false,
                        ]);
                        $processedDeliverableIds[] = $deliverable->id;

                        // Handle new deliverable document
                        if (isset($livrableFiles[$i]) && $livrableFiles[$i]->isValid()) {
                            $file = $livrableFiles[$i];
                            $storedFilename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                            $path = $file->storeAs('deliverables/' . $project->id, $storedFilename, 'private');

                            $project->documents()->create([
                                'document_type' => DocumentType::LIVRABLE->value,
                                'deliverable_id' => $deliverable->id,
                                'name' => 'Document - ' . $livrable,
                                'original_filename' => $file->getClientOriginalName(),
                                'stored_filename' => $storedFilename,
                                'path' => $path,
                                'mime_type' => $file->getMimeType(),
                                'size' => $file->getSize(),
                                'uploaded_by' => Auth::id(),
                            ]);
                        }
                    }
                }
            }

            // Delete deliverables that are no longer present (and their documents)
            foreach ($existingDeliverables as $existingDeliverable) {
                if (!in_array($existingDeliverable->id, $processedDeliverableIds)) {
                    // Delete associated documents
                    foreach ($existingDeliverable->documents as $document) {
                        Storage::disk('private')->delete($document->path);
                        $document->delete();
                    }
                    $existingDeliverable->delete();
                }
            }

            // ------------------
            // Actions - Sync intelligente pour préserver ms_task_id
            // ------------------
            $existingActions = $project->actions()->orderBy('ordre')->get();
            $newActions = $request->input('actions', []);

            // Map existing actions by ordre
            $existingMap = [];
            foreach ($existingActions as $action) {
                $existingMap[$action->ordre] = $action;
            }

            // Update or create actions while preserving ms_task_id
            $processedIds = [];
            foreach ($newActions as $index => $libelle) {
                $libelle = trim($libelle);
                if (!empty($libelle)) {
                    $ordre = $index + 1;

                    // If action at this ordre exists, update it (keep ms_task_id and ms_task_etag)
                    if (isset($existingMap[$ordre])) {
                        $existingMap[$ordre]->update(['libelle' => $libelle, 'ordre' => $ordre]);
                        $processedIds[] = $existingMap[$ordre]->id;
                    } else {
                        // Create new action (will get ms_task_id during sync)
                        $newAction = $project->actions()->create([
                            'libelle' => $libelle,
                            'ordre' => $ordre,
                        ]);
                        $processedIds[] = $newAction->id;
                    }
                }
            }

            // Delete actions that are no longer in the list
            $project->actions()->whereNotIn('id', $processedIds)->delete();

            Log::info('Actions synchronized in controller', [
                'project_id' => $project->id,
                'total_actions' => count($processedIds),
                'new_actions' => count($newActions) - count($existingActions),
            ]);

            // ------------------
            // Stakeholders (RACI) - with deduplication to prevent MS Graph errors
            // ------------------
            $project->stakeholders()->delete(); // Supprimer les anciens
            $empIds = $request->input('stake_emp_id', []);
            $roles = $request->input('stake_role', []);
            $attentes = $request->input('stake_attentes', []);

            $seenEmployeeIds = []; // Track seen employee IDs to prevent duplicates

            foreach ($empIds as $i => $empId) {
                if ($empId !== null && $empId !== '') {
                    $empId = (int) $empId;
                    // Check for duplicate employee ID
                    if (in_array($empId, $seenEmployeeIds, true)) {
                        Log::warning('Duplicate stakeholder skipped during project update', [
                            'project_id' => $project->id,
                            'employe_id' => $empId,
                            'index' => $i,
                            'role' => $roles[$i] ?? null
                        ]);
                        continue; // Skip this duplicate stakeholder
                    }

                    // Add to seen list
                    $seenEmployeeIds[] = $empId;

                    $employeeData = $employeeMap[$empId] ?? null;
                    $employeeRecord = $employeeData ? $this->syncEmployeeRecord($empId, $employeeData) : null;

                    $project->stakeholders()->create([
                        'employe_id' => $employeeRecord?->id ?? $empId, // id synced with API via CASCADE
                        'role' => $roles[$i] ?? null,
                        'prenom_nom' => $employeeRecord?->prenom_nom ?? ($employeeData['prenom_nom'] ?? null),
                        'email' => $employeeRecord?->email ?? ($employeeData['email'] ?? null),
                        'attentes' => $attentes[$i] ?? null,
                        'aad_id' => $employeeRecord?->aad_id ?? ($employeeData['aad_id'] ?? null),
                    ]);
                }
            }

            // ------------------
            // External Stakeholders
            // ------------------
            $project->externalStakeholders()->delete();
            $extOrganisations = $request->input('ext_stake_organisation', []);
            $extNomsComplets = $request->input('ext_stake_nom_complet', []);
            $extEmails = $request->input('ext_stake_email', []);
            $extTelephones = $request->input('ext_stake_telephone', []);
            $extRoles = $request->input('ext_stake_role', []);
            $extAttentes = $request->input('ext_stake_attentes', []);

            foreach ($extNomsComplets as $i => $nomComplet) {
                $nomComplet = trim($nomComplet);
                $organisation = trim($extOrganisations[$i] ?? '');

                if (!empty($nomComplet)) {
                    $project->externalStakeholders()->create([
                        'organisation' => $organisation ?: null,
                        'nom_complet' => $nomComplet,
                        'email' => $extEmails[$i] ?? null,
                        'telephone' => $extTelephones[$i] ?? null,
                        'role' => $extRoles[$i] ?? null,
                        'attentes' => $extAttentes[$i] ?? null,
                    ]);
                }
            }

            // ------------------
            // General Documents
            // ------------------
            // Add new general documents (keep existing ones)
            $docNames = $request->input('doc_name', []);
            $docFiles = $request->file('doc_file', []);

            foreach ($docNames as $i => $docName) {
                $docName = trim($docName);
                if (!empty($docName) && isset($docFiles[$i]) && $docFiles[$i]->isValid()) {
                    $file = $docFiles[$i];
                    $storedFilename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('documents/' . $project->id, $storedFilename, 'private');

                    $project->documents()->create([
                        'document_type' => \App\Enums\DocumentType::GENERAL->value,
                        'name' => $docName,
                        'original_filename' => $file->getClientOriginalName(),
                        'stored_filename' => $storedFilename,
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => Auth::id(),
                    ]);
                }
            }

            // ------------------
            // Issues / Enjeux / Contraintes / Risques
            // ------------------
            $project->issues()->delete(); // Supprimer les anciens
            $issueCats    = $request->input('issue_cat', []);
            $issueDetails = $request->input('issue_detail', []);

            foreach ($issueDetails as $i => $detail) {
                $detail = trim($detail);
                if (!empty($detail)) {
                    $cat = $issueCats[$i] ?? 'Enjeux';
                    $project->issues()->create([
                        'categorie' => $cat,
                        'detail'    => $detail,
                    ]);
                }
            }

            // Force project updated_at to change - this triggers the observer's updated() event
            // which will dispatch the MS Planner sync job
            $project->touch();

            Log::info('Project touched to trigger observer', [
                'project_id' => $project->id,
                'has_ms_plan_id' => !empty($project->ms_plan_id),
                'auto_sync_enabled' => config('microsoft-graph.auto_sync', false),
            ]);

            DB::commit();

            return redirect()
                ->route('projects.show', $project)
                ->with('success', 'Projet modifié avec succès !');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Erreur modification projet', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'Erreur lors de la modification: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified project from storage (soft delete).
     */
    public function destroy(Project $project)
    {
        $this->authorize('delete', $project);

        try {
            // Delete all project documents before deleting project
            foreach ($project->documents as $document) {
                Storage::disk('private')->delete($document->path);
                $document->delete();
            }

            $project->delete();

            return redirect()
                ->route('projects.index')
                ->with('success', 'Projet supprimé avec succès.');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }


    /**
     * Prepare project duplication by pre-filling the create form.
     * Uses flash session with form-aligned keys for seamless pre-population.
     *
     * @param Project $project
     * @return \Illuminate\Http\RedirectResponse
     */
    public function duplicate(Project $project)
    {
        /** @var User $user */
        $this->authorize('duplicate', $project);

        // Load all relations
        $project->load(['actions', 'deliverables', 'stakeholders', 'externalStakeholders', 'issues']);

        // Extract enum values (handle both enum objects and strings)
        $typeProjet = $project->type_projet instanceof \BackedEnum
            ? $project->type_projet->value
            : $project->type_projet;

        $natureProjet = $project->nature_projet instanceof \BackedEnum
            ? $project->nature_projet->value
            : $project->nature_projet;

        // Prepare duplicated data with ALIGNED keys matching form field names
        // IMPORTANT: Keys must match exactly what store() expects (Recommendation A)
        $duplicatedData = [
                // Flash message for user
                'info' => '📋 Duplication du projet : <strong>' . e($project->nom_projet) . '</strong>. Vérifiez et ajustez les informations ci-dessous.',

                // Source project ID for tracking
                'duplicate_source_id' => $project->id,

                // Main project fields (aligned with form input names)
                'nom_projet' => $project->nom_projet . ' - Copie',
                'type_projet' => $typeProjet,
                'nature_projet' => $natureProjet,
                'axe_strategique' => $project->axe_strategique,
                'objectif_projet' => $project->objectif_projet,
                'budget_initial' => $project->budget_initial,
                'montant_encaissement' => $project->montant_encaissement,
                'montant_decaissement_2' => $project->montant_decaissement_2,
                'prochaine_etape' => $project->prochaine_etape,
                'notes' => $project->notes,

                // Financial and contract fields
                'statut_financier' => $project->statut_financier?->value,
                'ressource_a_mobiliser' => $project->ressource_a_mobiliser,
                'resource_type' => $project->resource_type?->value,
                'resource_bank' => $project->resource_bank?->value,
                'contractualisation' => $project->contractualisation,
                'contractualisation_type' => $project->contractualisation_type?->value,
                // Note: contractualisation_document is NOT duplicated (files don't copy)

                // Reset status to "Planifié" for new project
                'statut_initial' => 'Planifié',

                // Executant/Contractant structure
                'filiale_executant' => $project->filiale_executant,
                'filiale_contractant' => $project->filiale_contractant,
                'direction_executant' => $project->direction_executant,
                'direction_contractant' => $project->direction_contractant,
                'owner_executant' => $project->owner_executant,
                'owner_contractant' => $project->owner_contractant,
                'account_manager' => $project->account_manager ?? $project->owner_contractant,

                // Reset dates to null (user will fill manually)
                'date_demarrage' => null,
                'date_fin' => null,

                // Relations - Actions (array aligned with form: actions[])
                'actions' => $project->actions->pluck('libelle')->toArray(),

                // Relations - Deliverables (aligned with form: livrable_nom[], livrable_desc[], livrable_date[], livrable_realise[])
                'livrable_nom' => $project->deliverables->pluck('livrable')->toArray(),
                'livrable_desc' => $project->deliverables->pluck('description')->toArray(),
                'livrable_date' => array_fill(0, $project->deliverables->count(), null), // Reset dates
                'livrable_realise' => array_fill(0, $project->deliverables->count(), false), // Reset to non-réalisé

                // Relations - Stakeholders (aligned with form: stake_emp_id[], stake_role[], stake_attentes[])
                'stake_emp_id' => $project->stakeholders->pluck('employe_id')->toArray(),
                'stake_role' => $project->stakeholders->pluck('role')->toArray(),
                'stake_attentes' => $project->stakeholders->pluck('attentes')->toArray(),

                // Relations - External Stakeholders
                'ext_stake_organisation' => $project->externalStakeholders->pluck('organisation')->toArray(),
                'ext_stake_nom_complet' => $project->externalStakeholders->pluck('nom_complet')->toArray(),
                'ext_stake_email' => $project->externalStakeholders->pluck('email')->toArray(),
                'ext_stake_telephone' => $project->externalStakeholders->pluck('telephone')->toArray(),
                'ext_stake_role' => $project->externalStakeholders->pluck('role')->toArray(),
                'ext_stake_attentes' => $project->externalStakeholders->pluck('attentes')->toArray(),

                // Relations - General Documents (names only, files NOT copied)
                'doc_name' => $project->documents()
                    ->where('document_type', \App\Enums\DocumentType::GENERAL->value)
                    ->pluck('name')
                    ->toArray(),
                // Note: doc_file[] is NOT duplicated (files don't copy for security)

                // Relations - Issues (aligned with form: issue_cat[], issue_detail[])
                'issue_cat' => $project->issues->pluck('categorie')->toArray(),
                'issue_detail' => $project->issues->pluck('detail')->toArray(),

                // CRITICAL: Do NOT copy Salesforce data (Recommendation D)
                // External projects must select a NEW opportunity
                'sf_opportunity_id' => null,
                'sf_opportunity_name' => null,
                'sf_opportunity_stage' => null,
                'sf_opportunity_amount' => null,

                // CRITICAL: Do NOT copy Microsoft Planner IDs (Recommendation D)
                // New project will get its own MS Group/Plan if auto-sync is enabled
                // ms_group_id, ms_plan_id, ms_bucket_id are NOT copied
                // ms_task_id and ms_task_etag in actions are NOT copied (handled in store())

                // Auto-sync control (Recommendation C)
                // Default to false for duplicated projects to let user review first
                'auto_sync_enabled' => false,
        ];

        // Check data size to determine storage method
        // If data > 3KB, use cache to avoid session overflow
        $dataSize = strlen(json_encode($duplicatedData));
        $maxSessionSize = 3072; // 3KB limit for safety (session cookie limit is 4KB)

        if ($dataSize > $maxSessionSize) {
            // Large project: Store in cache with 30-minute TTL
            $cacheKey = 'project_duplicate_' . $project->id . '_' . Auth::id() . '_' . time();
            Cache::put($cacheKey, $duplicatedData, now()->addMinutes(30));

            return redirect()
                ->route('projects.create')
                ->with([
                    'info' => '📋 Duplication du projet : <strong>' . e($project->nom_projet) . '</strong>. Vérifiez et ajustez les informations ci-dessous.',
                    'duplicate_cache_key' => $cacheKey, // Signal to JS to fetch from cache
                    'duplicate_source_id' => $project->id,
                ]);
        }

        // Normal flow: data fits in session
        return redirect()
            ->route('projects.create')
            ->with($duplicatedData);
    }

    /**
     * API endpoint to retrieve cached duplication data for large projects.
     *
     * @param string $cacheKey The cache key from session
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDuplicateData(string $cacheKey)
    {
        // Security: Verify cache key format (prevent arbitrary cache access)
        if (!preg_match('/^project_duplicate_(\d+)_(\d+)_(\d+)$/', $cacheKey, $matches)) {
            Log::warning('Invalid cache key format attempted', [
                'cache_key' => $cacheKey,
                'user_id' => Auth::id(),
                'ip' => request()->ip()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Invalid cache key format'
            ], 400);
        }

        // Extract components from cache key
        $projectId = (int) $matches[1];
        $ownerId = (int) $matches[2];
        $timestamp = (int) $matches[3];

        // Security: Verify that the current user is the owner of this cache
        if ($ownerId !== Auth::id())  {
            Log::warning('Unauthorized cache access attempt', [
                'cache_key' => $cacheKey,
                'cache_owner_id' => $ownerId,
                'requesting_user_id' => Auth::id(),
                'ip' => request()->ip()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Unauthorized access'
            ], 403);
        }

        // Retrieve data from cache
        $data = Cache::get($cacheKey);

        if (!$data) {
            return response()->json([
                'success' => false,
                'error' => 'Cache expired or not found. Please restart duplication.'
            ], 404);
        }

        // Clear cache after retrieval (one-time use)
        Cache::forget($cacheKey);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Load employees from the API for selection lists.
     *
     * @return array{employees: \Illuminate\Support\Collection, error: string|null}
     */
    protected function loadEmployeesFromApi(EmployeeApiService $employeeApi, array $includeEmployeeIds = []): array
    {
        $response = $employeeApi->listEmployees();

        if (!$response['ok']) {
            return [
                'employees' => collect(),
                'error' => $response['error'] ?? 'Employee API request failed.',
            ];
        }

        $includeEmployeeIds = array_values(array_unique(array_filter($includeEmployeeIds, function ($id) {
            return $id !== null && $id !== '';
        })));
        $includeLookup = array_flip(array_map('intval', $includeEmployeeIds));

        $employees = collect($response['items'])
            ->filter(function (array $employee) use ($includeLookup) {
                $employeeId = isset($employee['id']) ? (int) $employee['id'] : null;
                if ($employeeId !== null && isset($includeLookup[$employeeId])) {
                    return true;
                }

                if (!array_key_exists('actif', $employee) || $employee['actif'] === null) {
                    return true;
                }

                return $employee['actif'] === true;
            })
            ->sortBy(function (array $employee) {
                return strtolower((string) ($employee['prenom_nom'] ?? ''));
            })
            ->map(function (array $employee) {
                return (object) $employee;
            })
            ->values();

        return [
            'employees' => $employees,
            'error' => null,
        ];
    }

    /**
     * Load employees from the API and build a map for selected IDs.
     *
     * @return array{ok: bool, map: array<int, array<string, mixed>>, error: string|null}
     */
    protected function loadEmployeeMapFromApi(EmployeeApiService $employeeApi, array $employeeIds): array
    {
        $employeeIds = array_values(array_unique(array_filter($employeeIds, function ($id) {
            return $id !== null && $id !== '';
        })));
        $employeeIds = array_map('intval', $employeeIds);

        if ($employeeIds === []) {
            return [
                'ok' => true,
                'map' => [],
                'error' => null,
            ];
        }

        $response = $employeeApi->listEmployees();

        if (!$response['ok']) {
            return [
                'ok' => false,
                'map' => [],
                'error' => $response['error'] ?? 'Employee API request failed.',
            ];
        }

        $map = [];
        foreach ($response['items'] as $employee) {
            if (!is_array($employee) || !isset($employee['id'])) {
                continue;
            }

            $employeeId = (int) $employee['id'];
            if (in_array($employeeId, $employeeIds, true)) {
                $map[$employeeId] = $employee;
            }
        }

        $missing = array_diff($employeeIds, array_keys($map));
        if (!empty($missing)) {
            return [
                'ok' => false,
                'map' => [],
                'error' => 'Employe introuvable dans l API.',
            ];
        }

        foreach ($map as $employee) {
            if (array_key_exists('actif', $employee) && $employee['actif'] === false) {
                return [
                    'ok' => false,
                    'map' => [],
                    'error' => 'Employe inactif dans l API.',
                ];
            }
        }

        return [
            'ok' => true,
            'map' => $map,
            'error' => null,
        ];
    }

    /**
     * Sync the API employee payload into the local database for FK integrity.
     *
     * @param array<string, mixed> $employeeData
     */
    protected function syncEmployeeRecord(int $employeeId, array $employeeData): Employee
    {
        $employee = Employee::find($employeeId);

        $filiale = trim((string) ($employeeData['filiale'] ?? ''));
        if ($filiale === '' && $employee) {
            $filiale = trim((string) ($employee->filiale ?? ''));
        }

        if ($filiale === '') {
            throw new \RuntimeException('Employee filiale is missing in API.');
        }

        $payload = [
            'prenom_nom' => $employeeData['prenom_nom'] ?? ($employee?->prenom_nom),
            'email' => $employeeData['email'] ?? ($employee?->email),
            'filiale' => $filiale,
            'aad_id' => $employeeData['aad_id'] ?? ($employee?->aad_id),
        ];

        if (array_key_exists('actif', $employeeData)) {
            $payload['actif'] = (bool) $employeeData['actif'];
        } elseif ($employee) {
            $payload['actif'] = $employee->actif;
        } else {
            $payload['actif'] = true;
        }

        if ($employee) {
            $employee->fill($payload);
        } else {
            // Employee not found by ID — check by email
            // (the API may have reassigned a new ID to an existing employee)
            $existingByEmail = !empty($payload['email'])
                ? Employee::where('email', $payload['email'])->first()
                : null;

            if ($existingByEmail && $existingByEmail->id !== $employeeId) {
                // Update the PK to match the API ID.
                // ON UPDATE CASCADE on project_stakeholders will sync employe_id automatically.
                DB::table('employes')
                    ->where('id', $existingByEmail->id)
                    ->update(['id' => $employeeId]);

                $employee = Employee::find($employeeId);
                $employee->fill($payload);
            } elseif ($existingByEmail) {
                $employee = $existingByEmail;
                $employee->fill($payload);
            } else {
                $employee = new Employee($payload);
                $employee->id = $employeeId;
            }
        }

        $employee->save();

        return $employee;
    }

    /**
     * Download a project document.
     */
    public function downloadDocument(Project $project, ProjectDocument $document)
    {
        $this->authorize('downloadDocument', $project);

        if ($document->project_id !== $project->id) {
            abort(403, 'Ce document n\'appartient pas à ce projet.');
        }

        if (!Storage::disk('private')->exists($document->path)) {
            abort(404, 'Fichier introuvable.');
        }

        return Storage::disk('private')->download($document->path, $document->original_filename);
    }

    /**
     * Delete a specific document from a project.
     */
    public function deleteDocument(Project $project, ProjectDocument $document)
    {
        $this->authorize('update', $project);

        if ($document->project_id !== $project->id) {
            abort(403, 'Ce document n\'appartient pas à ce projet.');
        }

        // Delete file from storage
        if (Storage::disk('private')->exists($document->path)) {
            Storage::disk('private')->delete($document->path);
        }

        // Delete database record
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => 'Document supprimé avec succès'
        ]);
    }

    /**
     * Redirect to Microsoft Planner report with secure token.
     */
    public function report(Project $project)
    {
        $this->authorize('view', $project);

        if (!$project->ms_plan_id) {
            return redirect()->route('projects.index')
                ->with('error', 'Ce projet n\'est pas synchronisé avec Microsoft Planner.');
        }

        $tokenKey = 'report_access_' . Auth::id() . '_' . $project->id . '_' . time();

        Cache::put(
            $tokenKey,
            [
                'user_id' => Auth::id(),
                'project_id' => $project->id,
                'ip' => request()->ip(),
                'timestamp' => time(),
            ],
            now()->addMinutes(5)
        );

        $reportUrl = url('/rapport/detail.php') . '?' . http_build_query([
            'pid' => $project->id,
            'planId' => $project->ms_plan_id,
            'bucketId' => '',
            'token' => $tokenKey,
        ]);

        return redirect($reportUrl);
    }

    /**
     * Normalize budget input (handles French comma format and Unicode spaces).
     */
    protected function normalizeBudget($value)
    {
        if (is_null($value) || $value === '') {
            return null;
        }

        // Remove all Unicode whitespace (incl. narrow no-break space U+202F used by fr-FR Intl.NumberFormat)
        $tmp = preg_replace('/\p{Z}/u', '', (string)$value);
        // Replace French decimal comma with period
        $tmp = str_replace(',', '.', $tmp);

        if (is_numeric($tmp) && (float)$tmp >= 0) {
            return number_format((float)$tmp, 2, '.', '');
        }

        return null;
    }

    /**
     * Validate a wizard step (AJAX) - Create mode.
     */
    public function wizardValidateStep(Request $request, int $step)
    {
        $rules = $this->getWizardStepRules($step);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $this->getWizardStepMessages());

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Store validated data in session
        $sessionKey = 'project_wizard_create';
        $wizardData = session($sessionKey, ['step' => 1, 'data' => []]);
        $wizardData['step'] = $step + 1;
        $wizardData['data'] = array_merge($wizardData['data'], $request->except(['_token']));
        session([$sessionKey => $wizardData]);

        return response()->json([
            'ok' => true,
            'next_step' => min($step + 1, 3),
        ]);
    }

    /**
     * Validate a wizard step (AJAX) - Edit mode.
     */
    public function wizardValidateStepEdit(Request $request, Project $project, int $step)
    {
        $this->authorize('update', $project);

        $rules = $this->getWizardStepRules($step);

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules, $this->getWizardStepMessages());

        if ($validator->fails()) {
            return response()->json([
                'ok' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Store validated data in session
        $sessionKey = "project_wizard_edit_{$project->id}";
        $wizardData = session($sessionKey, ['step' => 1, 'data' => []]);
        $wizardData['step'] = $step + 1;
        $wizardData['data'] = array_merge($wizardData['data'], $request->except(['_token']));
        session([$sessionKey => $wizardData]);

        return response()->json([
            'ok' => true,
            'next_step' => min($step + 1, 3),
        ]);
    }

    /**
     * Get validation rules for a specific wizard step.
     */
    protected function getWizardStepRules(int $step): array
    {
        return match($step) {
            1 => [
                'nom_projet' => ['required', 'string', 'max:255'],
                'type_projet' => ['required', 'in:Interne,Externe'],
                'nature_projet' => ['nullable', 'in:B2B,B2C,GOV,Autres'],
                'statut_initial' => ['required', 'in:Planifié,En cours,Pause,Suspendu,Mis en pause,Retard,Terminé'],
                'statut_financier' => ['required', 'in:En cours,Terminé,Non démarré'],
                'ressource_a_mobiliser' => ['required', 'boolean'],
                'contractualisation' => ['required', 'boolean'],
                'filiale_executant' => ['required', 'string'],
                'filiale_contractant' => ['nullable', 'string'],
                'owner_executant' => ['required', 'string'],
                'account_manager' => ['required', 'string'],
                'date_demarrage' => ['nullable', 'date'],
                'date_fin' => ['nullable', 'date', 'after_or_equal:date_demarrage'],
            ],
            2 => [
                'contexte' => ['nullable', 'string'],
                'objectif_projet' => ['nullable', 'string'],
                'synthese' => ['nullable', 'string'],
                'prochaine_etape' => ['nullable', 'string'],
                'actions' => ['nullable', 'array', 'max:100'],
                'actions.*' => ['nullable', 'string', 'max:500'],
                'livrable_nom' => ['nullable', 'array', 'max:50'],
                'livrable_nom.*' => ['nullable', 'string', 'max:255'],
            ],
            3 => [
                'stake_emp_id' => ['nullable', 'array', 'max:50'],
                'ext_stake_organisation' => ['nullable', 'array', 'max:50'],
                'ext_stake_nom_complet' => ['nullable', 'array', 'max:50'],
                'ext_stake_telephone' => ['nullable', 'array', 'max:50'],
                'ext_stake_telephone.*' => ['nullable', 'string', 'max:30'],
                'issue_cat' => ['nullable', 'array', 'max:50'],
            ],
            default => [],
        };
    }

    /**
     * Get custom error messages for wizard validation.
     */
    protected function getWizardStepMessages(): array
    {
        return [
            'nom_projet.required' => 'Le nom du projet est obligatoire.',
            'type_projet.required' => 'Le type de projet est obligatoire.',
            'filiale_executant.required' => 'La filiale exécutant est obligatoire.',
            'owner_executant.required' => 'Le chef de projet est obligatoire.',
            'account_manager.required' => 'L\'Account Manager est obligatoire.',
            'statut_initial.required' => 'Le statut initial est obligatoire.',
            'statut_financier.required' => 'Le statut financier est obligatoire.',
            'date_fin.after_or_equal' => 'La date de fin doit être après ou égale à la date de démarrage.',
        ];
    }
}
