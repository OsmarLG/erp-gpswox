<x-layouts.empty>
    <div class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <x-icon name="o-shield-check" class="w-24 h-24 text-warning mx-auto" />
            <h1 class="text-6xl font-bold mt-4">401</h1>
            <p class="text-xl mt-2">No autorizado</p>
            <p class="mt-4">Debes iniciar sesión para acceder a esta página.</p>
            <x-button label="Iniciar sesión" class="btn-primary mt-6"
                onclick="window.location.href='{{ route('login') }}'" />
        </div>
    </div>
</x-layouts.empty>
