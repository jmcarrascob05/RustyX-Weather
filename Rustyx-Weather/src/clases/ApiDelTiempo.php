<?php
class ApiDelTiempo {

    // Hace una petición HTTP y devuelve el resultado como array
    private function peticion(string $url): ?array {
        $contexto  = stream_context_create(['http' => ['timeout' => 10]]);
        $respuesta = @file_get_contents($url, false, $contexto);
        if ($respuesta === false) return null;
        return json_decode($respuesta, true);
    }

    // Busca ciudades por nombre y devuelve lat/lon
    public function buscarCiudad(string $nombre): ?array {
        $url       = URL_BASE_API . '/geo/1.0/direct?q=' . urlencode($nombre) . '&limit=5&appid=' . CLAVE_API;
        $resultado = $this->peticion($url);
        return empty($resultado) ? null : $resultado;
    }

    // Tiempo actual
    public function tiempoActual(float $lat, float $lon): ?array {
        $url = URL_BASE_API . '/data/2.5/weather?lat=' . $lat . '&lon=' . $lon
             . '&appid=' . CLAVE_API . '&units=' . UNIDADES . '&lang=' . IDIOMA;
        return $this->peticion($url);
    }

    // Previsión por horas (próximas 24h = 8 tramos de 3h)
    public function previsionHoras(float $lat, float $lon): ?array {
        $url = URL_BASE_API . '/data/2.5/forecast?lat=' . $lat . '&lon=' . $lon
             . '&appid=' . CLAVE_API . '&units=' . UNIDADES . '&lang=' . IDIOMA . '&cnt=8';
        return $this->peticion($url);
    }

    // Previsión semanal (5 días)
    public function previsionSemanal(float $lat, float $lon): ?array {
        $url = URL_BASE_API . '/data/2.5/forecast?lat=' . $lat . '&lon=' . $lon
             . '&appid=' . CLAVE_API . '&units=' . UNIDADES . '&lang=' . IDIOMA . '&cnt=40';
        return $this->peticion($url);
    }

    // URL del icono del tiempo
    public static function urlIcono(string $icono): string {
        return 'https://openweathermap.org/img/wn/' . $icono . '@2x.png';
    }

    // Convierte grados a punto cardinal
    public static function direccionViento(int $grados): string {
        $puntos = ['N', 'NE', 'E', 'SE', 'S', 'SO', 'O', 'NO'];
        return $puntos[round($grados / 45) % 8];
    }
}
