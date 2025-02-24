<?php

namespace App\Livewire\Notifications\V1;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Drawer extends Component
{
    public $notifications = [];
    public $selectedNotification = null;
    public $showDrawer = false; // Estado del drawer

    public function mount()
    {
        $this->loadNotifications();
    }

    public function toggleDrawer()
    {
        // Alternar el estado del drawer
        $this->showDrawer = !$this->showDrawer;

        // Si se va a mostrar, cargamos las notificaciones
        if ($this->showDrawer) {
            $this->loadNotifications();
        }
    }

    public function loadNotifications()
    {
        $this->notifications = Auth::user()->unreadNotifications;
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
        $this->loadNotifications();
    }

    public function deleteNotification($notificationId)
    {
        $notification = Auth::user()->notifications()->find($notificationId);
        if ($notification) {
            $notification->delete();
        }
        $this->loadNotifications();
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->loadNotifications();
    }

    public function viewNotification($notificationId)
    {
        $this->selectedNotification = Auth::user()->notifications()->find($notificationId);
    }

    public function closeNotification()
    {
        $this->selectedNotification = null;
    }

    public function render()
    {
        return view('livewire.notifications.v1.drawer');
    }
}
