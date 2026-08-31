<?php

namespace Tests\Feature;

use App\Enums\DocumentType;
use App\Http\Controllers\Web\ProjectController;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProjectDeliverableDocumentDeletionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
        ]);
        DB::purge('sqlite');
        DB::setDefaultConnection('sqlite');
        DB::reconnect('sqlite');
    }

    public function test_deleting_a_deliverable_document_keeps_the_deliverable(): void
    {
        $this->createTestTables();
        Storage::fake('private');

        $projectId = DB::table('projects')->insertGetId([
            'nom_projet' => 'Projet de test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $deliverableId = DB::table('project_deliverables')->insertGetId([
            'project_id' => $projectId,
            'livrable' => 'Rapport final',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $path = 'projects/'.$projectId.'/deliverables/rapport-final.pdf';
        Storage::disk('private')->put($path, 'document content');

        $documentId = DB::table('project_documents')->insertGetId([
            'project_id' => $projectId,
            'document_type' => DocumentType::LIVRABLE->value,
            'deliverable_id' => $deliverableId,
            'name' => 'Rapport final',
            'original_filename' => 'rapport-final.pdf',
            'stored_filename' => 'rapport-final.pdf',
            'path' => $path,
            'mime_type' => 'application/pdf',
            'size' => 16,
            'uploaded_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new class extends ProjectController
        {
            public function authorize($ability, $arguments = []): Response
            {
                return Response::allow();
            }
        };

        $response = $controller->deleteDocument(
            Project::findOrFail($projectId),
            ProjectDocument::findOrFail($documentId),
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->getData(true)['success']);
        Storage::disk('private')->assertMissing($path);
        $this->assertSoftDeleted('project_documents', ['id' => $documentId]);
        $this->assertDatabaseHas('project_deliverables', ['id' => $deliverableId]);
    }

    private function createTestTables(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('nom_projet');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_deliverables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('livrable');
            $table->timestamps();
        });

        Schema::create('project_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id');
            $table->string('document_type');
            $table->unsignedBigInteger('deliverable_id')->nullable();
            $table->string('name');
            $table->string('original_filename');
            $table->string('stored_filename');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->string('contract_type')->nullable();
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
            $table->softDeletes();
        });
    }
}
