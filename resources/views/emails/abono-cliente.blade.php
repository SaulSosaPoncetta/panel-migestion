<!doctype html>
<html lang="es">
<body style="margin:0;padding:0;background:#f4f6f8;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;padding:24px 0;">
    <tr><td align="center">
        <table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;">
            <tr><td style="background:#14532d;color:#ffffff;padding:18px 24px;font-size:18px;font-weight:bold;">
                {{ $sistema->nombre }}
            </td></tr>
            <tr><td style="padding:24px;font-size:15px;line-height:1.55;">
                <p style="margin-top:0;">Hola {{ $cliente->nombre }},</p>

                @if($tipo === 'vencido')
                    <p>Tu servicio de <strong>{{ $sistema->nombre }}</strong> venció
                        @if($suscripcion->fecha_vencimiento) el <strong>{{ $suscripcion->fecha_vencimiento->format('d/m/Y') }}</strong>@endif
                        y todavía no registramos el pago de la renovación.</p>
                    <p>Para volver a usarlo sin limitaciones, renovalo desde el botón de abajo. Tarda un par de minutos y se activa apenas se acredita el pago.</p>
                @else
                    <p>Tu servicio de <strong>{{ $sistema->nombre }}</strong> vence
                        @if($dias === 0) <strong>hoy</strong>
                        @elseif($dias === 1) <strong>mañana</strong>
                        @else en <strong>{{ $dias }} días</strong>
                        @endif
                        (<strong>{{ $suscripcion->fecha_vencimiento->format('d/m/Y') }}</strong>).</p>
                    <p>Si lo renovás ahora, no pierdas ni un día: el nuevo período se suma a continuación del actual.</p>
                @endif

                <table cellpadding="0" cellspacing="0" style="margin:8px 0 20px;background:#f4f6f8;border-radius:6px;width:100%;">
                    <tr><td style="padding:12px 16px;font-size:14px;">
                        <div><span style="color:#6b7280;">Plan:</span> {{ $suscripcion->plan }}</div>
                        @if($suscripcion->cantidad > 1)
                            <div><span style="color:#6b7280;">Cantidad:</span> {{ $suscripcion->cantidad }} {{ $suscripcion->planCatalogo?->unidad_nombre ?: 'unidades' }}</div>
                        @endif
                        <div><span style="color:#6b7280;">Importe:</span> {{ $suscripcion->moneda }} {{ number_format($suscripcion->monto, 2, ',', '.') }}</div>
                    </td></tr>
                </table>

                <p style="text-align:center;margin:24px 0;">
                    <a href="{{ $urlRenovar }}" style="background:#14532d;color:#ffffff;text-decoration:none;padding:12px 28px;border-radius:6px;font-weight:bold;display:inline-block;">
                        {{ $tipo === 'vencido' ? 'Renovar ahora' : 'Renovar mi servicio' }}
                    </a>
                </p>

                <p style="font-size:13px;color:#6b7280;">Podés pagar con Mercado Pago o por transferencia bancaria. Si ya pagaste, ignorá este mensaje: puede que el pago todavía se esté acreditando.</p>
            </td></tr>
            <tr><td style="padding:14px 24px;background:#f9fafb;font-size:12px;color:#9ca3af;text-align:center;">
                MiGestión · Este es un aviso automático.
            </td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
