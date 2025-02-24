<?php

namespace App\Livewire\User\V1;

use Livewire\Component;
use Mary\Traits\Toast;

class Show extends Component
{
    use Toast;

    public $user;
    public $selectedTab = 'info-tab';

    // Para ver imagen en grande
    public bool $viewImageModal = false;
    public ?string $viewImageModalUrl = null;

    public function openViewImageModal($imageUrl)
    {
        $this->viewImageModalUrl = $imageUrl;
        $this->viewImageModal = true;
    }

    public function closeViewImageModal()
    {
        $this->viewImageModal = false;
        $this->viewImageModalUrl = null;
    }

    public function mount()
    {
        if (!auth()->user()->hasPermissionTo('view_user')) {
            return abort(403);
        }
    }

    public function render()
    {
        return view('livewire.user.v1.show');
    }
}
