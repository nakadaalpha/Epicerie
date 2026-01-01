<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'id_user';

    protected $fillable = [
        'nama',
        'username',
        'password',
        'role',
        'email',
        'no_hp',
        'foto_profil'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    protected function username(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => strtolower(trim($value)),
        );
    }

    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn(string $value) => ucwords(strtolower($value)),
        );
    }

    // --- RELASI KE ALAMAT PENGIRIMAN ---
    public function alamat()
    {
        return $this->hasMany(\Illuminate\Support\Facades\DB::table('alamat_pengiriman') ? 'App\Models\AlamatPengiriman' : 'App\Models\User', 'id_user', 'id_user');
    }

    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_user_pembeli', 'id_user');
    }

    // Accessor untuk mendapatkan status membership otomatis
    // app/Models/User.php

    public function getMembershipAttribute()
    {
        // 1. Ambil data total belanja & frekuensi
        $history = $this->transaksi()->where('status', 'selesai');

        $totalNominal = $history->sum('total_bayar');
        $frekuensi    = $history->count();

        // 2. Cek Logika
        // UBAH DARI '&&' (DAN) MENJADI '||' (ATAU)
        // Tambahkan tanda '=' agar jika pas di angka target, tetap terhitung.

        // Syarat Gold: Belanja >= 2 Juta ATAU Transaksi >= 30x
        if ($totalNominal >= 2000000 || $frekuensi >= 30) {
            return 'Gold';
        }

        // Syarat Silver: Belanja >= 1 Juta ATAU Transaksi >= 20x
        if ($totalNominal >= 1000000 || $frekuensi >= 20) {
            return 'Silver';
        }

        // Syarat Bronze: Belanja >= 500 Ribu ATAU Transaksi >= 10x
        if ($totalNominal >= 500000 || $frekuensi >= 10) {
            return 'Bronze';
        }

        // Jika belum memenuhi syarat apapun
        return 'Classic';
    }

    // Accessor untuk warna badge (biar rapi di View)
    public function getMembershipColorAttribute()
    {
        switch ($this->membership) {
            case 'Gold':
                return 'bg-yellow-100 text-yellow-700 border-yellow-200';
            case 'Silver':
                return 'bg-gray-100 text-gray-600 border-gray-200';
            case 'Bronze':
                return 'bg-orange-100 text-orange-700 border-orange-200';
            default:
                return 'bg-slate-100 text-slate-600 border-slate-200';
        }
    }
}
