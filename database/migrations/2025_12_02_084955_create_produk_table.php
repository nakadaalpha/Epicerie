<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produk', function (Blueprint $table) {
            $table->id('id_produk');

            // 1. BUAT KOLOMNYA DULU (Tipe Data HARUS SAMA dengan Kategori)
            $table->unsignedBigInteger('id_kategori');

            // 2. BARU BUAT RELASINYA
            $table->foreign('id_kategori')
                ->references('id_kategori') // Kolom tujuan di tabel kategori
                ->on('kategori')            // Nama tabel tujuan
                ->onDelete('cascade');

            $table->string('nama_produk');
            $table->double('harga_produk');
            $table->integer('stok');
            $table->text('deskripsi_produk')->nullable();
            $table->string('gambar')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produk');
    }
};
