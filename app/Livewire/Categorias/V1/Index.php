<?php

namespace App\Livewire\Categorias\V1;

use Livewire\Component;
use Mary\Traits\Toast;
use App\Models\Categoria;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, Toast;

    // Búsqueda y modales
    public $search = '';
    public bool $create_categoria_modal = false;
    public bool $edit_categoria_modal = false;

    // Campos para crear/editar
    public ?int $editing_categoria_id = null;
    public string $categoriaNombre = '';

    public bool $continuarCreando = false;

    public bool $show_partes_modal = false;
    public array $partes = [];
    public string $categoriaNombreSeleccionada = '';

    // Cabeceras de la tabla
    public array $headers = [
        ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
        ['key' => 'nombre', 'label' => 'Nombre', 'class' => 'w-1'],
        ['key' => 'count_partes_column', 'label' => 'Cant. de Partes', 'class' => 'text-black dark:text-white'],

    ];
    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public function mount()
    {
        // Verifica que el categoria tenga permiso de ver categorias
        if (!auth()->user() || !auth()->user()->hasPermissionTo('view_any_categoria')) {
            abort(403);
        }
    }

    /**
     * Abre el modal de crear categoria (limpia variables)
     */
    public function openCreateModal()
    {
        $this->reset([
            'categoriaNombre',
        ]);
        $this->create_categoria_modal = true;
    }

    /**
     * Crea un nueva categoria
     */
    public function createCategoria()
    {
        $this->validate([
            'categoriaNombre'     => 'required|string',
        ]);

        // Creamos el servicio
        $categoria = Categoria::create([
            'nombre'     => $this->categoriaNombre,
        ]);

        if (!$this->continuarCreando) {
            $this->reset([
                'categoriaNombre',
            ]);
            $this->create_categoria_modal = false;
        } else {
            $this->reset(['categoriaNombre']);
        }

        $this->success('Categoria creada con éxito!');
    }

    /**
     * Abre el modal de edición de una categoria existente
     */
    public function editCategoria(int $categoriaId)
    {
        $categoria = Categoria::findOrFail($categoriaId);

        $this->editing_categoria_id = $categoria->id;
        $this->categoriaNombre  = $categoria->nombre;

        $this->edit_categoria_modal = true;
    }

    /**
     * Actualiza datos de la categoria
     */
    public function updateCategoria()
    {
        $this->validate([
            'categoriaNombre'     => 'nullable|string',
        ]);

        $categoria = Categoria::findOrFail($this->editing_categoria_id);

        $data = [
            'nombre'  => $this->categoriaNombre,
        ];

        $categoria->update($data);

        $this->reset([
            'categoriaNombre',
        ]);

        $this->edit_categoria_modal = false;

        $this->toast(
            type: 'success',
            title: 'Actualizado',
            description: 'Categoria Actualizada Con Éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    /**
     * Elimina un categoria
     */
    public function deleteCategoria(int $categoriaId)
    {
        $categoria = Categoria::findOrFail($categoriaId);

        $categoria->delete();

        $this->toast(
            type: 'success',
            title: 'Eliminado',
            description: 'Categoria Eliminada Con Éxito',
            icon: 'o-information-circle',
            css: 'alert-success text-white text-sm',
            timeout: 3000,
        );
    }

    /**
     * Carga las partes de una categoría y abre el modal
     */
    public function showPartes(int $categoriaId)
    {
        $categoria = Categoria::with('partes')->findOrFail($categoriaId);

        $this->categoriaNombreSeleccionada = $categoria->nombre;
        $this->partes = $categoria->partes->map(function ($parte) {
            return [
                'id' => $parte->id,
                'nombre' => $parte->nombre,
            ];
        })->toArray();

        $this->show_partes_modal = true;
    }

    public function render()
    {
        $categorias = Categoria::where('nombre', 'like', '%' . $this->search . '%')
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);

        return view('livewire.categorias.v1.index', [
            'headers' => $this->headers,
            'sortBy' => $this->sortBy,
            'categorias' => $categorias,
        ]);
    }
}
