<?php

namespace App\Http\Controllers;

use App\Imports\CatalogueRawImport;
use App\Models\Cause;
use App\Models\Departement;
use App\Models\Priorite;
use App\Models\Statut;
use App\Models\TypeIncident;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class CatalogueImportController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'catalogues_file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
            'mode' => ['nullable', 'in:upsert,insert'],
        ], [
            'catalogues_file.required' => 'Veuillez sélectionner un fichier Excel.',
            'catalogues_file.mimes' => 'Le fichier doit être au format xlsx, xls ou csv.',
            'catalogues_file.max' => 'Le fichier ne doit pas dépasser 10 Mo.',
        ]);

        $mode = $request->input('mode', 'upsert');
        $summary = [
            'departements' => 0,
            'types' => 0,
            'causes' => 0,
            'priorites' => 0,
            'statuts' => 0,
            'ignored' => 0,
        ];

        try {
            $sheets = Excel::toArray(new CatalogueRawImport(), $request->file('catalogues_file'));

            DB::transaction(function () use ($sheets, $mode, &$summary): void {
                foreach ($sheets as $sheetIndex => $rows) {
                    $normalizedRows = $this->rowsWithHeadings($rows);

                    if ($normalizedRows === []) {
                        continue;
                    }

                    $kind = $this->detectSheetKind($normalizedRows, $sheetIndex);

                    match ($kind) {
                        'departements' => $summary['departements'] += $this->importDepartements($normalizedRows, $mode),
                        'types' => $summary['types'] += $this->importTypes($normalizedRows, $mode),
                        'causes' => $summary['causes'] += $this->importCauses($normalizedRows, $mode),
                        'priorites' => $summary['priorites'] += $this->importPriorites($normalizedRows, $mode),
                        'statuts' => $summary['statuts'] += $this->importStatuts($normalizedRows, $mode),
                        default => $summary['ignored']++,
                    };
                }
            });
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Import impossible : '.$exception->getMessage());
        }

        cache()->flush();

        $message = sprintf(
            'Import terminé : %d départ(s), %d type(s), %d cause(s), %d priorité(s), %d statut(s).',
            $summary['departements'],
            $summary['types'],
            $summary['causes'],
            $summary['priorites'],
            $summary['statuts'],
        );

        if ($summary['ignored'] > 0) {
            $message .= ' '.$summary['ignored'].' feuille(s) ignorée(s).';
        }

        return redirect()->route('catalogues.index')->with('success', $message);
    }

    public function template(): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        $this->addSheet($spreadsheet, 'departements', [
            ['code', 'nom', 'zone', 'direction_exploitation', 'poste_repartition', 'poste_source', 'transformateur', 'arrivee', 'charge_maximale', 'charge_unite', 'description', 'is_active'],
            ['DEP-001', 'SGGG', 'Zone réseau CEET', 'Direction Lomé', 'Poste A', 'Poste source A', 'TR 1', 'Arrivée TR 1', '120', 'A', 'Départ principal', '1'],
        ]);

        $this->addSheet($spreadsheet, 'types_incidents', [
            ['code', 'libelle', 'description', 'is_active'],
            ['CC', 'Court-circuit', 'Court-circuit sur le réseau', '1'],
        ]);

        $this->addSheet($spreadsheet, 'causes', [
            ['code', 'libelle', 'description', 'type_incident_code', 'is_active'],
            ['SURC', 'Surcharge', 'Surcharge du réseau', 'CC', '1'],
        ]);

        $this->addSheet($spreadsheet, 'priorites', [
            ['code', 'libelle', 'description', 'niveau', 'couleur', 'is_active'],
            ['CRITIQUE', 'Critique', 'Traitement prioritaire', '1', '#dc3545', '1'],
        ]);

        $this->addSheet($spreadsheet, 'statuts', [
            ['code', 'libelle', 'description', 'ordre', 'couleur', 'is_active', 'is_final'],
            ['EN_COURS', 'En cours', 'Incident ouvert ou en traitement', '1', '#0d6efd', '1', '0'],
        ]);

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer): void {
            $writer->save('php://output');
        }, 'modele-import-catalogues-ceet.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    private function addSheet(Spreadsheet $spreadsheet, string $title, array $rows): void
    {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle($title);

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $columnIndex => $value) {
                $sheet->setCellValue([$columnIndex + 1, $rowIndex + 1], $value);
            }
        }

        foreach (range(1, max(array_map('count', $rows))) as $column) {
            $sheet->getColumnDimensionByColumn($column)->setAutoSize(true);
        }
    }

    private function rowsWithHeadings(array $rows): array
    {
        $rows = array_values(array_filter($rows, fn ($row) => is_array($row) && array_filter($row, fn ($cell) => $cell !== null && $cell !== '')));

        if (count($rows) < 2) {
            return [];
        }

        $headings = array_map(fn ($value) => $this->key($value), $rows[0]);
        $mapped = [];

        foreach (array_slice($rows, 1) as $row) {
            $item = [];

            foreach ($headings as $index => $heading) {
                if ($heading === '') {
                    continue;
                }

                $item[$heading] = Arr::get($row, $index);
            }

            if (array_filter($item, fn ($value) => $value !== null && $value !== '')) {
                $mapped[] = $item;
            }
        }

        return $mapped;
    }

    private function detectSheetKind(array $rows, int $sheetIndex): string
    {
        $keys = array_keys($rows[0] ?? []);
        $joined = implode('|', $keys);

        if (Str::contains($joined, ['direction_exploitation', 'poste_repartition', 'poste_source', 'transformateur', 'arrivee'])) {
            return 'departements';
        }

        if (Str::contains($joined, ['type_incident_code', 'type_incident'])) {
            return 'causes';
        }

        if (Str::contains($joined, ['niveau'])) {
            return 'priorites';
        }

        if (Str::contains($joined, ['ordre', 'is_final'])) {
            return 'statuts';
        }

        if (in_array($sheetIndex, [0, 1, 2, 3, 4], true)) {
            return ['departements', 'types', 'causes', 'priorites', 'statuts'][$sheetIndex];
        }

        return 'unknown';
    }

    private function importDepartements(array $rows, string $mode): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $code = $this->value($row, ['code']);
            $nom = $this->value($row, ['nom', 'libelle', 'departement', 'depart']);

            if ($code === '' || $nom === '') {
                continue;
            }

            $this->saveByCode(Departement::class, [
                'code' => $code,
                'nom' => $nom,
                'zone' => $this->nullable($row, ['zone', 'region']),
                'direction_exploitation' => $this->nullable($row, ['direction_exploitation', 'direction']),
                'poste_repartition' => $this->nullable($row, ['poste_repartition', 'repartition']),
                'poste_source' => $this->nullable($row, ['poste_source']),
                'transformateur' => $this->nullable($row, ['transformateur']),
                'arrivee' => $this->nullable($row, ['arrivee']),
                'charge_maximale' => $this->decimal($this->value($row, ['charge_maximale', 'charge'])),
                'charge_unite' => $this->value($row, ['charge_unite', 'unite'], 'A'),
                'description' => $this->nullable($row, ['description']),
                'is_active' => $this->bool($this->value($row, ['is_active', 'actif', 'active'], '1')),
            ], $mode);

            $count++;
        }

        return $count;
    }

    private function importTypes(array $rows, string $mode): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $code = $this->value($row, ['code']);
            $libelle = $this->value($row, ['libelle', 'nom', 'type']);

            if ($libelle === '') {
                continue;
            }

            $this->saveByCode(TypeIncident::class, [
                'code' => $code !== '' ? $code : Str::upper(Str::slug($libelle, '_')),
                'libelle' => $libelle,
                'description' => $this->nullable($row, ['description']),
                'is_active' => $this->bool($this->value($row, ['is_active', 'actif', 'active'], '1')),
            ], $mode);

            $count++;
        }

        return $count;
    }

    private function importCauses(array $rows, string $mode): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $code = $this->value($row, ['code']);
            $libelle = $this->value($row, ['libelle', 'nom', 'cause']);

            if ($libelle === '') {
                continue;
            }

            $typeCode = $this->value($row, ['type_incident_code', 'type_code', 'type_incident']);
            $typeId = null;

            if ($typeCode !== '') {
                $typeId = TypeIncident::query()
                    ->where('code', $typeCode)
                    ->orWhere('libelle', $typeCode)
                    ->value('id');
            }

            $this->saveByCode(Cause::class, [
                'code' => $code !== '' ? $code : Str::upper(Str::slug($libelle, '_')),
                'libelle' => $libelle,
                'description' => $this->nullable($row, ['description']),
                'type_incident_id' => $typeId,
                'is_active' => $this->bool($this->value($row, ['is_active', 'actif', 'active'], '1')),
            ], $mode);

            $count++;
        }

        return $count;
    }

    private function importPriorites(array $rows, string $mode): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $code = $this->value($row, ['code']);
            $libelle = $this->value($row, ['libelle', 'nom', 'priorite']);

            if ($code === '' || $libelle === '') {
                continue;
            }

            $this->saveByCode(Priorite::class, [
                'code' => $code,
                'libelle' => $libelle,
                'description' => $this->nullable($row, ['description']),
                'niveau' => (int) ($this->value($row, ['niveau'], '3') ?: 3),
                'couleur' => $this->value($row, ['couleur', 'color'], '#6c757d'),
                'is_active' => $this->bool($this->value($row, ['is_active', 'actif', 'active'], '1')),
            ], $mode);

            $count++;
        }

        return $count;
    }

    private function importStatuts(array $rows, string $mode): int
    {
        $count = 0;

        foreach ($rows as $row) {
            $code = $this->value($row, ['code']);
            $libelle = $this->value($row, ['libelle', 'nom', 'statut']);

            if ($code === '' || $libelle === '') {
                continue;
            }

            $this->saveByCode(Statut::class, [
                'code' => $code,
                'libelle' => $libelle,
                'description' => $this->nullable($row, ['description']),
                'ordre' => (int) ($this->value($row, ['ordre', 'order'], '0') ?: 0),
                'couleur' => $this->value($row, ['couleur', 'color'], '#6c757d'),
                'is_active' => $this->bool($this->value($row, ['is_active', 'actif', 'active'], '1')),
                'is_final' => $this->bool($this->value($row, ['is_final', 'final'], '0')),
            ], $mode);

            $count++;
        }

        return $count;
    }

    private function saveByCode(string $modelClass, array $payload, string $mode): void
    {
        if ($mode === 'insert') {
            $modelClass::query()->create($payload);
            return;
        }

        $modelClass::query()->updateOrCreate(['code' => $payload['code']], $payload);
    }

    private function value(array $row, array $keys, mixed $default = ''): string
    {
        foreach ($keys as $key) {
            $normalizedKey = $this->key($key);

            if (array_key_exists($normalizedKey, $row) && $row[$normalizedKey] !== null) {
                return trim((string) $row[$normalizedKey]);
            }
        }

        return trim((string) $default);
    }

    private function nullable(array $row, array $keys): ?string
    {
        $value = $this->value($row, $keys);

        return $value === '' ? null : $value;
    }

    private function bool(mixed $value): bool
    {
        $value = Str::of((string) $value)->ascii()->lower()->trim()->toString();

        return in_array($value, ['1', 'true', 'vrai', 'yes', 'oui', 'actif', 'active'], true);
    }

    private function decimal(string $value): ?float
    {
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function key(mixed $value): string
    {
        return Str::of((string) $value)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->toString();
    }
}
