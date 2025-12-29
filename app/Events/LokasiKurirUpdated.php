<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Pakai Now biar realtime instan
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LokasiKurirUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    // Data yang mau dikirim ke HP Pelanggan
    public $id_transaksi;
    public $lat;
    public $long;

    /**
     * Create a new event instance.
     * Pas event dipanggil, kita wajib setor ID Transaksi & Koordinatnya
     */
    public function __construct($id_transaksi, $lat, $long)
    {
        $this->id_transaksi = $id_transaksi;
        $this->lat = $lat;
        $this->long = $long;
    }

    /**
     * Get the channels the event should broadcast on.
     * Ini nama 'Radio Channel' nya. Pelanggan harus 'tune in' ke channel ini.
     */
    public function broadcastOn(): array
    {
        // Channel Publik: tracking.{id_transaksi}
        // Contoh: tracking.1766382672
        return [
            new Channel('tracking.' . $this->id_transaksi),
        ];
    }
    
    /**
     * Nama event yang akan didengar oleh Javascript (Frontend)
     */
    public function broadcastAs()
    {
        return 'lokasi-update';
    }
}