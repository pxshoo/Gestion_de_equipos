<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\Equipo;
use Symfony\Component\Console\Command\Command;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('equipos:import {path? : Ruta al CSV exportado desde Excel}', function (?string $path = null) {
    $path = $path ?: storage_path('app/Registro de equipos(Hoja1).csv');

    if (! file_exists($path)) {
        $this->error("No se encontró el archivo CSV: {$path}");

        return Command::FAILURE;
    }

    $handle = fopen($path, 'r');

    if ($handle === false) {
        $this->error("No se pudo abrir el archivo CSV: {$path}");

        return Command::FAILURE;
    }

    Equipo::query()->truncate();

    $section = 'equipos';
    $imported = 0;
    $equipmentIndex = 0;
    $phoneIndex = 0;

    $clean = function ($value): string {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', (string) $value);

        return trim($value);
    };

    $normalize = function ($value) use ($clean): string {
        return mb_strtolower($clean($value));
    };

    $normalizeBoolean = function ($value) use ($normalize): ?string {
        $value = $normalize($value);

        if ($value === '') {
            return null;
        }

        if (in_array($value, ['si,2', 'si2', 'si, 2'], true)) {
            return 'SI2';
        }

        return in_array($value, ['si', 'sí', 's', 'true', '1', 'yes'], true) ? 'SI' : 'NO';
    };

    $parseCurrency = function ($value) use ($clean): ?string {
        $value = $clean($value);

        return $value === '' ? null : $value;
    };

    $parseCurrencyNumber = function ($value) use ($clean): ?float {
        $value = $clean($value);

        if ($value === '') {
            return null;
        }

        $numeric = preg_replace('/[^0-9]/', '', $value);

        return $numeric === '' ? null : (float) $numeric;
    };

    $inferTipo = function (?string $modelo, ?string $categoria): string {
        if ($categoria === 'telefonos') {
            return 'Telefono';
        }

        return 'Computador';
    };

    while (($row = fgetcsv($handle, 0, ',')) !== false) {
        $row = array_map($clean, $row);

        if (count(array_filter($row, static fn ($value) => $value !== '')) === 0) {
            continue;
        }

        if (($row[0] ?? '') === 'Telefonos') {
            $section = 'telefonos';
            continue;
        }

        if ($section === 'equipos' && $normalize($row[0] ?? '') === 'nombre' && $normalize($row[1] ?? '') === 'usuario pc') {
            continue;
        }

        if ($section === 'telefonos' && $normalize($row[0] ?? '') === 'nombre' && $normalize($row[1] ?? '') === 'n-serie') {
            continue;
        }

        if ($section === 'telefonos') {
            $phoneIndex++;

            Equipo::create([
                'categoria' => 'Telefonos',
                'codigo_inventario' => sprintf('TEL-%04d', $phoneIndex),
                'tipo' => 'Telefono',
                'marca' => $row[2] ?: 'Sin marca',
                'modelo' => $row[3] ?: null,
                'numero_serie' => $row[1] ?: null,
                'nombre' => $row[0] ?: null,
                'asignado_a' => $row[0] ?: null,
                'valoracion_equipo_actual' => $parseCurrency($row[4] ?? null),
                'valoracion_equipo_actual_numero' => $parseCurrencyNumber($row[4] ?? null),
                'estado' => 'Bueno',
                'observaciones' => 'Importado desde CSV de teléfonos',
            ]);

            $imported++;
            continue;
        }

        $equipmentIndex++;
        $estado = $normalizeBoolean($row[21] ?? null) === 'SI' ? 'Excelente' : 'Regular';

        Equipo::create([
            'categoria' => 'Equipos',
            'codigo_inventario' => sprintf('EQ-%04d', $equipmentIndex),
            'tipo' => $inferTipo($row[3] ?? null, 'equipos'),
            'marca' => $row[2] ?: 'Sin marca',
            'modelo' => $row[3] ?: null,
            'numero_serie' => $row[4] ?: null,
            'nombre' => $row[0] ?: null,
            'usuario_pc' => $row[1] ?: null,
            'procesador' => $row[5] ?: null,
            'tipo_disco_duro' => $row[6] ?: null,
            'ram_instalada' => $row[7] ?: null,
            'pantalla_externa' => $normalizeBoolean($row[8] ?? null),
            'marca_monitor' => $row[9] ?: null,
            'modelo_monitor' => $row[10] ?: null,
            'numero_serie_monitor' => $row[11] ?: null,
            'teclado' => $normalizeBoolean($row[12] ?? null),
            'mouse' => $normalizeBoolean($row[13] ?? null),
            'base_notebook' => $normalizeBoolean($row[14] ?? null),
            'onedrive_funcionando' => $normalizeBoolean($row[15] ?? null),
            'respaldo_onedrive' => $normalizeBoolean($row[16] ?? null),
            'equipo_reasignado_a' => $row[17] ?: null,
            'valoracion_equipo' => $parseCurrency($row[18] ?? null),
            'valoracion_monitor' => $parseCurrency($row[19] ?? null),
            'valoracion_equipo_actual' => $parseCurrency($row[20] ?? null),
            'valoracion_equipo_actual_numero' => $parseCurrencyNumber($row[20] ?? null),
            'mantencion_realizada' => $normalizeBoolean($row[21] ?? null),
            'asignado_a' => $row[1] ?: null,
            'estado' => $estado,
            'observaciones' => 'Importado desde CSV de equipos',
        ]);

        $imported++;
    }

    fclose($handle);

    $this->info("Importación completada: {$imported} registros cargados.");

    return Command::SUCCESS;
})->purpose('Importa y reemplaza los equipos desde un CSV exportado de Excel');
