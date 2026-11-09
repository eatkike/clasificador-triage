<!DOCTYPE html>
<html>
<head>
    <title>Recuperar Contraseña</title>
</head>
<body style="font-family: Arial, sans-serif; text-align: center; padding: 20px;">
    <h2>Sistema de Triage - UPEMOR</h2>
    <p>Recibimos una solicitud para restablecer tu contraseña.</p>
    <p>Haz clic en el siguiente enlace para crear una nueva:</p>
    <a href="{{ route('password.reset', ['token' => $token, 'correo' => $correo]) }}" 
       style="display: inline-block; padding: 10px 20px; background-color: #0f4c5c; color: #ffffff; text-decoration: none; border-radius: 5px;">
       Restablecer Contraseña
    </a>
    <p style="margin-top: 20px; font-size: 12px; color: #777;">Si no solicitaste este cambio, ignora este correo.</p>
</body>
</html>