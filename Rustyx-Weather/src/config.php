<?php
// Clave de la API de OpenWeatherMap
define('CLAVE_API',   getenv('OWM_API_KEY') ?: '25e7d3a28b3787cf76fff13dd02bf706');
define('URL_BASE_API', 'https://api.openweathermap.org');

// Conexión a la base de datos
define('BD_HOST',     getenv('DB_HOST') ?: 'db');
define('BD_NOMBRE',   getenv('DB_NAME') ?: 'weatherapp');
define('BD_USUARIO',  getenv('DB_USER') ?: 'weatheruser');
define('BD_CLAVE',    getenv('DB_PASS') ?: 'weatherpass');

// Idioma y unidades de medida
define('IDIOMA',      'es');
define('UNIDADES',    'metric');
