<x-layouts.app :title="'Vehiculo - ' . $vehiculo->nombre_unidad">
    @livewire('vehiculos.v1.show', ['vehiculo' => $vehiculo])
</x-layouts.app>
