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
        if (Schema::hasTable('alamat_pengiriman')) {
            if (Schema::hasColumn('alamat_pengiriman', 'is_utama') && !Schema::hasColumn('alamat_pengiriman', 'is_primary')) {
                Schema::table('alamat_pengiriman', function (Blueprint $table) {
                    $table->renameColumn('is_utama', 'is_primary');
                });
            } elseif (!Schema::hasColumn('alamat_pengiriman', 'is_primary')) {
                Schema::table('alamat_pengiriman', function (Blueprint $table) {
                    $table->boolean('is_primary')->default(false);
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('alamat_pengiriman') && Schema::hasColumn('alamat_pengiriman', 'is_primary')) {
            Schema::table('alamat_pengiriman', function (Blueprint $table) {
                $table->renameColumn('is_primary', 'is_utama');
            });
        }
    }
};
