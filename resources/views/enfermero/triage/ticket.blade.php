<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Triage</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .urgency-box { 
            text-align: center; padding: 15px; margin: 20px 0; border: 2px solid #333; font-weight: bold; font-size: 18px; text-transform: uppercase;
        }
        .text-Rojo { background-color: #ff4d4d; color: #fff; }
        .text-Naranja { background-color: #ff9933; color: #fff; }
        .text-Amarillo { background-color: #ffcc00; color: #000; }
        .text-Verde { background-color: #33cc33; color: #fff; }
        .text-Azul { background-color: #3399ff; color: #fff; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Hospital General - Área de Urgencias</h2>
        <p><strong>Comprobante de Clasificación Triage (NOM-027-SSA3-2013)</strong></p>
        <p>Folio de Atención: #{{ str_pad($triage->id_triage, 6, '0', STR_PAD_LEFT) }}</p>
        <p>Fecha y Hora: {{ \Carbon\Carbon::parse($triage->fecha_clasificacion)->format('d/m/Y H:i:s') }}</p>
    </div>

    <table>
        <tr>
            <th>Paciente:</th>
            <td>{{ $triage->nombre }} {{ $triage->apellidoPat }}</td>
            <th>Nacimiento:</th>
            <td>{{ \Carbon\Carbon::parse($triage->fechaNacimiento)->format('d/m/Y') }}</td>
        </tr>
    </table>

    <div class="urgency-box text-{{ $triage->color_calculado }}">
        CÓDIGO {{ $triage->color_calculado }}
    </div>
    <p style="text-align: center;"><strong>Criterio aplicado:</strong> {{ $triage->criterio_aplicado }}</p>

    <h3>Signos Vitales Capturados</h3>
    <table>
        <tr>
            <th>Presión Arterial</th>
            <td>{{ $triage->presion_sistolica }} / {{ $triage->presion_diastolica }} mmHg</td>
            <th>Frecuencia Cardíaca</th>
            <td>{{ $triage->frecuencia_cardiaca }} lpm</td>
        </tr>
        <tr>
            <th>Frecuencia Respiratoria</th>
            <td>{{ $triage->frecuencia_respiratoria }} rpm</td>
            <th>Saturación O2</th>
            <td>{{ $triage->saturacion_oxigeno }} %</td>
        </tr>
        <tr>
            <th>Temperatura</th>
            <td>{{ $triage->temperatura }} °C</td>
            <th></th>
            <td></td>
        </tr>
    </table>

    <p style="text-align: center; font-size: 10px; margin-top: 40px; color: #777;">
        Este documento es para control interno clínico y no constituye una receta médica. <br>
        Generado automáticamente por el Sistema de Clasificador Triage.
    </p>

</body>
</html>