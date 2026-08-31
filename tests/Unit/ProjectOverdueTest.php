<?php

namespace Tests\Unit;

use App\Models\Project;
use Carbon\Carbon;
use Tests\TestCase;

class ProjectOverdueTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-08-31 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_an_unfinished_project_past_its_end_date_is_overdue(): void
    {
        $project = new Project([
            'date_fin' => '2026-08-25',
            'statut_initial' => 'En cours',
        ]);

        $this->assertTrue($project->isOverdue());
        $this->assertSame(6, $project->overdueDays());
    }

    public function test_a_completed_project_is_not_overdue(): void
    {
        $project = new Project([
            'date_fin' => '2026-08-25',
            'statut_initial' => 'Terminé',
        ]);

        $this->assertFalse($project->isOverdue());
    }

    public function test_a_project_due_today_is_not_yet_overdue(): void
    {
        $project = new Project([
            'date_fin' => '2026-08-31',
            'statut_initial' => 'En cours',
        ]);

        $this->assertFalse($project->isOverdue());
    }
}
