<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StatusTransaksiUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id_transaksi;
    public $status;

    public function __construct($id_transaksi, $status)
    {
        $this->id_transaksi = $id_transaksi;
        $this->status = $status;
    }

    public function broadcastOn(): array
    {
        return [new Channel('tracking.' . $this->id_transaksi)];
    }

    public function broadcastAs()
    {
        return 'status-update';
    }
}