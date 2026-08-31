<?php

namespace Tests\Unit;

use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use PHPUnit\Framework\TestCase;

class ProjectDocumentValidationTest extends TestCase
{
    public function test_store_request_allows_excel_for_contractualisation_and_deliverables(): void
    {
        $rules = (new StoreProjectRequest())->rules();

        $this->assertContains('mimes:pdf,doc,docx,zip,xls,xlsx', $rules['contractualisation_document']);
        $this->assertContains('mimes:pdf,doc,docx,zip,xls,xlsx', $rules['livrable_document.*']);
    }

    public function test_update_request_allows_excel_for_contractualisation_and_deliverables(): void
    {
        $rules = (new UpdateProjectRequest())->rules();

        $this->assertContains('mimes:pdf,doc,docx,zip,xls,xlsx', $rules['contractualisation_document']);
        $this->assertContains('mimes:pdf,doc,docx,zip,xls,xlsx', $rules['livrable_document.*']);
    }

    public function test_project_requests_accept_external_stakeholder_telephone_numbers(): void
    {
        $storeRules = (new StoreProjectRequest())->rules();
        $updateRules = (new UpdateProjectRequest())->rules();

        $this->assertSame(['nullable', 'array'], $storeRules['ext_stake_telephone']);
        $this->assertSame(['nullable', 'string', 'max:30'], $storeRules['ext_stake_telephone.*']);
        $this->assertSame(['nullable', 'array'], $updateRules['ext_stake_telephone']);
        $this->assertSame(['nullable', 'string', 'max:30'], $updateRules['ext_stake_telephone.*']);
    }
}
