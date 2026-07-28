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
    Schema::create('equipos', function (Blueprint $table) {
        $table->id();
        $table->string('codigo_inventario')->unique(); // Ej: INF-PC-001
        $table->string('tipo');                        // Notebook, Desktop, Monitor, Celular
        $table->string('marca');                       // Lenovo, Dell, HP, Samsung
        $table->string('modelo')->nullable();
        $table->string('numero_serie')->nullable();
        $table->string('asignado_a')->nullable();     // Nombre del usuario/funcionario
        $table->string('ubicacion')->nullable();        // Oficina, Sucursal, Terreno
        $table->enum('estado', ['Excelente', 'Bueno', 'Regular', 'Malo', 'De Baja'])->default('Bueno');
        $table->text('observaciones')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipos');
    }
};
