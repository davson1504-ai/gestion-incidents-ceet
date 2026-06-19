@php
    use Illuminate\Support\Str;

    $departements = $departements ?? collect();
    $types = $types ?? ($typeIncidents ?? collect());
    $causes = $causes ?? collect();
    $priorites = $priorites ?? collect();
    $statuts = $statuts ?? collect();
    $operateurs = $operateurs ?? collect();
    $users = $users ?? $operateurs;

    $incident->loadMissing(['departement', 'typeIncident', 'cause', 'status', 'priorite', 'responsable', 'operateur', 'superviseur']);

    $statusLabel = $incident->status?->libelle ?? 'Non défini';
    $priorityLabel = $incident->priorite?->libelle ?? 'Non définie';
    $incidentCode = $incident->code_incident ?? ('INC-'.$incident->id);
    $updateUrl = route('incidents.update', $incident);
    $showUrl = route('incidents.show', $incident);
    $incidentsUrl = route('incidents.index');

    $statusClass = function (?string $label): string {
        $key = Str::lower(Str::ascii((string) $label));

        if (Str::contains($key, ['cloture', 'clos', 'closed'])) return 'status-cloture';
        if (Str::contains($key, ['resolu', 'resolved'])) return 'status-resolu';
        if (Str::contains($key, ['rapport'])) return 'status-rapporte';
        if (Str::contains($key, ['valid'])) return 'status-valide';
        if (Str::contains($key, ['cours', 'intervention'])) return 'status-en-cours';
        if (Str::contains($key, ['affect', 'assign'])) return 'status-affecte';

        return 'status-ouvert';
    };

    $priorityClass = function (?string $label): string {
        $key = Str::lower(Str::ascii((string) $label));

        if (Str::contains($key, ['critique', 'critical', 'urgent', 'haute', 'p1'])) return 'priority-critique';
        if (Str::contains($key, ['moyenne', 'normale'])) return 'priority-moyenne';

        return 'priority-basse';
    };
@endphp

<x-app-layout>
    <div class="ceet-page ceet-page-shell ceet-incident-create-page ceet-incident-edit-page">
        <header class="ceet-page-header d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <span class="ceet-page-kicker">Modification incident</span>
                <h1 class="ceet-page-title">Modifier l’incident {{ $incidentCode }}</h1>
                <p class="ceet-page-subtitle">
                    Mettez à jour les informations de l’incident, sa classification, son affectation et ses données de résolution.
                </p>
            </div>

            <div class="ceet-page-actions d-flex flex-wrap gap-2">
                <a href="{{ $showUrl }}" class="btn btn-outline-secondary">Voir le détail</a>
                <a href="{{ $incidentsUrl }}" class="btn btn-outline-secondary">Retour à la liste</a>
            </div>
        </header>

        <section class="ceet-card ceet-incident-summary-card">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-xl-4">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Référence</div>
                    <div class="incident-code fs-5">{{ $incidentCode }}</div>
                    <div class="text-muted small mt-1">Créé le {{ $incident->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                </div>

                <div class="col-12 col-md-4 col-xl-2">
                    <div class="small text-muted text-uppercase fw-bold mb-2">Statut actuel</div>
                    <span class="ceet-status-badge {{ $statusClass($statusLabel) }}">{{ $statusLabel }}</span>
                </div>

                <div class="col-12 col-md-4 col-xl-2">
                    <div class="small text-muted text-uppercase fw-bold mb-2">Priorité</div>
                    <span class="ceet-priority-badge {{ $priorityClass($priorityLabel) }}">{{ $priorityLabel }}</span>
                </div>

                <div class="col-12 col-md-4 col-xl-4">
                    <div class="small text-muted text-uppercase fw-bold mb-1">Contexte</div>
                    <div class="fw-semibold">{{ $incident->departement?->nom ?? 'Départ non défini' }}</div>
                    <div class="text-muted small">
                        {{ $incident->typeIncident?->libelle ?? 'Type non défini' }}
                        @if($incident->localisation)
                            • {{ $incident->localisation }}
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <section class="ceet-card ceet-incident-form-card">
            @if($errors->any())
                <div class="alert alert-danger">
                    <div class="fw-semibold mb-2">Le formulaire contient des erreurs.</div>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ $updateUrl }}" data-incident-form>
                @csrf
                @method('PUT')

                {{-- Le champ statut est affiché en lecture seule dans _form.blade.php, mais UpdateIncidentRequest exige status_id. --}}
                <input type="hidden" name="status_id" value="{{ old('status_id', $incident->status_id) }}">

                @include('incidents._form', ['incident' => $incident])
            </form>
        </section>
    </div>
</x-app-layout>
