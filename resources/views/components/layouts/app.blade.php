<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/jaguar-removebg.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js">
    </script>
    @livewireStyles
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/photoswipe@5.4.3/dist/umd/photoswipe.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/photoswipe@5.4.3/dist/umd/photoswipe-lightbox.umd.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/photoswipe@5.4.3/dist/photoswipe.min.css" rel="stylesheet">
</head>

<body class="min-h-screen font-sans antialiased bg-base-200/50 dark:bg-base-200">

    <x-nav sticky full-width>

        <x-slot:brand>
            {{-- Brand --}}
            <div class="hidden sm:block">
                {{-- <img src="{{ asset('storage/jaguar-removebg.png') }}" alt="Logo"> --}}
                <h1 class="font-bold text-2xl">{{ config('app.name') }}</h1>
            </div>
        </x-slot:brand>

        {{-- Right side actions --}}
        <x-slot:actions>
            <x-theme-toggle class="btn btn-circle btn-ghost" />

            @if ($user = auth()->user())
                @if ($user->hasPermissionTo('view_menu_notifications'))
                    @livewire('notifications.v1.drawer')
                @endif
            @endif
            <label for="main-drawer" class="lg:hidden mr-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main full-width>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible collapse-text="Colapsar" right-mobile
            class="bg-white dark:bg-slate-800 md:bg-slate-400/10 md:dark:bg-slate-400/10">
            <div class="sm:hidden md:hidden lg:hidden">
                <h1 class="font-bold text-2xl p-2">{{ config('app.name') }}</h1>
            </div>
            {{-- MENU --}}
            <hr>
            <x-menu activate-by-route title="">
                {{-- User --}}
                @if ($user = auth()->user())
                    <x-list-item :item="$user" no-separator no-hover>
                        <x-slot:avatar>
                            @if ($user->avatar)
                                <x-avatar :image="asset('storage/' . $user->avatar)" class="!w-10" />
                            @else
                                <?php
                                $words = explode(' ', $user->name);
                                
                                $initials = '';
                                foreach ($words as $word) {
                                    $initials .= strtoupper($word[0]);
                                }
                                ?>
                                <x-avatar placeholder="{{ $initials }}" class="!w-10" />
                            @endif
                        </x-slot:avatar>
                        <x-slot:value>
                            {{ $user->name }}
                        </x-slot:value>
                        <x-slot:sub-value>
                            {{ $user->email }}
                        </x-slot:sub-value>
                        <x-slot:actions>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="btn-circle btn-ghost btn-xs" tooltip-left="Cerrar Sesión">
                                    <x-icon name="o-power" />
                                </button>
                            </form>
                        </x-slot:actions>
                    </x-list-item>

                @endif
                @guest
                    <x-menu-item title="Login" icon="o-lock-closed" link="{{ route('login') }}" />
                    {{-- <x-menu-item title="Register" icon="o-plus" link="{{ route('register') }}" /> --}}
                @endguest
            </x-menu>
            <hr>
            @if ($user = auth()->user())
                <x-menu activate-by-route title="">
                    <x-menu-item title="Inicio" icon="o-home" link="{{ route('dashboard') }}" />
                    {{-- @if ($user->hasPermissionTo('view_menu_settings'))
                        <x-menu-sub title="Settings" icon="o-cog-6-tooth">
                            <x-menu-item title="Archives" icon="o-archive-box" link="####" />
                        </x-menu-sub>
                    @endif --}}
                    @if ($user->hasPermissionTo('view_menu_vehicle'))
                        <x-menu-sub title="Vehículos" icon="o-truck">
                            <x-menu-item title="Catalogo" icon="o-truck" link="{{ route('vehiculos.index') }}" />
                            <x-menu-sub title="Partes" icon="o-clipboard-document-list">
                                <x-menu-item title="Catalogo" icon="o-clipboard-document-list" link="{{ route('partes.index') }}" />
                                <x-menu-item title="Categorias" icon="o-cube" link="{{ route('categorias.index') }}" />
                            </x-menu-sub    >
                        </x-menu-sub>
                    @endif
                    @if ($user->hasPermissionTo('view_menu_servicio'))
                        <x-menu-item title="Servicios" icon="o-wrench" link="{{ route('servicios.index') }}" />
                    @endif
                    @if ($user->hasRole('operador') && $user->vehiculo)
                        <x-menu-item title="Solicitar Servicio" icon="o-wrench" link="{{ route('servicios.create') }}" />
                    @endif
                    @if ($user->hasPermissionTo('view_menu_users'))
                        <x-menu-sub title="Users" icon="o-users">
                            @if ($user->hasPermissionTo('view_any_user'))
                                <x-menu-item title="Catálogo" icon="o-user-circle" link="{{ route('users.index') }}" />
                            @endif
                        </x-menu-sub>
                    @endif
                    @if ($user->hasPermissionTo('view_menu_security'))
                        <x-menu-sub title="Security" icon="o-lock-closed">
                            @if ($user->hasPermissionTo('view_any_role'))
                                <x-menu-item title="Roles" icon="o-user-circle" link="{{ route('roles.index') }}" />
                            @endif
                            @if ($user->hasPermissionTo('view_any_permission'))
                                <x-menu-item title="Permissions" icon="o-key"
                                    link="{{ route('permissions.index') }}" />
                            @endif
                        </x-menu-sub>
                    @endif
                    @if ($user->hasPermissionTo('view_menu_profile'))
                        <x-menu-item title="Perfil" icon="o-user" link="{{ route('profile.index') }}" />
                    @endif
                </x-menu>
                <hr>
                {{-- <x-menu title="">
                    <x-menu-item icon="o-magnifying-glass">
                        Buscar
                        <x-badge value="Cmd + G" class="text-sm" />
                    </x-menu-item>
                </x-menu> --}}
            @endif
        </x-slot:sidebar>

        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
    <x-spotlight />

    @livewireScripts
    @yield('js')
</body>

</html>
