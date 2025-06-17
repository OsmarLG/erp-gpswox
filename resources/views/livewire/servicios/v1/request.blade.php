<div class="p-6 bg-base-100 shadow rounded-lg">
    <x-header title="Solicitud de Evidencia: {{ $request->parte->nombre }}" subtitle="Gestionar Solicitud">
        <x-slot:actions>
            {{-- Solo admin/master pueden finalizar o reabrir la solicitud --}}
            @if (auth()->user()->hasRole(['master', 'admin']) && $request->status !== 'accepted')
                <x-button icon="o-check" class="btn-primary" wire:click="finalizarSolicitud">
                    Finalizar Solicitud
                </x-button>
            @endif

            @if (auth()->user()->hasRole(['master', 'admin']) && $request->status === 'finished')
                <x-button icon="o-arrow-left" class="btn-warning" wire:click="reabrirSolicitud">
                    Reabrir Solicitud
                </x-button>
            @endif

            @if (auth()->user()->hasRole(['operador']) && $request->status === 'initiated')
                <x-button icon="o-check" class="btn-success" wire:click="marcarTerminado">
                    Marcar Terminado
                </x-button>
            @endif
        </x-slot:actions>
    </x-header>

    <div class="mt-6">

        {{-- Si es 'field', operador edita valor, admin/master solo ve --}}
        @if ($request->type === 'field')
            @if (auth()->user()->hasRole('operador'))
                <h2 class="text-xl font-semibold mb-4">Modificar Información</h2>
                <span class="block mb-2 text-gray-700">
                    <strong>Nombre del campo:</strong> {{ ucfirst($request->field) }}
                </span>
                <input type="text" wire:model="fieldValue" class="w-full border rounded-lg p-2"
                    placeholder="Ingrese el nuevo valor...">

                <x-button icon="o-pencil" class="btn-primary mt-4" wire:click="actualizarField">
                    Guardar Cambios
                </x-button>
            @else
                <h2 class="text-xl font-semibold mb-4">Campo Modificado</h2>
                <p class="mb-2 text-gray-700">
                    <strong>{{ ucfirst($request->field) }}:</strong>
                    {{ $request->vehicle->{$request->field} }}
                </p>
            @endif
        @endif

        {{-- Si es 'part', operador sube archivos --}}
        @if ($request->type === 'part')
            @if (auth()->user()->hasRole('operador') && ($request->status === 'initiated' || $request->status === 'pending'))
                <h2 class="text-xl font-semibold mb-4">Subir Evidencia</h2>
                <div x-data="{
                    isMobile: /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),
                    role: '{{ auth()->user()->getRoleNames()->first() }}'
                }">

                    <div x-show="role === 'master' || role === 'admin' || (role === 'operador' && isMobile)">
                        <x-file wire:model="files" label="Subir Imágenes" accept="image/*" capture="environment"
                            crop-after-change>
                            <div class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                <img src="{{ is_array($files) && isset($files[0]) ? $files[0]->temporaryUrl() : asset('storage/picture.png') }}"
                                    class="w-full h-full object-cover rounded-lg" />
                            </div>
                        </x-file>

                        <br>
                        <br>

                        <div class="mt-4">
                            <label class="btn btn-primary cursor-pointer inline-flex items-center gap-2">
                                <x-icon name="o-video-camera" class="w-5 h-5" />
                                Seleccionar Video
                                <input type="file" wire:model="videos" accept="video/mp4" class="hidden">
                            </label>

                            @if (is_object($videos) && method_exists($videos, 'temporaryUrl'))
                                <div class="mt-4">
                                    <video controls class="w-full rounded-lg">
                                        <source src="{{ $videos->temporaryUrl() }}" type="video/mp4">
                                        Tu navegador no soporta reproducción de video.
                                    </video>
                                </div>
                            @endif
                        </div>

                    </div>

                    <div x-show="role === 'operador' && !isMobile" class="text-sm italic text-gray-500 mt-2">
                        Solo puedes subir archivos desde un dispositivo móvil.
                    </div>

                    <span wire:loading wire:target="files">Subiendo...</span>
                </div>

                <x-button icon="o-pencil" class="btn-primary mt-4" wire:click="subirArchivos">
                    Subir Evidencia
                </x-button>
            @endif
        @endif

        {{-- Archivos guardados en la BD --}}
        @if ($persistedFiles->count() > 0)
            <h2 class="text-xl font-semibold mt-6">Archivos Subidos</h2>
            <div class="grid grid-cols-2 gap-4 mt-4">
                @foreach ($persistedFiles as $file)
                    @php
                        $extension = pathinfo($file->path, PATHINFO_EXTENSION);
                    @endphp

                    <div class="border p-4 rounded-lg">
                        @if (in_array($extension, ['jpg', 'jpeg', 'png']))
                            {{-- USO DE X-IMAGE-GALLERY para 1 o varias imgs:
                                Si deseas agrupar imágenes, conviene
                                agrupar primero en un array y mandarlas juntas.
                                Aquí, cada file es una sola imagen, así que
                                podrías mandar [asset('storage/'.$file->path)] como array
                             --}}
                            <x-image-gallery :images="[asset('storage/' . $file->path)]" class="h-40 rounded-box" />
                        @elseif($extension === 'mp4')
                            <video controls class="w-full rounded-lg">
                                <source src="{{ asset('storage/' . $file->path) }}" type="video/mp4">
                                Tu navegador no soporta la reproducción de videos.
                            </video>
                        @endif

                        <p class="text-sm text-gray-700 mt-2">
                            {{ $file->description }}
                        </p>

                        {{-- Botón eliminar si admin/master o dueño del archivo --}}
                        @if (auth()->user()->hasRole(['master', 'admin']) ||
                                ($file->operador_id == auth()->id() && $request->status == 'initiated'))
                            <x-button icon="o-trash" class="btn-error btn-sm mt-2"
                                wire:click="eliminarArchivo({{ $file->id }})" />
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
