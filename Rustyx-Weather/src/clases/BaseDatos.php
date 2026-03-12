<?php
class BaseDatos {
    private static ?PDO $instancia = null;

    // Devuelve la conexión a la base de datos (patrón singleton)
    public static function conexion(): PDO {
        if (self::$instancia === null) {
            $dsn      = 'mysql:host=' . BD_HOST . ';dbname=' . BD_NOMBRE . ';charset=utf8mb4';
            $intentos = 5;

            while ($intentos > 0) {
                try {
                    self::$instancia = new PDO($dsn, BD_USUARIO, BD_CLAVE, [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    ]);
                    break;
                } catch (PDOException $e) {
                    $intentos--;
                    if ($intentos === 0) throw $e;
                    sleep(2);
                }
            }
        }
        return self::$instancia;
    }
}

class ConsultaDAO {
    private PDO $bd;

    public function __construct() {
        $this->bd = BaseDatos::conexion();
    }

    // Guarda una nueva consulta en la base de datos
    public function guardar(string $ciudad, string $pais, float $lat, float $lon, string $tipo): bool {
        $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $sql  = 'INSERT INTO consultas (nombre_ciudad, codigo_pais, latitud, longitud, tipo_consulta, ip_usuario)
                 VALUES (?, ?, ?, ?, ?, ?)';
        $stmt = $this->bd->prepare($sql);
        return $stmt->execute([$ciudad, $pais, $lat, $lon, $tipo, $ip]);
    }

    // Devuelve las últimas consultas realizadas
    public function obtenerTodas(int $limite = 100): array {
        $sql  = 'SELECT * FROM consultas ORDER BY fecha_consulta DESC LIMIT ' . $limite;
        $stmt = $this->bd->query($sql);
        return $stmt->fetchAll();
    }

    // Devuelve las ciudades más consultadas para las estadísticas
    public function obtenerEstadisticas(): array {
        $sql  = 'SELECT nombre_ciudad, codigo_pais, tipo_consulta, COUNT(*) as total
                 FROM consultas
                 GROUP BY nombre_ciudad, codigo_pais, tipo_consulta
                 ORDER BY total DESC
                 LIMIT 10';
        $stmt = $this->bd->query($sql);
        return $stmt->fetchAll();
    }
}
