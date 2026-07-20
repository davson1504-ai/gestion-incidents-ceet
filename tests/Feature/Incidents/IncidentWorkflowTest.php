<?php

namespace Tests\Feature\Incidents;

use App\Models\Cause;
use App\Models\Incident;
use App\Models\IncidentAction;
use App\Models\IncidentReport;
use App\Models\Intervention;
use App\Models\Log;
use App\Models\TypeIncident;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class IncidentWorkflowTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_supervisor_can_create_open_incident_and_operator_cannot_create(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');
        $supervisor = $this->makeUserWithRole('supervisor');

        $now = now()->startOfMinute();
        Carbon::setTestNow($now);

        try {
            $operatorResponse = $this->actingAs($operator)->post(route('incidents.store'), [
                'titre' => 'Incident operateur refuse',
                'description' => 'Test creation incident',
                'departement_id' => $context['departement']->id,
                'type_incident_id' => $context['type']->id,
                'cause_id' => $context['cause']->id,
                'priorite_id' => $context['priorite']->id,
                'localisation' => 'Poste test',
                'date_debut' => $now->copy()->subHours(2)->format('Y-m-d H:i:s'),
            ]);

            $response = $this->actingAs($supervisor)->post(route('incidents.store'), [
                'titre' => 'Incident ouvert',
                'description' => 'Test creation incident',
                'departement_id' => $context['departement']->id,
                'type_incident_id' => $context['type']->id,
                'cause_id' => $context['cause']->id,
                'priorite_id' => $context['priorite']->id,
                'localisation' => 'Poste test',
                'date_debut' => $now->copy()->subHours(2)->format('Y-m-d H:i:s'),
            ]);
        } finally {
            Carbon::setTestNow();
        }

        $operatorResponse->assertForbidden();
        $response->assertRedirect();

        $incident = Incident::query()->firstOrFail();
        $this->assertSame($supervisor->id, $incident->operateur_id);
        $this->assertSame('OUVERT', $incident->status->code);
        $this->assertNull($incident->date_fin);
        $this->assertNull($incident->clotured_at);
        $this->assertNull($incident->duree_minutes);

        $this->assertDatabaseHas('incident_actions', [
            'incident_id' => $incident->id,
            'action_type' => 'create',
        ]);
        $this->assertDatabaseHas('logs', [
            'incident_id' => $incident->id,
            'action' => 'create',
        ]);

        $this->assertTrue(IncidentAction::query()->exists());
        $this->assertTrue(Log::query()->exists());
    }

    public function test_operator_cannot_delete_incident_without_delete_permission(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');
        $incident = $this->makeIncident($context, ['operateur_id' => $operator->id]);

        $response = $this->actingAs($operator)->delete(route('incidents.destroy', $incident));

        $response->assertForbidden();
        $this->assertDatabaseHas('incidents', ['id' => $incident->id]);
    }

    public function test_admin_can_delete_incident(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $admin = $this->makeUserWithRole('admin');
        $incident = $this->makeIncident($context, ['operateur_id' => $admin->id]);

        $response = $this->actingAs($admin)->delete(route('incidents.destroy', $incident));

        $response->assertRedirect(route('incidents.index'));
        $this->assertDatabaseMissing('incidents', ['id' => $incident->id]);
    }

    public function test_incident_index_filters_by_cause_and_operator(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operatorA = $this->makeUserWithRole('operator', ['name' => 'Operateur A']);
        $operatorB = $this->makeUserWithRole('operator', ['name' => 'Operateur B']);

        $incidentA = $this->makeIncident($context, [
            'code_incident' => 'INC-FILTER-A',
            'cause_id' => $context['cause']->id,
            'operateur_id' => $operatorA->id,
            'responsable_id' => $operatorA->id,
        ]);
        $incidentB = $this->makeIncident($context, [
            'code_incident' => 'INC-FILTER-B',
            'cause_id' => $context['causeAlt']->id,
            'operateur_id' => $operatorB->id,
        ]);

        $response = $this->actingAs($operatorA)->get(route('incidents.index', [
            'cause_id' => $context['cause']->id,
            'operateur_id' => $operatorA->id,
        ]));

        $response->assertOk();
        $response->assertSee($incidentA->code_incident);
        $response->assertDontSee($incidentB->code_incident);
    }

    public function test_operator_incident_index_lists_all_incidents_without_management_actions(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');
        $other = $this->makeUserWithRole('operator');

        $ownIncident = $this->makeIncident($context, [
            'code_incident' => 'INC-INDEX-OWN',
            'responsable_id' => $operator->id,
        ]);
        $unrelatedIncident = $this->makeIncident($context, [
            'code_incident' => 'INC-INDEX-OTHER',
            'responsable_id' => $other->id,
            'operateur_id' => $other->id,
        ]);

        $response = $this->actingAs($operator)->get(route('incidents.index'));

        $response->assertOk();
        $response->assertSee($ownIncident->code_incident);
        $response->assertDontSee($unrelatedIncident->code_incident);
        $response->assertDontSee('Affecter');
        $response->assertDontSee('Supprimer');
    }

    public function test_operator_can_fetch_causes_by_type_for_dynamic_filter(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');

        $inactiveCause = Cause::create([
            'code' => 'CAUSE_INACTIVE',
            'libelle' => 'Cause inactive',
            'type_incident_id' => $context['type']->id,
            'is_active' => false,
        ]);

        $otherType = TypeIncident::create([
            'code' => 'TYPE_OTHER',
            'libelle' => 'Type autre',
            'is_active' => true,
        ]);

        $otherTypeCause = Cause::create([
            'code' => 'CAUSE_OTHER_TYPE',
            'libelle' => 'Cause autre type',
            'type_incident_id' => $otherType->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($operator)->get(route('incidents.causes.by-type', $context['type']));

        $response->assertOk();
        $response->assertJsonFragment(['id' => $context['cause']->id, 'libelle' => $context['cause']->libelle]);
        $response->assertJsonFragment(['id' => $context['causeAlt']->id, 'libelle' => $context['causeAlt']->libelle]);
        $response->assertJsonMissing(['id' => $inactiveCause->id, 'libelle' => $inactiveCause->libelle]);
        $response->assertJsonMissing(['id' => $otherTypeCause->id, 'libelle' => $otherTypeCause->libelle]);
    }

    public function test_operator_cannot_view_unrelated_incident_on_web(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $owner = $this->makeUserWithRole('operator');
        $other = $this->makeUserWithRole('operator');
        $incident = $this->makeIncident($context, ['operateur_id' => $owner->id]);

        $response = $this->actingAs($other)->get(route('incidents.show', $incident));

        $response->assertForbidden();
    }

    public function test_open_incidents_json_returns_only_open_incidents_summary_and_rows(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $operator = $this->makeUserWithRole('operator');

        $openIncident = $this->makeIncident($context, [
            'code_incident' => 'INC-OPEN-JSON',
            'operateur_id' => $operator->id,
            'responsable_id' => $operator->id,
            'status_id' => $context['statusAssigned']->id,
        ]);

        $closedIncident = $this->makeIncident($context, [
            'code_incident' => 'INC-CLOSED-JSON',
            'operateur_id' => $operator->id,
            'responsable_id' => $operator->id,
            'status_id' => $context['statusFinal']->id,
            'date_fin' => now(),
            'duree_minutes' => 25,
        ]);

        $declaredButUnassignedIncident = $this->makeIncident($context, [
            'code_incident' => 'INC-DECLARED-ONLY',
            'operateur_id' => $operator->id,
            'responsable_id' => null,
            'status_id' => $context['statusAssigned']->id,
        ]);

        $response = $this->actingAs($operator)->getJson(route('incidents.en-cours'));

        $response->assertOk();
        $response->assertJsonPath('totalEnCours', 1);
        $response->assertJsonCount(1, 'incidents');
        $response->assertJsonPath('incidents.0.code_incident', $openIncident->code_incident);
        $response->assertSee($openIncident->code_incident);
        $response->assertDontSee($closedIncident->code_incident);
        $response->assertDontSee($declaredButUnassignedIncident->code_incident);
    }

    public function test_operator_work_queue_contains_only_open_incidents_assigned_to_responsible_operator(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');
        $other = $this->makeUserWithRole('operator');

        $assignedOpen = $this->makeIncident($context, [
            'code_incident' => 'INC-QUEUE-OPEN',
            'responsable_id' => $operator->id,
            'status_id' => $context['statusOpen']->id,
        ]);

        $this->makeIncident($context, [
            'code_incident' => 'INC-QUEUE-CLOSED',
            'responsable_id' => $operator->id,
            'status_id' => $context['statusFinal']->id,
            'date_fin' => now(),
            'duree_minutes' => 15,
        ]);

        $this->makeIncident($context, [
            'code_incident' => 'INC-QUEUE-OTHER',
            'responsable_id' => $other->id,
            'status_id' => $context['statusOpen']->id,
        ]);

        $response = $this->actingAs($operator)->get(route('incidents.en-cours'));

        $response->assertOk();
        $response->assertSee($assignedOpen->code_incident);
        $response->assertDontSee('INC-QUEUE-CLOSED');
        $response->assertDontSee('INC-QUEUE-OTHER');
        $response->assertDontSee('Affecter');
        $response->assertDontSee('Supprimer');
    }

    public function test_operator_personal_history_contains_assigned_incidents_taken_in_charge(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');
        $other = $this->makeUserWithRole('operator');

        $plainAssigned = $this->makeIncident($context, [
            'code_incident' => 'INC-MINE-ASSIGNED-ONLY',
            'responsable_id' => $operator->id,
            'status_id' => $context['statusOpen']->id,
        ]);

        $handledOpen = $this->makeIncident($context, [
            'code_incident' => 'INC-MINE-HANDLED-OPEN',
            'responsable_id' => $operator->id,
            'status_id' => $context['statusOpen']->id,
        ]);

        Intervention::create([
            'incident_id' => $handledOpen->id,
            'user_id' => $operator->id,
            'action_type' => 'diagnostic',
            'description' => 'Diagnostic terrain.',
            'started_at' => now()->subMinutes(30),
            'ended_at' => now(),
            'duree_minutes' => 30,
        ]);

        $closedAssignedWithoutIntervention = $this->makeIncident($context, [
            'code_incident' => 'INC-MINE-CLOSED-ONLY',
            'responsable_id' => $operator->id,
            'status_id' => $context['statusFinal']->id,
            'date_fin' => now(),
            'duree_minutes' => 45,
        ]);

        $interventionWithoutAssignment = $this->makeIncident($context, [
            'code_incident' => 'INC-MINE-INTERVENTION-ONLY',
            'responsable_id' => $other->id,
            'status_id' => $context['statusOpen']->id,
        ]);

        Intervention::create([
            'incident_id' => $interventionWithoutAssignment->id,
            'user_id' => $operator->id,
            'action_type' => 'diagnostic',
            'description' => 'Intervention sans affectation courante.',
            'started_at' => now()->subMinutes(25),
        ]);

        $unrelatedHandled = $this->makeIncident($context, [
            'code_incident' => 'INC-MINE-OTHER-HANDLED',
            'responsable_id' => $other->id,
            'status_id' => $context['statusOpen']->id,
        ]);

        Intervention::create([
            'incident_id' => $unrelatedHandled->id,
            'user_id' => $other->id,
            'action_type' => 'diagnostic',
            'description' => 'Diagnostic autre operateur.',
            'started_at' => now()->subMinutes(20),
        ]);

        $response = $this->actingAs($operator)->get(route('incidents.mine'));

        $response->assertOk();
        $response->assertSee('Mes traitements');
        $response->assertDontSee($plainAssigned->code_incident);
        $response->assertSee($handledOpen->code_incident);
        $response->assertDontSee($closedAssignedWithoutIntervention->code_incident);
        $response->assertDontSee($interventionWithoutAssignment->code_incident);
        $response->assertDontSee($unrelatedHandled->code_incident);
    }

    public function test_supervisor_index_is_global_and_shows_authorized_management_actions(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $supervisor = $this->makeUserWithRole('supervisor');

        $incidentA = $this->makeIncident($context, ['code_incident' => 'INC-GLOBAL-A']);
        $incidentB = $this->makeIncident($context, ['code_incident' => 'INC-GLOBAL-B']);

        $response = $this->actingAs($supervisor)->get(route('incidents.index'));

        $response->assertOk();
        $response->assertSee($incidentA->code_incident);
        $response->assertSee($incidentB->code_incident);
        $response->assertDontSee('Supprimer');
    }

    public function test_store_rejects_cause_that_does_not_belong_to_selected_type(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();
        $supervisor = $this->makeUserWithRole('supervisor');

        $otherType = TypeIncident::create([
            'code' => 'TYPE_MISMATCH',
            'libelle' => 'Type mismatch',
            'is_active' => true,
        ]);

        $mismatchCause = Cause::create([
            'code' => 'CAUSE_MISMATCH',
            'libelle' => 'Cause mismatch',
            'type_incident_id' => $otherType->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($supervisor)->post(route('incidents.store'), [
            'titre' => 'Incident cause invalide',
            'description' => 'Validation type/cause',
            'departement_id' => $context['departement']->id,
            'type_incident_id' => $context['type']->id,
            'cause_id' => $mismatchCause->id,
            'status_id' => $context['statusOpen']->id,
            'priorite_id' => $context['priorite']->id,
            'localisation' => 'Lome',
            'date_debut' => now()->subHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertSessionHasErrors('cause_id');
        $this->assertDatabaseMissing('incidents', ['titre' => 'Incident cause invalide']);
    }

    public function test_supervisor_can_assign_incident_from_web(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $supervisor = $this->makeUserWithRole('supervisor');
        $responsable = $this->makeUserWithRole('operator');
        $incident = $this->makeIncident($context, ['superviseur_id' => $supervisor->id]);

        $response = $this->actingAs($supervisor)->post(route('incidents.assign', $incident), [
            'responsable_id' => $responsable->id,
            'superviseur_id' => $supervisor->id,
            'commentaire' => 'Prise en charge prioritaire.',
        ]);

        $response->assertRedirect(route('incidents.show', $incident));
        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'responsable_id' => $responsable->id,
            'superviseur_id' => $supervisor->id,
        ]);
        $this->assertDatabaseHas('incident_actions', [
            'incident_id' => $incident->id,
            'action_type' => 'assignation',
        ]);
        $this->assertDatabaseHas('logs', [
            'incident_id' => $incident->id,
            'action' => 'assign',
        ]);
    }

    public function test_responsible_operator_can_add_intervention_and_supervisor_can_close_incident_from_web(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');
        $supervisor = $this->makeUserWithRole('supervisor');
        $incident = $this->makeIncident($context, [
            'operateur_id' => $operator->id,
            'responsable_id' => $operator->id,
            'superviseur_id' => $supervisor->id,
            'status_id' => $context['statusAssigned']->id,
            'date_debut' => Carbon::parse('2026-04-20 08:00:00'),
        ]);

        $interventionResponse = $this->actingAs($operator)->post(route('incidents.interventions.store', $incident), [
            'action_type' => 'prise_en_charge',
            'description' => 'Verification terrain et sécurisation.',
            'started_at' => '2026-04-20 08:30:00',
            'ended_at' => '2026-04-20 09:10:00',
            'resultat' => 'Défaut localisé.',
            'statut' => 'terminee',
        ]);

        $interventionResponse->assertRedirect(route('incidents.show', $incident));
        $this->assertDatabaseHas('interventions', [
            'incident_id' => $incident->id,
            'user_id' => $operator->id,
            'action_type' => 'prise_en_charge',
            'duree_minutes' => 40,
        ]);
        $this->assertDatabaseHas('logs', [
            'incident_id' => $incident->id,
            'action' => 'take',
        ]);

        $this->assertSame('EN_COURS', $incident->refresh()->status->code);

        $resolveResponse = $this->actingAs($operator)->post(route('incidents.resolve', $incident), [
            'actions_menees' => 'Reparation et remise sous tension.',
            'resultat' => 'Service retabli.',
            'ended_at' => '2026-04-20 09:10:00',
        ]);

        $resolveResponse->assertRedirect(route('incidents.show', $incident));
        $this->assertSame('RESOLU', $incident->refresh()->status->code);

        $reportResponse = $this->actingAs($operator)->post(route('incidents.report', $incident), [
            'actions_realisees' => 'Controle du depart et remplacement fusible.',
            'resultat' => 'Depart remis en service.',
            'observations' => 'RAS',
            'submitted_at' => '2026-04-20 09:30:00',
        ]);

        $reportResponse->assertRedirect(route('incidents.show', $incident));
        $this->assertDatabaseHas('incident_reports', [
            'incident_id' => $incident->id,
            'user_id' => $operator->id,
        ]);
        $this->assertSame('RAPPORTE', $incident->refresh()->status->code);

        $validateResponse = $this->actingAs($supervisor)->post(route('incidents.validate', $incident));
        $validateResponse->assertRedirect(route('incidents.show', $incident));
        $this->assertSame('VALIDE', $incident->refresh()->status->code);

        $closeResponse = $this->actingAs($supervisor)->post(route('incidents.close', $incident), [
            'status_id' => $context['statusFinal']->id,
            'date_fin' => '2026-04-20 10:00:00',
            'actions_menees' => 'Réparation et remise sous tension.',
            'resolution_summary' => 'Service rétabli après intervention terrain.',
        ]);

        $closeResponse->assertRedirect(route('incidents.show', $incident));
        $this->assertDatabaseHas('incidents', [
            'id' => $incident->id,
            'status_id' => $context['statusFinal']->id,
            'duree_minutes' => 120,
        ]);
        $this->assertDatabaseHas('incident_actions', [
            'incident_id' => $incident->id,
            'action_type' => 'cloture',
        ]);
        $this->assertDatabaseHas('logs', [
            'incident_id' => $incident->id,
            'action' => 'close',
        ]);

        $this->assertTrue(Intervention::query()->where('incident_id', $incident->id)->exists());
    }

    public function test_operator_navigation_hides_supervisor_and_reporting_entries(): void
    {
        $this->seedRolesAndPermissions();

        $operator = $this->makeUserWithRole('operator');

        $response = $this->actingAs($operator)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Mes incidents');
        $response->assertSee('Incidents en cours');
        $response->assertDontSee('Créer incident');
        $response->assertDontSee('Nouvel Incident');
        $response->assertDontSee('Rapports');
        $response->assertDontSee('Catalogue');
    }

    public function test_operator_sees_status_driven_actions_on_incident_detail(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');

        $assigned = $this->makeIncident($context, [
            'responsable_id' => $operator->id,
            'status_id' => $context['statusAssigned']->id,
        ]);
        $this->actingAs($operator)->get(route('incidents.show', $assigned))
            ->assertOk()
            ->assertSee('Prendre en charge')
            ->assertDontSee("Clôturer l'incident", false);

        $inProgress = $this->makeIncident($context, [
            'responsable_id' => $operator->id,
            'status_id' => $context['statusInProgress']->id,
        ]);
        $this->actingAs($operator)->get(route('incidents.show', $inProgress))
            ->assertOk()
            ->assertSee('Marquer comme résolu');

        $resolved = $this->makeIncident($context, [
            'responsable_id' => $operator->id,
            'status_id' => $context['statusResolved']->id,
        ]);
        $this->actingAs($operator)->get(route('incidents.show', $resolved))
            ->assertOk()
            ->assertSee("Rapport d'intervention", false)
            ->assertSee('Soumettre le rapport au superviseur');
    }

    public function test_supervisor_sees_validation_and_closure_actions_only_at_expected_statuses(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $operator = $this->makeUserWithRole('operator');
        $supervisor = $this->makeUserWithRole('supervisor');

        $reported = $this->makeIncident($context, [
            'responsable_id' => $operator->id,
            'superviseur_id' => $supervisor->id,
            'status_id' => $context['statusReported']->id,
        ]);
        IncidentReport::create([
            'incident_id' => $reported->id,
            'user_id' => $operator->id,
            'actions_realisees' => 'Controle du depart.',
            'resultat' => 'Service retabli.',
            'submitted_at' => now(),
            'date_soumission' => now(),
            'statut_rapport' => IncidentReport::STATUS_SUBMITTED,
        ]);

        $this->actingAs($supervisor)->get(route('incidents.show', $reported))
            ->assertOk()
            ->assertSee('Valider le rapport')
            ->assertDontSee("Clôturer l'incident", false);

        $validated = $this->makeIncident($context, [
            'responsable_id' => $operator->id,
            'superviseur_id' => $supervisor->id,
            'status_id' => $context['statusValidated']->id,
        ]);
        IncidentReport::create([
            'incident_id' => $validated->id,
            'user_id' => $operator->id,
            'actions_realisees' => 'Reprise definitive.',
            'resultat' => 'Incident resolu.',
            'submitted_at' => now(),
            'date_soumission' => now(),
            'statut_rapport' => IncidentReport::STATUS_VALIDATED,
            'date_validation' => now(),
            'valide_par' => $supervisor->id,
        ]);

        $this->actingAs($supervisor)->get(route('incidents.show', $validated))
            ->assertOk()
            ->assertSee("Clôturer l'incident", false)
            ->assertDontSee('Valider le rapport');
    }

    public function test_incident_create_form_does_not_allow_free_initial_status_choice(): void
    {
        $this->seedRolesAndPermissions();
        $this->createCatalogContext();

        $supervisor = $this->makeUserWithRole('supervisor');

        $response = $this->actingAs($supervisor)->get(route('incidents.create'));

        $response->assertOk();
        $response->assertSee('Statut initial');
        $response->assertSee('OUVERT');
        $response->assertSee('AFFECTE si un opérateur est affecté');
        $response->assertDontSee('name="status_id"', false);
    }
}
