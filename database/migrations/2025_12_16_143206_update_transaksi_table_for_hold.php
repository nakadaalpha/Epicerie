<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // 1. Tambah kolom 'status' (Pilihannya: pending, selesai, batal)
            // Kita kasih default 'selesai' biar data lama gak error
            $table->enum('status', ['pending', 'selesai', 'batal'])
                  ->default('selesai')
                  ->after('total_bayar'); 
            
            // 2. Tambah kolom 'nama_pelanggan_hold'
            // nullable() artinya boleh kosong
            $table->string('nama_pelanggan_hold')->nullable()->after('status');
            
            // 3. Ubah kolom 'metode_pembayaran' jadi boleh kosong
            // PENTING: Perlu install doctrine/dbal dulu kalau error di sini
            $table->string('metode_pembayaran')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi', function (Blueprint $table) {
            // Ini buat menghapus kolom kalau migration di-rollback
            $table->dropColumn(['status', 'nama_pelanggan_hold']);
            
            // Balikin metode_pembayaran jadi wajib isi lagi
            $table->string('metode_pembayaran')->nullable(false)->change();
        });
    }
};