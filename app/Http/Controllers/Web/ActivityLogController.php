<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityReportExcel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        [$start, $end, $userId] = $this->filters($request);
        $statistics = $this->statistics($start, $end);

        $activities = $this->activityQuery($start, $end, $userId)
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity.index', [
            ...$statistics,
            'activities' => $activities,
            'filterUsers' => User::orderBy('name')->orderBy('email')->get(['id', 'name', 'email']),
            'dateStart' => $start->toDateString(),
            'dateEnd' => $end->toDateString(),
            'selectedUserId' => $userId,
        ]);
    }

    public function export(Request $request, ActivityReportExcel $excel): BinaryFileResponse
    {
        [$start, $end, $userId] = $this->filters($request);
        $statistics = $this->statistics($start, $end);
        $activities = $this->activityQuery($start, $end, $userId)->get();

        $summaryRows = [[
            'Utilisateur', 'Email', 'Rôle', 'Statut', 'Actions', 'Part des actions (%)', 'Jours actifs', 'Dernière activité',
        ]];

        foreach ($statistics['userStatistics'] as $user) {
            $summaryRows[] = [
                $user->name ?: '—',
                $user->email,
                $user->getRoleNames()->first() ?: $user->role,
                $user->is_active ? 'Actif' : 'Inactif',
                (int) $user->activity_count,
                (float) $user->usage_rate,
                (int) $user->active_days,
                $user->last_activity_at ? Carbon::parse($user->last_activity_at)->format('d/m/Y H:i:s') : 'Jamais',
            ];
        }

        $activityRows = [[
            'Date et heure', 'Utilisateur', 'Email', 'Action', 'Méthode', 'Route', 'Adresse IP', 'Statut HTTP',
        ]];

        foreach ($activities as $activity) {
            $activityRows[] = [
                $activity->created_at->format('d/m/Y H:i:s'),
                $activity->user_name ?: '—',
                $activity->user_email,
                $activity->action,
                $activity->method,
                $activity->route_name ?: '—',
                $activity->ip_address ?: '—',
                (int) $activity->status_code,
            ];
        }

        $path = $excel->create([
            ['name' => 'Taux utilisation', 'rows' => $summaryRows],
            ['name' => 'Journal activité', 'rows' => $activityRows],
        ]);

        $filename = 'journal-activite-'.$start->format('Ymd').'-'.$end->format('Ymd').'.xlsx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /** @return array{Carbon, Carbon, int|null} */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'date_debut' => ['nullable', 'date'],
            'date_fin' => ['nullable', 'date', 'after_or_equal:date_debut'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        $start = Carbon::parse($validated['date_debut'] ?? now()->startOfMonth()->toDateString())->startOfDay();
        $end = Carbon::parse($validated['date_fin'] ?? now()->toDateString())->endOfDay();

        return [$start, $end, isset($validated['user_id']) ? (int) $validated['user_id'] : null];
    }

    /** @return array<string, mixed> */
    private function statistics(Carbon $start, Carbon $end): array
    {
        $periodQuery = ActivityLog::query()->whereBetween('created_at', [$start, $end]);
        $totalActivities = (clone $periodQuery)->count();
        $activeUserCount = (clone $periodQuery)->whereNotNull('user_id')->distinct()->count('user_id');
        $enabledUserCount = User::active()->count();

        $activeDays = (clone $periodQuery)
            ->whereNotNull('user_id')
            ->selectRaw('user_id, COUNT(DISTINCT DATE(created_at)) as active_days')
            ->groupBy('user_id')
            ->pluck('active_days', 'user_id');

        $users = User::with('roles')
            ->withCount(['activityLogs as activity_count' => fn (Builder $query) => $query->whereBetween('created_at', [$start, $end])])
            ->withMax(['activityLogs as last_activity_at' => fn (Builder $query) => $query->whereBetween('created_at', [$start, $end])], 'created_at')
            ->orderBy('name')
            ->orderBy('email')
            ->get();

        $users->each(function (User $user) use ($activeDays, $totalActivities): void {
            $user->setAttribute('active_days', (int) ($activeDays[$user->id] ?? 0));
            $user->setAttribute('usage_rate', $totalActivities > 0
                ? round(((int) $user->activity_count / $totalActivities) * 100, 2)
                : 0.0);
        });

        return [
            'totalActivities' => $totalActivities,
            'activeUserCount' => $activeUserCount,
            'enabledUserCount' => $enabledUserCount,
            'globalUsageRate' => $enabledUserCount > 0 ? round(($activeUserCount / $enabledUserCount) * 100, 1) : 0,
            'userStatistics' => $users,
        ];
    }

    private function activityQuery(Carbon $start, Carbon $end, ?int $userId): Builder
    {
        return ActivityLog::query()
            ->whereBetween('created_at', [$start, $end])
            ->when($userId, fn (Builder $query) => $query->where('user_id', $userId))
            ->latest('created_at')
            ->latest('id');
    }
}
