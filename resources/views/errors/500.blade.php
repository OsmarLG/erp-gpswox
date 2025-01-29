<x-layouts.empty>
    <div class="flex items-center justify-center min-h-screen">
        <div class="text-center">
            <x-icon name="o-server" class="w-24 h-24 text-error mx-auto" />
            <h1 class="text-6xl font-bold mt-4">500</h1>
            <p class="text-xl mt-2">Error interno del servidor</p>
            <p class="mt-4">Ha ocurrido un problema en el servidor. Estamos trabajando para solucionarlo.</p>
            <x-button label="Ir al inicio" class="btn-primary mt-6" onclick="window.location.href='{{ url('/') }}'" />
        </div>
    </div>
</x-layouts.empty>
