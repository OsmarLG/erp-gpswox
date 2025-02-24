<div class="p-6 bg-base-100 shadow rounded-lg">
    {{-- Título / Path (opcional) --}}
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}
    <h2 class="text-xl font-bold mb-4 text-center">Dashboard</h2>

    @if (auth()->user()->hasRole(['master', 'admin']))
        {{-- Stats en la parte superior --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat title="Total Vehiculos" :value="$totalVehicles" icon="o-truck" />
            <x-stat title="Total Servicios" :value="$totalServices" icon="o-wrench" />
            <x-stat title="Total Usuarios" :value="$totalUsers" icon="o-users" />
            <x-stat title="Total Operadores" :value="$totalOperadores" icon="o-briefcase" />
            <x-stat title="Users Today" :value="$usersTodayCount" icon="o-user-plus" />
        </div>

        <div class="h-6 mt-10"></div>

        {{-- Contenedor de 2 columnas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Servicios Pendientes --}}
            <div class="mt-6">
                {{-- Stats en la parte superior --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <x-stat title="Total Servicios Pendientes" :value="$servicesPending->count()" icon="o-wrench" />
                </div>
                <x-card title="Servicios Pendientes">
                    @if ($servicesPending->count())
                        <table class="table-auto w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Vehículo</th>
                                    <th>Operador</th>
                                    <th>Fecha de Asignación</th>
                                    <th>Tiempo Atrasado</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($servicesPending as $service)
                                    <tr>
                                        <td>{{ $service->service->nombre }}</td>
                                        <td>{{ $service->vehicle->nombre_unidad ?? 'N/A' }}</td>
                                        <td>{{ $service->operador->name ?? 'N/A' }}</td>
                                        <td>{{ $service->created_at->format('d-m-Y') }}</td>
                                        <td>{{ $service->created_at->diffForHumans() }}</td>
                                        <td>{{ ucfirst($service->status) }}</td>
                                        <td>
                                            <x-button wire:click="initService({{ $service->id }})"
                                                label="Ver" class="btn-primary btn-sm" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $servicesPending->links() }}
                    @else
                        <p class="text-gray-500">No hay servicios pendientes.</p>
                    @endif
                </x-card>
            </div>

            {{-- Servicios Realizados --}}
            <div class="mt-6">
                {{-- Stats en la parte superior --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <x-stat title="Total Servicios Completados" :value="$servicesCompleted->count()" icon="o-wrench" />
                </div>
                <x-card title="Servicios Realizados">
                    @if ($servicesCompleted->count())
                        <table class="table-auto w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Vehículo</th>
                                    <th>Operador</th>
                                    <th>Fecha de Realización</th>
                                    <th>Fecha de Asignación</th>
                                    <th>Tiempo Atrasado</th>
                                    <th>Estatus</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($servicesCompleted as $service)
                                    <tr>
                                        <td>{{ $service->service->nombre }}</td>
                                        <td>{{ $service->vehicle->nombre_unidad ?? 'N/A' }}</td>
                                        <td>{{ $service->operador->name ?? 'N/A' }}</td>
                                        <td>{{ $service->fecha_realizacion ? $service->fecha_realizacion->format('d-m-Y') : 'Sin fecha' }}
                                        <td>{{ $service->created_at->format('d-m-Y') }}</td>
                                        </td>
                                        <td>{{ $service->created_at->diffForHumans() }}</td>
                                        <td>{{ ucfirst($service->status) }}</td>
                                        <td>
                                                <x-button wire:click="initService({{ $service->id }})"
                                                    label="Ver" class="btn-primary btn-sm" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $servicesCompleted->links() }}
                    @else
                        <p class="text-gray-500">No hay servicios realizados.</p>
                    @endif
                </x-card>
            </div>

            {{-- Columna Derecha: Chart + Botones --}}
            {{-- <div>
                <div class="flex gap-4 mb-4 justify-center md:justify-start">
                    <x-button label="Cambiar Tipo de Gráfico" wire:click="switch"
                        class="bg-blue-400 text-black dark:bg-slate-800 dark:text-white" />
                </div>

                <div class="max-w-md">
                    <x-chart wire:model="myChart" />
                </div>
            </div> --}}
        </div>
    @endif
    @if (auth()->user()->hasRole(['operador']))
        {{-- Información del vehículo asignado --}}
        @if (auth()->user()->vehiculo)
            <x-card title="Vehículo Asignado">
                <p><strong>Nombre Unidad:</strong> {{ auth()->user()->vehiculo->nombre_unidad }}</p>
                <p><strong>Placa:</strong> {{ auth()->user()->vehiculo->placa }}</p>
                <p><strong>Tipo/Marca:</strong> {{ auth()->user()->vehiculo->tipo_marca }}</p>
            </x-card>
        @else
            <p>No tienes un vehículo asignado.</p>
        @endif

        {{-- Servicios pendientes con paginación --}}
        {{-- Servicios Pendientes --}}
        <div class="mt-6">
            {{-- Stats en la parte superior --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-stat title="Total Servicios Pendientes" :value="$servicesPending->count()" icon="o-wrench" />
            </div>
            <x-card title="Servicios Pendientes">
                @if ($servicesPending->count())
                    <table class="table-auto w-full text-sm text-left">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Vehículo</th>
                                <th>Fecha de Asignación</th>
                                <th>Tiempo Atrasado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($servicesPending as $service)
                                <tr>
                                    <td>{{ $service->service->nombre }}</td>
                                    <td>{{ $service->vehicle->nombre_unidad ?? 'N/A' }}</td>
                                    <td>{{ $service->created_at->format('d-m-Y') }}</td>
                                    </td>
                                    <td>{{ $service->created_at->diffForHumans() }}</td>
                                    <td>
                                        @if($service->status === 'initiated')
                                            <x-button wire:click="initService({{ $service->id }})" icon="o-cog"
                                                label="Continuar" class="btn-primary btn-sm" />
                                        @else
                                            <x-button wire:click="initService({{ $service->id }})" icon="o-cog"
                                                label="Iniciar" class="btn-primary btn-sm" />
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $servicesPending->links() }}
                @else
                    <p class="text-gray-500">No hay servicios pendientes.</p>
                @endif
            </x-card>
        </div>

        {{-- Servicios Realizados --}}
        <div class="mt-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-stat title="Total Servicios Completados" :value="$servicesCompleted->count()" icon="o-wrench" />
            </div>
            <x-card title="Servicios Realizados">
                @if ($servicesCompleted->count())
                    <table class="table-auto w-full text-sm text-left">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Fecha Realización</th>
                                <th>Tiempo Transcurrido</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($servicesCompleted as $service)
                                <tr>
                                    <td>{{ $service->service->nombre }}</td>
                                    <td>{{ $service->fecha_realizacion ? $service->fecha_realizacion->format('d-m-Y') : 'Sin fecha' }}
                                    <td>{{ $service->created_at->diffForHumans($service->fecha_realizacion) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $servicesCompleted->links() }}
                @else
                    <p class="text-gray-500">No hay servicios realizados.</p>
                @endif
            </x-card>
        </div>
    @endif
</div>
