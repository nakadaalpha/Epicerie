<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatusTransaksiUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id_transaksi;
    public $status;

    // Terima ID dan Status Baru
    public function __construct($id_transaksi, $status)
    {
        $this->id_transaksi = $id_transaksi;
        $this->status = $status;
    }

    // Teriak ke Channel 'tracking.{id}'
    public function broadcastOn()
    {
        return new Channel('tracking.' . $this->id_transaksi);
    }

    // Nama Event yang didengar JS (WAJIB SAMA dengan tracking.blade.php)
    public function broadcastAs()
    {
        return 'status-update';
    }
}