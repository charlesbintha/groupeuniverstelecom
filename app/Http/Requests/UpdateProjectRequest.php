<?php

namespace App\Http\Requests;

use App\Enums\BankType;
use App\Enums\ResourceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Main project fields
            'nom_projet' => ['required', 'string', 'max:255'],
            'type_projet' => ['required', 'in:Interne,Externe'],
            'nature_projet' => ['nullable', 'in:B2B,B2C,GOV,Autres'],
            'statut_initial' => ['required', 'in:Planifié,En cours,Pause,Suspendu,Mis en pause,Retard,Terminé'],
            'axe_strategique' => ['nullable', 'string'],
            'objectif_projet' => ['nullable', 'string'],
            'contexte' => ['nullable', 'string'],
            'synthese' => ['nullable', 'string'],
            'budget_initial' => ['nullable', 'string'],
            'montant_encaissement' => ['nullable', 'string'],
            'montant_decaissement_2' => ['nullable', 'string'],
            'prochaine_etape' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'date_demarrage' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_demarrage'],

            // Financial and contract fields
            'statut_financier' => ['required', 'in:En cours,Terminé,Non démarré'],
            'ressource_a_mobiliser' => ['required', 'boolean'],
            'resource_type' => ['nullable', 'required_if:ressource_a_mobiliser,true', Rule::enum(ResourceType::class)],
            'resource_bank' => ['nullable', 'required_if:resource_type,Banque', Rule::enum(BankType::class)],
            'contractualisation' => ['required', 'boolean'],
            'contractualisation_type' => ['nullable', 'in:Bon de commande,Annexes'],
            'contractualisation_document' => ['sometimes', 'nullable', 'file', 'mimes:pdf,doc,docx,zip,xls,xlsx', 'max:51200'],

            // Executant/Contractant structure
            'filiale_executant' => ['required', 'string'],
            'filiale_contractant' => ['nullable', 'string'],
            'direction_executant' => ['nullable', 'string'],
            'direction_contractant' => ['nullable', 'string'],
            'owner_executant' => ['required', 'string'],
            'owner_contractant' => ['nullable', 'string'],
            'account_manager' => ['required', 'string'],

            // Salesforce fields
            'sf_opportunity_amount' => ['nullable', 'string'],

            // Dynamic arrays - Actions
            'actions' => ['nullable', 'array', 'max:100'],
            'actions.*' => ['nullable', 'string', 'max:500'],

            // Dynamic arrays - Deliverables
            'livrable_nom' => ['nullable', 'array', 'max:50'],
            'livrable_nom.*' => ['nullable', 'string', 'max:255'],
            'livrable_desc' => ['nullable', 'array'],
            'livrable_desc.*' => ['nullable', 'string', 'max:1000'],
            'livrable_date' => ['nullable', 'array'],
            'livrable_date.*' => ['nullable', 'date'],
            'livrable_document' => ['sometimes', 'nullable', 'array'],
            'livrable_document.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,zip,xls,xlsx', 'max:51200'],
            'livrable_realise' => ['nullable', 'array'],
            'livrable_realise.*' => ['nullable', 'boolean'],

            // Dynamic arrays - Stakeholders
            'stake_emp_id' => ['nullable', 'array', 'max:50'],
            'stake_emp_id.*' => ['nullable', 'integer'],
            'stake_role' => ['nullable', 'array'],
            'stake_role.*' => ['nullable', 'string', 'max:255'],
            'stake_attentes' => ['nullable', 'array'],
            'stake_attentes.*' => ['nullable', 'string', 'max:1000'],

            // Dynamic arrays - External Stakeholders
            'ext_stake_organisation' => ['nullable', 'array', 'max:50'],
            'ext_stake_organisation.*' => ['nullable', 'string', 'max:100'],
            'ext_stake_nom_complet' => ['nullable', 'array', 'max:50'],
            'ext_stake_nom_complet.*' => ['nullable', 'string', 'max:200'],
            'ext_stake_email' => ['nullable', 'array'],
            'ext_stake_email.*' => ['nullable', 'email', 'max:255'],
            'ext_stake_telephone' => ['nullable', 'array'],
            'ext_stake_telephone.*' => ['nullable', 'string', 'max:30'],
            'ext_stake_role' => ['nullable', 'array'],
            'ext_stake_role.*' => ['nullable', 'string', 'max:255'],
            'ext_stake_attentes' => ['nullable', 'array'],
            'ext_stake_attentes.*' => ['nullable', 'string', 'max:1000'],

            // Dynamic arrays - General Documents
            'doc_name' => ['nullable', 'array', 'max:20'],
            'doc_name.*' => ['nullable', 'string', 'max:255'],
            'doc_file' => ['nullable', 'array', 'max:20'],
            'doc_file.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip', 'max:51200'],

            // Dynamic arrays - Issues
            'issue_cat' => ['nullable', 'array', 'max:50'],
            'issue_cat.*' => ['nullable', 'string', 'in:Enjeux,Contraintes,Risques,REX'],
            'issue_detail' => ['nullable', 'array'],
            'issue_detail.*' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            // Champs principaux
            'nom_projet.required' => 'Le nom du projet est obligatoire.',
            'nom_projet.max' => 'Le nom du projet ne peut pas dépasser 255 caractères.',
            'type_projet.required' => 'Le type de projet est obligatoire.',
            'type_projet.in' => 'Le type de projet doit être "Interne" ou "Externe".',
            'nature_projet.in' => 'La nature du projet doit être B2B, B2C, GOV ou Autres.',
            'statut_initial.required' => 'Le statut initial est obligatoire.',
            'statut_initial.in' => 'Le statut initial sélectionné est invalide.',
            'statut_financier.required' => 'Le statut financier est obligatoire.',
            'statut_financier.in' => 'Le statut financier doit être Non démarré, En cours ou Terminé.',
            'date_demarrage.date' => 'La date de démarrage doit être une date valide.',
            'date_fin.date' => 'La date de fin doit être une date valide.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de démarrage.',
            'notes.max' => 'Les notes ne peuvent pas dépasser 5000 caractères.',

            // Filiales et responsables
            'filiale_executant.required' => 'La filiale exécutant est obligatoire.',
            'owner_executant.required' => 'Le Chef de Projet est obligatoire.',
            'account_manager.required' => 'L\'Account Manager est obligatoire.',

            // Ressources
            'ressource_a_mobiliser.required' => 'Veuillez indiquer si des ressources sont à mobiliser.',
            'resource_type.required_if' => 'Le type de ressource est obligatoire si des ressources sont à mobiliser.',
            'resource_bank.required_if' => 'La banque est obligatoire si le type de ressource est "Banque".',

            // Contractualisation
            'contractualisation.required' => 'Veuillez indiquer si une contractualisation est requise.',
            'contractualisation_type.in' => 'Le type de contractualisation doit être "Bon de commande" ou "Annexes".',
            'contractualisation_document.file' => 'Le document contractuel doit être un fichier valide.',
            'contractualisation_document.mimes' => 'Le document contractuel doit être au format PDF, DOC, DOCX ou ZIP.',
            'contractualisation_document.max' => 'Le document contractuel ne peut pas dépasser 50 Mo.',

            // Actions
            'actions.max' => 'Le nombre maximum d\'actions est de 100.',
            'actions.*.max' => 'Une action ne peut pas dépasser 500 caractères.',

            // Livrables
            'livrable_nom.max' => 'Le nombre maximum de livrables est de 50.',
            'livrable_nom.*.max' => 'Le nom d\'un livrable ne peut pas dépasser 255 caractères.',
            'livrable_desc.*.max' => 'La description d\'un livrable ne peut pas dépasser 1000 caractères.',
            'livrable_date.*.date' => 'La date prévue du livrable doit être une date valide.',
            'livrable_document.*.file' => 'Le document du livrable doit être un fichier valide.',
            'livrable_document.*.mimes' => 'Le document du livrable doit être au format PDF, DOC, DOCX ou ZIP.',
            'livrable_document.*.max' => 'Le document du livrable ne peut pas dépasser 50 Mo.',

            // Parties prenantes internes
            'stake_emp_id.max' => 'Le nombre maximum de parties prenantes internes est de 50.',
            'stake_emp_id.*.integer' => 'L\'identifiant de l\'employé doit être un nombre.',
            'stake_emp_id.*.exists' => 'L\'employé sélectionné n\'existe pas.',
            'stake_role.*.max' => 'Le rôle RACI ne peut pas dépasser 255 caractères.',
            'stake_attentes.*.max' => 'Les attentes ne peuvent pas dépasser 1000 caractères.',

            // Parties prenantes externes
            'ext_stake_organisation.max' => 'Le nombre maximum de parties prenantes externes est de 50.',
            'ext_stake_organisation.*.max' => 'Le nom de l\'organisation ne peut pas dépasser 100 caractères.',
            'ext_stake_nom_complet.*.max' => 'Le nom complet ne peut pas dépasser 200 caractères.',
            'ext_stake_email.*.email' => 'L\'adresse email de la partie prenante externe doit être valide.',
            'ext_stake_email.*.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'ext_stake_telephone.*.max' => 'Le numéro de téléphone ne peut pas dépasser 30 caractères.',
            'ext_stake_role.*.max' => 'Le rôle de la partie prenante externe ne peut pas dépasser 255 caractères.',
            'ext_stake_attentes.*.max' => 'Les attentes de la partie prenante externe ne peuvent pas dépasser 1000 caractères.',

            // Documents du projet
            'doc_name.max' => 'Le nombre maximum de documents est de 20.',
            'doc_name.*.required_with' => 'Le nom du document est obligatoire lorsqu\'un fichier est joint.',
            'doc_name.*.string' => 'Le nom du document doit être du texte.',
            'doc_name.*.max' => 'Le nom du document ne peut pas dépasser 255 caractères.',
            'doc_file.max' => 'Le nombre maximum de documents est de 20.',
            'doc_file.*.file' => 'Le fichier doit être un document valide.',
            'doc_file.*.mimes' => 'Le document doit être au format PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX ou ZIP.',
            'doc_file.*.max' => 'Le document ne peut pas dépasser 50 Mo.',

            // Enjeux, Contraintes, Risques
            'issue_cat.max' => 'Le nombre maximum d\'enjeux/contraintes/risques/REX est de 50.',
            'issue_cat.*.in' => 'La catégorie doit être Enjeux, Contraintes, Risques ou REX.',
            'issue_detail.*.max' => 'Le détail ne peut pas dépasser 1000 caractères.',
        ];
    }

    /**
     * Configure the validator instance.
     * Validates manager filiale constraint and document names.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $user = auth()->user();

            if (!$user) {
                return;
            }

            // Check manager filiale constraint via policy
            $policy = new \App\Policies\ProjectPolicy();

            if (!$policy->validateManagerFiliale($user, $this->all())) {
                $userFiliale = $user->getFiliale();

                if (!$userFiliale) {
                    $validator->errors()->add(
                        'filiale_executant',
                        'Votre compte n\'a pas de filiale associée. Vous ne pouvez pas modifier les filiales du projet. Contactez un administrateur.'
                    );
                } else {
                    $validator->errors()->add(
                        'filiale_executant',
                        'En tant que manager, au moins une des filiales (Exécutant ou Contractant) doit correspondre à votre filiale (' . $userFiliale . '). Sinon, vous ne pourrez plus voir ce projet après modification.'
                    );
                }
            }

            // Validate document names when files are present
            $docFiles = $this->file('doc_file', []);
            $docNames = $this->input('doc_name', []);

            foreach ($docFiles as $index => $file) {
                if ($file && (empty($docNames[$index]) || trim($docNames[$index]) === '')) {
                    $validator->errors()->add(
                        "doc_name.{$index}",
                        'Le nom du document #' . ($index + 1) . ' est obligatoire lorsqu\'un fichier est joint.'
                    );
                }
            }
        });
    }
}
