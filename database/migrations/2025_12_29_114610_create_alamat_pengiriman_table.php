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
        Schema::create('alamat_pengiriman', function (Blueprint $table) {
            $table->id('id_alamat'); // Primary Key

            // --- INI KUNCINYA ---
            // Kita paksa namanya 'id_user' agar cocok dengan kodingan navbar Anda
            $table->unsignedBigInteger('id_user');
            $table->foreign('id_user')->references('id_user')->on('user')->onDelete('cascade');
            // --------------------

            $table->string('label', 50)->nullable();
            $table->string('penerima', 100)->nullable();
            $table->string('no_hp_penerima', 20)->nullable();
            $table->text('detail_alamat')->nullable();
            $table->boolean('is_utama')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alamat_pengiriman');
    }

};
