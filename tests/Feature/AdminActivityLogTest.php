<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityReportExcel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use ZipArchive;

class AdminActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_an_admin_can_open_the_activity_page(): void
    {
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        Role::create(['name' => 'User', 'guard_name' => 'web']);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('Admin');

        $regularUser = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $regularUser->assignRole('User');

        $this->actingAs($regularUser)
            ->get(route('admin.activity.index'))
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.activity.index'))
            ->assertOk()
            ->assertSee('Journal d’activité des utilisateurs');
    }

    public function test_activity_page_filters_logs_and_calculates_usage_statistics(): void
    {
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        Role::create(['name' => 'User', 'guard_name' => 'web']);

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('Admin');
        $user = User::factory()->create(['name' => 'Utilisateur Test', 'role' => 'user', 'is_active' => true]);
        $user->assignRole('User');

        ActivityLog::create($this->activityData($user, 'Consultation des projets', '2026-08-10 09:00:00'));
        ActivityLog::create($this->activityData($user, 'Création d’un projet', '2026-08-11 10:00:00'));
        ActivityLog::create($this->activityData($admin, 'Ancienne activité', '2026-07-01 10:00:00'));

        $response = $this->actingAs($admin)->get(route('admin.activity.index', [
            'date_debut' => '2026-08-01',
            'date_fin' => '2026-08-31',
            'user_id' => $user->id,
        ]));

        $response->assertOk()
            ->assertViewHas('totalActivities', 2)
            ->assertViewHas('activeUserCount', 1)
            ->assertSee('Consultation des projets')
            ->assertDontSee('Ancienne activité');
    }

    public function test_excel_service_creates_a_real_xlsx_workbook(): void
    {
        $path = app(ActivityReportExcel::class)->create([
            ['name' => 'Taux utilisation', 'rows' => [['Utilisateur', 'Taux'], ['Awa', 75.5]]],
            ['name' => 'Journal activité', 'rows' => [['Action'], ['Connexion']]],
        ]);

        $zip = new ZipArchive;

        try {
            $this->assertSame(true, $zip->open($path));
            $this->assertNotFalse($zip->locateName('xl/workbook.xml'));
            $this->assertNotFalse($zip->locateName('xl/worksheets/sheet1.xml'));
            $this->assertNotFalse($zip->locateName('xl/worksheets/sheet2.xml'));
            $this->assertStringContainsString('Taux utilisation', $zip->getFromName('xl/workbook.xml'));
        } finally {
            $zip->close();
            @unlink($path);
        }
    }

    public function test_export_route_downloads_an_xlsx_file(): void
    {
        Role::create(['name' => 'Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $admin->assignRole('Admin');

        $this->actingAs($admin)
            ->get(route('admin.activity.export', [
                'date_debut' => '2026-08-01',
                'date_fin' => '2026-08-31',
            ]))
            ->assertOk()
            ->assertDownload('journal-activite-20260801-20260831.xlsx');
    }

    public function test_successful_login_is_recorded(): void
    {
        $user = User::factory()->create([
            'email' => 'journal@example.com',
            'password' => 'secret-password',
            'is_active' => true,
        ]);

        $this->post('/login', [
            'email' => 'journal@example.com',
            'password' => 'secret-password',
        ])->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'action' => 'Connexion',
            'route_name' => 'login.attempt',
        ]);
    }

    /** @return array<string, mixed> */
    private function activityData(User $user, string $action, string $createdAt): array
    {
        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'action' => $action,
            'route_name' => 'projects.index',
            'method' => 'GET',
            'url' => 'http://localhost/projects',
            'ip_address' => '127.0.0.1',
            'status_code' => 200,
            'created_at' => $createdAt,
        ];
    }
}
