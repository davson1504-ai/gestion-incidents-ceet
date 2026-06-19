<?php

namespace Tests\Feature\Notifications;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuildsIncidentContext;
use Tests\TestCase;

class IncidentAssignmentNotificationTest extends TestCase
{
    use BuildsIncidentContext;
    use RefreshDatabase;

    public function test_assigned_operator_receives_personal_dashboard_notification(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $supervisor = $this->makeUserWithRole('supervisor');
        $operator = $this->makeUserWithRole('operator');

        $incident = $this->makeIncident($context, [
            'operateur_id' => $supervisor->id,
            'responsable_id' => null,
            'superviseur_id' => $supervisor->id,
        ]);

        $assignResponse = $this->actingAs($supervisor)->postJson("/api/v1/incidents/{$incident->id}/assign", [
            'responsable_id' => $operator->id,
            'commentaire' => 'Prise en charge terrain immediate',
        ]);

        $assignResponse->assertOk();

        $notification = DB::table('notifications')
            ->where('notifiable_type', $operator->getMorphClass())
            ->where('notifiable_id', $operator->id)
            ->first();

        $this->assertNotNull($notification);
        $this->assertStringContainsString($incident->code_incident, $notification->data);
        $this->assertNull($notification->read_at);

        $listResponse = $this->actingAs($operator)->getJson(route('notifications.index'));

        $listResponse->assertOk();
        $listResponse->assertJsonPath('unread_count', 1);
        $listResponse->assertJsonPath('notifications.0.data.kind', 'incident_assigned');
        $listResponse->assertJsonPath('notifications.0.data.incident_id', $incident->id);
        $listResponse->assertJsonPath('notifications.0.data.assigned_by_id', $supervisor->id);
    }

    public function test_operator_can_mark_assignment_notification_as_read(): void
    {
        $this->seedRolesAndPermissions();
        $context = $this->createCatalogContext();

        $supervisor = $this->makeUserWithRole('supervisor');
        $operator = $this->makeUserWithRole('operator');
        $incident = $this->makeIncident($context, ['superviseur_id' => $supervisor->id]);

        $this->actingAs($supervisor)->postJson("/api/v1/incidents/{$incident->id}/assign", [
            'responsable_id' => $operator->id,
        ])->assertOk();

        $notificationId = DB::table('notifications')
            ->where('notifiable_id', $operator->id)
            ->value('id');

        $this->actingAs($operator)
            ->postJson(route('notifications.read', $notificationId))
            ->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertNotNull(DB::table('notifications')->where('id', $notificationId)->value('read_at'));
    }
}
