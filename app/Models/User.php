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
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucwords(strtolower($value)),
        );
    }

    // --- RELASI KE ALAMAT PENGIRIMAN ---
    public function alamat()
    {
        // Relasi One-to-Many ke tabel 'alamat_pengiriman'
        // pastikan foreign key 'id_user' sesuai dengan di database
        return $this->hasMany(\Illuminate\Support\Facades\DB::table('alamat_pengiriman') ? 'App\Models\AlamatPengiriman' : 'App\Models\User', 'id_user', 'id_user');
        // Catatan: Karena kita tadi pakai Query Builder DB::table('alamat_pengiriman'),
        // idealnya bikin Model AlamatPengiriman.php.
        // Tapi untuk simplenya kita bisa akses manual di view atau controller.
    }
}