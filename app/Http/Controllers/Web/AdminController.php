<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Project;
use App\Models\User;
use App\Services\External\EmployeeApiService;
use App\Notifications\PasswordResetRequested;
use App\Notifications\UserAccountCreated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    /**
     * Display listing of users.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = User::with('employee')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(EmployeeApiService $employeeApi)
    {
        $this->authorize('create', User::class);

        $employeesData = $this->loadEmployeesFromApi($employeeApi);
        if ($employeesData['error'] !== null) {
            session()->flash('error', $employeesData['error']);
        }
        $employees = $employeesData['employees'];

        $roles = Role::orderBy('name')->get();

        return view('admin.users.create', compact('employees', 'roles'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request, EmployeeApiService $employeeApi)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'employe_id' => 'required|integer',
            'email' => 'required|email|unique:users,email',
            'spatie_role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $employeeId = (int) $validated['employe_id'];
        $employeesResponse = $employeeApi->listEmployees();

        if (!$employeesResponse['ok']) {
            return back()
                ->withErrors(['employe_id' => $employeesResponse['error'] ?? 'Employee API request failed.'])
                ->withInput();
        }

        $employee = $this->findEmployeeFromList($employeesResponse['items'] ?? [], $employeeId);

        if (!$employee) {
            return back()
                ->withErrors(['employe_id' => 'Employe introuvable dans l API.'])
                ->withInput();
        }

        if (array_key_exists('actif', $employee) && $employee['actif'] === false) {
            return back()
                ->withErrors(['employe_id' => 'Employe inactif dans l API.'])
                ->withInput();
        }

        try {
            $employeeRecord = $this->syncEmployeeRecord($employeeId, $employee);
        } catch (\RuntimeException $e) {
            return back()
                ->withErrors(['employe_id' => $e->getMessage()])
                ->withInput();
        }

        if ($validated['spatie_role'] == 'Manager' && empty(trim($employeeRecord->filiale ?? ''))) {
            return back()
                ->withErrors(['employe_id' => 'Un Manager doit etre associe a un employe avec une filiale definie.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $employeeName = trim((string) ($employeeRecord->prenom_nom ?? ''));
            if ($employeeName === '') {
                $employeeName = trim((string) ($employee['prenom_nom'] ?? '')) ?: $validated['email'];
            }

            $user = User::create([
                'employe_id' => $employeeId,
                'name' => $employeeName,
                'email' => strtolower($validated['email']),
                'password' => Hash::make(Str::random(32)),
                'role' => $this->mapSpatieToEnum($validated['spatie_role']),
                'is_active' => $request->boolean('is_active', true),
            ]);

            $user->assignRole($validated['spatie_role']);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            $user->notify(new UserAccountCreated());

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Utilisateur créé avec succès. Un email a été envoyé avec les instructions de connexion.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Erreur lors de la création de l\'utilisateur: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user, EmployeeApiService $employeeApi)
    {
        $this->authorize('update', $user);

        $employeesData = $this->loadEmployeesFromApi($employeeApi, $user->employe_id);
        if ($employeesData['error'] !== null) {
            session()->flash('error', $employeesData['error']);
        }
        $employees = $employeesData['employees'];

        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'employees', 'roles'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user, EmployeeApiService $employeeApi)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'employe_id' => 'required|integer',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'spatie_role' => 'required|exists:roles,name',
            'is_active' => 'boolean',
        ]);

        $this->authorize('changeRole', [User::class, $user, $validated['spatie_role']]);

        $employeeId = (int) $validated['employe_id'];
        $employeesResponse = $employeeApi->listEmployees();

        if (!$employeesResponse['ok']) {
            return back()
                ->withErrors(['employe_id' => $employeesResponse['error'] ?? 'Employee API request failed.'])
                ->withInput();
        }

        $employee = $this->findEmployeeFromList($employeesResponse['items'] ?? [], $employeeId);

        if (!$employee) {
            return back()
                ->withErrors(['employe_id' => 'Employe introuvable dans l API.'])
                ->withInput();
        }

        if (array_key_exists('actif', $employee) && $employee['actif'] === false) {
            return back()
                ->withErrors(['employe_id' => 'Employe inactif dans l API.'])
                ->withInput();
        }

        try {
            $employeeRecord = $this->syncEmployeeRecord($employeeId, $employee);
        } catch (\RuntimeException $e) {
            return back()
                ->withErrors(['employe_id' => $e->getMessage()])
                ->withInput();
        }

        if ($validated['spatie_role'] == 'Manager' && empty(trim($employeeRecord->filiale ?? ''))) {
            return back()
                ->withErrors(['employe_id' => 'Un Manager doit etre associe a un employe avec une filiale definie.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            $employeeName = trim((string) ($employeeRecord->prenom_nom ?? ''));
            if ($employeeName === '') {
                $employeeName = trim((string) ($employee['prenom_nom'] ?? '')) ?: $validated['email'];
            }

            $user->update([
                'employe_id' => $employeeId,
                'name' => $employeeName,
                'email' => strtolower($validated['email']),
                'role' => $this->mapSpatieToEnum($validated['spatie_role']),
                'is_active' => $request->boolean('is_active'),
            ]);

            $user->syncRoles([$validated['spatie_role']]);

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

            DB::commit();

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'Utilisateur modifié avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withErrors(['error' => 'Erreur lors de la modification: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Load employees from the API for selection lists.
     *
     * @return array{employees: \Illuminate\Support\Collection, error: string|null}
     */
    protected function loadEmployeesFromApi(EmployeeApiService $employeeApi, ?int $includeEmployeeId = null): array
    {
        $response = $employeeApi->listEmployees();

        if (!$response['ok']) {
            return [
                'employees' => collect(),
                'error' => $response['error'] ?? 'Employee API request failed.',
            ];
        }

        $employees = collect($response['items'])
            ->filter(function (array $employee) use ($includeEmployeeId) {
                if ($includeEmployeeId !== null && isset($employee['id']) && (int) $employee['id'] === $includeEmployeeId) {
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
     * Find an employee by ID from the API list.
     *
     * @param array<int, array<string, mixed>> $employees
     * @return array<string, mixed>|null
     */
    protected function findEmployeeFromList(array $employees, int $employeeId): ?array
    {
        foreach ($employees as $employee) {
            if (isset($employee['id']) && (int) $employee['id'] === $employeeId) {
                return $employee;
            }
        }

        return null;
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
            $employee = new Employee($payload);
            $employee->id = $employeeId;
        }

        $employee->save();

        return $employee;
    }

    /**
     * Toggle user active/inactive status.
     */
    public function toggle(User $user)
    {
        $this->authorize('update', $user);

        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas désactiver votre propre compte.']);
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'activé' : 'désactivé';

        return back()->with('success', "Compte « {$user->email} » {$status}.");
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user)
    {
        $this->authorize('update', $user);

        $user->notify(new PasswordResetRequested());

        return back()->with('success', "Un email de réinitialisation a été envoyé à « {$user->email} ».");
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        $email = $user->email;
        $user->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()
            ->route('admin.users.index')
            ->with('success', "Compte « {$email} » supprimé.");
    }

    /**
     * Map Spatie role to enum role for backward compatibility.
     */
    private function mapSpatieToEnum(string $spatieRole): string
    {
        return match($spatieRole) {
            'Admin', 'Project Admin' => 'admin',
            'Manager' => 'manager',
            'User' => 'user',
            default => 'user'
        };
    }

    /**
     * Generate random password.
     */
    protected function generateRandomPassword(int $length = 12): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789@#%!';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $alphabet[random_int(0, strlen($alphabet) - 1)];
        }

        return $password;
    }
}
