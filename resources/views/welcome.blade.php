@auth
    <script>window.location.href = @json(route('dashboard'));</script>
@else
    <script>window.location.href = @json(route('login'));</script>
@endauth

<noscript>
    @auth
        <a href="{{ route('dashboard') }}">Ouvrir le tableau de bord</a>
    @else
        <a href="{{ route('login') }}">Ouvrir la page de connexion</a>
    @endauth
</noscript>
