{{-- Remplacer uniquement le bloc <script> Echo en bas du fichier dashboard.blade.php --}}
{{-- Le reste du fichier (HTML, Chart.js) reste identique --}}

{{--
    ✅ CORRECTION: Vérifier window.EchoReady avant d'écouter les events
    pour éviter les erreurs JS si Reverb n'est pas démarré
--}}
<script>
    // ── Initialisation Echo temps réel (optionnel si Reverb non démarré) ──
    const realtimeStatus = document.getElementById('realtime-status');

    const setRealtimeBadge = (connected) => {
        if (!realtimeStatus) return;
        realtimeStatus.className = connected
            ? 'badge text-bg-success'
            : 'badge text-bg-secondary';
        realtimeStatus.textContent = connected
            ? '🟢 Temps réel: connecté'
            : '⚪ Temps réel: inactif';
    };

    if (window.Echo && window.EchoReady) {
        let reloadTimer = null;

        const scheduleReload = () => {
            if (reloadTimer) return;
            reloadTimer = setTimeout(() => window.location.reload(), 1000);
        };

        window.Echo.channel('incidents')
            .listen('.incident.changed', () => {
                scheduleReload();
            });

        const connector = window.Echo.connector?.pusher?.connection;
        if (connector) {
            connector.bind('connected',    () => setRealtimeBadge(true));
            connector.bind('disconnected', () => setRealtimeBadge(false));
            connector.bind('failed',       () => setRealtimeBadge(false));
            setRealtimeBadge(connector.state === 'connected');
        }
    } else {
        // Reverb non disponible → badge discret
        setRealtimeBadge(false);
    }
</script>