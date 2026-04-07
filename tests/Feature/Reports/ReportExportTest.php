<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;
    use BuildsIncidentContext;

    public function test_operator_can_download_daily_report_as_pdf(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');

        $this->makeIncident($context, [
            'code_incident' => 'INC-DAILY-PDF',
            'date_debut' => Carbon::parse('2026-04-07 09:15:00'),
            'duree_minutes' => 75,
            'operateur_id' => $operator->id,
        ]);

        $response = $this->actingAs($operator)->get(route('reports.daily', [
            'date' => '2026-04-07',
        ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'application/pdf',
            (string) $response->headers->get('content-type')
        );
        $this->assertStringContainsString(
            'rapport-journalier-2026-04-07.pdf',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_operator_can_download_monthly_report_as_excel(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');

        $this->makeIncident($context, [
            'code_incident' => 'INC-MONTH-EXCEL',
            'date_debut' => Carbon::parse('2026-04-03 08:00:00'),
            'duree_minutes' => 30,
            'operateur_id' => $operator->id,
        ]);

        $response = $this->actingAs($operator)->get(route('reports.monthly', [
            'month' => '2026-04',
            'format' => 'excel',
        ]));

        $response->assertOk();
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            (string) $response->headers->get('content-type')
        );
        $this->assertStringContainsString(
            'rapport-mensuel-2026-04.xlsx',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_report_routes_require_authentication(): void
    {
        $response = $this->get(route('reports.daily'));

        $response->assertRedirect(route('login'));
    }

    public function test_user_without_allowed_role_cannot_export_reports(): void
    {
        $this->seedRolesAndPermissions();
        $plainUser = User::factory()->create();

        $response = $this->actingAs($plainUser)->get(route('reports.daily'));

        $response->assertForbidden();
    }

    public function test_monthly_report_rejects_invalid_month_format(): void
    {
        $this->seedRolesAndPermissions();
        $operator = $this->makeUserWithRole('operator');

        $response = $this->actingAs($operator)
            ->from(route('dashboard'))
            ->get(route('reports.monthly', ['month' => '2026/04']));

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasErrors('month');
    }
}

