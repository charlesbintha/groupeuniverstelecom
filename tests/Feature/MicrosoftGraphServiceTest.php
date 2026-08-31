<?php

namespace Tests\Feature;

use App\Exceptions\MicrosoftGraphException;
use App\Models\Project;
use App\Services\External\MicrosoftGraphService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MicrosoftGraphServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        config(['filesystems.default' => 'local']);

        Storage::put('msgraph_token.json', json_encode([
            'access_token' => 'test-token',
            'expires_at' => time() + 3600,
        ]));

        Http::preventStrayRequests();
    }

    public function test_transient_graph_server_errors_are_retried(): void
    {
        Http::fakeSequence()
            ->push(['error' => ['code' => 'UnknownError', 'message' => 'Temporary failure']], 503)
            ->push(['id' => 'plan-1', 'title' => 'Plan'], 200);

        $result = $this->service()->request('GET', '/planner/plans/plan-1');

        $this->assertSame('plan-1', $result['id']);
        Http::assertSentCount(2);
    }

    public function test_client_errors_keep_the_complete_graph_message_without_retrying(): void
    {
        $message = str_repeat('Detailed Graph validation message. ', 10);

        Http::fake([
            '*' => Http::response([
                'error' => [
                    'code' => 'Request_BadRequest',
                    'message' => $message,
                ],
            ], 400),
        ]);

        try {
            $this->service()->request('POST', '/planner/tasks', ['title' => 'Task']);
            $this->fail('A MicrosoftGraphException was not thrown.');
        } catch (MicrosoftGraphException $e) {
            $this->assertSame(400, $e->status);
            $this->assertSame('Request_BadRequest', $e->graphCode);
            $this->assertSame($message, $e->graphMessage);
        }

        Http::assertSentCount(1);
    }

    public function test_existing_group_owner_is_not_added_again(): void
    {
        Http::fake([
            '*' => Http::response([
                'value' => [
                    ['id' => 'user-1'],
                ],
            ]),
        ]);

        $added = $this->service()->ensureGroupOwner('group-1', 'user-1');

        $this->assertFalse($added);
        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->method() === 'GET'
            && str_contains($request->url(), '/groups/group-1/owners'));
    }

    public function test_duplicate_owner_race_is_treated_as_idempotent(): void
    {
        Http::fakeSequence()
            ->push(['value' => []], 200)
            ->push([
                'error' => [
                    'code' => 'Request_BadRequest',
                    'message' => 'One or more added object references already exist.',
                ],
            ], 400);

        $added = $this->service()->ensureGroupOwner('group-1', 'user-1');

        $this->assertFalse($added);
        Http::assertSentCount(2);
    }

    public function test_missing_planner_plan_returns_null(): void
    {
        Http::fake([
            '*' => Http::response([
                'error' => [
                    'code' => 'UnknownError',
                    'message' => 'Referenced Plan is not found.',
                ],
            ], 404),
        ]);

        $this->assertNull($this->service()->getPlan('missing-plan'));
        Http::assertSentCount(1);
    }

    public function test_sync_recreates_a_missing_plan_and_its_tasks(): void
    {
        $this->createPlannerTestTables();

        $projectId = DB::table('projects')->insertGetId([
            'code_projet' => 'TEST-001',
            'nom_projet' => 'Test Planner Recovery',
            'type_projet' => 'Interne',
            'date_demarrage' => '2026-08-01',
            'date_fin' => '2026-08-31',
            'ms_group_id' => 'group-1',
            'ms_plan_id' => 'missing-plan',
            'ms_bucket_id' => 'stale-bucket',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $actionId = DB::table('project_actions')->insertGetId([
            'project_id' => $projectId,
            'libelle' => 'Recovered task',
            'ordre' => 1,
            'ms_task_id' => 'stale-task',
            'ms_task_etag' => 'stale-etag',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Http::fake(function (Request $request) {
            if ($request->method() === 'GET' && str_ends_with($request->url(), '/planner/plans/missing-plan')) {
                return Http::response([
                    'error' => [
                        'code' => 'UnknownError',
                        'message' => 'Referenced Plan is not found.',
                    ],
                ], 404);
            }

            if ($request->method() === 'POST' && str_ends_with($request->url(), '/planner/plans')) {
                return Http::response(['id' => 'new-plan', 'title' => 'Recovered Plan']);
            }

            if ($request->method() === 'POST' && str_ends_with($request->url(), '/planner/buckets')) {
                return Http::response(['id' => 'new-bucket', 'name' => 'Actions']);
            }

            if ($request->method() === 'POST' && str_ends_with($request->url(), '/planner/tasks')) {
                return Http::response([
                    'id' => 'new-task',
                    'title' => 'Recovered task',
                    '@odata.etag' => 'new-etag',
                ]);
            }

            return Http::response(['error' => ['message' => 'Unexpected request']], 500);
        });

        $result = $this->service()->syncProjectToPlanner(Project::findOrFail($projectId));

        $this->assertTrue($result['success']);
        $this->assertSame('new-plan', $result['ms_plan_id']);
        $this->assertSame('new-bucket', $result['ms_bucket_id']);
        $this->assertSame(1, $result['tasks_created']);
        $this->assertDatabaseHas('projects', [
            'id' => $projectId,
            'ms_plan_id' => 'new-plan',
            'ms_bucket_id' => 'new-bucket',
        ]);
        $this->assertDatabaseHas('project_actions', [
            'id' => $actionId,
            'ms_task_id' => 'new-task',
            'ms_task_etag' => 'new-etag',
        ]);
        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && str_ends_with($request->url(), '/planner/tasks')
            && $request['startDateTime'] === '2026-08-01T00:00:00Z'
            && $request['dueDateTime'] === '2026-08-31T00:00:00Z');
    }

    private function createPlannerTestTables(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('code_projet')->nullable();
            $table->string('nom_projet');
            $table->string('type_projet');
            $table->text('objectif_projet')->nullable();
            $table->date('date_demarrage')->nullable();
            $table->date('date_fin')->nullable();
            $table->string('owner_executant')->nullable();
            $table->string('ms_group_id')->nullable();
            $table->string('ms_plan_id')->nullable();
            $table->string('ms_bucket_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_actions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('libelle');
            $table->unsignedInteger('ordre')->default(0);
            $table->string('ms_task_id')->nullable();
            $table->string('ms_task_etag')->nullable();
            $table->timestamps();
        });

        Schema::create('employes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable();
            $table->string('aad_id')->nullable();
        });

        Schema::create('project_stakeholders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('employe_id')->nullable();
            $table->string('role')->nullable();
            $table->string('email')->nullable();
            $table->string('aad_id')->nullable();
        });
    }

    private function service(): MicrosoftGraphService
    {
        return new class extends MicrosoftGraphService
        {
            public function request(
                string $method,
                string $endpoint,
                ?array $body = null,
                array $headers = [],
                bool $returnHeaders = false,
            ): array {
                return $this->call($method, $endpoint, $body, $headers, $returnHeaders);
            }
        };
    }
}
