<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h4 mb-0">{{ $incident->code_incident }} — {{ $incident->titre }}</h1>
                <small class="text-muted">Créé le {{ $incident->created_at->format('d/m/Y H:i') }}</small>
            </div>
            <span class="badge"
                  style="background-color: {{ $incident->statut?->couleur ?? '#6c757d' }}; color:#fff;">
                {{ $incident->statut?->libelle }}
            </span>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <span>Détails</span>
                    @can('incidents.update')
                        <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-sm btn-outline-primary">Éditer</a>
                    @endcan
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Département</dt>
                        <dd class="col-sm-8">{{ $incident->departement?->nom }}</dd>

                        <dt class="col-sm-4">Type / Cause</dt>
                        <dd class="col-sm-8">{{ $incident->typeIncident?->libelle }} @if($incident->cause) — {{ $incident->cause?->libelle }} @endif</dd>

                        <dt class="col-sm-4">Priorité</dt>
                        <dd class="col-sm-8">
                            <span class="badge" style="background: {{ $incident->priorite?->couleur ?? '#e9ecef' }};">
                                {{ $incident->priorite?->libelle }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Localisation</dt>
                        <dd class="col-sm-8">{{ $incident->localisation ?? '—' }}</dd>

                        <dt class="col-sm-4">Dates</dt>
                        <dd class="col-sm-8">
                            Début : {{ $incident->date_debut?->format('d/m/Y H:i') }}<br>
                            Fin : {{ $incident->date_fin?->format('d/m/Y H:i') ?? '—' }}<br>
                            Durée (min) : {{ $incident->duree_minutes ?? '—' }}
                        </dd>

                        <dt class="col-sm-4">Responsables</dt>
                        <dd class="col-sm-8">
                            Opérateur : {{ $incident->operateur?->name ?? '—' }}<br>
                            Responsable terrain : {{ $incident->responsable?->name ?? '—' }}<br>
                            Superviseur : {{ $incident->superviseur?->name ?? '—' }}
                        </dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $incident->description ?? '—' }}</dd>

                        <dt class="col-sm-4">Actions menées</dt>
                        <dd class="col-sm-8">{{ $incident->actions_menees ?? '—' }}</dd>

                        <dt class="col-sm-4">Résumé de résolution</dt>
                        <dd class="col-sm-8">{{ $incident->resolution_summary ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white">Historique</div>
                <div class="card-body">
                    @forelse($incident->actions()->latest('action_date')->limit(10)->get() as $action)
                        <div class="mb-3">
                            <div class="fw-semibold">{{ ucfirst($action->action_type) }}</div>
                            <div class="text-muted small">
                                {{ $action->action_date?->format('d/m/Y H:i') }} — {{ $action->user?->name }}
                            </div>
                            <div class="small">{{ $action->description }}</div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Aucune action enregistrée.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @can('incidents.delete')
    <form action="{{ route('incidents.destroy', $incident) }}" method="POST" onsubmit="return confirm('Supprimer cet incident ?')">
        @csrf
        @method('DELETE')
        <button class="btn btn-outline-danger">Supprimer l'incident</button>
    </form>
    @endcan
</x-app-layout>
