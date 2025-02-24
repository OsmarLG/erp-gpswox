<x-layouts.empty>
    <div class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <x-icon name="o-lock-closed" class="w-24 h-24 text-warning mx-auto" />
            <h1 class="text-6xl font-bold mt-4">403</h1>
            <p class="text-xl mt-2">Acceso denegado</p>
            <p class="mt-4">No tienes permiso para acceder a esta página.</p>
            <x-button label="Ir al inicio" class="btn-primary mt-6" onclick="window.location.href='{{ url('/') }}'" />
        </div>
    </div>
</x-layouts.empty>
