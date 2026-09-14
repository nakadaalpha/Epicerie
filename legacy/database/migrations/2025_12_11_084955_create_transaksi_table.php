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
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id('id_transaksi');

            // Relasi ke user (Pembeli)
            $table->foreignId('id_user_pembeli')->constrained('user', 'id_user');

            // Relasi ke user (Kasir) - Nullable jika transaksi mandiri
            $table->foreignId('id_user_kasir')->nullable()->constrained('user', 'id_user');

            $table->string('kode_transaksi')->unique();
            $table->double('total_bayar');
            $table->string('metode_pembayaran');
            $table->dateTime('tanggal_transaksi');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
