<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\External\EmployeeApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncEmployees extends Command
{
    protected $signature = 'employees:sync {--dry-run : Afficher les changements sans les appliquer}';
    protected $description = 'Synchronise les employés locaux avec l\'API (corrige les IDs décalés via CASCADE)';

    public function handle(EmployeeApiService $api): int
    {
        $response = $api->listEmployees();

        if (!$response['ok']) {
            $this->error('Impossible de contacter l\'API : ' . ($response['error'] ?? 'erreur inconnue'));
            return self::FAILURE;
        }

        $items = $response['items'] ?? [];
        $dryRun = $this->option('dry-run');

        $created = 0;
        $updated = 0;
        $idFixed = 0;
        $merged  = 0;

        DB::beginTransaction();

        try {
            foreach ($items as $item) {
                $apiId = (int) $item['id'];
                $email = $item['email'] ?? null;

                if (!$apiId || !$email) {
                    continue;
                }

                $payload = array_filter([
                    'prenom_nom' => $item['prenom_nom'] ?? null,
                    'email'      => $email,
                    'filiale'    => $item['filiale'] ?? null,
                    'direction'  => $item['direction'] ?? null,
                    'aad_id'     => $item['aad_id'] ?? null,
                    'actif'      => isset($item['actif']) ? (bool) $item['actif'] : true,
                ], fn($v) => $v !== null);

                $byId    = Employee::find($apiId);
                $byEmail = Employee::where('email', $email)->first();

                if ($byId) {
                    // Vérifier si une autre entrée a déjà cet email (doublon)
                    if ($byEmail && $byEmail->id !== $byId->id) {
                        $this->line("  FUSION doublon email {$email} : id={$byEmail->id} → id={$apiId}");
                        if (!$dryRun) {
                            $this->mergeInto($byEmail->id, $apiId);
                        }
                        $merged++;
                    }

                    if (!$dryRun) {
                        $byId->refresh()->fill($payload)->save();
                    }
                    $updated++;
                } elseif ($byEmail && $byEmail->id !== $apiId) {
                    // Employé trouvé par email avec un ID différent → corriger via CASCADE
                    $this->line("  ID {$byEmail->id} → {$apiId}  ({$email})");
                    if (!$dryRun) {
                        DB::table('employes')->where('id', $byEmail->id)->update(['id' => $apiId]);
                        Employee::find($apiId)?->fill($payload)->save();
                    }
                    $idFixed++;
                } else {
                    // Nouvel employé
                    if (!$dryRun) {
                        $emp = new Employee($payload);
                        $emp->id = $apiId;
                        $emp->save();
                    }
                    $created++;
                }
            }

            if ($dryRun) {
                DB::rollBack();
                $this->info("[dry-run] Aucune modification enregistrée.");
            } else {
                DB::commit();
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Erreur : ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info("Terminé : {$created} créés, {$updated} mis à jour, {$idFixed} IDs corrigés, {$merged} doublons fusionnés.");
        return self::SUCCESS;
    }

    /**
     * Fusionne le doublon (fromId) dans l'enregistrement cible (toId).
     * Réattribue les stakeholders du doublon vers la cible, puis supprime le doublon.
     */
    private function mergeInto(int $fromId, int $toId): void
    {
        // Réattribuer les références project_stakeholders du doublon vers la cible
        // (uniquement celles qui ne créent pas de conflit de doublon sur le même projet)
        DB::table('project_stakeholders')
            ->where('employe_id', $fromId)
            ->whereNotExists(function ($q) use ($toId) {
                $q->from('project_stakeholders as ps2')
                    ->whereColumn('ps2.project_id', 'project_stakeholders.project_id')
                    ->where('ps2.employe_id', $toId);
            })
            ->update(['employe_id' => $toId]);

        // Supprimer les références restantes du doublon (même projet, déjà couvert par toId)
        DB::table('project_stakeholders')->where('employe_id', $fromId)->delete();

        // Supprimer le doublon
        DB::table('employes')->where('id', $fromId)->delete();
    }
}
