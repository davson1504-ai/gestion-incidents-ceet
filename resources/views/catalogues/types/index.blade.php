@php
    use Illuminate\Support\Str;

    $typeRows = $types instanceof \Illuminate\Pagination\AbstractPaginator
        ? $types->getCollection()
        : collect($types ?? []);

    $totalTypes = method_exists($types ?? null, 'total') ? $types->total() : $typeRows->count();
    $activeOnPage = $typeRows->filter(fn ($type) => (bool) ($type->is_active ?? true))->count();
    $inactiveOnPage = max(0, $typeRows->count() - $activeOnPage);
    $latestUpdate = $typeRows->map(fn ($type) => $type->updated_at ?? null)->filter()->sortDesc()->first();

    $formatDate = function ($date): string {
        if (! $date) {
            return '—';
        }

        try {
            if ($date instanceof \Carbon\CarbonInterface) {
                return $date->format('d/m/Y H:i');
            }

            return \Carbon\Carbon::parse($date)->format('d/m/Y H:i');
        } catch (\Throwable $e) {
            return (string) $date;
        }
    };
@endphp

<x-app-layout>
    <div class="ceet-catalogue-page">
        <header class="ceet-catalogue-header">
            <div>
                <nav class="ceet-catalogue-breadcrumb" aria-label="Fil d’Ariane">
                    @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
                    <a href="{{ route('catalogues.index') }}">Configuration</a>
                    @endunless
                    <span>/</span>
                    <strong>Types d’incidents</strong>
                </nav>

                <h1 class="ceet-page-title">Types d’incidents</h1>
                <p class="ceet-page-subtitle">
                    Administrez les familles d’incidents utilisées lors de la déclaration et du suivi opérationnel.
                </p>
            </div>

            @can('catalogues.manage')
                <div class="ceet-catalogue-table-actions">
                    @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
                    <a href="{{ route('catalogues.index') }}" class="btn btn-outline-secondary">Retour catalogues</a>
                    @endunless
                    <a href="{{ route('catalogues.types.create') }}" class="btn btn-primary">Créer un type</a>
                </div>
            @endcan
        </header>

        @if(session('success') || session('status'))
            <div class="alert alert-success">
                {{ session('success') ?? session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <section class="ceet-catalogue-metrics mb-4" aria-label="Indicateurs types d’incidents">
            <article class="ceet-catalogue-metric-card">
                <p>Total référentiel</p>
                <div>
                    <strong>{{ number_format($totalTypes, 0, ',', ' ') }}</strong>
                    <span class="material-symbols-outlined" aria-hidden="true">category</span>
                </div>
            </article>

            <article class="ceet-catalogue-metric-card">
                <p>Actifs affichés</p>
                <div>
                    <strong>{{ number_format($activeOnPage, 0, ',', ' ') }}</strong>
                    <span class="ceet-catalogue-dot is-success" aria-hidden="true"></span>
                </div>
            </article>

            <article class="ceet-catalogue-metric-card">
                <p>Inactifs affichés</p>
                <div>
                    <strong>{{ number_format($inactiveOnPage, 0, ',', ' ') }}</strong>
                    <span class="ceet-catalogue-dot" aria-hidden="true"></span>
                </div>
            </article>

            <article class="ceet-catalogue-metric-card">
                <p>Dernière mise à jour</p>
                <div>
                    <strong class="is-small">{{ $formatDate($latestUpdate) }}</strong>
                    <span class="material-symbols-outlined" aria-hidden="true">update</span>
                </div>
            </article>
        </section>

        <section class="ceet-catalogue-table-card">
            <div class="ceet-catalogue-table-header">
                <div>
                    <h2>Référentiel des types</h2>
                    <p>Codes techniques, libellés publics et disponibilité à la saisie.</p>
                </div>

                <div class="ceet-catalogue-table-actions">
                    @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
                    <a href="{{ route('catalogues.index') }}" class="btn btn-outline-secondary btn-sm">Vue globale</a>
                    @endunless
                    @can('catalogues.manage')
                        <a href="{{ route('catalogues.types.create') }}" class="btn btn-dark btn-sm">Nouveau type</a>
                    @endcan
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 ceet-catalogue-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Libellé</th>
                            <th>Description</th>
                            <th>Statut</th>
                            <th>Dernière modification</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($typeRows as $type)
                            @php
                                $isActive = (bool) ($type->is_active ?? true);
                                $description = trim((string) ($type->description ?? ''));
                            @endphp

                            <tr class="{{ $isActive ? '' : 'is-inactive' }}">
                                <td>
                                    <span class="ceet-flag-badge {{ $isActive ? 'is-primary' : '' }}">
                                        {{ $type->code ?: '—' }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $type->libelle }}</strong>
                                </td>
                                <td>
                                    @if($description !== '')
                                        {{ Str::limit($description, 90) }}
                                    @else
                                        <span class="ceet-muted-italic">Aucune description</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="ceet-flag-badge {{ $isActive ? 'is-primary' : '' }}">
                                        {{ $isActive ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td>{{ $formatDate($type->updated_at ?? null) }}</td>
                                <td class="text-end">
                                    @can('catalogues.manage')
                                        <div class="ceet-row-actions">
                                            <a href="{{ route('catalogues.types.edit', $type) }}" class="btn btn-sm btn-outline-secondary">Modifier</a>
                                            <form method="POST" action="{{ route('catalogues.types.destroy', $type) }}" onsubmit="return confirm('Supprimer ce type d’incident ? Cette action est irréversible.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="ceet-muted-italic">Lecture seule</span>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <strong>Aucun type d’incident configuré.</strong><br>
                                    <span class="text-muted">Créez le premier type pour alimenter le formulaire de déclaration.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($types ?? null, 'links'))
                <div class="ceet-catalogue-pagination">
                    {{ $types->links() }}
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
