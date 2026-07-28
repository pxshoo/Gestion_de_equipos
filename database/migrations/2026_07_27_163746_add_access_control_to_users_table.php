<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_super_admin')->default(false)->after('password');
            $table->boolean('can_crear')->default(true)->after('is_super_admin');
            $table->boolean('can_editar')->default(true)->after('can_crear');
            $table->boolean('can_eliminar')->default(true)->after('can_editar');
        });

        // El usuario más antiguo (la cuenta ya existente) queda como super admin por defecto.
        $primerUsuarioId = DB::table('users')->orderBy('id')->value('id');

        if ($primerUsuarioId) {
            DB::table('users')->where('id', $primerUsuarioId)->update([
                'is_super_admin' => true,
                'can_crear' => true,
                'can_editar' => true,
                'can_eliminar' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_super_admin', 'can_crear', 'can_editar', 'can_eliminar']);
        });
    }
};
