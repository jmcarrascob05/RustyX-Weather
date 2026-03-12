<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tituloPagina ?? 'RustyX Weather') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="/recursos/estilos/style.css">
</head>
<body>

<header class="cabecera">
    <a href="/" class="logo">RustyX Weather</a>
    <nav>
        <a href="/">Buscar</a>
        <a href="/paginas/historial.php">Historial</a>
    </nav>
</header>

<main class="contenido-principal">
