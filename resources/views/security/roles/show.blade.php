<x-layouts.app :title="'Roles - ' . $role->name">
    {{-- @livewire('security.v1.roles') --}}
    @livewire('security.v2.roles.show', ['role' => $role])
</x-layouts.app>
