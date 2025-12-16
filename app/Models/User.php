<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute; // Tambahkan ini untuk sanitasi data

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'user';
    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     * * CATATAN KEAMANAN:
     * Saya MENGHAPUS 'role' dari sini.
     * Tujuannya agar user tidak bisa memanipulasi role mereka sendiri
     * melalui input form (Mass Assignment Attack).
     * Role harus di-set secara manual di Controller.
     */
    protected $fillable = [
        'nama',
        'username',
        'password',
        // 'role', <--- Dihapus demi keamanan
    ];

    /**
     * The attributes that should be hidden for serialization.
     * Menyembunyikan data sensitif saat model di-convert jadi JSON/Array.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'role', // Opsi tambahan: Sembunyikan role jika tidak ingin diekspos di API publik
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed', // Otomatis hash password saat disimpan
            // Jika Anda menggunakan PHP Enum untuk role, bisa ditambahkan di sini:
            // 'role' => \App\Enums\UserRole::class, 
        ];
    }

    /**
     * KEAMANAN & KONSISTENSI DATA:
     * Memastikan username selalu lowercase (huruf kecil) dan tanpa spasi
     * sebelum masuk ke database.
     * Contoh: Input " Admin " -> Tersimpan "admin"
     */
    protected function username(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => strtolower(trim($value)),
        );
    }

    /**
     * KEAMANAN & KONSISTENSI DATA:
     * Memastikan Nama selalu Title Case (Huruf Depan Besar).
     * Contoh: "mas acheng" -> "Mas Acheng"
     */
    protected function nama(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => ucwords(strtolower($value)),
        );
    }

    /**
     * Memberi tahu Laravel kolom mana yang dipakai untuk Login.
     * Defaultnya adalah 'email', kita ubah jadi 'username'.
     */
    public function getAuthIdentifierName()
    {
        return 'username';
    }
}