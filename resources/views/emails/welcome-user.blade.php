<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f4f7f6; padding: 24px; margin:0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius: 12px; overflow:hidden; border:1px solid #e2e8f0;">
                    <tr>
                        <td style="background:#0e6f6a; padding: 28px;">
                            <h1 style="color:#ffffff; margin:0; font-size:20px;">¡Bienvenido a {{ config('app.name') }}!</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 28px;">
                            <p style="margin:0 0 16px; color:#0f172a; font-size:15px;">
                                Hola <strong>{{ $nombre }}</strong>, se ha creado una cuenta para ti en el sistema de gestión de inventario TI.
                                A continuación encontrarás tus credenciales de acceso:
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:14px 18px; font-size:14px; color:#475569; width:40%;">Usuario / Correo</td>
                                    <td style="padding:14px 18px; font-size:14px; color:#0f172a; font-weight:bold;">{{ $email }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:14px 18px; font-size:14px; color:#475569; border-top:1px solid #e2e8f0;">Contraseña temporal</td>
                                    <td style="padding:14px 18px; font-size:14px; color:#0f172a; font-weight:bold; border-top:1px solid #e2e8f0;">{{ $passwordTemporal }}</td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; margin-bottom:20px;">
                                <tr>
                                    <td style="padding:14px 18px; font-size:13px; color:#9a3412;">
                                        <strong>Recomendación de seguridad:</strong> por tu seguridad, cambia esta contraseña temporal apenas inicies sesión por primera vez.
                                    </td>
                                </tr>
                            </table>

                            <p style="text-align:center; margin: 24px 0;">
                                <a href="{{ config('app.url') }}" style="background:#0e6f6a; color:#ffffff; text-decoration:none; padding:12px 28px; border-radius:8px; font-size:14px; font-weight:bold; display:inline-block;">
                                    Ingresar al sistema
                                </a>
                            </p>

                            <p style="margin:0; color:#64748b; font-size:12px;">
                                Si no esperabas este correo, contacta a tu administrador de TI.
                            </p>
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
