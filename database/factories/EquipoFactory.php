<?php

namespace Database\Factories;

use App\Models\Equipo;
use Illuminate\Database\Eloquent\Factories\Factory;

class EquipoFactory extends Factory
{
    protected $model = Equipo::class;

    public function definition(): array
    {
        $tipos = ['Notebook', 'Desktop', 'Tablet', 'Smartphone'];
        $marcas = ['Lenovo', 'HP', 'Dell', 'Apple', 'Samsung'];
        $estados = ['Excelente', 'Bueno', 'Regular', 'Malo', 'De Baja'];
        $ubicaciones = ['Oficina Central', 'Pozo Almonte', 'Faena', 'Bodega'];
        $siNo = ['SI', 'NO'];

        $tipo = $this->faker->randomElement($tipos);
        $codigoInventario = strtoupper(substr($tipo, 0, 2)) . '-' . $this->faker->unique()->numberBetween(100, 999);

        return [
            'codigo_inventario' => $codigoInventario,
            'tipo' => $tipo,
            'marca' => $this->faker->randomElement($marcas),
            'modelo' => $this->faker->words(2, true),
            'numero_serie' => $this->faker->unique()->ean13,
            'asignado_a' => $this->faker->name,
            'ubicacion' => $this->faker->randomElement($ubicaciones),
            'estado' => $this->faker->randomElement($estados),
            'observaciones' => $this->faker->sentence,
            'categoria' => 'Equipos',
            'nombre' => $this->faker->name,
            'usuario_pc' => $this->faker->userName,
            'procesador' => 'Intel Core i' . $this->faker->randomElement(['5', '7', '9']),
            'tipo_disco_duro' => $this->faker->randomElement(['SSD', 'HDD']),
            'ram_instalada' => $this->faker->randomElement(['8GB', '16GB', '32GB']),
            'pantalla_externa' => $this->faker->randomElement(['SI', 'NO', 'SI2']),
            'marca_monitor' => $this->faker->randomElement($marcas),
            'modelo_monitor' => $this->faker->words(2, true),
            'numero_serie_monitor' => $this->faker->unique()->ean13,
            'marca_monitor2' => $this->faker->randomElement($marcas),
            'modelo_monitor2' => $this->faker->words(2, true),
            'numero_serie_monitor2' => $this->faker->unique()->ean13,
            'teclado' => $this->faker->randomElement($siNo),
            'mouse' => $this->faker->randomElement($siNo),
            'base_notebook' => $this->faker->randomElement($siNo),
            'onedrive_funcionando' => $this->faker->randomElement($siNo),
            'respaldo_onedrive' => $this->faker->randomElement($siNo),
            'equipo_reasignado_a' => '',
            'valoracion_equipo' => $this->faker->numberBetween(500, 2000),
            'valoracion_monitor' => $this->faker->numberBetween(100, 500),
            'valoracion_equipo_actual' => $this->faker->numberBetween(300, 1500),
            'valoracion_equipo_actual_numero' => $this->faker->numberBetween(300, 1500),
            'mantencion_realizada' => $this->faker->randomElement($siNo),
        ];
    }
}
