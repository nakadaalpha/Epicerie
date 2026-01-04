<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sliders', function (Blueprint $table) {
            $table->bigIncrements('id_slider');
            $table->string('judul')->nullable(); // Judul Promo (Opsional)
            $table->string('deskripsi')->nullable(); // Keterangan (Opsional)
            $table->string('gambar'); // Wajib
            $table->integer('urutan')->default(1); // Untuk mengatur urutan tampil
            $table->boolean('is_active')->default(true); // Status aktif/tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sliders');
    }
};
