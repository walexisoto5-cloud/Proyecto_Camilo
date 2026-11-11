<?php

date_default_timezone_set('America/Bogota');

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        $envFile = __DIR__ . '/../.env';
        $env = $this->cargarEnv($envFile);

        // Detectar si corre en local (XAMPP / localhost) para fallbacks seguros
        $httpHost = $_SERVER['HTTP_HOST'] ?? '';
        $isLocal = empty($httpHost)
            || in_array($httpHost, ['localhost', '127.0.0.1', '::1'])
            || (strpos($httpHost, 'localhost:') === 0)
            || (strpos($httpHost, '127.0.0.1:') === 0)
            || (php_sapi_name() === 'cli');

        // Lee directamente las variables estándar del .env de cada entorno
        // Si no existen en local, aplica los valores por defecto de XAMPP
        $this->host     = !empty($env['DB_HOST']) ? $env['DB_HOST'] : ($env['DB_LOCAL_HOST'] ?? 'localhost');
        $this->username = isset($env['DB_USER']) ? $env['DB_USER'] : ($env['DB_LOCAL_USER'] ?? ($isLocal ? 'root' : ''));
        $this->password = isset($env['DB_PASS']) ? $env['DB_PASS'] : ($env['DB_LOCAL_PASS'] ?? '');
        $this->db_name  = !empty($env['DB_NAME']) ? $env['DB_NAME'] : ($env['DB_LOCAL_NAME'] ?? ($isLocal ? 'asistencia_db' : ''));
    }

    /**
     * Lector de .env robusto y seguro.
     * Lee línea por línea evitando que caracteres especiales como '~', '#', comillas o espacios
     * rompan el parser como ocurre con parse_ini_file().
     */
    private function cargarEnv(string $envFile): array {
        if (!file_exists($envFile)) {
            return [];
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $data = [];

        foreach ($lines as $line) {
            $line = trim($line);

            // Ignorar líneas vacías o comentarios
            if ($line === '' || str_starts_with($line, '#') || str_starts_with($line, ';')) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $val) = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val);

                // Quitar comillas simples o dobles envolventes si existen
                $len = strlen($val);
                if ($len >= 2) {
                    $first = $val[0];
                    $last = $val[$len - 1];
                    if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                        $val = substr($val, 1, -1);
                    }
                }

                $data[$key] = $val;
            }
        }

        return $data;
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = @new mysqli($this->host, $this->username, $this->password, $this->db_name);

            if ($this->conn->connect_error) {
                die("Error de conexión a la base de datos (" . $this->db_name . ") en el host (" . $this->host . "): " . $this->conn->connect_error);
            }

            $this->conn->set_charset("utf8mb4");
            $this->conn->query("SET time_zone = '-05:00'");
        } catch (Throwable $e) {
            die("Error crítico de base de datos: " . $e->getMessage());
        }

        return $this->conn;
    }
}
?>
