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
        Schema::create('tambal_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('nama_bengkel');
            $table->string('gambar')->nullable();
            $table->enum('kategori', ['motor', 'mobil', 'keduanya'])->default('motor');
            $table->string('alamat')->nullable();
            $table->string('nomer_telepon');
            $table->time('jam_buka')->nullable();
            $table->time('jam_tutup')->nullable();
            $table->string('latitude');
            $table->string('longitude');
            $table->text('deskripsi')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tambal_bans');
    }
};
