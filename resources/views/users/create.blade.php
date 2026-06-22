@extends('layouts.app')

@section('title', 'Nouvel utilisateur')

@section('page_css')
    @vite('resources/css/pages/users.css')
@endsection

@section('content')
    <div class="ceet-page ceet-page-shell ceet-user-form-page">
        <header class="ceet-page-header">
            <div>
                <span class="ceet-page-kicker">Utilisateurs</span>
                <h1 class="ceet-page-title">Nouvel utilisateur</h1>
                <p class="ceet-page-subtitle">Créer un compte et lui attribuer un rôle applicatif.</p>
            </div>

            <div class="ceet-page-actions">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Retour</a>
            </div>
        </header>

        <section class="ceet-card">
            <form method="POST" action="{{ route('users.store') }}">
                @csrf
                @include('users._form')
            </form>
        </section>
    </div>
@endsection

@section('page_js')
    @vite('resources/js/pages/users.js')
@endsection
