<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte NOM-027</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 3px solid #0f4c5c; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #0f4c5c; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; color: #555; font-size: 12px; }
        .info-bar { background-color: #f1f5f9; padding: 10px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; text-align: center; }
        .chart-container { text-align: center; margin-bottom: 30px; }
        .chart-container img { max-width: 400px; height: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #0f4c5c; color: #fff; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { border-bottom: 1px solid #ddd; padding: 8px; vertical-align: middle; }
        .color-badge { padding: 4px 8px; color: #fff; font-weight: bold; border-radius: 3px; font-size: 10px; }
        .bg-rojo { background-color: #dc3545; }
        .bg-naranja { background-color: #fd7e14; }
        .bg-amarillo { background-color: #ffc107; color: #000; }
        .bg-verde { background-color: #198754; }
        .bg-azul { background-color: #0d6efd; }
    </style>
</head>
<body>

    <div class="header">
        <h1>Sistema Clasificador de Urgencias</h1>
        <p>Reporte Estadístico Oficial (Estándar NOM-027-SSA3-2013)</p>
    </div>

    <div class="info-bar">
        Tipo de Reporte: {{ $tipo_reporte }} | Turno: {{ $turno }}<br>
        @if($request->fecha_inicio && $request->fecha_fin)
            Periodo: {{ \Carbon\Carbon::parse($request->fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($request->fecha_fin)->format('d/m/Y') }}
        @else
            Periodo: Histórico Completo
        @endif
    </div>

    @if($chartUrl)
        <div class="chart-container">
            <img src="{{ $chartUrl }}" alt="Gráfica de Códigos">
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Fecha y Hora</th>
                <th>Paciente Atendido</th>
                <th>Clasificación</th>
                <th>Criterio Clínico Determinante</th>
            </tr>
        </thead>
        <tbody>
            @foreach($datosTabla as $d)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($d->fecha_clasificacion)->format('d/m/Y H:i') }}</td>
                    <td style="font-weight: bold;">{{ $d->nombre }} {{ $d->apellidoPat }}</td>
                    <td>
                        @php
                            $clase = 'bg-' . strtolower($d->color_calculado);
                        @endphp
                        <span class="color-badge {{ $clase }}">CÓDIGO {{ strtoupper($d->color_calculado) }}</span>
                    </td>
                    <td>{{ $d->criterio_aplicado }}</td>
                </tr>
            @endforeach
            
            @if(count($datosTabla) == 0)
                <tr>
                    <td colspan="4" style="text-align: center; color: #999; padding: 20px;">No hay registros en el periodo/turno seleccionado.</td>
                </tr>
            @endif
        </tbody>
    </table>

</body>
</html>