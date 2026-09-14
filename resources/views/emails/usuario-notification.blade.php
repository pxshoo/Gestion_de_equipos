<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación de cuenta</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f4f7f6; padding: 24px; margin:0;">
    @php
        $colores = ['actualizado' => '#0a58ca', 'eliminado' => '#b02a37'];
        $titulos = [
            'actualizado' => 'Tu cuenta ha sido actualizada',
            'eliminado' => 'Tu cuenta ha sido dada de baja',
        ];
        $color = $colores[$accion] ?? '#0e6f6a';
        $titulo = $titulos[$accion] ?? 'Actualización de tu cuenta';
    @endphp
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius: 12px; overflow:hidden; border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:{{ $color }}; padding: 24px 28px;">
                            <h2 style="color:#ffffff; margin:0; font-size:18px;">{{ $titulo }}</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 24px 28px;">
                            <p style="margin:0 0 16px; color:#0f172a; font-size:14px;">
                                Hola <strong>{{ $usuario->name }}</strong>,
                                @if ($accion === 'eliminado')
                                    tu cuenta de acceso al sistema de inventario TI ha sido dada de baja y ya no podrás iniciar sesión.
                                @else
                                    se han actualizado los datos de tu cuenta en el sistema de inventario TI.
                                @endif
                            </p>

                            @if (! empty($cambios))
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse; margin-bottom:16px;">
                                    <thead>
                                        <tr>
                                            <td style="padding:8px 10px; font-size:12px; color:#64748b; border-bottom:2px solid #e2e8f0;">Campo</td>
                                            <td style="padding:8px 10px; font-size:12px; color:#64748b; border-bottom:2px solid #e2e8f0;">Antes</td>
                                            <td style="padding:8px 10px; font-size:12px; color:#64748b; border-bottom:2px solid #e2e8f0;">Ahora</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cambios as $cambio)
                                            <tr>
                                                <td style="padding:8px 10px; font-size:13px; color:#0f172a; border-bottom:1px solid #e2e8f0;">{{ $cambio['label'] }}</td>
                                                <td style="padding:8px 10px; font-size:13px; color:#94a3b8; border-bottom:1px solid #e2e8f0;">{{ $cambio['before'] }}</td>
                                                <td style="padding:8px 10px; font-size:13px; color:#0f172a; font-weight:bold; border-bottom:1px solid #e2e8f0;">{{ $cambio['after'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif

                            @if ($accion === 'eliminado')
                                <p style="margin:0; color:#64748b; font-size:13px;">
                                    Si crees que esto es un error, contacta a tu administrador de TI.
                                </p>
                            @else
                                <p style="text-align:center; margin: 20px 0;">
                                    <a href="{{ config('app.url') }}" style="background:#0e6f6a; color:#ffffff; text-decoration:none; padding:12px 28px; border-radius:8px; font-size:14px; font-weight:bold; display:inline-block;">
                                        Ingresar al sistema
                                    </a>
                                </p>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f8fafc; padding:16px 28px; border-top:1px solid #e2e8f0;">
                            <p style="margin:0; color:#94a3b8; font-size:11px;">Este es un correo automático, por favor no lo respondas.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
