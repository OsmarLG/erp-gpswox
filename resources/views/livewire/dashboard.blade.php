<div class="p-6 bg-base-100 shadow rounded-lg">
    {{-- Título / Path (opcional) --}}
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}
    <h2 class="text-xl font-bold mb-4 text-center">Dashboard</h2>

    {{-- ADMIN --}}
    @if (auth()->user()->hasRole(['master', 'admin']))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat title="Total Vehiculos" :value="$totalVehicles" icon="o-truck" />
            <x-stat title="Total Servicios" :value="$totalServices" icon="o-wrench" />
            <x-stat title="Total Usuarios" :value="$totalUsers" icon="o-users" />
            <x-stat title="Total Operadores" :value="$totalOperadores" icon="o-briefcase" />
            {{-- <x-stat title="Users Today" :value="$usersTodayCount" icon="o-user-plus" /> --}}
        </div>

        <div class="h-6 mt-10"></div>

        {{-- <x-card title="Servicios Próximos por Kilómetros o Días">
            @if ($upcomingServices->count())
                <div class="overflow-x-auto">
                    <table class="table-auto w-full text-sm text-left">
                        <thead>
                            <tr>
                                <th>Vehículo</th>
                                <th>Operador</th>
                                <th>Servicio</th>
                                <th>Faltan (KM)</th>
                                <th>Faltan (Días)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($upcomingServices as $item)
                                <tr>
                                    <td>{{ $item['vehiculo']->nombre_unidad ?? 'N/A' }}</td>
                                    <td>{{ $item['operador']->name ?? 'N/A' }}</td>
                                    <td>{{ $item['servicio']->nombre }}</td>
                                    <td>{{ $item['faltan_km'] ?? 'N/A' }}</td>
                                    <td>{{ $item['faltan_dias'] ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-500">No hay servicios próximos por vencimiento.</p>
            @endif
        </x-card>         --}}

        <x-card title="Servicios Próximos por Kilómetros o Días">
            @if ($upcomingServices->count())
                <div class="overflow-x-auto">
                    @foreach (['VENCIDO', 'URGENTE', 'PROXIMO'] as $estado)
                        @if ($upcomingServices->has($estado))
                            <h3 class="text-lg font-bold mt-6 mb-2 text-{{ $estado === 'VENCIDO' ? 'red' : ($estado === 'URGENTE' ? 'yellow' : 'blue') }}-600">
                                {{ ucfirst(strtolower($estado)) }}
                            </h3>
                            <table class="table-auto w-full text-sm text-left mb-4">
                                <thead>
                                    <tr>
                                        <th>Vehículo</th>
                                        <th>Operador</th>
                                        <th>Servicio</th>
                                        <th>KMs</th>
                                        <th>Días</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($upcomingServices[$estado] as $item)
                                        <tr>
                                            <td>{{ $item['vehiculo']->nombre_unidad ?? 'N/A' }}</td>
                                            <td>{{ $item['operador']->name ?? 'N/A' }}</td>
                                            <td>{{ $item['servicio']->nombre }}</td>
                                            <td class="w-72">
                                                <div class="mb-1 text-xs text-gray-600">
                                                    {{ $item['km_transcurridos'] }} / {{ $item['servicio']->periodicidad_km ?? 'N/A' }} km
                                                    ({{ $item['porcentaje_km'] }}%)
                                                </div>
                                                <div class="w-full bg-gray-200 rounded h-2">
                                                    <div class="h-2 rounded {{ $item['porcentaje_km'] >= 100 ? 'bg-red-500' : 'bg-blue-500' }}"
                                                         style="width: {{ $item['porcentaje_km'] }}%"></div>
                                                </div>
                                                <div class="mt-1">
                                                    <small class="font-semibold">
                                                        @if ($item['estado_km'] === 'VENCIDO')
                                                            <span class="text-red-600">VENCIDO</span>
                                                        @elseif ($item['estado_km'] === 'URGENTE')
                                                            <span class="text-yellow-600">URGENTE</span>
                                                        @elseif ($item['estado_km'] === 'PROXIMO')
                                                            <span class="text-blue-600">PRÓXIMO</span>
                                                        @endif
                                                    </small>
                                                    <br>
                                                    <small>{{ $item['faltan_km'] }} km restantes</small>
                                                </div>
                                            </td>
                                            <td class="w-72">
                                                @if ($item['porcentaje_dias'] !== null)
                                                    <div class="mb-1 text-xs text-gray-600">
                                                        {{ round($item['dias_transcurridos']) }} / {{ $item['servicio']->periodicidad_dias }} días
                                                        ({{ $item['porcentaje_dias'] }}%)
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded h-2">
                                                        <div class="h-2 rounded {{ $item['porcentaje_dias'] >= 100 ? 'bg-red-500' : 'bg-green-500' }}"
                                                             style="width: {{ $item['porcentaje_dias'] }}%"></div>
                                                    </div>
                                                    <div class="mt-1">
                                                        <small class="font-semibold">
                                                            @if ($item['estado_dias'] === 'VENCIDO')
                                                                <span class="text-red-600">VENCIDO</span>
                                                            @elseif ($item['estado_dias'] === 'URGENTE')
                                                                <span class="text-yellow-600">URGENTE</span>
                                                            @elseif ($item['estado_dias'] === 'PROXIMO')
                                                                <span class="text-green-600">PRÓXIMO</span>
                                                            @endif
                                                        </small>
                                                        <br>
                                                        <small>{{ $item['faltan_dias'] }} días restantes</small>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 italic">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    @endforeach
                </div>
            @else
                <p class="text-gray-500">No hay servicios próximos por vencimiento.</p>
            @endif
        </x-card>                  

        <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- <x-card title="Solicitudes Globales de Información General">
                @if ($globalVehicleRequestsField->count())
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Campo</th>
                                    <th>Vehículo</th>
                                    <th>Operador</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($globalVehicleRequestsField as $request)
                                    <tr>
                                        <td>{{ ucfirst(str_replace('_', ' ', $request->field)) }}</td>
                                        <td>{{ $request->vehicle->nombre_unidad ?? 'N/A' }}</td>
                                        <td>{{ $request->operador->name ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($request->status) }}</td>
                                        <td>
                                            @if ($request->status === 'initiated' || $request->status === 'pending')
                                                <x-button wire:click="showRequest2({{ $request->id }})" icon="o-cog"
                                                    label="Ver" class="btn-primary btn-sm" />
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $globalVehicleRequestsField->links() }}
                @else
                    <p class="text-sm text-gray-500">No hay solicitudes de información general.</p>
                @endif
            </x-card> --}}

            <x-card title="Solicitudes de Evidencia">
                @if ($globalVehicleRequestsPart->count())
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Parte</th>
                                    <th>Vehículo</th>
                                    <th>Operador</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($globalVehicleRequestsPart as $request)
                                    <tr>
                                        <td>{{ $request->parte->nombre ?? 'N/A' }}</td>
                                        <td>{{ $request->vehicle->nombre_unidad ?? 'N/A' }}</td>
                                        <td>{{ $request->operador->name ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($request->status) }}</td>
                                        <td>
                                            @if ($request->status === 'initiated' || $request->status === 'pending')
                                                <x-button wire:click="showRequest2({{ $request->id }})" icon="o-cog"
                                                    label="Ver" class="btn-primary btn-sm" />
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $globalVehicleRequestsPart->links() }}
                @else
                    <p class="text-sm text-gray-500">No hay solicitudes de partes de vehículos.</p>
                @endif
            </x-card>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <x-stat title="Total Servicios Pendientes" :value="$servicesPending->count()" icon="o-wrench" />
                </div>
                <x-card title="Servicios Pendientes">
                    @if ($servicesPending->count())
                        <div class="overflow-x-auto">
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
                                                <x-button wire:click="initService({{ $service->id }})" label="Ver"
                                                    class="btn-primary btn-sm whitespace-nowrap" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $servicesPending->links() }}
                    @else
                        <p class="text-gray-500">No hay servicios pendientes.</p>
                    @endif
                </x-card>
            </div>

            <div class="mt-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <x-stat title="Total Servicios Completados" :value="$servicesCompleted->count()" icon="o-wrench" />
                </div>
                <x-card title="Servicios Realizados">
                    @if ($servicesCompleted->count())
                        <div class="overflow-x-auto">
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
                                            </td>
                                            <td>{{ $service->created_at->format('d-m-Y') }}</td>
                                            <td>{{ $service->created_at->diffForHumans() }}</td>
                                            <td>{{ ucfirst($service->status) }}</td>
                                            <td>
                                                <x-button wire:click="initService({{ $service->id }})" label="Ver"
                                                    class="btn-primary btn-sm whitespace-nowrap" />
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $servicesCompleted->links() }}
                    @else
                        <p class="text-gray-500">No hay servicios realizados.</p>
                    @endif
                </x-card>
            </div>
        </div>
    @endif

    {{-- OPERADOR --}}
    @if (auth()->user()->hasRole(['operador']))
        @if (auth()->user()->vehiculo)
            {{-- <x-card title="Vehículo Asignado">
                <p><strong>Nombre Unidad:</strong> {{ auth()->user()->vehiculo->nombre_unidad }}</p>
                <p><strong>Placa:</strong> {{ auth()->user()->vehiculo->placa }}</p>
                <p><strong>Tipo/Marca:</strong> {{ auth()->user()->vehiculo->tipo_marca }}</p>
            </x-card> --}}
        @else
            <p>No tienes un vehículo asignado.</p>
        @endif

        <div class="mt-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-stat title="Total Servicios Pendientes" :value="$servicesPending->count()" icon="o-wrench" />
                <x-stat title="Total Peticiones de Vehiculo Pendientes" :value="$vehicleRequestsPending->count()" icon="o-wrench" />
            </div>
            <x-card title="Servicios Pendientes">
                @if ($servicesPending->count())
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm text-left whitespace-nowrap">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Servicio</th>
                                    <th>Vehículo</th>
                                    <th>Fecha de Asignación</th>
                                    <th>Tiempo Atrasado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($servicesPending as $service)
                                    <tr>
                                        <td>
                                            @if ($service->status === 'initiated')
                                                <x-button wire:click="initService({{ $service->id }})"
                                                    label="Continuar" class="btn-primary btn-sm whitespace-nowrap" />
                                            @else
                                                <x-button wire:click="initService({{ $service->id }})" label="Iniciar"
                                                    class="btn-primary btn-sm whitespace-nowrap" />
                                            @endif
                                        </td>
                                        <td>{{ $service->service->nombre }}</td>
                                        <td>{{ $service->vehicle->nombre_unidad ?? 'N/A' }}</td>
                                        <td>{{ $service->created_at->format('d-m-Y') }}</td>
                                        <td>{{ $service->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $servicesPending->links() }}
                @else
                    <p class="text-gray-500">No hay servicios pendientes.</p>
                @endif
            </x-card>

            <x-card title="Peticiones de Evidencia">
                @if ($vehicleRequestsPending->count())
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm text-left whitespace-nowrap ">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Tipo</th>
                                    <th>Evidencia</th>
                                    <th>Tiempo Atrasado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($vehicleRequestsPending as $service)
                                    <tr>
                                        <td>
                                            @if ($service->status === 'initiated')
                                                <x-button wire:click="showRequest({{ $service->id }})"
                                                    label="Continuar" class="btn-primary btn-sm whitespace-nowrap" />
                                            @else
                                                <x-button wire:click="initRequest({{ $service->id }})" label="Iniciar"
                                                    class="btn-primary btn-sm whitespace-nowrap" />
                                            @endif
                                        </td>
                                        <td>{{ $service->type === 'field' ? 'Campo' : 'Parte de Vehículo' }}</td>
                                        <td>{{ $service->type === 'field' ? ucfirst($service->field) : ucfirst($service->parte->nombre) }}
                                        </td>
                                        <td>{{ $service->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $servicesPending->links() }}
                @else
                    <p class="text-gray-500">No hay peticiones de vehiculos pendientes.</p>
                @endif
            </x-card>
        </div>

        <div class="mt-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <x-stat title="Total Servicios Completados" :value="$servicesCompleted->count()" icon="o-wrench" />
            </div>
            <x-card title="Servicios Realizados">
                @if ($servicesCompleted->count())
                    <div class="overflow-x-auto">
                        <table class="table-auto w-full text-sm text-left whitespace-nowrap">
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
                                        </td>
                                        <td>{{ $service->created_at->diffForHumans($service->fecha_realizacion) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $servicesCompleted->links() }}
                @else
                    <p class="text-gray-500">No hay servicios realizados.</p>
                @endif
            </x-card>
        </div>
    @endif
</div>
