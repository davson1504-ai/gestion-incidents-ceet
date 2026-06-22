@extends('layouts.app')

@section('title', 'Détail incident')))

@section('page_css')
    @vite([
        'resources/css/app.css',
        'resources/css/pages/incidents-show.css'
    ])
@endsection

@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();
    

    $isAdmin = $isAdmin ?? ($currentUser?->isAdmin() ?? false);
    $isSupervisor = $isSupervisor ?? ($currentUser?->isSuperviseur() ?? false);
$userName = $currentUser?->name ?? 'Utilisateur';
    $userEmail = $currentUser?->email ?? 'Central Grid Office';
    $roleName = $currentUser && method_exists($currentUser, 'getRoleNames') ? ($currentUser->getRoleNames()->first() ?: 'Utilisateur') : 'Utilisateur';
    $isOperator = $currentUser && method_exists($currentUser, 'isOperateur') && $currentUser->isOperateur();
    $isSupervisor = $currentUser && method_exists($currentUser, 'isSuperviseur') && $currentUser->isSuperviseur();

    $initials = collect(preg_split('/\s+/', trim($userName)))->filter()->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('');
    $initials = mb_strtoupper($initials ?: 'US');

    $safeRoute = function (string $name, $params = [], ?string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

    $incidentCode = $incident->code ?? $incident->reference ?? $incident->numero ?? ('#' . $incident->id);
    $priority = $incident->priorite?->libelle ?? 'Non classé';
    $priorityToken = mb_strtolower($priority . ' ' . ($incident->priorite?->code ?? ''));
    $isCritical = str_contains($priorityToken, 'critique') || str_contains($priorityToken, 'critical') || ((int) ($incident->priorite?->niveau ?? 0) >= 3);

    $status = $incident->status?->libelle ?? 'Non défini';
    $statusCode = mb_strtoupper((string) ($incident->status?->code ?? ''));
    $statusToken = mb_strtolower($status . ' ' . $statusCode);

    $isAffected = $statusCode === 'AFFECTE' || str_contains($statusToken, 'affect');
    $isInProgress = $statusCode === 'EN_COURS' || str_contains($statusToken, 'cours');
    $isResolved = $statusCode === 'RESOLU' || str_contains($statusToken, 'résolu') || str_contains($statusToken, 'resolu');
    $isReported = $statusCode === 'RAPPORTE' || str_contains($statusToken, 'rapport');
    $isValidated = $statusCode === 'VALIDE' || str_contains($statusToken, 'valid');
    $isClosed = (bool) ($incident->status?->is_final ?? false) || $statusCode === 'CLOTURE' || str_contains($statusToken, 'clos') || str_contains($statusToken, 'clot') || str_contains($statusToken, 'termin');

    $canTakeIncident = ($currentUser?->can('take', $incident) ?? false) && $isAffected;
    $canResolveIncident = ($currentUser?->can('resolve', $incident) ?? false) && $isInProgress;
    $canReportIncident = ($currentUser?->can('report', $incident) ?? false) && $isResolved;
    $canValidateIncident = ($currentUser?->can('validateResolution', $incident) ?? false) && $isReported;
    $canCloseIncident = ($currentUser?->can('close', $incident) ?? false) && $isValidated;

    $backRoute = $isOperator ? $safeRoute('incidents.mine', [], '/mes-incidents') : $safeRoute('incidents.index', [], '/incidents');
    $searchRoute = $isOperator ? $safeRoute('incidents.mine', [], '/mes-incidents') : $safeRoute('incidents.index', [], '/incidents');

    $location = $incident->localisation ?: ($incident->departement?->nom ?? 'Localisation non renseignée');
    $description = $incident->description ?: 'Aucune description technique n’a été enregistrée pour cet incident.';
    $interventions = ($incident->interventions ?? collect())->sortByDesc(fn ($item) => $item->started_at ?? $item->created_at);
    $actions = ($incident->actions ?? collect())->sortByDesc(fn ($item) => $item->action_date ?? $item->created_at)->take(5);

    $incidentActionLabel = function ($action) {
        $key = mb_strtolower(trim((string) $action));

        $labels = [
            'create' => 'Création',
            'creation' => 'Création',
            'create_incident' => 'Création',
            'update' => 'Modification',
            'modification' => 'Modification',
            'update_incident' => 'Modification',
            'assign' => 'Affectation',
            'assignation' => 'Affectation',
            'affectation' => 'Affectation',
            'prise_en_charge' => 'Prise en charge',
            'resolution' => 'Résolution',
            'resolu' => 'Résolution',
            'rapport' => 'Rapport d’intervention',
            'validation' => 'Validation',
            'close' => 'Clôture',
            'cloture' => 'Clôture',
            'clôture' => 'Clôture',
            'delete' => 'Suppression',
            'suppression' => 'Suppression',
            'commentaire' => 'Commentaire',
            'comment' => 'Commentaire',
        ];

        return $labels[$key] ?? str($action ?: 'Action système')->replace('_', ' ')->title();
    };

    $incidentActionDescription = function ($description) {
        $value = trim((string) $description);

        $descriptions = [
            "Creation de l'incident" => "Création de l'incident",
            "Incident cree" => "Incident créé",
            "Mise a jour de l'incident" => "Modification de l'incident",
            "Incident mis a jour" => "Incident mis à jour",
            "Incident assigne a un responsable" => "Incident affecté à un responsable",
            "Prise en charge de l incident" => "Prise en charge de l'incident",
            "Incident marque comme resolu" => "Incident marqué comme résolu",
            "Rapport d intervention redige" => "Rapport d’intervention rédigé",
            "Resolution validee par le superviseur" => "Résolution validée par le superviseur",
            "Cloture de l'incident" => "Clôture de l'incident",
            "Suppression de l'incident" => "Suppression de l'incident",
        ];

        return $descriptions[$value] ?? ($value ?: 'Action enregistrée sur cet incident.');
    };

    $report = $incident->report;

    $formatMinutes = function ($minutes) {
        if ($minutes === null || $minutes === '') return 'Non calculée';
        $minutes = (int) $minutes;
        if ($minutes < 60) return $minutes . ' min';
        $hours = intdiv($minutes, 60);
        $remaining = $minutes % 60;
        return $remaining ? $hours . ' h ' . $remaining . ' min' : $hours . ' h';
    };
@endphp

@section('content')
<div class="ceet-admin-dashboard-page ceet-incident-show-page" data-admin-dashboard data-incident-show-page>
<main class="ceet-admin-main ceet-incident-show-main">
    @if (session('success'))
        <div class="ceet-incident-notice">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="ceet-incident-notice is-danger">{{ session('error') }}</div>
    @endif

    @if ($errors->any())
        <div class="ceet-incident-notice is-danger">
            <strong>Action impossible.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="ceet-incident-show-header">
        <div>
            <div class="ceet-incident-title-row">
                <a href="{{ $backRoute }}" aria-label="Retour">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h2>{{ $incident->code ?? $incident->reference ?? $incident->numero ?? ('#' . $incident->id) }}</h2>
            </div>

            <div class="ceet-incident-meta-row">
                <span class="ceet-incident-badge {{ $isCritical ? 'is-critical' : 'is-muted' }}">{{ mb_strtoupper($priority) }}</span>
                <span class="ceet-incident-badge {{ $isClosed ? 'is-closed' : 'is-muted' }}">{{ mb_strtoupper($status) }}</span>
                <span>Assigné à <strong>{{ $incident->responsable?->name ?? 'Non assigné' }}</strong></span>
            </div>
        </div>

        <div class="ceet-incident-actions">
            @if (! $isOperator && ($currentUser?->can('incidents.export') ?? false))
                <button type="button" class="ceet-incident-btn is-light" data-print-incident>
                    <span class="material-symbols-outlined">print</span>Exporter
                </button>
            @endif

            @if (! $isOperator && ($currentUser?->can('update', $incident) ?? false))
                <a href="{{ $safeRoute('incidents.edit', $incident) }}" class="ceet-incident-btn is-dark">
                    <span class="material-symbols-outlined">edit</span>Modifier
                </a>
            @endif
        </div>
    </section>

    @if ($canTakeIncident || $canResolveIncident || $canReportIncident || $canValidateIncident || $canCloseIncident)
        <section class="ceet-incident-card ceet-incident-action-card">
            <header class="ceet-incident-card-title ceet-incident-action-title">
                <div>
                    <h3><span class="material-symbols-outlined">play_circle</span>Action autorisée</h3>
                    <p>Renseignez les informations opérationnelles avant de changer le statut de l’incident.</p>
                </div>
            </header>

            @if ($canTakeIncident)
                <form action="{{ $safeRoute('incidents.take', $incident) }}" method="POST" class="ceet-quick-intervention-form is-take">
                    @csrf
                    <input type="hidden" name="action_type" value="prise_en_charge">
                    <input type="hidden" name="started_at" value="{{ now()->format('Y-m-d H:i:s') }}">
                    <input type="hidden" name="statut" value="en_cours">

                    <div class="ceet-action-field is-wide">
                        <label for="take-description-{{ $incident->id }}">Description de la prise en charge <span>*</span></label>
                        <textarea id="take-description-{{ $incident->id }}" name="description" required placeholder="Exemple : Équipe terrain mobilisée, diagnostic initial lancé, zone sécurisée...">{{ old('description', 'Prise en charge terrain démarrée.') }}</textarea>
                    </div>

                    <div class="ceet-action-submit">
                        <button type="submit" class="ceet-incident-btn is-dark">
                            <span class="material-symbols-outlined">play_arrow</span>Prendre en charge
                        </button>
                    </div>
                </form>
            @endif

            @if ($canResolveIncident)
                <form action="{{ $safeRoute('incidents.resolve', $incident) }}" method="POST" class="ceet-quick-intervention-form is-resolve">
                    @csrf
                    <input type="hidden" name="ended_at" value="{{ now()->format('Y-m-d H:i:s') }}">

                    <div class="ceet-action-field">
                        <label for="resolve-actions-{{ $incident->id }}">Actions menées</label>
                        <textarea id="resolve-actions-{{ $incident->id }}" name="actions_menees" placeholder="Exemple : Remplacement fusible, contrôle transformateur, réalimentation progressive...">{{ old('actions_menees', $incident->actions_menees) }}</textarea>
                    </div>

                    <div class="ceet-action-field">
                        <label for="resolve-result-{{ $incident->id }}">Résultat obtenu <span>*</span></label>
                        <textarea id="resolve-result-{{ $incident->id }}" name="resultat" required placeholder="Exemple : Alimentation rétablie, tension stabilisée, clients réalimentés...">{{ old('resultat', $incident->resolution_summary) }}</textarea>
                    </div>

                    <div class="ceet-action-field is-wide">
                        <label for="resolve-summary-{{ $incident->id }}">Résumé de résolution</label>
                        <textarea id="resolve-summary-{{ $incident->id }}" name="resolution_summary" placeholder="Résumé final destiné au suivi technique...">{{ old('resolution_summary', $incident->resolution_summary) }}</textarea>
                    </div>

                    <div class="ceet-action-submit">
                        <button type="submit" class="ceet-incident-btn is-dark">
                            <span class="material-symbols-outlined">task_alt</span>Marquer comme résolu
                        </button>
                    </div>
                </form>
            @endif

            @if ($canReportIncident)
                <form action="{{ $safeRoute('incidents.report', $incident) }}" method="POST" class="ceet-quick-intervention-form is-report">
                    @csrf
                    <input type="hidden" name="submitted_at" value="{{ now()->format('Y-m-d H:i:s') }}">

                    <div class="ceet-action-info is-wide ceet-report-intro">
                        <span class="material-symbols-outlined">description</span>
                        <div>
                            <strong>Rapport d'intervention</strong>
                            <p>Soumettre le rapport au superviseur</p>
                        </div>
                    </div>

                    <div class="ceet-action-field">
                        <label for="report-actions-{{ $incident->id }}">Actions réalisées <span>*</span></label>
                        <textarea id="report-actions-{{ $incident->id }}" name="actions_realisees" required placeholder="Détail des travaux réalisés par l’équipe terrain...">{{ old('actions_realisees', $incident->actions_menees) }}</textarea>
                    </div>

                    <div class="ceet-action-field">
                        <label for="report-result-{{ $incident->id }}">Résultat final <span>*</span></label>
                        <textarea id="report-result-{{ $incident->id }}" name="resultat" required placeholder="Résultat final constaté après intervention...">{{ old('resultat', $incident->resolution_summary) }}</textarea>
                    </div>

                    <div class="ceet-action-field is-wide">
                        <label for="report-observations-{{ $incident->id }}">Observations complémentaires</label>
                        <textarea id="report-observations-{{ $incident->id }}" name="observations" placeholder="Contraintes, risques résiduels, recommandations...">{{ old('observations') }}</textarea>
                    </div>

                    <div class="ceet-action-submit">
                        <button type="submit" class="ceet-incident-btn is-dark">
                            <span class="material-symbols-outlined">description</span>Soumettre le rapport au superviseur
                        </button>
                    </div>
                </form>
            @endif

            @if ($canValidateIncident)
                <form action="{{ $safeRoute('incidents.validate', $incident) }}" method="POST" class="ceet-quick-intervention-form is-validate">
                    @csrf

                    <div class="ceet-action-info is-wide">
                        <span class="material-symbols-outlined">verified</span>
                        <div>
                            <strong>Validation superviseur</strong>
                            <p>Le rapport d’intervention a été soumis. Cette action valide la résolution avant clôture.</p>
                        </div>
                    </div>

                    <div class="ceet-action-submit">
                        <button type="submit" class="ceet-incident-btn is-dark">
                            <span class="material-symbols-outlined">verified</span>Valider la résolution
                        </button>
                    </div>
                </form>
            @endif

            @if ($canCloseIncident)
                <form action="{{ $safeRoute('incidents.close', $incident) }}" method="POST" class="ceet-quick-intervention-form is-close">
                    @csrf

                    <div class="ceet-action-field">
                        <label for="close-date-{{ $incident->id }}">Date de clôture <span>*</span></label>
                        <input id="close-date-{{ $incident->id }}" type="datetime-local" name="date_fin" value="{{ old('date_fin', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>

                    <div class="ceet-action-field">
                        <label for="close-actions-{{ $incident->id }}">Actions menées</label>
                        <textarea id="close-actions-{{ $incident->id }}" name="actions_menees" placeholder="Actions finales avant clôture...">{{ old('actions_menees', $incident->actions_menees) }}</textarea>
                    </div>

                    <div class="ceet-action-field is-wide">
                        <label for="close-summary-{{ $incident->id }}">Résumé de clôture <span>*</span></label>
                        <textarea id="close-summary-{{ $incident->id }}" name="resolution_summary" required placeholder="Résumé final de clôture...">{{ old('resolution_summary', $incident->resolution_summary) }}</textarea>
                    </div>

                    <div class="ceet-action-submit">
                        <button type="submit" class="ceet-incident-btn is-dark">
                            <span class="material-symbols-outlined">lock</span>Clôturer l'incident
                        </button>
                    </div>
                </form>
            @endif
        </section>
    @endif

    <section class="ceet-incident-detail-layout">
        <div class="ceet-incident-left-column">
            <article class="ceet-incident-card">
                <header class="ceet-incident-card-title">
                    <h3><span class="material-symbols-outlined">info</span>Détails Techniques</h3>
                </header>

                <div class="ceet-incident-detail-grid">
                    <div>
                        <span>Type d'incident</span>
                        <strong>{{ $incident->typeIncident?->libelle ?? 'Non renseigné' }}</strong>
                        <small>{{ $incident->titre ?: 'Incident sans titre' }}</small>
                    </div>
                    <div>
                        <span>Localisation</span>
                        <strong class="with-icon"><span class="material-symbols-outlined">location_on</span>{{ $location }}</strong>
                    </div>
                    <div>
                        <span>Date d'apparition</span>
                        <strong>{{ $incident->date_debut?->format('d/m/Y H:i:s') ?? 'Non renseignée' }}</strong>
                    </div>
                    <div>
                        <span>Dernière mise à jour</span>
                        <strong>{{ $incident->updated_at?->format('d/m/Y H:i:s') ?? 'Non renseignée' }}</strong>
                    </div>
                    <div>
                        <span>Cause probable</span>
                        <strong>{{ $incident->cause?->libelle ?? 'Non renseignée' }}</strong>
                    </div>
                    <div>
                        <span>Durée</span>
                        <strong>{{ $formatMinutes($incident->duree_minutes) }}</strong>
                    </div>
                    <div>
                        <span>Superviseur</span>
                        <strong>{{ $incident->superviseur?->name ?? 'Non renseigné' }}</strong>
                    </div>
                    <div>
                        <span>Déclarant</span>
                        <strong>{{ $incident->operateur?->name ?? 'Système Auto-Diag' }}</strong>
                    </div>
                    <div class="is-wide">
                        <span>Description du problème</span>
                        <p>{{ $description }}</p>
                    </div>
                </div>
            </article>

            <article class="ceet-incident-card is-table-card">
                <header class="ceet-incident-table-title">
                    <h3><span class="material-symbols-outlined">engineering</span>Interventions & Rapports</h3>
                </header>

                <div class="ceet-incident-table-wrap">
                    <table class="ceet-incident-table">
                        <thead>
                            <tr>
                                <th>ID Interv.</th>
                                <th>Intervenant</th>
                                <th>Statut</th>
                                <th class="is-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($interventions as $intervention)
                                @php
                                    $interventionStatus = $intervention->statut ?: ($intervention->ended_at ? 'Terminée' : 'En cours');
                                    $done = $intervention->ended_at || str_contains(mb_strtolower($interventionStatus), 'termin') || str_contains(mb_strtolower($interventionStatus), 'resolu');
                                @endphp
                                <tr>
                                    <td>INT-{{ str_pad((string) $intervention->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <span class="ceet-person-chip">
                                            <b>{{ mb_strtoupper(mb_substr($intervention->user?->name ?? 'N', 0, 1)) }}</b>
                                            {{ $intervention->user?->name ?? 'Non assigné' }}
                                        </span>
                                    </td>
                                    <td><span class="ceet-status-line {{ $done ? 'is-done' : '' }}">{{ $interventionStatus }}</span></td>
                                    <td class="is-right"><button type="button" data-intervention-detail="intervention-{{ $intervention->id }}">Consulter</button></td>
                                </tr>
                                <tr id="intervention-{{ $intervention->id }}" class="ceet-intervention-detail-row" hidden>
                                    <td colspan="4">
                                        <strong>{{ $intervention->action_type }}</strong>
                                        <p>{{ $intervention->description }}</p>
                                        @if ($intervention->resultat)<small>Résultat : {{ $intervention->resultat }}</small>@endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="ceet-empty-cell">Aucune intervention enregistrée.</td></tr>
                            @endforelse

                            @if ($report)
                                <tr>
                                    <td>RAP-{{ str_pad((string) $report->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <span class="ceet-person-chip">
                                            <b>{{ mb_strtoupper(mb_substr($report->user?->name ?? 'N', 0, 1)) }}</b>
                                            {{ $report->user?->name ?? 'Non renseigné' }}
                                        </span>
                                    </td>
                                    <td><span class="ceet-status-line is-done">Rapport soumis</span></td>
                                    <td class="is-right"><button type="button" data-intervention-detail="report-{{ $report->id }}">Consulter</button></td>
                                </tr>
                                <tr id="report-{{ $report->id }}" class="ceet-intervention-detail-row" hidden>
                                    <td colspan="4">
                                        <strong>Rapport d’intervention</strong>
                                        <p>{{ $report->actions_realisees }}</p>
                                        <small>Résultat : {{ $report->resultat }}</small>
                                        @if ($report->observations)<small>Observations : {{ $report->observations }}</small>@endif
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </article>
        </div>

        <aside class="ceet-incident-right-column">
            <article class="ceet-incident-card ceet-history-card">
                <h3><span class="material-symbols-outlined">history</span>Historique récent</h3>

                <div class="ceet-timeline">
                    @forelse ($actions as $action)
                        @php
                            $actionDate = $action->action_date ?? $action->created_at;
                            $actionTitle = $incidentActionLabel($action->action_type ?: 'update');
                        @endphp
                        <div class="ceet-timeline-item">
                            <i></i>
                            <div>
                                <header><strong>{{ $actionTitle }}</strong><time>{{ $actionDate?->format('H:i') ?? '--:--' }}</time></header>
                                <p>{{ $incidentActionDescription($action->description) }}</p>
                                @if ($action->user)<small>{{ mb_strtoupper($action->user->name) }}</small>@endif
                            </div>
                        </div>
                    @empty
                        <div class="ceet-timeline-item is-alert">
                            <i></i>
                            <div>
                                <header><strong>Alerte initiale</strong><time>{{ $incident->date_debut?->format('H:i') ?? '--:--' }}</time></header>
                                <p>Incident déclaré dans le système CEET.</p>
                                <small>{{ $incident->code ?? $incident->reference ?? $incident->numero ?? ('#' . $incident->id) }}</small>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if (! $isOperator && Route::has('historique.index'))
                    <a href="{{ $safeRoute('historique.index') }}" class="ceet-history-export">Voir tout l'historique</a>
                @endif
            </article>

            <article class="ceet-incident-card ceet-site-card">
                <label>Vue du site</label>
                <div class="ceet-site-preview">
                    <span class="material-symbols-outlined">electrical_services</span>
                    <strong>{{ $incident->departement?->poste_source ?: 'Poste électrique' }}</strong>
                </div>
                <div class="ceet-site-lines">
                    <div><span>Réseau :</span><strong>{{ $incident->departement?->direction_exploitation ?: 'CEET' }}</strong></div>
                    <div><span>Département :</span><strong>{{ $incident->departement?->nom ?? 'N/A' }}</strong></div>
                    <div><span>Clôture :</span><strong>{{ $incident->date_fin?->format('d/m/Y H:i') ?? 'Non clôturé' }}</strong></div>
                </div>
            </article>
        </aside>
    </section>
</main>
</div>
@endsection

@section('page_js')
    @vite([
        'resources/js/pages/incidents-show.js'
    ])
@endsection
