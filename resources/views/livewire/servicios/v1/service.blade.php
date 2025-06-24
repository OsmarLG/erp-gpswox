<div class="p-6 bg-base-100 shadow rounded-lg">
    <x-header
        title="Servicio: {{ $servicio->service->nombre }} Vehículo: {{ $servicio->vehicle->id }} - {{ $servicio->vehicle->nombre_unidad }}"
        subtitle="Realizar Servicio">
        <x-slot:actions>
            @if (auth()->user()->hasRole(['master', 'admin']) && $servicio->status !== 'completed')
                <x-button icon="o-check" class="btn-primary" wire:click="finalizarServicio" />
            @endif

            @if (auth()->user()->hasRole(['master', 'admin']) && $servicio->status === 'completed')
                <x-button icon="o-arrow-left" class="btn-warning" wire:click="cambiarAInitiated">
                    Reabrir Servicio
                </x-button>
            @endif
        </x-slot:actions>
    </x-header>

    <div class="mt-6">
        <h2 class="text-xl font-semibold mb-4">Detalles del Servicio</h2>

        @if ($servicio->solicitud)
            <p class="text-sm text-gray-700"><span class="font-bold">Notas:</span> {{ $servicio->solicitud->notas_operador ?? 'N/A' }}</p>
        @endif

        @if (auth()->user()->hasRole(['master', 'admin']))
            @if ($servicioAnterior)
                <h3 class="text-xl font-semibold mb-4">Servicio Anterior</h3>
                <p class="text-sm text-gray-700"><span class="font-bold">Notas:</span> {{ $servicioAnterior->solicitud->notas_operador ?? 'N/A' }}</p>
                <p class="text-sm text-gray-700"><span class="font-bold">Fecha:</span> {{ $servicioAnterior->fecha_realizacion->format('d/m/Y H:i:s') }}</p>
                <p class="text-sm text-gray-700"><span class="font-bold">Kilometraje:</span> {{ $servicioAnterior->valor_kilometraje }}</p>
                <p class="text-sm text-gray-700"><span class="font-bold">Operador:</span> {{ $servicioAnterior->operador->name }}</p>
                <br>
                <a href="{{ route('servicios.service', $servicioAnterior->id) }}" class="btn btn-primary">Ver Servicio Anterior</a>
            @endif

            @if ($servicioSiguiente)
                <h3 class="text-xl font-semibold mb-4">Servicio Siguiente</h3>
                <p class="text-sm text-gray-700"><span class="font-bold">Notas:</span> {{ $servicioSiguiente->solicitud->notas_operador ?? 'N/A' }}</p>
                <p class="text-sm text-gray-700"><span class="font-bold">Fecha:</span> {{ $servicioSiguiente->fecha_realizacion->format('d/m/Y H:i:s') }}</p>
                <p class="text-sm text-gray-700"><span class="font-bold">Kilometraje:</span> {{ $servicioSiguiente->valor_kilometraje }}</p>
                <p class="text-sm text-gray-700"><span class="font-bold">Operador:</span> {{ $servicioSiguiente->operador->name }}</p>
                <br>
                <a href="{{ route('servicios.service', $servicioSiguiente->id) }}" class="btn btn-primary">Ver Servicio Siguiente</a>
            @endif
        @endif

        <div class="space-y-4">
            @foreach ($detalles as $detalle)
                <div class="border p-4 rounded-lg">
                    <p class="text-sm text-gray-700">{{ $detalle->detalle }}</p>
                    <p class="text-xs text-gray-500">Escrito por: {{ $detalle->operador->name }} -
                        {{ $detalle->created_at->format('d/m/Y H:i') }}</p>

                    {{-- Botón de eliminar para master/admin --}}
                    @if (auth()->user()->hasRole(['master', 'admin']) || $detalle->operador_id == auth()->user()->id)
                        @if (in_array($servicio->status, ['initiated', 'pending']))
                            <x-button icon="o-trash" class="btn-error btn-sm mt-4"
                                wire:click="eliminarDetalle({{ $detalle->id }})" />
                        @endIf
                    @endif

                    {{-- Galería de imágenes --}}
                    @if (!empty($detalle->imagenes))
                        <div class="mt-4">
                            <h4 class="text-sm font-semibold">Imágenes:</h4>
                            <x-image-gallery :images="$detalle->imagenes" class="h-40 rounded-box" />
                        </div>
                    @endif

                    {{-- Sección de Videos --}}
                    @if (!empty($detalle->videos))
                        <div class="mt-4">
                            <h4 class="text-sm font-semibold">Videos:</h4>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach ($detalle->videos as $video)
                                    <video controls class="w-full rounded-lg">
                                        <source src="{{ $video }}" type="video/mp4">
                                        Tu navegador no soporta la reproducción de videos.
                                    </video>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Formulario para agregar detalles (solo si el servicio no está completado) --}}
        @if (auth()->user()->hasRole(['master', 'admin', 'operador']) && in_array($servicio->status, ['initiated', 'pending']))
            <div class="mt-6">
                <h2 class="text-xl font-semibold mb-3">Agregar Detalle</h2>
                <textarea wire:model="detalle" class="w-full border rounded-lg p-2" placeholder="Escribe un detalle..."></textarea>

                <div class="mt-3" x-data="{
                    isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
                    role: '{{ auth()->user()->getRoleNames()->first() }}'}">

                    <template x-if="role === 'master' || role === 'admin' || (role === 'operador' && isMobile)">
                        <x-file wire:model="files" label="Tomar Foto" accept="image/*" capture="environment" crop-after-change>
                            <div class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                <img src="{{ is_array($files) && isset($files[0]) ? $files[0]->temporaryUrl() : asset('storage/picture.png') }}"
                                    class="w-full h-full object-cover rounded-lg" />
                            </div>
                        </x-file>
                    </template>

                    <template x-if="role === 'operador' && !isMobile">
                        <div class="text-sm italic text-gray-500 mt-2">
                            Solo puedes subir archivos desde un dispositivo móvil.
                        </div>
                    </template>

                    <span wire:loading wire:target="files">Subiendo...</span>
                </div>

                <x-button icon="o-plus" class="btn-primary mt-4" wire:click="agregarDetalle">
                    Agregar Detalle
                </x-button>
            </div>
        @endif
    </div>
</div>
