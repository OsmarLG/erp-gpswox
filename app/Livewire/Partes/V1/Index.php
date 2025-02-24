<?php

namespace App\Livewire\Partes\V1;

use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Parte;
use App\Models\Categoria;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, Toast;

    // Búsqueda y modales
    public $search = '';
    public bool $create_parte_modal = false;
    public bool $edit_parte_modal = false;

    // Campos para crear/editar
    public ?int $editing_parte_id = null;
    public string $parteNombre = '';
    public ?int $categoria_id = null;

    public bool $continuarCreando = false;

    // Cabeceras de la tabla
    public array $headers = [
        ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
        ['key' => 'nombre', 'label' => 'Nombre', 'class' => 'w-1'],
        ['key' => 'categoria.nombre', 'label' => 'Categoría', 'class' => 'text-black dark:text-white'], // Muestra el nombre de la categoría
    ];

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public function mount()
    {
        if (!auth()->user() || !auth()->user()->hasPermissionTo('view_any_parte')) {
            abort(403);
        }
    }

    public function openCreateModal()
    {
        $this->reset([
            'parteNombre',
            'categoria_id',
        ]);
        $this->create_parte_modal = true;
    }

    public function createParte()
    {
        $this->validate([
            'parteNombre'  => 'required|string',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        Parte::create([
            'nombre'       => $this->parteNombre,
            'categoria_id' => $this->categoria_id,
        ]);

        if (!$this->continuarCreando) {
            $this->reset([
                'parteNombre',
                'categoria_id',
            ]);
            $this->create_parte_modal = false;
        } else {
            $this->reset(['parteNombre', 'categoria_id']);
        }

        $this->success('Parte creada con éxito!');
    }

    public function editParte(int $parteId)
    {
        $parte = Parte::findOrFail($parteId);

        $this->editing_parte_id = $parte->id;
        $this->parteNombre  = $parte->nombre;
        $this->categoria_id = $parte->categoria_id;

        $this->edit_parte_modal = true;
    }

    public function updateParte()
    {
        $this->validate([
            'parteNombre'  => 'nullable|string',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        $parte = Parte::findOrFail($this->editing_parte_id);

        $parte->update([
            'nombre'       => $this->parteNombre,
            'categoria_id' => $this->categoria_id,
        ]);

        $this->reset([
            'parteNombre',
            'categoria_id',
        ]);

        $this->edit_parte_modal = false;

        $this->toast(
            type: 'success',
            title: 'Actualizado',
            description: 'Parte Actualizada Con Éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    public function render()
    {
        $partes = Parte::where('nombre', 'like', '%' . $this->search . '%')
            ->with('categoria')
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);

        $categorias = Categoria::all();

        return view('livewire.partes.v1.index', [
            'headers'    => $this->headers,
            'sortBy'     => $this->sortBy,
            'partes'     => $partes,
            'categorias' => $categorias,
        ]);
    }
}

