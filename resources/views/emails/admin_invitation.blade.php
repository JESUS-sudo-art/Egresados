<!DOCTYPE html>
<html lang="es">
<head><meta charset="utf-8"><title>Invitación Administrador</title></head>
<body style="font-family: Arial, sans-serif;">
    <h2>Invitación</h2>
    <p>Hola {{ $name }},</p>
    <p>Has sido invitado como <strong>{{ $role }}</strong> en el Sistema de Egresados.</p>
    <p>Para crear tu contraseña y activar tu acceso haz clic en el siguiente enlace:</p>
    <p><a href="{{ $url }}" style="background:#2563eb;color:#fff;padding:10px 15px;text-decoration:none;border-radius:4px;">Aceptar invitación</a></p>
    @if($expires)
        <p>Esta invitación expira el {{ $expires->format('d/m/Y H:i') }}.</p>
    @endif
    <p>Si no esperabas este correo puedes ignorarlo.</p>
    <p>Saludos.</p>
</body>
</html>
