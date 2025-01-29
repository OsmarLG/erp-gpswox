<x-layouts.empty>
    <div class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <x-icon name="o-exclamation-circle" class="w-24 h-24 text-error mx-auto" />
            <h1 class="text-6xl font-bold mt-4">404</h1>
            <p class="text-xl mt-2">Página no encontrada</p>
            <p class="mt-4">Lo sentimos, pero la página que buscas no existe o ha sido movida.</p>
            <x-button label="Volver al inicio" class="btn-primary mt-6"
                onclick="window.location.href='{{ url('/') }}'" />
        </div>
    </div>
</x-layouts.empty>
