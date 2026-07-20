<?php

namespace Tests\Feature;

use App\Models\Incident;
use App\Models\IncidentReport;
use App\Notifications\IncidentEventNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class IncidentReportWorkflowTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_operator_submits_report_for_assigned_resolved_incident(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');

        $incident = $this->resolvedIncident($context, $operator->id);

        $response = $this->actingAs($operator)->post(route('incidents.report', $incident), [
            'actions_realisees' => 'Remplacement du fusible et contrôle tension.',
            'resultat' => 'Alimentation rétablie.',
            'observations' => 'RAS.',
            'submitted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('incidents.show', $incident));

        $this->assertDatabaseHas('incident_reports', [
            'incident_id' => $incident->id,
            'operateur_id' => $operator->id,
            'statut_rapport' => IncidentReport::STATUS_SUBMITTED,
        ]);

        $this->assertSame('RAPPORTE', $incident->refresh()->status->code);
        $this->assertDatabaseHas('incident_actions', [
            'incident_id' => $incident->id,
            'action_type' => 'rapport_soumission',
        ]);
    }

    public function test_supervisor_rejects_report_with_required_reason_and_operator_is_notified(): void
    {
        Notification::fake();

        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $supervisor = $this->makeUserWithRole('supervisor');
        $incident = $this->submittedIncidentReport($context, $operator->id);

        $response = $this->actingAs($supervisor)->post(route('incidents.report.reject', $incident), [
            'motif_refus' => 'Les actions réalisées sont trop imprécises.',
        ]);

        $response->assertRedirect(route('incidents.show', $incident));

        $report = $incident->refresh()->report;
        $this->assertSame(IncidentReport::STATUS_REJECTED, $report->statut_rapport);
        $this->assertSame('Les actions réalisées sont trop imprécises.', $report->motif_refus);
        $this->assertSame('RESOLU', $incident->status->code);

        Notification::assertSentTo($operator, IncidentEventNotification::class);
        $this->assertDatabaseHas('logs', [
            'incident_id' => $incident->id,
            'action' => 'report_rejected',
        ]);
    }

    public function test_rejecting_report_without_reason_is_impossible(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $supervisor = $this->makeUserWithRole('supervisor');
        $incident = $this->submittedIncidentReport($context, $operator->id);

        $response = $this->actingAs($supervisor)->from(route('incidents.show', $incident))->post(route('incidents.report.reject', $incident), [
            'motif_refus' => '',
        ]);

        $response->assertRedirect(route('incidents.show', $incident));
        $response->assertSessionHasErrors('motif_refus');
        $this->assertSame(IncidentReport::STATUS_SUBMITTED, $incident->refresh()->report->statut_rapport);
    }

    public function test_operator_sees_report_rejection_reason(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $incident = $this->rejectedIncidentReport($context, $operator->id, 'Compléter les observations terrain.');

        $response = $this->actingAs($operator)->get(route('incidents.show', $incident));

        $response->assertOk();
        $response->assertSee('Rapport refusé');
        $response->assertSee('Compléter les observations terrain.');
    }

    public function test_operator_corrects_rejected_report(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $incident = $this->rejectedIncidentReport($context, $operator->id, 'Actions incomplètes.');

        $response = $this->actingAs($operator)->patch(route('incidents.report.update', $incident), [
            'actions_realisees' => 'Remplacement fusible, contrôle tension, réalimentation progressive.',
            'resultat' => 'Service rétabli avec tension stable.',
            'observations' => 'Correction effectuée.',
            'submitted_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('incidents.show', $incident));

        $incident->refresh();
        $this->assertSame('RAPPORTE', $incident->status->code);
        $this->assertSame(IncidentReport::STATUS_SUBMITTED, $incident->report->statut_rapport);
        $this->assertSame('Remplacement fusible, contrôle tension, réalimentation progressive.', $incident->report->actions_realisees);
        $this->assertDatabaseHas('incident_actions', [
            'incident_id' => $incident->id,
            'action_type' => 'rapport_correction',
        ]);
    }

    public function test_supervisor_validates_report(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $supervisor = $this->makeUserWithRole('supervisor');
        $incident = $this->submittedIncidentReport($context, $operator->id);

        $response = $this->actingAs($supervisor)->post(route('incidents.report.validate', $incident));

        $response->assertRedirect(route('incidents.show', $incident));

        $incident->refresh();
        $this->assertSame('VALIDE', $incident->status->code);
        $this->assertSame(IncidentReport::STATUS_VALIDATED, $incident->report->statut_rapport);
        $this->assertSame($supervisor->id, $incident->report->valide_par);
    }

    public function test_supervisor_closes_incident_after_validated_report(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $supervisor = $this->makeUserWithRole('supervisor');
        $incident = $this->validatedIncidentReport($context, $operator->id, $supervisor->id);

        $response = $this->actingAs($supervisor)->post(route('incidents.close', $incident), [
            'date_fin' => now()->format('Y-m-d H:i:s'),
            'resolution_summary' => 'Clôture après contrôle conforme du rapport.',
            'actions_menees' => 'Contrôle final.',
        ]);

        $response->assertRedirect(route('incidents.show', $incident));
        $this->assertSame('CLOTURE', $incident->refresh()->status->code);
    }

    public function test_closing_incident_without_validated_report_is_impossible(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $supervisor = $this->makeUserWithRole('supervisor');
        $incident = $this->submittedIncidentReport($context, $operator->id);
        $incident->forceFill(['status_id' => $context['statusValidated']->id])->save();

        $response = $this->actingAs($supervisor)->from(route('incidents.show', $incident))->post(route('incidents.close', $incident), [
            'date_fin' => now()->format('Y-m-d H:i:s'),
            'resolution_summary' => 'Tentative de clôture.',
        ]);

        $response->assertRedirect(route('incidents.show', $incident));
        $response->assertSessionHasErrors('rapport');
        $this->assertNotSame('CLOTURE', $incident->refresh()->status->code);
    }

    public function test_operator_cannot_modify_validated_report(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $supervisor = $this->makeUserWithRole('supervisor');
        $incident = $this->validatedIncidentReport($context, $operator->id, $supervisor->id);

        $response = $this->actingAs($operator)->patch(route('incidents.report.update', $incident), [
            'actions_realisees' => 'Modification non autorisée.',
            'resultat' => 'Non autorisé.',
            'observations' => 'Non autorisé.',
        ]);

        $response->assertForbidden();
        $this->assertNotSame('Modification non autorisée.', $incident->refresh()->report->actions_realisees);
    }

    public function test_operator_cannot_submit_report_for_unassigned_incident(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');
        $otherOperator = $this->makeUserWithRole('operator');
        $incident = $this->resolvedIncident($context, $otherOperator->id);

        $response = $this->actingAs($operator)->post(route('incidents.report', $incident), [
            'actions_realisees' => 'Action non autorisée.',
            'resultat' => 'Non autorisé.',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('incident_reports', [
            'incident_id' => $incident->id,
            'operateur_id' => $operator->id,
        ]);
    }

    private function resolvedIncident(array $context, int $operatorId): Incident
    {
        return $this->makeIncident($context, [
            'responsable_id' => $operatorId,
            'status_id' => $context['statusResolved']->id,
            'actions_menees' => 'Diagnostic et correction initiale.',
            'resolution_summary' => 'Incident résolu techniquement.',
        ]);
    }

    private function submittedIncidentReport(array $context, int $operatorId): Incident
    {
        $incident = $this->makeIncident($context, [
            'responsable_id' => $operatorId,
            'status_id' => $context['statusReported']->id,
            'actions_menees' => 'Actions terrain.',
            'resolution_summary' => 'Résultat terrain.',
        ]);

        IncidentReport::create([
            'incident_id' => $incident->id,
            'user_id' => $operatorId,
            'operateur_id' => $operatorId,
            'actions_realisees' => 'Actions terrain.',
            'resultat' => 'Résultat terrain.',
            'observations' => 'Observation initiale.',
            'submitted_at' => now(),
            'date_soumission' => now(),
            'statut_rapport' => IncidentReport::STATUS_SUBMITTED,
        ]);

        return $incident->refresh();
    }

    private function rejectedIncidentReport(array $context, int $operatorId, string $reason): Incident
    {
        $incident = $this->makeIncident($context, [
            'responsable_id' => $operatorId,
            'status_id' => $context['statusResolved']->id,
        ]);

        IncidentReport::create([
            'incident_id' => $incident->id,
            'user_id' => $operatorId,
            'operateur_id' => $operatorId,
            'actions_realisees' => 'Actions insuffisantes.',
            'resultat' => 'Résultat incomplet.',
            'observations' => 'Observation à compléter.',
            'submitted_at' => now()->subHour(),
            'date_soumission' => now()->subHour(),
            'statut_rapport' => IncidentReport::STATUS_REJECTED,
            'motif_refus' => $reason,
            'date_refus' => now(),
        ]);

        return $incident->refresh();
    }

    private function validatedIncidentReport(array $context, int $operatorId, int $supervisorId): Incident
    {
        $incident = $this->makeIncident($context, [
            'responsable_id' => $operatorId,
            'status_id' => $context['statusValidated']->id,
        ]);

        IncidentReport::create([
            'incident_id' => $incident->id,
            'user_id' => $operatorId,
            'operateur_id' => $operatorId,
            'actions_realisees' => 'Actions conformes.',
            'resultat' => 'Résultat conforme.',
            'observations' => 'Aucune anomalie.',
            'submitted_at' => now()->subHour(),
            'date_soumission' => now()->subHour(),
            'statut_rapport' => IncidentReport::STATUS_VALIDATED,
            'date_validation' => now(),
            'valide_par' => $supervisorId,
        ]);

        return $incident->refresh();
    }
}
