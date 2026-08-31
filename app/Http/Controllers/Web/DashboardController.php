<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Filiale;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $filialeFilter = $request->get('filiale');

        $query = Project::query();

        // Admin/Project Admin: see all projects (with optional filiale filter)
        if ($user->can('projects.view-any')) {
            if ($filialeFilter) {
                $query->where('filiale_contractant', $filialeFilter);
            }
        }
        // Manager: see projects from their filiale
        elseif ($user->can('projects.view-filiale')) {
            $userFiliale = $user->getFiliale();
            if ($userFiliale) {
                $query->where(function ($q) use ($userFiliale) {
                    $q->whereRaw('LOWER(filiale_executant) COLLATE utf8mb4_unicode_ci = LOWER(?)', [$userFiliale])
                        ->orWhereRaw('LOWER(filiale_contractant) COLLATE utf8mb4_unicode_ci = LOWER(?)', [$userFiliale]);
                });
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        // User: see only own projects
        elseif ($user->can('projects.view-own')) {
            $query->where('user_id', $user->id);
        }
        // Fallback: no access
        else {
            $query->whereRaw('1 = 0');
        }

        $stats = [
            'total' => (clone $query)->count(),
            'en_cours' => (clone $query)->whereIn('statut_initial', ['En cours'])->count(),
            'non_demarrer' => (clone $query)->whereIn('statut_initial', ['Planifié'])->count(),
            'suspendu' => (clone $query)->where('statut_initial', 'Suspendu')->count(),
            'mis_en_pause' => (clone $query)->where('statut_initial', 'Mis en pause')->count(),
            'retard' => (clone $query)->where('statut_initial', 'Retard')->count(),
            'completed' => (clone $query)->where('statut_initial', 'Terminé')->count(),
            'budget_total' => (clone $query)->sum('budget_initial'),
            'me_total' => (clone $query)->sum('montant_encaissement'),
            'md_total' => (clone $query)->sum('montant_decaissement'),
            'montant_recouvrement_total' => (clone $query)->sum('montant_recouvrement'),
            'montant_recouvre_total' => (clone $query)->sum('montant_recouvre'),
            'budget_active' => (clone $query)->whereIn('statut_initial', ['Terminé'])->sum('budget_initial'),
            'budget_en_cours' => (clone $query)->whereIn('statut_initial', ['En cours'])->sum('budget_initial'),
            'budget_non_demarrer' => (clone $query)->whereIn('statut_initial', ['Planifié'])->sum('budget_initial'),
            'this_month' => (clone $query)->whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count(),
        ];

        $stats['completion_rate'] = $stats['total'] > 0
            ? round(($stats['completed'] / $stats['total']) * 100, 1)
            : 0;

        $byStatus = (clone $query)->select('statut_initial', DB::raw('count(*) as count'))
            ->groupBy('statut_initial')
            ->pluck('count', 'statut_initial')
            ->toArray();

        $byType = (clone $query)->select('type_projet', DB::raw('count(*) as count'))
            ->groupBy('type_projet')
            ->pluck('count', 'type_projet')
            ->toArray();

        $byNature = (clone $query)->select('nature_projet', DB::raw('count(*) as count'))
            ->groupBy('nature_projet')
            ->pluck('count', 'nature_projet')
            ->toArray();

        $budgetByFiliale = null;
        $projectsByOwner = null;

        // Admin/Project Admin: show budget by filiale
        if ($user->can('projects.view-any')) {
            $budgetByFiliale = (clone $query)->select('filiale_executant', DB::raw('SUM(budget_initial) as budget'))
                ->whereNotNull('filiale_executant')
                ->groupBy('filiale_executant')
                ->orderByDesc('budget')
                ->limit(10)
                ->get();
        }
        // Manager: show projects by owner
        elseif ($user->can('projects.view-filiale')) {
            $projectsByOwner = (clone $query)->select('owner_executant', DB::raw('count(*) as count'))
                ->whereNotNull('owner_executant')
                ->groupBy('owner_executant')
                ->orderByDesc('count')
                ->limit(10)
                ->get();
        }

        $monthlyEvolution = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthlyEvolution[] = [
                'month' => $date->format('M Y'),
                'count' => (clone $query)->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        }

        $velocity = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $velocity[] = [
                'month' => $date->format('M Y'),
                'created' => (clone $query)->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
                'completed' => (clone $query)->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->where('statut_initial', 'Terminé')
                    ->count(),
            ];
        }

        $filiales = $user->hasRole('Admin')
            ? Filiale::orderBy('nom_filiale')->pluck('nom_filiale')->unique()
            : collect();

        return view('dashboard.index', compact(
            'stats',
            'byStatus',
            'byType',
            'byNature',
            'budgetByFiliale',
            'projectsByOwner',
            'monthlyEvolution',
            'velocity',
            'filiales',
            'filialeFilter'
        ));
    }
}
