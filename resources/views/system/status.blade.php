@extends('layouts.app')

@section('title', 'État technique du système')

@section('page_css')
    @vite([
        'resources/css/app.css',
        'resources/css/pages/system-status.css'
    ])
@endsection

@php
    use Illuminate\Support\Facades\Route;

    $currentUser = auth()->user();
    

$userName = $currentUser?->name ?? 'Administrateur';
    $userEmail = $currentUser?->email ?? 'admin@ceet.tg';
    $roleName = $currentUser && method_exists($currentUser, 'getRoleNames') ? ($currentUser->getRoleNames()->first() ?: 'Administrateur') : 'Administrateur';

    $safeRoute = function (string $name, $params = [], ?string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };

    $initials = collect(preg_split('/\s+/', trim($userName)))
        ->filter()
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->take(2)
        ->implode('');

    $initials = mb_strtoupper($initials ?: 'AD');

    $systemEvents = collect($systemEvents ?? []);
    $storageUsedPercent = (float) ($storage['used_percent'] ?? 0);
    $integrity = (float) ($integrity ?? 0);

    $statusBadge = fn ($ok) => $ok ? 'OPÉRATIONNEL' : 'À VÉRIFIER';
    $statusClass = fn ($ok) => $ok ? 'is-ok' : 'is-error';

    $severityLabel = function ($log) {
        $text = mb_strtolower(($log->action ?? '') . ' ' . ($log->module ?? '') . ' ' . json_encode($log->details ?? []));

        if (str_contains($text, 'critical') || str_contains($text, 'critique')) {
            return ['label' => 'CRITIQUE', 'class' => 'is-critical'];
        }

        if (str_contains($text, 'error') || str_contains($text, 'erreur') || str_contains($text, 'fail')) {
            return ['label' => 'ERREUR', 'class' => 'is-error'];
        }

        if (str_contains($text, 'warning') || str_contains($text, 'alerte')) {
            return ['label' => 'AVERTISSEMENT', 'class' => 'is-warning'];
        }

        return ['label' => 'INFO', 'class' => 'is-info'];
    };

    $moduleLabel = function ($module) {
        return match (mb_strtolower((string) $module)) {
            'auth' => 'Authentification',
            'system' => 'Système',
            'database' => 'Base de données',
            'cache' => 'Cache',
            'mail' => 'Mail',
            'queue' => 'File d’attente',
            'incidents' => 'Incidents',
            'catalogues' => 'Catalogues',
            'reporting' => 'Rapports',
            'users' => 'Utilisateurs',
            default => str($module ?: 'Système')->replace('_', ' ')->title(),
        };
    };

    $messageLabel = function ($log) {
        if ($log->details) {
            if (is_array($log->details)) {
                return str(json_encode($log->details, JSON_UNESCAPED_UNICODE))->limit(100);
            }

            return str((string) $log->details)->limit(100);
        }

        return str($log->action ?: 'Action système enregistrée')->replace('_', ' ')->title();
    };
@endphp

