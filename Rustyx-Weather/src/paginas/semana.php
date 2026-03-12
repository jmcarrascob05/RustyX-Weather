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
$datos = $api->previsionSemanal($latitud, $longitud);

// Guardar consulta
if ($datos && isset($datos['list'])) {
    try {
        $dao = new ConsultaDAO();
        $dao->guardar($ciudad, $pais, $latitud, $longitud, 'semana');
    } catch (Exception $e) {}
}

// Agrupar los datos por día y calcular máxima/mínima
$dias = [];
if ($datos && isset($datos['list'])) {
    foreach ($datos['list'] as $franja) {
        $fechaClave = date('Y-m-d', $franja['dt']);

        if (!isset($dias[$fechaClave])) {
            $dias[$fechaClave] = [
                'timestamp'   => $franja['dt'],
                'temperaturas'=> [],
                'icono'       => $franja['weather'][0]['icon'] ?? '01d',
                'descripcion' => ucfirst($franja['weather'][0]['description'] ?? ''),
            ];
        }

        $dias[$fechaClave]['temperaturas'][] = $franja['main']['temp'];

        // Preferimos el icono del mediodía
        $hora = (int)date('H', $franja['dt']);
        if ($hora >= 11 && $hora <= 14) {
            $dias[$fechaClave]['icono']      = $franja['weather'][0]['icon'] ?? $dias[$fechaClave]['icono'];
            $dias[$fechaClave]['descripcion'] = ucfirst($franja['weather'][0]['description'] ?? $dias[$fechaClave]['descripcion']);
        }
    }
}

$tituloPagina = "RustyX Weather · Semana · $ciudad";
include __DIR__ . '/../recursos/layout_header.php';

$nombresDias = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
?>

<a class="enlace-volver" href="javascript:history.back()">← Volver</a>

<?php if (empty($dias)): ?>
    <div class="alerta">No se pudieron obtener datos. Inténtalo de nuevo.</div>
<?php else: ?>

<h1 class="titulo-pagina"><?= htmlspecialchars($ciudad) ?>, <?= htmlspecialchars($pais) ?></h1>
<p class="subtitulo-pagina">Previsión semanal · <?= count($dias) ?> días</p>

<!-- Filas por día -->
<div class="lista-dias">
<?php foreach ($dias as $dia):
    $tempMaxima = max($dia['temperaturas']);
    $tempMinima = min($dia['temperaturas']);
    $nombreDia  = $nombresDias[(int)date('w', $dia['timestamp'])];
    $fechaDia   = date('d/m', $dia['timestamp']);
?>
    <div class="fila-dia">
        <div class="nombre-dia"><?= $nombreDia ?> <span class="fecha-dia"><?= $fechaDia ?></span></div>
        <img src="<?= ApiDelTiempo::urlIcono($dia['icono']) ?>" alt="">
        <div class="desc-dia"><?= $dia['descripcion'] ?></div>
        <div class="temp-maxima"><?= round($tempMaxima) ?>°C</div>
        <div class="temp-minima"><?= round($tempMinima) ?>°C</div>
    </div>
<?php endforeach; ?>
</div>

<!-- Gráfica semanal -->
<div class="contenedor-grafica">
    <canvas id="graficaSemana"></canvas>
</div>

<?php
// Datos para la gráfica
$etiquetas  = [];
$maximas    = [];
$minimas    = [];
foreach ($dias as $dia) {
    $etiquetas[] = $nombresDias[(int)date('w', $dia['timestamp'])] . ' ' . date('d/m', $dia['timestamp']);
    $maximas[]   = round(max($dia['temperaturas']), 1);
    $minimas[]   = round(min($dia['temperaturas']), 1);
}
?>
<script>
new Chart(document.getElementById('graficaSemana').getContext('2d'), {
    type: 'line',
    data: {
        labels: <?= json_encode($etiquetas) ?>,
        datasets: [
            {
                label: 'Máxima (°C)',
                data: <?= json_encode($maximas) ?>,
                borderColor: '#00c8ff',
                backgroundColor: 'rgba(0,200,255,.12)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#00c8ff',
            },
            {
                label: 'Mínima (°C)',
                data: <?= json_encode($minimas) ?>,
                borderColor: '#a855f7',
                backgroundColor: 'rgba(168,85,247,.08)',
                fill: true,
                tension: 0.4,
                pointRadius: 5,
                pointBackgroundColor: '#a855f7',
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: '#e8edf5', font: { family: 'Space Mono', size: 11 } } },
            title: { display: true, text: 'Temperaturas semanales', color: '#64748b', font: { family: 'Space Mono', size: 12 } }
        },
        scales: {
            x: { ticks: { color: '#64748b' }, grid: { color: '#1e2a40' } },
            y: { ticks: { color: '#64748b', callback: v => v + '°C' }, grid: { color: '#1e2a40' } }
        }
    }
});
</script>

<?php endif; ?>
<?php include __DIR__ . '/../recursos/layout_footer.php'; ?>
