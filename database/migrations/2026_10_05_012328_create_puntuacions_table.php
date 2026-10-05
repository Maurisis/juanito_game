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
        Schema::create('puntuaciones', function (Blueprint $table) {
            $table->id(); 
            $table->integer('nivel_alcanzado');
            $table->string('estado');
            $table->integer('tiempo_restante');
            $table->integer('puntuacion_final');       
            $table->timestamps();             
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puntuaciones');
    }
};