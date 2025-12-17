<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // 1. Konfigurasi Tabel (Sesuai Database Anda)
    protected $table = 'user';
    protected $primaryKey = 'id_user';

    /**
     * 2. Mass Assignment (PENTING!)
     * 'role' HARUS ada di sini agar kita bisa set 'karyawan' saat register.
     * Keamanan dijaga di Controller (jangan gunakan $request->all()).
     */
    protected $fillable = [
        'nama',
        'username',
        'password',
        'role', // <-- Wajib di-uncomment agar fitur register berfungsi
    ];

    /**
     * 3. Hidden Attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 4. Casting
     * 'password' => 'hashed' akan otomatis mengenkripsi password saat save/update.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * 5. Mutator: Username
     * Otomatis huruf kecil & tanpa spasi saat disimpan.
     */
    protected function username(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    /**
     * 6. Mutator: Nama
     * Otomatis Huruf Kapital di awal kata (Title Case).
     */
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucwords(strtolower($value)),
        );
    }

    /**
     * 7. Custom Auth Login
     * Memberi tahu Laravel kita login pakai kolom 'username', bukan 'email'.
     */
    // public function getAuthIdentifierName()
    // {
    //     return 'username';
    // }
    
    // Opsional: Jika Anda menggunakan timestamp tapi nama kolomnya beda
    // const CREATED_AT = 'created_at';
    // const UPDATED_AT = 'updated_at';
}