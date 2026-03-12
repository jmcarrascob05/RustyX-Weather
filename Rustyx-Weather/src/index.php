<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/clases/ApiDelTiempo.php';
require_once __DIR__ . '/clases/BaseDatos.php';

$tituloPagina = 'RustyX Weather · Buscar ciudad';
$ciudades     = [];
$error        = '';
$busqueda     = trim($_GET['ciudad'] ?? '');

// Si hay búsqueda, llamar a la API de geocodificación
if ($busqueda !== '') {
    $api      = new ApiDelTiempo();
    $ciudades = $api->buscarCiudad($busqueda);

    if ($ciudades === null) {
        $error = 'No se encontró ninguna ciudad con el nombre "' . htmlspecialchars($busqueda) . '". Prueba con otro nombre.';
    }
}

include __DIR__ . '/recursos/layout_header.php';
?>

<div class="hero">
    <h1><span>El tiempo</span><br>del mundo</h1>
    <p>Consulta la previsión meteorológica de cualquier ciudad</p>
</div>

<form class="formulario-busqueda" method="GET" action="/">
    <input
        type="text"
        name="ciudad"
        placeholder="Escribe una ciudad... (ej: Madrid, Tokyo)"
        value="<?= htmlspecialchars($busqueda) ?>"
        autofocus
        autocomplete="off"
    >
    <button type="submit">Buscar</button>
</form>

<?php if ($error): ?>
    <div class="alerta"><?= $error ?></div>
<?php endif; ?>

<?php if (!empty($ciudades)): ?>
<div class="lista-ciudades">
    <?php foreach ($ciudades as $ciudad): ?>
    <div class="tarjeta-ciudad">
        <div>
            <h3>
                <?= htmlspecialchars($ciudad['name']) ?>
                <?= isset($ciudad['state']) ? ', ' . htmlspecialchars($ciudad['state']) : '' ?>
            </h3>
            <div class="coordenadas">
                <?= htmlspecialchars($ciudad['country'] ?? '') ?> &nbsp;·&nbsp;
                <?= round($ciudad['lat'], 4) ?>°N, <?= round($ciudad['lon'], 4) ?>°E
            </div>
        </div>
        <div class="grupo-botones">
            <a class="boton boton-principal"
               href="/paginas/actual.php?lat=<?= $ciudad['lat'] ?>&lon=<?= $ciudad['lon'] ?>&ciudad=<?= urlencode($ciudad['name']) ?>&pais=<?= urlencode($ciudad['country'] ?? '') ?>">
                Ahora
            </a>
            <a class="boton boton-secundario"
               href="/paginas/horas.php?lat=<?= $ciudad['lat'] ?>&lon=<?= $ciudad['lon'] ?>&ciudad=<?= urlencode($ciudad['name']) ?>&pais=<?= urlencode($ciudad['country'] ?? '') ?>">
                Por horas
            </a>
            <a class="boton boton-terciario"
               href="/paginas/semana.php?lat=<?= $ciudad['lat'] ?>&lon=<?= $ciudad['lon'] ?>&ciudad=<?= urlencode($ciudad['name']) ?>&pais=<?= urlencode($ciudad['country'] ?? '') ?>">
                Semana
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php include __DIR__ . '/recursos/layout_footer.php'; ?>
