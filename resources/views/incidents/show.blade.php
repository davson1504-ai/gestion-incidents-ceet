<x-app-layout>
    <x-slot name="header">
        <div class="w-100 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h1 class="h4 mb-0">{{ $incident->code_incident }} - {{ $incident->titre }}</h1>
                <p class="text-muted mb-0">Cree le {{ $incident->created_at?->format('d/m/Y H:i') }}</p>
            </div>
            <div class="d-flex gap-2">
                <span class="badge" style="background-color: {{ $incident->statut?->couleur ?? '#6c757d' }}">
                    {{ $incident->statut?->libelle ?? 'N/A' }}
                </span>
                @can('incidents.update')
                    <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-sm btn-outline-primary">Editer</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Details de l'incident</div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Departement</dt>
                        <dd class="col-sm-8">{{ $incident->departement?->nom ?? '-' }}</dd>

                        <dt class="col-sm-4">Type / Cause</dt>
                        <dd class="col-sm-8">
                            {{ $incident->typeIncident?->libelle ?? '-' }}
                            @if($incident->cause)
                                - {{ $incident->cause->libelle }}
                            @endif
                        </dd>

                        <dt class="col-sm-4">Priorite</dt>
                        <dd class="col-sm-8">
                            <span class="badge" style="background-color: {{ $incident->priorite?->couleur ?? '#adb5bd' }}">
                                {{ $incident->priorite?->libelle ?? 'N/A' }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Localisation</dt>
                        <dd class="col-sm-8">{{ $incident->localisation ?? '-' }}</dd>

                        <dt class="col-sm-4">Date debut</dt>
                        <dd class="col-sm-8">{{ $incident->date_debut?->format('d/m/Y H:i') ?? '-' }}</dd>

                        <dt class="col-sm-4">Date fin</dt>
                        <dd class="col-sm-8">{{ $incident->date_fin?->format('d/m/Y H:i') ?? '-' }}</dd>

                        <dt class="col-sm-4">Duree (minutes)</dt>
                        <dd class="col-sm-8">{{ $incident->duree_minutes ?? '-' }}</dd>

                        <dt class="col-sm-4">Operateur</dt>
                        <dd class="col-sm-8">{{ $incident->operateur?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Responsable terrain</dt>
                        <dd class="col-sm-8">{{ $incident->responsable?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Superviseur</dt>
                        <dd class="col-sm-8">{{ $incident->superviseur?->name ?? '-' }}</dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $incident->description ?: '-' }}</dd>

                        <dt class="col-sm-4">Actions menees</dt>
                        <dd class="col-sm-8">{{ $incident->actions_menees ?: '-' }}</dd>

                        <dt class="col-sm-4">Resume resolution</dt>
                        <dd class="col-sm-8">{{ $incident->resolution_summary ?: '-' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white fw-semibold">Historique des actions</div>
                <div class="card-body">
                    @php
                        $actions = $incident->actions->sortByDesc('action_date')->take(10);
                    @endphp

                    @forelse($actions as $action)
                        <div class="border-bottom pb-2 mb-2">
                            <div class="fw-semibold text-capitalize">{{ $action->action_type }}</div>
                            <div class="small text-muted">
                                {{ $action->action_date?->format('d/m/Y H:i') ?? '-' }}
                                -
                                {{ $action->user?->name ?? 'Systeme' }}
                            </div>
                            <div class="small">{{ $action->description }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune action enregistree pour cet incident.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-3 d-flex justify-content-between flex-wrap gap-2">
        <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary">Retour a la liste</a>

        @can('incidents.delete')
            <form action="{{ route('incidents.destroy', $incident) }}" method="POST" onsubmit="return confirm('Supprimer cet incident ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">Supprimer</button>
            </form>
        @endcan
    </div>
</x-app-layout>
