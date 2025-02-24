<x-layouts.app :title="'Vehiculo - ' . $vehiculo->id">
    @livewire('vehiculos.v1.show', ['vehiculo' => $vehiculo])
</x-layouts.app>
