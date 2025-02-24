<x-layouts.app :title="'Usuario - ' . $user->id">
    @livewire('user.v1.show', ['user' => $user])
</x-layouts.app>
