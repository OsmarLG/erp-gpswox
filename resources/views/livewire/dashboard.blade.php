<div class="p-6 bg-base-100 shadow rounded-lg">
    {{-- Título / Path (opcional) --}}
    {{ $path = implode(' / ', array_map('ucfirst', explode('/', request()->path()))) }}
    <h2 class="text-xl font-bold mb-4 text-center">Dashboard</h2>

    @if (auth()->user()->hasRole(['master', 'admin']))
        {{-- Stats en la parte superior --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-stat title="Total Users" :value="$totalUsers" icon="o-users" />
            <x-stat title="Total Roles" :value="$totalRoles" icon="o-lock-closed" />
            <x-stat title="Total Perms" :value="$totalPermissions" icon="o-shield-check" />
            <x-stat title="Users Today" :value="$usersTodayCount" icon="o-user-plus" />
        </div>

        <div class="h-6 mt-10"></div>

        {{-- Contenedor de 2 columnas --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Columna Izquierda: lista de usuarios de hoy --}}
            <div>
                <x-card title="Usuarios Registrados Hoy">
                    @if ($usersToday->count())
                        <ul class="divide-y divide-gray-200">
                            @foreach ($usersToday as $u)
                                <li class="py-2 flex items-center justify-between">
                                    {{-- Flex con avatar+nombre --}}
                                    <div class="flex items-center gap-2">
                                        @if ($u->avatar)
                                            <x-avatar :image="asset('storage/' . $u->avatar)" class="!w-10 !h-10" />
                                        @else
                                            @php
                                                $words = explode(' ', $u->name);
                                                $initials = collect($words)
                                                    ->map(fn($w) => strtoupper($w[0]))
                                                    ->implode('');
                                            @endphp
                                            <x-avatar placeholder="{{ $initials }}" class="!w-10 !h-10" />
                                        @endif

                                        <div>
                                            <strong>{{ $u->name }}</strong>
                                            <span class="text-gray-500 ml-1">{{ '@' . $u->username }}</span>
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-400">
                                        {{ $u->created_at->format('H:i') }}h
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-gray-500">Nadie se ha registrado hoy.</p>
                    @endif
                </x-card>
            </div>

            {{-- Columna Derecha: Chart + Botones --}}
            <div>
                <div class="flex gap-4 mb-4 justify-center md:justify-start">
                    {{-- Descomenta si quieres randomize
                <x-button
                    label="Randomize"
                    wire:click="randomize"
                    class="btn-primary"
                />
                --}}
                    <x-button label="Cambiar Tipo de Gráfico" wire:click="switch"
                        class="bg-blue-400 text-black dark:bg-slate-800 dark:text-white" />
                </div>

                <div class="max-w-md">
                    <x-chart wire:model="myChart" />
                </div>
            </div>
        </div>
    @else
    @endif
</div>
