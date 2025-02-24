<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ServiceNotification extends Notification
{
    use Queueable;

    protected $vehicle;
    protected $service;

    public function __construct($vehicle, $service)
    {
        $this->vehicle = $vehicle;
        $this->service = $service;
    }

    public function via($notifiable)
    {
        // return ['mail', 'database'];
        return ['database'];
    }

    // public function toMail($notifiable)
    // {
    //     return (new MailMessage)
    //         ->subject('Nuevo servicio pendiente')
    //         ->line("El vehículo {$this->vehicle->placa} tiene un servicio pendiente: {$this->service->nombre}.")
    //         ->action('Ver detalles', url('/services/pending'));
    // }

    public function toArray($notifiable)
    {
        return [
            'vehicle' => [
                'nombre_unidad' => $this->vehicle->nombre_unidad,
                'current_mileage' => $this->vehicle->current_mileage
            ],
            'service' => [
                'nombre' => $this->service->nombre,
                'observaciones' => $this->service->observaciones
            ],
        ];
    }
}
