<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes Estadísticos - Triage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark">Analítica de Triage NOM-027</h2>
            <a href="{{ route('admin.personal.index') }}" class="btn btn-outline-secondary">Volver al Panel</a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <h5 class="text-center fw-bold mb-4">Pacientes Clasificados por Nivel de Urgencia</h5>
                    
                    <!-- Contenedor del Canvas para Chart.js -->
                    <div style="position: relative; height:400px; width:100%">
                        <canvas id="graficaTriage"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Recibimos los datos procesados desde el Controlador de Laravel
        const etiquetas = {!! json_encode($colores) !!};
        const valores = {!! json_encode($totales) !!};
        
        // Mapeo de colores reales de la NOM-027 para la gráfica
        const paletaColores = etiquetas.map(color => {
            if(color === 'Rojo') return '#d90429';
            if(color === 'Naranja') return '#f97316';
            if(color === 'Amarillo') return '#eab308';
            if(color === 'Verde') return '#22c55e';
            if(color === 'Azul') return '#3b82f6';
            return '#6c757d';
        });

        // Configuración de Chart.js
        const ctx = document.getElementById('graficaTriage').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut', // Puedes cambiarlo a 'bar' o 'pie'
            data: {
                labels: etiquetas,
                datasets: [{
                    label: 'Total de Pacientes',
                    data: valores,
                    backgroundColor: paletaColores,
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    </script>
</body>
</html>