<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address');   // Mencatat IP
            $table->date('visit_date');     // Mencatat Tanggal
            $table->dateTime('last_activity')->nullable(); 
            $table->string('user_agent')->nullable(); // Info Browser
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('visitors');
    }
};