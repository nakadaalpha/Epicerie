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
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id('id_detail_transaksi');

            // FK ke transaksi
            $table->foreignId('id_transaksi')
                ->constrained('transaksi', 'id_transaksi')
                ->onDelete('cascade');

            // FK ke produk — perbaiki tabelnya!
            $table->foreignId('id_produk')
                ->constrained('produk', 'id_produk')
                ->onDelete('cascade');

            $table->integer('jumlah');
            $table->double('harga_produk_saat_beli');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
