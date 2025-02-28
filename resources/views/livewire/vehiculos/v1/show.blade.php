<div class="p-6 bg-base-100 shadow rounded-lg">
    {{-- Encabezado del Vehículo --}}
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <h2 class="text-2xl font-bold text-center mb-6">Detalles de {{ $vehiculo->nombre_unidad }}</h2>

    {{-- Mostrar info de operador si existe --}}
    @if ($vehiculo->operador)
        {{-- Header con Avatar e info básica --}}
        <div class="flex items-center space-x-4 mb-6">
            <div class="relative group">
                @php
                    // Generar iniciales
                    $initials = collect(explode(' ', $vehiculo->operador->name))
                        ->map(fn($word) => strtoupper($word[0]))
                        ->implode('');
                @endphp

                @if ($vehiculo->operador->avatar ?? false)
                    <x-avatar :image="asset('storage/' . $vehiculo->operador->avatar)" class="!w-24 !h-24 shadow-lg" />
                @else
                    <x-avatar placeholder="{{ $initials }}" class="!w-24 !h-24 shadow-lg" />
                @endif
            </div>

            <div>
                <h3 class="text-xl font-semibold text-primary">{{ $vehiculo->operador->name }}</h3>
                <p class="text-sm text-gray-500">{{ $vehiculo->placa }}</p>
            </div>
        </div>
    @endif

    {{-- Si el usuario es admin/master, permitir cambiar operador --}}
    @if (auth()->user()->hasRole(['master', 'admin']))
        <div class="mb-6">
            <h3 class="text-lg font-bold mb-2">Cambiar Operador</h3>
            <form wire:submit.prevent="changeOperator" class="flex items-center gap-4">
                <select wire:model="newOperatorId" class="form-select rounded-lg border-gray-300 shadow-sm w-1/2">
                    <option value="">Seleccionar operador (Seleccionar para dejar sin operador)</option>
                    @foreach ($operators as $operator)
                        <option value="{{ $operator->id }}">{{ $operator->name }}</option>
                    @endforeach
                </select>
                <x-button type="submit" label="Actualizar Operador" icon="o-user" class="btn-primary" />
            </form>
        </div>
    @endif

    {{-- Tabs con Mary UI --}}
    <x-tabs wire:model="selectedTab">
        <x-tab name="info-tab" label="Detalles Generales" icon="o-information-circle">
            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-card title="Solicitar Modificación de Información General">
                    <div class="flex items-center gap-4">
                        <select wire:model="selectedField"
                            class="form-select rounded-lg border-gray-300 shadow-sm w-1/2">
                            <option value="">Seleccionar campo a modificar</option>
                            @foreach ($vehicleFields as $field)
                                <option value="{{ $field }}" @if ($requests->where('type', 'field')->where('field', $field)->isNotEmpty()) disabled @endif>
                                    {{ ucfirst(str_replace('_', ' ', $field)) }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Botón para solicitar cambio --}}
                        @if ($vehiculo->operador_id != null)
                            <x-button wire:click="requestModification('field', {{ $selectedField }})"
                                label="Solicitar Cambio" icon="o-pencil" class="btn-warning" />
                        @else
                            <p class="text-sm text-gray-500">Seleccionar operador para solicitar cambio</p>
                        @endif
                    </div>
                </x-card>

                {{-- Fin de la sección de campos --}}
                <x-card title="Solicitudes Pendientes de Información General">
                    @if ($requests->where('type', 'field')->count())
                        <table class="table-auto w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Operador</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests->where('type', 'field') as $request)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $request->field)) }}</td>
                                        <td>{{ $request->operador->name }}</td>
                                        <td>{{ ucfirst($request->status) }}</td>
                                        <td>
                                            @if ($request->status === 'initiated' || $request->status === 'pending')
                                                <x-button wire:click="showRequest({{ $request->id }})" icon="o-cog"
                                                    label="Ver" class="btn-primary btn-sm" />

                                                <x-button wire:click="deleteRequest({{ $request->id }})"
                                                    label="Eliminar" icon="o-trash" class="btn-error btn-sm" />
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-sm text-gray-500">No hay solicitudes pendientes.</p>
                    @endif
                </x-card>

                <x-card title="Información General">
                    <p><strong>Nombre Unidad:</strong> {{ $vehiculo->nombre_unidad ?? 'N/A' }}</p>
                    <p><strong>Placa:</strong> {{ $vehiculo->placa ?? 'N/A' }}</p>
                    <p><strong>Tipo/Marca:</strong> {{ $vehiculo->tipo_marca ?? 'N/A' }}</p>
                    <p><strong>VIN:</strong> {{ $vehiculo->vin ?? 'N/A' }}</p>
                    <p><strong>ID GPS WOX:</strong> {{ $vehiculo->gpswox_id ?? 'N/A' }}</p>
                    <p><strong>Odómetro:</strong>
                        {{ $odometerValue ? number_format($odometerValue, 2) . ' km' : 'No disponible' }}</p>
                    <p><strong>Teléfono Seguro:</strong> {{ $vehiculo->telefono_seguro ?? 'N/A' }}</p>
                    <p><strong>TAG Gasolina ID:</strong> {{ $vehiculo->tag_gasolina_id ?? 'N/A' }}</p>
                </x-card>
                <x-card title="Información de Documentación">
                    <p><strong>No. Tarjeta de Circulación:</strong> {{ $vehiculo->no_tarjeta_circulacion ?? 'N/A' }}
                    </p>
                    <p><strong>Vigencia:</strong> {{ $vehiculo->vigencia_tarjeta ?? 'N/A' }}</p>
                    <p><strong>No. Tag:</strong> {{ $vehiculo->tag_numero ?? 'N/A' }}</p>
                    <p><strong>Verificación Vencimiento:</strong> {{ $vehiculo->verificacion_vencimiento ?? 'N/A' }}
                    </p>
                    <p><strong>Poliza No:</strong> {{ $vehiculo->poliza_no ?? 'N/A' }}</p>
                    <p><strong>Compañía Seguros:</strong> {{ $vehiculo->compania_seguros ?? 'N/A' }}</p>
                    <p><strong>Poliza Vigencia:</strong> {{ $vehiculo->poliza_vigencia ?? 'N/A' }}</p>
                    <p><strong>Poliza Costo:</strong> {{ $vehiculo->costo_poliza ?? 'N/A' }}</p>
                </x-card>
                <x-card title="Información de GPS">
                    <p><strong>ID GPS 1:</strong> {{ $vehiculo->id_gps1 ?? 'N/A' }}</p>
                    <p><strong>Teléfono GPS 1:</strong> {{ $vehiculo->tel_gps1 ?? 'N/A' }}</p>
                    <p><strong>IMEI GPS 1:</strong> {{ $vehiculo->imei_gps1 ?? 'N/A' }}</p>
                    <p><strong>Vigencia GPS 1:</strong> {{ $vehiculo->vigencia_gps1 ?? 'N/A' }}</p>
                    <p><strong>Saldo GPS 1:</strong> {{ $vehiculo->saldo_gps1 ?? 'N/A' }}</p>
                    <p><strong>ID GPS 2:</strong> {{ $vehiculo->id_gps2 ?? 'N/A' }}</p>
                    <p><strong>Teléfono GPS 2:</strong> {{ $vehiculo->tel_gps2 ?? 'N/A' }}</p>
                    <p><strong>IMEI GPS 2:</strong> {{ $vehiculo->imei_gps2 ?? 'N/A' }}</p>
                    <p><strong>Vigencia GPS 2:</strong> {{ $vehiculo->vigencia_gps2 ?? 'N/A' }}</p>
                    <p><strong>Saldo GPS 2:</strong> {{ $vehiculo->saldo_gps2 ?? 'N/A' }}</p>
                </x-card>
            </div>
        </x-tab>

        <x-tab name="partes-tab" label="Partes del Vehículo" icon="o-truck">
            <div class="mt-4">
                <x-card title="Solicitudes Pendientes de Partes del Vehículo">
                    @if ($requests->where('type', 'part')->count())
                        <table class="table-auto w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Parte</th>
                                    <th>Operador</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests->where('type', 'part') as $request)
                                    <tr>
                                        <td>{{ $request->parte->nombre ?? 'N/A' }}</td>
                                        <td>{{ $request->operador->name }}</td>
                                        <td>{{ ucfirst($request->status) }}</td>
                                        <td>
                                            @if ($request->status === 'initiated' || $request->status === 'pending')
                                                <x-button wire:click="showRequest({{ $request->id }})" icon="o-cog"
                                                    label="Ver" class="btn-primary btn-sm" />

                                                <x-button wire:click="deleteRequest({{ $request->id }})"
                                                    label="Eliminar" icon="o-trash" class="btn-error btn-sm" />
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-sm text-gray-500">No hay solicitudes pendientes.</p>
                    @endif
                </x-card>

                <x-card title="Partes del Vehículo">
                    @foreach ($parts->groupBy('categoria.nombre') as $categoria => $partes)
                        <div class="mb-4">
                            <h3 class="text-lg font-bold text-primary">{{ $categoria }}</h3>
                            @foreach ($partes as $part)
                                <x-collapse>
                                    <x-slot:heading>
                                        {{ $part->nombre }}
                                    </x-slot:heading>
                                    <x-slot:content>
                                        {{-- {{ dd($part) }} --}}
                                        @if ($part->files->isNotEmpty())
                                            <ul>
                                                @foreach ($part->files as $file)
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
                                                            <x-image-gallery :images="[asset('storage/' . $file->path)]"
                                                                class="h-40 rounded-box" />
                                                        @elseif($extension === 'mp4')
                                                            <video controls class="w-full rounded-lg">
                                                                <source src="{{ asset('storage/' . $file->path) }}"
                                                                    type="video/mp4">
                                                                Tu navegador no soporta la reproducción de videos.
                                                            </video>
                                                        @endif

                                                        <p class="text-sm text-gray-700 mt-2">
                                                            {{ 'Descripción: ' . $file->description . '- Fecha: ' . $file->created_at }}
                                                        </p>
                                                        <p class="text-sm text-gray-700 mt-2">
                                                            <strong>{{ '('.$file->user->id.') ' . ' - ' . $file->user->name }}</strong>
                                                        </p>
                                                    </div>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-gray-500">No hay archivos para esta parte.</p>
                                        @endif

                                        @if ($vehiculo->operador_id != null)
                                            <x-button wire:click="requestModification('part', {{ $part->id }})"
                                                label="Solicitar Cambio" icon="o-pencil" class="btn-warning btn-sm"
                                                :disabled="$requests
                                                    ->where('type', 'part')
                                                    ->where('parte_id', $part->id)
                                                    ->isNotEmpty()" />
                                        @else
                                            <p class="text-sm text-gray-500">Seleccionar operador para solicitar cambio
                                            </p>
                                        @endif

                                    </x-slot:content>
                                </x-collapse>
                            @endforeach
                        </div>
                    @endforeach
                </x-card>
                {{-- Fin de la sección de partes --}}
            </div>
        </x-tab>

        {{-- Historial de Servicios --}}
        <x-tab name="services-tab" label="Historial de Servicios" icon="o-clock">
            <div class="mt-4">
                <x-card title="Servicios del Vehiculo">
                    @if ($serviceRecords->count())
                        <table class="table-auto w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Fecha de Realización</th>
                                    <th>Estatus</th>
                                    <th>Operador</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($serviceRecords as $record)
                                    <tr>
                                        <td>{{ $record->service->nombre }}</td>
                                        <td>{{ $record->fecha_realizacion ? $record->fecha_realizacion->format('d-m-Y') : 'Sin Realizar' }}
                                        </td>
                                        <td>{{ ucfirst($record->status) }}</td>
                                        <td>{{ $record->operador->name ?? 'N/A' }}</td>
                                        <td>
                                            <x-button wire:click="initService({{ $record->id }})" label="Ver"
                                                class="btn-primary btn-sm" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $serviceRecords->links() }}
                    @else
                        <p class="text-sm text-gray-500">No hay servicios registrados.</p>
                    @endif
                </x-card>
            </div>
        </x-tab>

        {{-- Historial de Operadores --}}
        <x-tab name="operators-tab" label="Historial de Operadores" icon="o-user-group">
            <div class="mt-4">
                <x-card title="Historial de Operadores">
                    <ul>
                        @foreach ($vehiculo->operatorHistory->sortByDesc('created_at') as $history)
                            <li>
                                Nombre: {{ $history->operador->name ?? 'N/A' }} -
                                Fecha Asignación: {{ $history->fecha_asignacion->format('d-m-Y') }}
                                @isset($history->fecha_liberacion)
                                    - Fecha Liberación: {{ $history->fecha_liberacion->format('d-m-Y') }}
                                @endisset
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            </div>
        </x-tab>

        <x-tab name="requests-tab" label="Solicitudes de Servicio" icon="o-inbox">
            <div class="mt-4">
                <x-card title="Solicitudes Pendientes">
                    @if ($vehiculo->serviceRequests->count())
                        <table class="table-auto w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Operador</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vehiculo->serviceRequests as $request)
                                    <tr>
                                        <td>{{ $request->service->nombre }}</td>
                                        <td>{{ $request->operador->name }}</td>
                                        <td>{{ ucfirst($request->status) }}</td>
                                        <td>
                                            @if ($request->status === 'pending')
                                                <x-button label="Aceptar" class="btn-success btn-sm"
                                                    wire:click="approveRequest({{ $request->id }})" />
                                                <x-button label="Rechazar" class="btn-error btn-sm"
                                                    wire:click="rejectRequest({{ $request->id }})" />
                                            @else
                                                <span class="text-gray-500">Procesado</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <p class="text-sm text-gray-500">No hay solicitudes pendientes.</p>
                    @endif
                </x-card>
            </div>
        </x-tab>
    </x-tabs>

    {{-- Modal para ver imagen en pantalla completa --}}
    <x-modal wire:model="viewImageModal" max-width="full" class="!max-w-full !h-screen !rounded-none p-0">
        <div class="relative w-full h-full flex items-center justify-center bg-black bg-opacity-90">
            @if ($viewImageModalUrl)
                <img src="{{ $viewImageModalUrl }}" alt="Vista Previa"
                    class="max-w-full max-h-full object-contain" />
            @endif
        </div>
    </x-modal>
</div>
