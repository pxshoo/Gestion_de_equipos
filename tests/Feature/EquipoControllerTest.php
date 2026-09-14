<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EquipoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_equipo_with_two_monitors()
    {
        $user = User::factory()->create(['role' => 'superadmin']);
        $this->actingAs($user);

        $equipoData = [
            'tipo' => 'Computador',
            'marca' => 'Dell',
            'estado' => 'Bueno',
            'pantalla_externa' => 'SI,2',
            'marca_monitor' => 'HP',
            'modelo_monitor' => '24f',
            'numero_serie_monitor' => '12345',
            'marca_monitor2' => 'Samsung',
            'modelo_monitor2' => 'Odyssey G5',
            'numero_serie_monitor2' => '67890',
        ];

        $response = $this->post(route('equipos.store'), $equipoData);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Equipo creado correctamente.');
        $this->assertDatabaseHas('equipos', [
            'pantalla_externa' => 'SI,2',
            'marca_monitor2' => 'Samsung',
            'modelo_monitor2' => 'Odyssey G5',
            'numero_serie_monitor2' => '67890',
        ]);
    }

    public function test_reassignment_records_the_user_who_made_the_change_and_shows_it_in_dashboard()
    {
        $editor = User::factory()->create([
            'name' => 'Ana Gestora',
            'email' => 'ana@example.com',
            'is_super_admin' => true,
        ]);

        $this->actingAs($editor);

        $equipo = \App\Models\Equipo::factory()->create([
            'codigo_inventario' => 'CP-1001',
            'tipo' => 'Computador',
            'nombre' => 'Lucía',
            'asignado_a' => 'Lucía',
            'equipo_reasignado_a' => null,
            'ubicacion' => 'Oficina Central',
            'estado' => 'Bueno',
        ]);

        $response = $this->put(route('equipos.update', $equipo), [
            'codigo_inventario' => 'CP-1001',
            'tipo' => 'Computador',
            'marca' => 'Dell',
            'modelo' => 'OptiPlex',
            'estado' => 'Bueno',
            'asignado_a' => 'María',
            'equipo_reasignado_a' => 'Gabriel',
            'ubicacion' => 'Oficina Central',
            'numero_serie' => 'SERIE-001',
            'nombre' => 'María',
            'valoracion_equipo_actual' => '$ 500',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('reasignaciones', [
            'equipo_id' => $equipo->id,
            'asignado_anterior' => 'Lucía',
            'asignado_nuevo' => 'María',
            'cambiado_por_user_id' => $editor->id,
            'cambiado_por_nombre' => 'Ana Gestora',
        ]);

        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Últimas reasignaciones')
            ->assertSee('Ana Gestora');
    }
}
