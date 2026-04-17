<?php

namespace App\Exports;

use App\Models\Incident;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncidentsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    public function __construct(private readonly array $filters) {}

    public function query(): Builder
    {
        return Incident::query()
            ->with([
                'departement',
                'status',
                'priorite',
                'typeIncident',
                'cause',
                'operateur',
                'responsable',
                'superviseur',
            ])
            ->when($this->filters['departement_id'] ?? null, fn (Builder $query, $value) => $query->where('departement_id', $value))
            ->when($this->filters['status_id'] ?? null, fn (Builder $query, $value) => $query->where('status_id', $value))
            ->when($this->filters['priorite_id'] ?? null, fn (Builder $query, $value) => $query->where('priorite_id', $value))
            ->when($this->filters['type_incident_id'] ?? null, fn (Builder $query, $value) => $query->where('type_incident_id', $value))
            ->when($this->filters['cause_id'] ?? null, fn (Builder $query, $value) => $query->where('cause_id', $value))
            ->when($this->filters['operateur_id'] ?? null, fn (Builder $query, $value) => $query->where('operateur_id', $value))
            ->when($this->filters['date_from'] ?? null, fn (Builder $query, $value) => $query->whereDate('date_debut', '>=', $value))
            ->when($this->filters['date_to'] ?? null, fn (Builder $query, $value) => $query->whereDate('date_debut', '<=', $value))
            ->when($this->filters['q'] ?? null, function (Builder $query, $value) {
                $query->where(function (Builder $searchQuery) use ($value) {
                    $searchQuery
                        ->where('code_incident', 'like', "%{$value}%")
                        ->orWhere('titre', 'like', "%{$value}%");
                });
            })
            ->orderByDesc('date_debut');
    }

    public function headings(): array
    {
        return [
            'Code',
            'Titre',
            'Département',
            'Statut',
            'Priorité',
            'Type d\'incident',
            'Cause',
            'Date début',
            'Date fin',
            'Durée (min)',
            'Opérateur',
            'Responsable',
            'Superviseur',
            'Actions menées',
            'Résolution',
        ];
    }

    public function map($incident): array
    {
        return [
            $incident->code_incident,
            $incident->titre,
            $incident->departement?->nom,
            $incident->status?->libelle,
            $incident->priorite?->libelle,
            $incident->typeIncident?->libelle,
            $incident->cause?->libelle,
            $incident->date_debut?->format('d/m/Y H:i'),
            $incident->date_fin?->format('d/m/Y H:i'),
            $incident->duree_minutes,
            $incident->operateur?->name,
            $incident->responsable?->name,
            $incident->superviseur?->name,
            $incident->actions_menees,
            $incident->resolution_summary,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F3F4F6'],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Incidents CEET';
    }
}
