<div class="p-6 bg-base-100 shadow rounded-lg">
    {{-- Encabezado del Vehículo --}}
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

    <h2 class="text-2xl font-bold text-center mb-6">Detalles de {{ $vehiculo->nombre_unidad }}</h2>
    <h4 class="text-2xl font-bold text-center mb-6">Odometro:
        {{ $odometerValue ? number_format($odometerValue, 0, '.', ',') . ' Km' : 'N/A' }}</h4>

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
                {{-- Solicitud de modificación --}}
                {{-- <x-card title="Solicitar Modificación de Información General">
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

                        <x-button wire:click="requestModification('field', '')"
                            label="Solicitar Cambio" class="btn-warning" />
                    </div>
                </x-card> --}}

                {{-- Solicitudes pendientes --}}
                {{-- <x-card title="Solicitudes Pendientes de Información General">
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
                                            @if (in_array($request->status, ['initiated', 'pending']))
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
                </x-card> --}}

                {{-- Campos editables por categoría --}}
                <x-card title="Información General">
                    <x-input label="Nombre Unidad" wire:model.defer="nombre_unidad" />
                    <x-input label="Placa" wire:model.defer="placa" />
                    <x-input label="Tipo Marca" wire:model.defer="tipo_marca" />
                    <x-input label="VIN" wire:model.defer="vin" />
                    <x-input label="Teléfono Seguro" wire:model.defer="telefono_seguro" />
                    <br>
                    <x-checkbox label="Recibir Datos de GPSWOX" wire:model.defer="get_datos_gpswox" />
                </x-card>

                <x-card title="Información Tarjeta y TAG">
                    <x-input label="No. Tarjeta de Circulación" wire:model.defer="no_tarjeta_circulacion" />
                    <x-datetime label="Vigencia Tarjeta" wire:model.defer="vigencia_tarjeta" />
                    <x-input label="Tag Número" wire:model.defer="tag_numero" />
                    <x-input label="TAG Gasolina ID" wire:model.defer="tag_gasolina_id" />
                </x-card>

                <x-card title="Información Verificación y Batería">
                    <x-datetime label="Verificación Vencimiento" wire:model.defer="verificacion_vencimiento" />
                    <x-datetime label="Fecha Batería" wire:model.defer="fecha_bateria" />
                </x-card>

                <x-card title="Información Llantas y Rines">
                    <x-input label="Rines Medida" wire:model.defer="rines_medida" />
                    <x-input label="Medida Llantas" wire:model.defer="medida_llantas" />
                </x-card>

                <x-card title="Información Póliza">
                    <x-input label="Póliza No." wire:model.defer="poliza_no" />
                    <x-input label="Compañía Seguros" wire:model.defer="compania_seguros" />
                    <x-datetime label="Vigencia Póliza" wire:model.defer="poliza_vigencia" />
                    <x-input label="Costo Póliza" wire:model.defer="costo_poliza" prefix="MXN" money />
                </x-card>

                <x-card title="Información GPSWOX">
                    <x-input label="ID GPSWOX" wire:model.defer="gpswox_id" />
                </x-card>

                <x-card title="GPS 1">
                    <x-input label="ID GPS 1" wire:model.defer="id_gps1" />
                    <x-input label="Teléfono GPS 1" wire:model.defer="tel_gps1" />
                    <x-input label="IMEI GPS 1" wire:model.defer="imei_gps1" />
                    <x-datetime label="Vigencia GPS 1" wire:model.defer="vigencia_gps1" />
                    <x-input label="Saldo GPS 1" wire:model.defer="saldo_gps1" />
                </x-card>

                <x-card title="GPS 2">
                    <x-input label="ID GPS 2" wire:model.defer="id_gps2" />
                    <x-input label="Teléfono GPS 2" wire:model.defer="tel_gps2" />
                    <x-input label="IMEI GPS 2" wire:model.defer="imei_gps2" />
                    <x-datetime label="Vigencia GPS 2" wire:model.defer="vigencia_gps2" />
                    <x-input label="Saldo GPS 2" wire:model.defer="saldo_gps2" />
                </x-card>
            </div>

            @if (auth()->user()->hasRole(['master', 'admin']))
                <div class="col-span-2 flex justify-end">
                    <x-button wire:click="saveVehicleFields" label="Guardar Cambios" class="btn-success mt-4"
                        icon="o-check-circle" />
                </div>
            @endif
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
                                    <th>Notas</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($requests->where('type', 'part') as $request)
                                    <tr>
                                        <td>{{ $request->parte->nombre ?? 'N/A' }}</td>
                                        <td>{{ $request->operador->name }}</td>
                                        <td>{{ $request->notas_admin ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($request->status) }}</td>
                                        <td>
                                            @if ($request->status === 'initiated' || $request->status === 'pending')
                                                <x-button wire:click="showRequest({{ $request->id }})"
                                                    icon="o-cog" label="Ver" class="btn-primary btn-sm" />

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
                                        @if ($part->archivos->isNotEmpty())
                                            <ul>
                                                @foreach ($part->archivos as $file)
                                                    @php
                                                        $extension = pathinfo($file->path, PATHINFO_EXTENSION);
                                                    @endphp

                                                    <div class="border p-4 rounded-lg">
                                                        @if (in_array($extension, ['jpg', 'jpeg', 'png']))
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
                                                            <strong>
                                                                @if ($file->operador)
                                                                    ({{ $file->operador->id }})
                                                                    - {{ $file->operador->name }}
                                                                @else
                                                                    Subido por el administrador del sistema
                                                                @endif
                                                            </strong>
                                                        </p>
                                                        @if ($file->description)
                                                            <p class="text-sm text-gray-700 mt-2">
                                                                {{ 'Descripción: ' . $file->description . '- Fecha: ' . $file->created_at }}
                                                            </p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="text-gray-500">No hay archivos para esta parte.</p>
                                        @endif

                                        <div class="mt-4">
                                            <x-file wire:model="files" label="Tomar Evidencia" accept="image/*"
                                                capture="environment" crop-after-change>
                                                <div
                                                    class="w-60 h-60 bg-black flex items-center justify-center rounded-lg">
                                                    <img src="{{ $files ? $files->temporaryUrl() : asset('storage/picture.png') }}"
                                                        class="w-full h-full object-cover rounded-lg" />
                                                </div>
                                            </x-file>
                                            <x-button wire:click="uploadEvidence({{ $part->id }})"
                                                label="Subir Evidencia" class="btn-primary mt-2" />
                                        </div>

                                        <br>

                                        @if ($vehiculo->operador_id != null)
                                            <x-button wire:click="requestModification('part', {{ $part->id }})"
                                                label="Solicitar Evidencia" icon="o-pencil"
                                                class="btn-warning btn-sm" :disabled="$requests
                                                    ->where('type', 'part')
                                                    ->where('parte_id', $part->id)
                                                    ->isNotEmpty()" />

                                            <x-input wire:model="notas_admin" wire:ignore label="Notas"
                                                placeholder="Notas" />
                                        @else
                                            <p class="text-sm text-gray-500">Seleccionar operador para solicitar
                                                evidencia</p>
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
                                    <th>Kilometraje</th>
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
                                        <td>
                                            {{ $record->valor_kilometraje !== null ? number_format($record->valor_kilometraje, 0, '.', ',') . ' Km' : 'N/A' }}
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
                @if ($vehiculo->operador_id != null)
                    <x-card title="Registrar Solicitud De Servicio" class="mb-6">
                        <form wire:submit.prevent="storeSolicitudServiceRecord"
                            class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            {{-- Seleccionar servicio --}}
                            <div>
                                <label for="service_id"
                                    class="block text-sm font-medium text-gray-700 mb-1">Servicio</label>
                                <select id="service_id" wire:model.defer="service_id"
                                    class="form-select rounded-lg border-gray-300 shadow-sm w-full">
                                    <option value="">Selecciona un servicio</option>
                                    @foreach ($availableServices as $service)
                                        <option value="{{ $service->id }}">{{ $service->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-input wire:model="notas_admin" wire:ignore label="Notas" placeholder="Notas" />
                            <br>
                            {{-- Botón de guardar --}}
                            <div class="flex items-end">
                                <x-button type="submit" label="Guardar Solicitud" class="btn-success w-full"
                                    icon="o-check-circle" />
                            </div>
                        </form>
                    </x-card>
                @endif
                <x-card title="Registrar Servicio Realizado" class="mb-6">
                    <form wire:submit.prevent="storeServiceRecord" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        {{-- Seleccionar servicio --}}
                        <div>
                            <label for="service_id"
                                class="block text-sm font-medium text-gray-700 mb-1">Servicio</label>
                            <select id="service_id" wire:model.defer="service_id"
                                class="form-select rounded-lg border-gray-300 shadow-sm w-full">
                                <option value="">Selecciona un servicio</option>
                                @foreach ($availableServices as $service)
                                    <option value="{{ $service->id }}">{{ $service->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kilometraje actual --}}
                        <div>
                            <label for="last_km" class="block text-sm font-medium text-gray-700 mb-1">Kilometraje
                                del Servicio</label>
                            <input type="number" id="last_km" wire:model.defer="last_km"
                                class="form-input rounded-lg border-gray-300 shadow-sm w-full"
                                placeholder="Ej. 154500">
                        </div>
                        <br>
                        {{-- Fecha de realización --}}
                        <div>
                            <label for="fecha_realizacion_servicio"
                                class="block text-sm font-medium text-gray-700 mb-1">Fecha de
                                Realización</label>
                            <x-datetime wire:model.defer="fecha_realizacion_servicio" />
                        </div>
                        <br>
                        {{-- Botón de guardar --}}
                        <div class="flex items-end">
                            <x-button type="submit" label="Guardar Servicio" class="btn-success w-full"
                                icon="o-check-circle" />
                        </div>
                    </form>
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
                                <p>
                                    <strong>Nombre:</strong> {{ $history->operador->name ?? 'N/A' }}
                                </p>
                                <p>
                                    <strong>Fecha Asignación:</strong>
                                    {{ optional($history->fecha_asignacion)->format('d-m-Y') }}
                                </p>
                                @isset($history->fecha_liberacion)
                                    <p>
                                        <strong>Fecha Liberación:</strong>
                                        {{ optional($history->fecha_liberacion)->format('d-m-Y') }}
                                    </p>
                                @endisset
                            </li>
                        @endforeach
                    </ul>
                </x-card>
            </div>
        </x-tab>

        <x-tab name="requests-tab" label="Solicitudes de Servicio" icon="o-inbox">
            <div class="mt-4 overflow-x-auto">
                <x-card title="Solicitudes Pendientes">
                    @if ($vehiculo->serviceRequests->count())
                        <table class="min-w-full text-sm text-left table-auto">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Operador</th>
                                    <th>Notas</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vehiculo->serviceRequests as $request)
                                    <tr>
                                        <td>{{ optional($request->service)->nombre ?? 'N/A' }}</td>
                                        <td>{{ optional($request->operador)->name ?? 'N/A' }}</td>
                                        <td>{{ $request->notas_operador ?? 'N/A' }}</td>
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

                        {{-- Paginación si aplica --}}
                        {{-- {{ $vehiculo->serviceRequests->links() }} --}}
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
