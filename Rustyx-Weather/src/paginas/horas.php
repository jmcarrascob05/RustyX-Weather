<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../clases/ApiDelTiempo.php';
require_once __DIR__ . '/../clases/BaseDatos.php';

$latitud  = (float)($_GET['lat']    ?? 0);
$longitud = (float)($_GET['lon']    ?? 0);
$ciudad   = $_GET['ciudad']         ?? 'Ciudad';
$pais     = $_GET['pais']           ?? '';

if (!$latitud && !$longitud) {
    header('Location: /');
    exit;
}

$api   = new ApiDelTiempo();
$datos = $api->previsionHoras($latitud, $longitud);

// Guardar consulta
if ($datos && isset($datos['list'])) {
    try {
        $dao = new ConsultaDAO();
        $dao->guardar($ciudad, $pais, $latitud, $longitud, 'horas');
    } catch (Exception $e) {}
}

$tituloPagina = "RustyX Weather · Por horas · $ciudad";
include __DIR__ . '/../recursos/layout_header.php';
?>

<a class="enlace-volver" href="javascript:history.back()">← Volver</a>

<?php if (!$datos || !isset($datos['list'])): ?>
    <div class="alerta">No se pudieron obtener datos. Inténtalo de nuevo.</div>
<?php else:
    $franjas = $datos['list'];
?>

<h1 class="titulo-pagina"><?= htmlspecialchars($ciudad) ?>, <?= htmlspecialchars($pais) ?></h1>
<p class="subtitulo-pagina">Previsión por horas · próximas 24h</p>

<!-- Tarjetas por hora -->
<div class="rejilla-horas">
<?php foreach ($franjas as $franja):
    $hora        = date('H:i', $franja['dt']);
    $icono       = $franja['weather'][0]['icon'] ?? '01d';
    $descripcion = ucfirst($franja['weather'][0]['description'] ?? '');
    $temperatura = round($franja['main']['temp']);
?>
    <div class="tarjeta-hora">
        <div class="hora"><?= $hora ?></div>
        <img src="<?= ApiDelTiempo::urlIcono($icono) ?>" alt="<?= $descripcion ?>">
        <div class="temperatura"><?= $temperatura ?>°C</div>
        <div class="descripcion"><?= $descripcion ?></div>
    </div>
<?php endforeach; ?>
</div>

<!-- Gráfica de evolución horaria -->
<div class="contenedor-grafica">
    <canvas id="graficaHoras"></canvas>
</div>

<?php
// Preparamos los datos para la gráfica
$etiquetas    = array_map(fn($f) => date('H:i', $f['dt']), $franjas);
$temperaturas = array_map(fn($f) => round($f['main']['temp'], 1), $franjas);
$sensaciones  = array_map(fn($f) => round($f['main']['feels_like'], 1), $franjas);
$humedades    = array_map(fn($f) => $f['main']['humidity'], $franjas);
?>

<script>
new Chart(document.getElementById('graficaHoras').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?= json_encode($etiquetas) ?>,
        datasets: [
            {
                label: 'Temperatura (°C)',
                data: <?= json_encode($temperaturas) ?>,
                borderColor: '#00c8ff',
                backgroundColor: 'rgba(0,200,255,.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 4,
                pointBackgroundColor: '#00c8ff',
            },
            {
                label: 'Sensación (°C)',
                data: <?= json_encode($sensaciones) ?>,
                borderColor: '#7c3aed',
                borderDash: [5, 5],
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#7c3aed',
                fill: false,
            },
            {
                label: 'Humedad (%)',
                data: <?= json_encode($humedades) ?>,
                borderColor: '#a855f7',
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#a855f7',
                fill: false,
                yAxisID: 'ejeDerecho',
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: '#e8edf5', font: { family: 'Space Mono', size: 11 } } },
            title: { display: true, text: 'Evolución horaria', color: '#64748b', font: { family: 'Space Mono', size: 12 } }
        },
        scales: {
            x:           { ticks: { color: '#64748b' }, grid: { color: '#1e2a40' } },
            y:           { ticks: { color: '#64748b', callback: v => v + '°C' }, grid: { color: '#1e2a40' } },
            ejeDerecho:  { position: 'right', ticks: { color: '#a855f7', callback: v => v + '%' }, grid: { display: false } }
        }
    }
});
</script>

<?php endif; ?>
<?php include __DIR__ . '/../recursos/layout_footer.php'; ?>
