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
        Schema::create('notification_emails', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // Se precargan los correos que ya estaban configurados en el .env
        // para no perder las notificaciones existentes.
        $iniciales = array_values(array_filter(array_map(
            'trim',
            explode(',', (string) env('EQUIPO_NOTIFICATION_EMAILS', ''))
        )));

        foreach ($iniciales as $correo) {
            DB::table('notification_emails')->insertOrIgnore([
                'email' => $correo,
                'descripcion' => 'Correo inicial configurado en .env',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_emails');
    }
};
