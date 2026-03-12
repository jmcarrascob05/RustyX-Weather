<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../clases/ApiDelTiempo.php';
require_once __DIR__ . '/../clases/BaseDatos.php';

// Recogemos los parámetros de la URL
$latitud  = (float)($_GET['lat']    ?? 0);
$longitud = (float)($_GET['lon']    ?? 0);
$ciudad   = $_GET['ciudad']         ?? 'Ciudad';
$pais     = $_GET['pais']           ?? '';

// Si no hay coordenadas, volver al inicio
if (!$latitud && !$longitud) {
    header('Location: /');
    exit;
}

// Llamada a la API del tiempo
$api   = new ApiDelTiempo();
$datos = $api->tiempoActual($latitud, $longitud);

// Guardar consulta en la base de datos
if ($datos && isset($datos['main'])) {
    try {
        $dao = new ConsultaDAO();
        $dao->guardar($ciudad, $pais, $latitud, $longitud, 'actual');
    } catch (Exception $e) {}
}

$tituloPagina = "RustyX Weather · Actual · $ciudad";
include __DIR__ . '/../recursos/layout_header.php';
?>

<a class="enlace-volver" href="javascript:history.back()">← Volver</a>

<?php if (!$datos || !isset($datos['main'])): ?>
    <div class="alerta">No se pudieron obtener datos. Inténtalo de nuevo.</div>
<?php else:
    // Extraemos los datos más usados en variables con nombre claro
    $temperatura    = round($datos['main']['temp']);
    $sensacion      = round($datos['main']['feels_like']);
    $tempMin        = round($datos['main']['temp_min'], 1);
    $tempMax        = round($datos['main']['temp_max'], 1);
    $humedad        = $datos['main']['humidity'];
    $presion        = $datos['main']['pressure'];
    $descripcion    = ucfirst($datos['weather'][0]['description'] ?? '');
    $icono          = $datos['weather'][0]['icon'] ?? '01d';
    $velocidadViento = round(($datos['wind']['speed'] ?? 0) * 3.6);
    $dirViento      = ApiDelTiempo::direccionViento($datos['wind']['deg'] ?? 0);
    $visibilidad    = isset($datos['visibility']) ? round($datos['visibility'] / 1000, 1) . ' km' : 'N/D';
    $amanecer       = date('H:i', $datos['sys']['sunrise']);
    $atardecer      = date('H:i', $datos['sys']['sunset']);
?>

<h1 class="titulo-pagina"><?= htmlspecialchars($ciudad) ?>, <?= htmlspecialchars($pais) ?></h1>
<p class="subtitulo-pagina">Tiempo actual · <?= date('d/m/Y H:i') ?></p>

<div class="rejilla-actual">

    <!-- Tarjeta principal con temperatura -->
    <div class="tarjeta-principal">
        <img src="<?= ApiDelTiempo::urlIcono($icono) ?>" alt="<?= $descripcion ?>">
        <div>
            <div class="temperatura"><?= $temperatura ?>°C</div>
            <div class="descripcion"><?= $descripcion ?></div>
            <div class="sensacion">Sensación: <?= $sensacion ?>°C</div>
        </div>
    </div>

    <!-- Tarjetas con datos adicionales -->
    <div class="rejilla-datos">
        <div class="tarjeta-dato">
            <div class="etiqueta">Humedad</div>
            <div class="valor"><?= $humedad ?>%</div>
        </div>
        <div class="tarjeta-dato">
            <div class="etiqueta">Viento</div>
            <div class="valor"><?= $velocidadViento ?> km/h <?= $dirViento ?></div>
        </div>
        <div class="tarjeta-dato">
            <div class="etiqueta">Presión</div>
            <div class="valor"><?= $presion ?> hPa</div>
        </div>
        <div class="tarjeta-dato">
            <div class="etiqueta">Visibilidad</div>
            <div class="valor"><?= $visibilidad ?></div>
        </div>
        <div class="tarjeta-dato">
            <div class="etiqueta">Amanecer</div>
            <div class="valor"><?= $amanecer ?></div>
        </div>
        <div class="tarjeta-dato">
            <div class="etiqueta">Atardecer</div>
            <div class="valor"><?= $atardecer ?></div>
        </div>
    </div>
</div>

<!-- Gráfica de temperaturas -->
<div class="contenedor-grafica">
    <canvas id="graficaActual"></canvas>
</div>

<script>
new Chart(document.getElementById('graficaActual').getContext('2d'), {
    type: 'bar',
    data: {
        labels: ['Temperatura', 'Sensación', 'Mínima', 'Máxima'],
        datasets: [{
            label: '°C',
            data: [<?= $temperatura ?>, <?= $sensacion ?>, <?= $tempMin ?>, <?= $tempMax ?>],
            backgroundColor: ['#00c8ffcc', '#7c3aedcc', '#5b4ff7cc', '#a855f7cc'],
            borderColor:     ['#00c8ff',   '#7c3aed',   '#5b4ff7',   '#a855f7'],
            borderWidth: 2,
            borderRadius: 8,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            title: { display: true, text: 'Resumen de temperaturas', color: '#64748b', font: { family: 'Space Mono', size: 12 } }
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
