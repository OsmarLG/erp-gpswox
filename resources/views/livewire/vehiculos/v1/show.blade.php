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
            <div class="mt-4">
                <x-card title="Información General">
                    <p><strong>Nombre:</strong> {{ $vehiculo->nombre_unidad }}</p>
                    <p><strong>Placa:</strong> {{ $vehiculo->placa }}</p>
                    <p><strong>Tipo/Marca:</strong> {{ $vehiculo->tipo_marca }}</p>
                    <p><strong>VIN:</strong> {{ $vehiculo->vin }}</p>
                    <p><strong>TAG Gasolina ID:</strong> {{ $vehiculo->tag_gasolina_id }}</p>
                    <p><strong>Fecha de Registro:</strong> {{ $vehiculo->created_at }}</p>
                    <br>
                    <p><strong>Telefono Seguro:</strong> {{ $vehiculo->telefono_seguro }}</p>
                    <p><strong>Operador:</strong> {{ $vehiculo->operador ? $vehiculo->operador->name : 'No asignado' }}</p>
                    <p><strong>ID GPS WOX:</strong> {{ $vehiculo->gpswox_id }}</p>
                    <p><strong>Odómetro:</strong>
                        {{ $odometerValue ? number_format($odometerValue, 2) . ' km' : 'No disponible' }}</p>
                </x-card>
                <x-card title="Información de Documentación">
                    <p><strong>No. Tarjeta de Circulación:</strong> {{ $vehiculo->no_tarjeta_circulacion }}</p>
                    <p><strong>Vigencia:</strong> {{ $vehiculo->vigencia_tarjeta }}</p>
                    <p><strong>No. Tag:</strong> {{ $vehiculo->tag_numero }}</p>
                    <p><strong>Verificación Vencimiento:</strong> {{ $vehiculo->verificacion_vencimiento }}</p>
                    <p><strong>Poliza No:</strong> {{ $vehiculo->poliza_no }}</p>
                    <p><strong>Poliza Vigencia:</strong> {{ $vehiculo->poliza_vigencia }}</p>
                    <p><strong>Poliza Costo:</strong> {{ $vehiculo->costo_poliza }}</p>
                </x-card>
                <x-card title="Información de Partes">
                    <p><strong>Fecha de Bateria:</strong> {{ $vehiculo->fecha_bateria }}</p>
                    <p><strong>Rines Medida:</strong> {{ $vehiculo->rines_medida }}</p>
                    <p><strong>Medida de Llantas:</strong> {{ $vehiculo->medida_llantas }}</p>
                </x-card>
            </div>
        </x-tab>
        
        <x-tab name="partes-tab" label="Partes del Vehículo" icon="o-truck">
            <div class="mt-4">
                <x-card title="Partes del Vehículo">
                    <p>Pendiente</p>
                </x-card>
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
                                            <x-button wire:click="initService({{ $record->id }})"
                                                label="Ver" class="btn-primary btn-sm" />
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
                                            @if($request->status === 'pending')
                                                <x-button label="Aceptar" class="btn-success btn-sm" wire:click="approveRequest({{ $request->id }})" />
                                                <x-button label="Rechazar" class="btn-error btn-sm" wire:click="rejectRequest({{ $request->id }})" />
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
                <img src="{{ $viewImageModalUrl }}" alt="Vista Previa" class="max-w-full max-h-full object-contain" />
            @endif
        </div>
    </x-modal>
</div>
