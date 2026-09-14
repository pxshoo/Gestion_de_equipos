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
        if (! Schema::hasColumn('equipos', 'marca_monitor2')) {
            Schema::table('equipos', function (Blueprint $table) {
                $table->string('marca_monitor2')->nullable()->after('numero_serie_monitor');
            });
        }

        if (! Schema::hasColumn('equipos', 'modelo_monitor2')) {
            Schema::table('equipos', function (Blueprint $table) {
                $table->string('modelo_monitor2')->nullable()->after('marca_monitor2');
            });
        }

        if (! Schema::hasColumn('equipos', 'numero_serie_monitor2')) {
            Schema::table('equipos', function (Blueprint $table) {
                $table->string('numero_serie_monitor2')->nullable()->after('modelo_monitor2');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('equipos', 'marca_monitor2')) {
            Schema::table('equipos', function (Blueprint $table) {
                $table->dropColumn('marca_monitor2');
            });
        }

        if (Schema::hasColumn('equipos', 'modelo_monitor2')) {
            Schema::table('equipos', function (Blueprint $table) {
                $table->dropColumn('modelo_monitor2');
            });
        }

        if (Schema::hasColumn('equipos', 'numero_serie_monitor2')) {
            Schema::table('equipos', function (Blueprint $table) {
                $table->dropColumn('numero_serie_monitor2');
            });
        }
    }
};
