<div class="p-6 bg-base-100 shadow rounded-lg">
    {{-- Encabezado del Vehículo --}}
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}

        <h2 class="text-2xl font-bold text-center mb-6">Solicitar Servicio para {{ auth()->user()->vehiculo->nombre_unidad }}</h2>
    
        <x-form wire:submit.prevent="submitRequest">
            <x-choices 
                label="Selecciona un Servicio" 
                icon="o-cog" 
                :options="$availableServices" 
                wire:model="selectedService" 
                single 
            />
            
            <x-slot:actions>
                <x-button label="Solicitar Servicio" class="btn-primary" type="submit" />
            </x-slot:actions>
        </x-form>
    
        <h2 class="text-lg font-bold mt-8 mb-4">Mis Solicitudes</h2>
    
        @if($requests->count())
            <ul class="space-y-4">
                @foreach($requests as $request)
                    <li class="border p-4 rounded-lg">
                        <p><strong>Servicio:</strong> {{ $request->service->nombre }}</p>
                        <p><strong>Estatus:</strong> 
                            <span class="px-2 py-1 rounded-lg text-white {{ $request->status === 'accepted' ? 'bg-green-500' : ($request->status === 'rejected' ? 'bg-red-500' : 'bg-yellow-500') }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </p>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="text-sm text-gray-500">No tienes solicitudes pendientes.</p>
        @endif
</div>
