<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LokasiKurirUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id_transaksi;
    public $lat;
    public $long;

    public function __construct($id_transaksi, $lat, $long)
    {
        $this->id_transaksi = $id_transaksi;
        $this->lat = $lat;
        $this->long = $long;
    }

    public function broadcastOn()
    {
        // Channel: tracking.1, tracking.2, dst
        return new Channel('tracking.' . $this->id_transaksi);
    }

    public function broadcastAs()
    {
        // INI KUNCINYA: Harus sama persis dengan 'channel.bind' di JS
        return 'lokasi-update'; 
    }
}