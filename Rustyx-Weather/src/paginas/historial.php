<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../clases/BaseDatos.php';

$tituloPagina = 'RustyX Weather · Historial';

// Obtenemos los datos del historial
$consultas    = [];
$estadisticas = [];
try {
    $dao          = new ConsultaDAO();
    $consultas    = $dao->obtenerTodas(100);
    $estadisticas = $dao->obtenerEstadisticas();
} catch (Exception $e) {}

include __DIR__ . '/../recursos/layout_header.php';

// Mapas de etiquetas para los tipos de consulta
$estiloInsignia = ['actual' => 'insignia-actual', 'horas' => 'insignia-horas', 'semana' => 'insignia-semana'];
$etiquetaTipo   = ['actual' => 'Actual', 'horas' => 'Por horas', 'semana' => 'Semanal'];
?>

<h1 class="titulo-pagina">Historial</h1>
<p class="subtitulo-pagina">Todas las consultas realizadas en la aplicación</p>

<!-- Gráfica de ciudades más buscadas -->
<?php if (!empty($estadisticas)): ?>
<div style="margin-bottom:2rem">
    <h2 style="font-family:var(--fuente-titulo);font-size:1rem;margin-bottom:1rem;color:var(--muted)">Ciudades más consultadas</h2>
    <div class="contenedor-grafica">
        <canvas id="graficaEstadisticas"></canvas>
    </div>
</div>
<script>
new Chart(document.getElementById('graficaEstadisticas').getContext('2d'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_map(fn($r) => $r['nombre_ciudad'] . ' (' . $r['tipo_consulta'] . ')', $estadisticas)) ?>,
        datasets: [{
            label: 'Consultas',
            data: <?= json_encode(array_map(fn($r) => (int)$r['total'], $estadisticas)) ?>,
            backgroundColor: '#5b4ff7aa',
            borderColor: '#5b4ff7',
            borderWidth: 2,
            borderRadius: 8,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { ticks: { color: '#64748b' }, grid: { color: '#1e2a40' } },
            y: { ticks: { color: '#e8edf5', font: { size: 11 } }, grid: { display: false } }
        }
    }
});
</script>
<?php endif; ?>

<!-- Tabla de consultas -->
<?php if (empty($consultas)): ?>
    <p style="color:var(--muted)">Todavía no se han realizado consultas.</p>
<?php else: ?>
<div style="overflow-x:auto">
    <table class="tabla-historial">
        <thead>
            <tr>
                <th>#</th>
                <th>Ciudad</th>
                <th>País</th>
                <th>Tipo</th>
                <th>Fecha</th>
                <th>IP</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($consultas as $consulta): ?>
            <tr>
                <td style="color:var(--muted)"><?= $consulta['id'] ?></td>
                <td><?= htmlspecialchars($consulta['nombre_ciudad']) ?></td>
                <td><?= htmlspecialchars($consulta['codigo_pais']) ?></td>
                <td>
                    <span class="insignia <?= $estiloInsignia[$consulta['tipo_consulta']] ?? '' ?>">
                        <?= $etiquetaTipo[$consulta['tipo_consulta']] ?? $consulta['tipo_consulta'] ?>
                    </span>
                </td>
                <td style="color:var(--muted)"><?= $consulta['fecha_consulta'] ?></td>
                <td style="color:var(--muted);font-size:.75rem"><?= htmlspecialchars($consulta['ip_usuario']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../recursos/layout_footer.php'; ?>
