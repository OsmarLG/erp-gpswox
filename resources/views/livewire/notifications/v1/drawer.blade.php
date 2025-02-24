<div class="relative">
    <!-- Botón para abrir/cerrar el drawer -->
    <x-button label="Notificaciones ({{ count($notifications) }})" icon="o-bell" class="btn-ghost btn-sm"
        wire:click="toggleDrawer" />

    <!-- Drawer: Solo se muestra si $showDrawer es true -->
    @if ($showDrawer)
        <div class="absolute top-12 right-0 w-80 bg-white dark:bg-slate-900 shadow-lg rounded-lg p-4"
            wire:click.away="$set('showDrawer', false)">
            <h2 class="text-lg font-bold">Notificaciones</h2>

            @if (!$notifications->isEmpty())
                <button class="text-blue-500 text-sm underline" wire:click="markAllAsRead">
                    Marcar todas como leídas
                </button>
            @endif

            <!-- Contenedor scrollable -->
            <div class="max-h-96 overflow-y-auto">
                @if ($notifications->isEmpty())
                    <p class="text-gray-500">No tienes notificaciones.</p>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach ($notifications as $notification)
                            <li class="py-2 flex justify-between items-center">
                                <div>
                                    <strong>{{ $notification->data['service']['nombre'] ?? 'Notificación' }}</strong>
                                    <p class="text-sm text-gray-600">{{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                                <div class="flex space-x-2">
                                    <button class="text-blue-500 text-sm"
                                        wire:click="viewNotification('{{ $notification->id }}')">Ver</button>
                                    <button class="text-green-500 text-sm"
                                        wire:click="markAsRead('{{ $notification->id }}')">✓</button>
                                    <button class="text-red-500 text-sm"
                                        wire:click="deleteNotification('{{ $notification->id }}')">✗</button>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endif

    @if ($selectedNotification)
        <div class="absolute top-16 right-0 w-96 bg-white dark:bg-slate-900 shadow-lg rounded-lg p-4">
            <h2 class="text-lg font-bold">Detalle de Notificación</h2>
            <p><strong>Unidad:</strong> {{ $selectedNotification->data['vehicle']['nombre_unidad'] ?? 'N/A' }}</p>
            <p><strong>Servicio:</strong> {{ $selectedNotification->data['service']['nombre'] ?? 'N/A' }}</p>
            <p><strong>Observaciones:</strong> {{ $selectedNotification->data['service']['observaciones'] ?? 'N/A' }}
            </p>
            <p class="text-sm text-gray-500">{{ $selectedNotification->created_at->diffForHumans() }}</p>
            <button class="mt-2 text-gray-200 bg-black rounded-lg p-2 dark:text-white text-sm"
                wire:click="closeNotification">Cerrar</button>
        </div>
    @endif
</div>
