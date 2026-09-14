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
        Schema::table('reasignaciones', function (Blueprint $table) {
            if (! Schema::hasColumn('reasignaciones', 'cambiado_por_user_id')) {
                $table->foreignId('cambiado_por_user_id')->nullable()->after('equipo_reasignado_nuevo')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('reasignaciones', 'cambiado_por_nombre')) {
                $table->string('cambiado_por_nombre')->nullable()->after('cambiado_por_user_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reasignaciones', function (Blueprint $table) {
            if (Schema::hasColumn('reasignaciones', 'cambiado_por_user_id')) {
                $table->dropColumn('cambiado_por_user_id');
            }

            if (Schema::hasColumn('reasignaciones', 'cambiado_por_nombre')) {
                $table->dropColumn('cambiado_por_nombre');
            }
        });
    }
};
