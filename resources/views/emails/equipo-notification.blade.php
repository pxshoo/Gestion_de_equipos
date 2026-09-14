<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Notificación de inventario</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f4f7f6; padding: 24px; margin:0;">
    @php
        $colores = [
            'creado' => '#0e6f6a',
            'actualizado' => '#0a58ca',
            'reasignado' => '#997404',
            'eliminado' => '#b02a37',
        ];
        $titulos = [
            'creado' => 'Notificación de nuevo equipo creado',
            'actualizado' => 'Notificación de equipo editado',
            'reasignado' => 'Notificación de equipo reasignado',
            'eliminado' => 'Notificación de equipo eliminado',
        ];
        $color = $colores[$accion] ?? '#0e6f6a';
        $titulo = $titulos[$accion] ?? 'Actualización de equipo';
        $val = fn ($valor) => filled($valor) ? $valor : '—';
        $bool = fn ($valor) => $valor === 'SI' ? 'Sí' : ($valor === 'NO' ? 'No' : '—');

        $secciones = [
            'Identificación' => [
                'Código inventario' => $equipo->codigo_inventario,
                'Nombre / Asignado a' => $equipo->asignado_a ?? $equipo->nombre,
                'Tipo' => $equipo->tipo,
                'Estado' => $equipo->estado,
                'Ubicación' => $equipo->ubicacion,
            ],
            'Especificaciones' => [
                'Marca' => $equipo->marca,
                'Modelo' => $equipo->modelo,
                'Nº Serie' => $equipo->numero_serie,
                'Usuario PC' => $equipo->usuario_pc,
                'Procesador' => $equipo->procesador,
                'Tipo disco duro' => $equipo->tipo_disco_duro,
                'RAM instalada' => $equipo->ram_instalada,
                'Pantalla externa' => $bool($equipo->pantalla_externa),
            ],
            'Monitor y periféricos' => [
                'Marca monitor' => $equipo->marca_monitor,
                'Modelo monitor' => $equipo->modelo_monitor,
                'Nº Serie monitor' => $equipo->numero_serie_monitor,
                'Marca monitor 2' => $equipo->marca_monitor2,
                'Modelo monitor 2' => $equipo->modelo_monitor2,
                'Nº Serie monitor 2' => $equipo->numero_serie_monitor2,
                'Teclado' => $bool($equipo->teclado),
                'Mouse' => $bool($equipo->mouse),
                'Base notebook' => $bool($equipo->base_notebook),
                'Reasignado a' => $equipo->equipo_reasignado_a,
                'OneDrive funciona' => $bool($equipo->onedrive_funcionando),
                'Respaldo OneDrive' => $bool($equipo->respaldo_onedrive),
            ],
            'Valores y notas' => [
                'Valoración equipo' => $equipo->valoracion_equipo,
                'Valoración monitor' => $equipo->valoracion_monitor,
                'Valor actual' => $equipo->valoracion_equipo_actual,
                'Mantención realizada' => $bool($equipo->mantencion_realizada),
                'Observaciones' => $equipo->observaciones,
            ],
        ];
    @endphp
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius: 12px; overflow:hidden; border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:{{ $color }}; padding: 20px 28px;">
                            <h2 style="color:#ffffff; margin:0; font-size:18px;">{{ $titulo }}</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 28px;">
                            <p style="margin:0 0 16px; color:#0f172a; font-size:14px;">
                                Se ha {{ $accion }} el siguiente equipo en el sistema de inventario TI
                                @if ($accion === 'eliminado')
                                    (registro removido de la base de datos):
                                @else
                                    con los datos ingresados en el formulario:
                                @endif
                            </p>

                            @foreach ($secciones as $tituloSeccion => $campos)
                                <h3 style="font-size:13px; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin:20px 0 8px;">{{ $tituloSeccion }}</h3>
                                <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-size:14px; color:#0f172a; background:#f8fafc; border-radius:8px;">
                                    @foreach ($campos as $etiqueta => $valor)
                                        <tr>
                                            <td style="width:45%; color:#64748b;">{{ $etiqueta }}</td>
                                            <td><strong>{{ $val($valor) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endforeach

                            @if (count($cambios) > 0)
                                <h3 style="font-size:13px; text-transform:uppercase; letter-spacing:.05em; color:#64748b; margin:20px 0 8px;">Cambios realizados</h3>
                                <table role="presentation" width="100%" cellpadding="8" cellspacing="0" style="border-collapse: collapse; font-size:13px;">
                                    <tr style="background:#f8fafc;">
                                        <td style="color:#64748b; font-weight:bold;">Campo</td>
                                        <td style="color:#64748b; font-weight:bold;">Antes</td>
                                        <td style="color:#64748b; font-weight:bold;">Después</td>
                                    </tr>
                                    @foreach ($cambios as $cambio)
                                        <tr>
                                            <td>{{ $cambio['label'] }}</td>
                                            <td>{{ $cambio['before'] }}</td>
                                            <td><strong>{{ $cambio['after'] }}</strong></td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif

                            <p style="margin:24px 0 0; color:#94a3b8; font-size:12px;">
                                Este es un correo automático generado por el Panel de inventario. No responder a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

