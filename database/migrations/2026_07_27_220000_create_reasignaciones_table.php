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
        Schema::create('reasignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipo_id')->nullable()->constrained('equipos')->nullOnDelete();
            $table->string('codigo_inventario')->nullable();
            $table->string('equipo_nombre')->nullable();
            $table->string('tipo')->nullable();
            $table->string('marca')->nullable();
            $table->string('modelo')->nullable();
            $table->string('asignado_anterior')->nullable();
            $table->string('asignado_nuevo')->nullable();
            $table->string('equipo_reasignado_anterior')->nullable();
            $table->string('equipo_reasignado_nuevo')->nullable();
            $table->string('ubicacion')->nullable();
            $table->timestamp('fecha_reasignacion')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reasignaciones');
    }
};
