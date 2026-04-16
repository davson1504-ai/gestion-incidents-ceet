<x-app-layout>
    <style>
        :root {
            --ceet-red: #ef2433;
            --ceet-red-dark: #ce1220;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.88));
            border: 1px solid rgba(226, 232, 240, 0.6);
            border-radius: 16px;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 6px rgba(15, 23, 42, 0.07);
            animation: fadeInUp 0.6s ease both;
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            transition: all 0.2s ease;
            background: linear-gradient(to right, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.95));
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--ceet-red);
            box-shadow: 0 0 0 3px rgba(239, 36, 51, 0.1);
        }
    </style>
    <x-slot name="header">
        <h1 class="h4 mb-0">Nouvelle cause</h1>
    </x-slot>
    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST" action="{{ route('catalogues.causes.store') }}">
                @csrf
                @include('catalogues.causes.partials.form')
            </form>
        </div>
    </div>
</x-app-layout>