@section('content')
<div class="ceet-admin-dashboard-page ceet-system-status-page" data-admin-dashboard data-system-status-page>
<main class="ceet-admin-main ceet-system-status-main">
        <section class="ceet-system-status-heading">
            <nav class="ceet-system-status-breadcrumb" aria-label="Fil d’Ariane">
                <span>Infrastructure</span>
                <span class="material-symbols-outlined" aria-hidden="true">chevron_right</span>
                <strong>État système</strong>
            </nav>

            <h2>État technique du système</h2>
            <p>Supervision en temps réel des services critiques de l’infrastructure électrique.</p>
        </section>

        <section class="ceet-system-status-card-grid">
            <article class="ceet-system-status-service-card">
                <header>
                    <span class="material-symbols-outlined">terminal</span>
                    <strong class="{{ $statusClass(true) }}">v{{ $app['laravel_version'] }}</strong>
                </header>

                <h3>Application</h3>
                <p>
                    <i class="is-green"></i>
                    Service opérationnel en environnement {{ $app['environment'] }}.
                </p>
            </article>

            <article class="ceet-system-status-service-card">
                <header>
                    <span class="material-symbols-outlined">database</span>
                    <strong class="{{ $statusClass($database['ok'] ?? false) }}">
                        {{ $statusBadge($database['ok'] ?? false) }}
                    </strong>
                </header>

                <h3>Base de données</h3>
                <p>
                    <i class="{{ ($database['ok'] ?? false) ? 'is-green' : 'is-red' }}"></i>
                    Latence : {{ $database['latency_ms'] ?? 'N/A' }} ms |
                    Connexion : {{ $database['connection'] ?? 'N/A' }}
                </p>
            </article>

            <article class="ceet-system-status-service-card">
                <header>
                    <span class="material-symbols-outlined">memory</span>
                    <strong class="{{ $statusClass($cache['ok'] ?? false) }}">
                        {{ $statusBadge($cache['ok'] ?? false) }}
                    </strong>
                </header>

                <h3>{{ ($cache['driver'] ?? '') === 'redis' ? 'Cache Redis' : 'Cache applicatif' }}</h3>
                <p>
                    <i class="{{ ($cache['ok'] ?? false) ? 'is-green' : 'is-red' }}"></i>
                    Pilote : {{ $cache['driver'] ?? 'N/A' }} |
                    Stockage : {{ $cache['store'] ?? 'N/A' }}
                </p>
            </article>
        </section>

        <section class="ceet-system-status-middle-grid">
            <article class="ceet-system-status-storage-card">
                <header>
                    <div>
                        <span class="material-symbols-outlined">cloud</span>
                        <h3>Stockage</h3>
                    </div>

                    <strong>{{ number_format($storageUsedPercent, 1, ',', ' ') }}% utilisé</strong>
                </header>

                <div class="ceet-system-status-progress">
                    <span style="width: {{ min(100, $storageUsedPercent) }}%"></span>
                </div>

                <div class="ceet-system-status-storage-values">
                    <div>
                        <span>Total</span>
                        <strong>{{ $storage['total_label'] ?? 'N/A' }}</strong>
                    </div>

                    <div>
                        <span>Utilisé</span>
                        <strong>{{ $storage['used_label'] ?? 'N/A' }}</strong>
                    </div>

                    <div>
                        <span>Libre</span>
                        <strong>{{ $storage['free_label'] ?? 'N/A' }}</strong>
                    </div>
                </div>
            </article>

            <article class="ceet-system-status-mail-card">
                <header>
                    <div>
                        <span class="material-symbols-outlined">mail</span>
                        <h3>Serveur de mail</h3>
                    </div>

                    <strong class="{{ ($mail['configured'] ?? false) ? 'is-ok' : 'is-error' }}">
                        {{ $mail['status_label'] ?? 'Non configuré' }}
                    </strong>
                </header>

                <div class="ceet-system-status-lines">
                    <div>
                        <span>Protocole</span>
                        <strong>
                            {{ $mail['protocol'] ?? 'N/A' }}
                            @if (! empty($mail['port']))
                                / port {{ $mail['port'] }}
                            @endif
                        </strong>
                    </div>

                    <div>
                        <span>File d’attente</span>
                        <strong>
                            {{ $mail['queue_pending'] ?? 0 }}
                            {{ ((int) ($mail['queue_pending'] ?? 0)) > 1 ? 'messages' : 'message' }}
                        </strong>
                    </div>

                    <div>
                        <span>Hôte</span>
                        <strong>{{ $mail['host'] ?? 'Configuration locale' }}</strong>
                    </div>
                </div>
            </article>
        </section>

        <section class="ceet-system-status-log-card">
            <header>
                <h3>Journal des dernières erreurs système</h3>

                @unless((auth()->user()?->isSuperviseur() ?? false) && ! (auth()->user()?->isAdmin() ?? false))
                <a href="{{ $safeRoute('system.status', [], '/system/status') }}">
                    <span class="material-symbols-outlined">refresh</span>
                    Actualiser
                </a>
                @endunless
            </header>

            <div class="ceet-system-status-table-wrap">
                <table class="ceet-system-status-table">
                    <thead>
                        <tr>
                            <th>Horodatage</th>
                            <th>Gravité</th>
                            <th>Composant</th>
                            <th>Message</th>
                            <th class="is-right">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($systemEvents as $log)
                            @php($severity = $severityLabel($log))

                            <tr>
                                <td class="is-mono">{{ $log->created_at?->format('Y-m-d H:i:s') ?? '--' }}</td>
                                <td>
                                    <span class="ceet-system-status-severity {{ $severity['class'] }}">
                                        {{ $severity['label'] }}
                                    </span>
                                </td>
                                <td>{{ $moduleLabel($log->module) }}</td>
                                <td>{{ $messageLabel($log) }}</td>
                                <td class="is-right">
                                    <button type="button" data-system-log-toggle="system-log-{{ $log->id }}">
                                        Détails
                                    </button>
                                </td>
                            </tr>

                            <tr id="system-log-{{ $log->id }}" class="ceet-system-status-detail-row" hidden>
                                <td colspan="5">
                                    <strong>Utilisateur :</strong>
                                    {{ $log->user?->name ?? 'Système' }}

                                    <br>

                                    <strong>Adresse IP :</strong>
                                    {{ $log->ip_address ?? 'Non renseignée' }}

                                    <br>

                                    <strong>Action :</strong>
                                    {{ $log->action ?? 'Action système' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="ceet-system-status-empty">
                                    Aucun événement système enregistré.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <footer>
                <a href="{{ $safeRoute('historique.index', [], '#') }}">Voir tous les logs</a>
            </footer>
        </section>

        <section class="ceet-system-status-analysis">
            <span>Analyse de performance réseau</span>
            <strong>
                Intégrité structurelle des données à {{ number_format($integrity, 2, ',', ' ') }}%
                au cours des dernières 24 heures.
            </strong>
        </section>
    </main>
</div>
@endsection

@section('page_js')
@endsection
