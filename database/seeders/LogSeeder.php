<?php

namespace Database\Seeders;

use App\Models\Incident;
use App\Models\Log;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class LogSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::query()->pluck('id')->all();
        $incidentIds = Incident::query()->pluck('id')->all();

        if (empty($userIds)) {
            $this->command?->warn('LogSeeder ignore: aucun utilisateur trouve.');

            return;
        }

        $events = [
            ['action' => 'login', 'module' => 'auth', 'target_type' => 'User'],
            ['action' => 'create_incident', 'module' => 'incidents', 'target_type' => 'Incident'],
            ['action' => 'update_incident', 'module' => 'incidents', 'target_type' => 'Incident'],
            ['action' => 'export_report', 'module' => 'reporting', 'target_type' => 'Report'],
            ['action' => 'update_catalogue', 'module' => 'catalogues', 'target_type' => 'Catalogue'],
            ['action' => 'create_user', 'module' => 'users', 'target_type' => 'User'],
        ];

        for ($i = 1; $i <= 120; $i++) {
            $event = Arr::random($events);
            $isIncidentEvent = $event['target_type'] === 'Incident' && ! empty($incidentIds);
            $incidentId = $isIncidentEvent ? Arr::random($incidentIds) : null;
            $targetId = $isIncidentEvent ? $incidentId : Arr::random($userIds);
            $createdAt = now()->subDays(random_int(0, 30))->subMinutes(random_int(0, 1440));

            $log = new Log([
                'user_id' => random_int(0, 100) < 95 ? Arr::random($userIds) : null,
                'action' => $event['action'],
                'module' => $event['module'],
                'target_type' => $event['target_type'],
                'target_id' => $targetId,
                'incident_id' => $incidentId,
                'ip_address' => '192.168.'.random_int(0, 10).'.'.random_int(1, 254),
                'user_agent' => 'SeederBot/1.0',
                'details' => [
                    'source' => 'database-seeder',
                    'batch' => 'demo-audit',
                    'sequence' => $i,
                ],
            ]);

            $log->created_at = $createdAt;
            $log->updated_at = $createdAt;
            $log->save();
        }

        $this->command?->info('120 logs de demonstration ont ete crees.');
    }
}
