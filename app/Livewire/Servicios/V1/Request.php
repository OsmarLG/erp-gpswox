<?php

namespace App\Livewire\Servicios\V1;

use App\Models\VehicleRequest;
use App\Models\File;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Mary\Traits\Toast;

class Request extends Component
{
    use WithFileUploads;
    use Toast;

    public $idRequest;
    public $request;

    // Para 'field'
    public $fieldValue;

    // Para 'part'
    public $files = [];
    public $fileDescriptions = []; // Descripciones opcionales

    public function mount($request)
    {
        $this->idRequest = $request;
        $this->request = VehicleRequest::findOrFail($this->idRequest);

        // Validar permisos
        $usuario = Auth::user();
        if (
            !$usuario->hasRole(['master','admin']) &&
            $this->request->operador_id !== $usuario->id
        ) {
            abort(403, 'No tienes permiso para ver esta solicitud.');
        }

        // Si es 'field', inicia fieldValue vacío
        if ($this->request->type === 'field') {
            $this->fieldValue = '';
        }
    }

    /**
     * Para type=field: el operador actualiza un campo de la tabla vehicles
     * y la solicitud pasa a 'finished'
     */
    public function actualizarField()
    {
        if (!Auth::user()->hasRole('operador')) {
            return;
        }

        $this->validate([
            'fieldValue' => 'required|string',
        ]);

        if ($this->request->type === 'field') {
            // Actualiza el campo real en vehicles
            $this->request->vehicle->update([
                $this->request->field => $this->fieldValue,
            ]);

            // Cambia status a 'finished'
            $this->request->update(['status' => 'finished']);

            $this->success('Información actualizada correctamente.');
            return redirect()->route('dashboard');
        }
    }

    /**
     * Para type=part: el operador sube archivos (imágenes/videos) con descripción opcional
     */
    public function subirArchivos()
    {
        if (!Auth::user()->hasRole('operador')) {
            return;
        }

        $this->validate([
            'files.*' => 'required|file|mimes:jpg,jpeg,png,mp4|max:20480',
        ]);

        if ($this->request->type === 'part') {
            foreach ($this->files as $index => $file) {
                $path = $file->store('vehicle_request_files', 'public');

                File::create([
                    'model_type'  => VehicleRequest::class,
                    'model_id'    => $this->request->id,
                    'path'        => $path,
                    'type'        => $file->getClientOriginalExtension(),
                    'operador_id' => Auth::id(),
                    'description' => $this->fileDescriptions[$index] ?? null,
                ]);
            }

            $this->request->update(['status' => 'finished']);

            $this->reset('files', 'fileDescriptions');
            $this->success('Archivos subidos correctamente.');
        }
    }

    /**
     * El admin/master finaliza => status='accepted'
     */
    public function finalizarSolicitud()
    {
        if (Auth::user()->hasRole(['master','admin'])) {
            $this->request->update(['status' => 'accepted']);
            $this->success('Solicitud finalizada con éxito!');
        }
        return redirect()->route('vehiculos.show', $this->request->vehicle_id);
    }

    public function marcarTerminado()
    {
        if (Auth::user()->hasRole(['operador']) && $this->request->status === 'initiated') {
            $this->request->update(['status' => 'finished']);
            $this->success('Solicitud marcada como terminada.');
        }
    }

    /**
     * El admin/master reabre => status='initiated' si estaba 'finished'
     */
    public function reabrirSolicitud()
    {
        if (
            Auth::user()->hasRole(['master','admin']) &&
            $this->request->status === 'finished'
        ) {
            $this->request->update(['status' => 'initiated']);
            $this->success('Solicitud reabierta correctamente.');
        }
    }

    /**
     * Eliminar un archivo persistido (admin/master o dueño del archivo)
     */
    public function eliminarArchivo($fileId)
    {
        $file = File::findOrFail($fileId);

        // Permitir si es admin/master o si el file->operador_id == current user
        if (
            Auth::user()->hasRole(['master','admin']) ||
            $file->operador_id === Auth::id()
        ) {
            $file->delete();
            $this->success('Archivo eliminado correctamente.');
        }
    }

    public function render()
    {
        // Archivos ya guardados en la BD
        $persistedFiles = File::where('model_type', VehicleRequest::class)
            ->where('model_id', $this->request->id)
            ->get();

        return view('livewire.servicios.v1.request', [
            'request'        => $this->request,
            'persistedFiles' => $persistedFiles,
        ]);
    }
}
