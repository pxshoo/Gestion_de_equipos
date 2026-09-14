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
        Schema::table('equipos', function (Blueprint $table) {
            $table->string('categoria')->default('Equipos')->after('id');
            $table->string('nombre')->nullable()->after('categoria');
            $table->string('usuario_pc')->nullable()->after('nombre');
            $table->string('procesador')->nullable()->after('usuario_pc');
            $table->string('tipo_disco_duro')->nullable()->after('procesador');
            $table->string('ram_instalada')->nullable()->after('tipo_disco_duro');
            $table->string('pantalla_externa')->nullable()->after('ram_instalada');
            $table->string('marca_monitor')->nullable()->after('pantalla_externa');
            $table->string('modelo_monitor')->nullable()->after('marca_monitor');
            $table->string('numero_serie_monitor')->nullable()->after('modelo_monitor');
            $table->string('marca_monitor2')->nullable()->after('numero_serie_monitor');
            $table->string('modelo_monitor2')->nullable()->after('marca_monitor2');
            $table->string('numero_serie_monitor2')->nullable()->after('modelo_monitor2');
            $table->string('teclado')->nullable()->after('numero_serie_monitor2');
            $table->string('mouse')->nullable()->after('teclado');
            $table->string('base_notebook')->nullable()->after('mouse');
            $table->string('onedrive_funcionando')->nullable()->after('base_notebook');
            $table->string('respaldo_onedrive')->nullable()->after('onedrive_funcionando');
            $table->string('equipo_reasignado_a')->nullable()->after('respaldo_onedrive');
            $table->string('valoracion_equipo')->nullable()->after('equipo_reasignado_a');
            $table->string('valoracion_monitor')->nullable()->after('valoracion_equipo');
            $table->string('valoracion_equipo_actual')->nullable()->after('valoracion_monitor');
            $table->string('mantencion_realizada')->nullable()->after('valoracion_equipo_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipos', function (Blueprint $table) {
            $table->dropColumn([
                'categoria',
                'nombre',
                'usuario_pc',
                'procesador',
                'tipo_disco_duro',
                'ram_instalada',
                'pantalla_externa',
                'marca_monitor',
                'modelo_monitor',
                'numero_serie_monitor',
                'marca_monitor2',
                'modelo_monitor2',
                'numero_serie_monitor2',
                'teclado',
                'mouse',
                'base_notebook',
                'onedrive_funcionando',
                'respaldo_onedrive',
                'equipo_reasignado_a',
                'valoracion_equipo',
                'valoracion_monitor',
                'valoracion_equipo_actual',
                'mantencion_realizada',
            ]);
        });
    }
};