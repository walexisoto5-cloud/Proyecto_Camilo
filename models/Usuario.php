<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Modelo Usuario - Responsable de la gestión de cuentas, roles, instructores y aprendices
 */
class Usuario
{
    private $conn;

    public function __construct($db = null)
    {
        if ($db) {
            $this->conn = $db;
        } else {
            $database = new Database();
            $this->conn = $database->getConnection();
        }
    }

    /**
     * Obtiene los datos del usuario por nombre de usuario, incluyendo el nombre de su rol
     */
    public function obtenerPorUsuario(string $nombre_usuario): ?array
    {
        $query = "SELECT u.*, r.nombre_rol 
                  FROM usuario u 
                  INNER JOIN rol r ON u.fk_rol = r.id_rol 
                  WHERE u.nombre_usuario = ? LIMIT 1";
        
        $stmt = $this->conn->prepare($query);

        if (!$stmt) {
            error_log("Error SQL en Usuario.php: " . $this->conn->error);
            return null;
        }

        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $fila = $resultado ? $resultado->fetch_assoc() : null;
        return $fila ?: null;
    }

    /**
     * Asegura dinámicamente el ID del rol en la base de datos para evitar errores de clave foránea
     */
    public function obtenerOcrearIdRol(string $nombreRol): int
    {
        $nombreLimpio = $this->conn->real_escape_string($nombreRol);
        $sql = "SELECT id_rol FROM rol WHERE LOWER(nombre_rol) = LOWER('$nombreLimpio') LIMIT 1";
        $res = $this->conn->query($sql);

        if ($res && $res->num_rows > 0) {
            $fila = $res->fetch_assoc();
            $id = (int)$fila['id_rol'];
            $res->free();
            return $id;
        }

        $this->conn->query("INSERT INTO rol (nombre_rol) VALUES ('$nombreLimpio')");
        return (int)$this->conn->insert_id;
    }

    /**
     * Crea un usuario con rol de instructor y contraseña encriptada
     */
    public function crearInstructor(string $identificacion, string $nombre, string $apellido, string $correo, string $contrasena): bool
    {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $idRolInstructor = $this->obtenerOcrearIdRol('Instructor');

        $sql = "INSERT INTO usuario (identificacion, nombre, apellido, nombre_usuario, contrasena, fk_rol) 
                VALUES (?, ?, ?, ?, ?, ?)";
                
        $stmt = $this->conn->prepare($sql);

        if (!$stmt) {
            error_log("Error en prepare SQL crearInstructor: " . $this->conn->error);
            return false;
        }

        $stmt->bind_param("sssssi", $identificacion, $nombre, $apellido, $correo, $hash, $idRolInstructor);
        return $stmt->execute();
    }

    /**
     * Crea un usuario con rol de aprendiz y su correspondiente registro vinculado en la tabla aprendiz
     */
    public function crearAprendiz(string $identificacion, string $nombre, string $apellido, string $correo, string $contrasena, int $idFicha, ?string $codigoRfid = null): bool
    {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $idRolAprendiz = $this->obtenerOcrearIdRol('Aprendiz');

        $sqlUsuario = "INSERT INTO usuario (identificacion, nombre, apellido, nombre_usuario, contrasena, fk_rol) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmtUser = $this->conn->prepare($sqlUsuario);
        if (!$stmtUser) {
            error_log("Error al preparar usuario aprendiz: " . $this->conn->error);
            return false;
        }

        $stmtUser->bind_param("sssssi", $identificacion, $nombre, $apellido, $correo, $hash, $idRolAprendiz);
        if (!$stmtUser->execute()) {
            error_log("Error al ejecutar usuario aprendiz: " . $stmtUser->error);
            return false;
        }

        $idUsuario = $this->conn->insert_id;

        $sqlAprendiz = "INSERT INTO aprendiz (codigo_rfid, fk_ficha, fk_usuario) VALUES (?, ?, ?)";
        $stmtAp = $this->conn->prepare($sqlAprendiz);
        if (!$stmtAp) {
            error_log("Error al preparar aprendiz: " . $this->conn->error);
            return false;
        }

        $stmtAp->bind_param("sii", $codigoRfid, $idFicha, $idUsuario);
        return $stmtAp->execute();
    }

    /**
     * Busca o crea un instructor automáticamente a partir del nombre completo (utilizado al importar cronogramas)
     */
    public function buscarOCrearInstructorPorNombre(string $nombreCompleto): ?int
    {
        $nombreCompleto = trim($nombreCompleto);
        if (empty($nombreCompleto)) {
            return null;
        }

        // 1. Intentar buscar por coincidencia en tabla usuario (rol instructor = 2)
        $sqlBuscar = "SELECT id_usuario FROM usuario 
                      WHERE fk_rol = 2 AND (
                          CONCAT(TRIM(nombre), ' ', TRIM(apellido)) LIKE ? 
                          OR nombre LIKE ? 
                          OR apellido LIKE ?
                      ) LIMIT 1";
        $stmtBuscar = $this->conn->prepare($sqlBuscar);
        if ($stmtBuscar) {
            $like = '%' . $nombreCompleto . '%';
            $stmtBuscar->bind_param("sss", $like, $like, $like);
            $stmtBuscar->execute();
            $res = $stmtBuscar->get_result()->fetch_assoc();
            if ($res && !empty($res['id_usuario'])) {
                return (int)$res['id_usuario'];
            }
        }

        // 2. Si no existe, crear el usuario con rol de Instructor
        $partes = preg_split('/\s+/', $nombreCompleto);
        $nombre = $partes[0] ?? 'Instructor';
        $apellido = isset($partes[1]) ? implode(' ', array_slice($partes, 1)) : 'SENA';

        // Generar nombre de usuario único
        $baseUser = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nombre . '.' . explode(' ', $apellido)[0]));
        if (empty($baseUser)) {
            $baseUser = 'inst.' . rand(100, 999);
        }
        $username = $baseUser;
        $counter = 1;
        while (true) {
            $sqlCheck = "SELECT id_usuario FROM usuario WHERE nombre_usuario = ? LIMIT 1";
            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bind_param("s", $username);
            $stmtCheck->execute();
            if ($stmtCheck->get_result()->num_rows === 0) {
                break;
            }
            $username = $baseUser . $counter;
            $counter++;
        }

        $identificacion = (string)rand(10000000, 99999999);
        $passwordHash = password_hash('sena1234', PASSWORD_DEFAULT);
        $rolInstructor = 2;

        $sqlInsert = "INSERT INTO usuario (identificacion, nombre, apellido, nombre_usuario, contrasena, fk_rol) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmtInsert = $this->conn->prepare($sqlInsert);
        if (!$stmtInsert) {
            error_log("Error al preparar usuario instructor: " . $this->conn->error);
            return null;
        }

        $stmtInsert->bind_param("sssssi", $identificacion, $nombre, $apellido, $username, $passwordHash, $rolInstructor);
        if ($stmtInsert->execute()) {
            return (int)$this->conn->insert_id;
        }

        return null;
    }

    /**
     * Retorna el número total de instructores en la base de datos
     */
    public function obtenerTotalInstructores(): int
    {
        $idRolInstructor = $this->obtenerOcrearIdRol('Instructor');
        $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM usuario WHERE fk_rol = ?");
        if (!$stmt) {
            return 0;
        }
        $stmt->bind_param("i", $idRolInstructor);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Obtiene una lista de instructores registrados con límite configurable
     */
    public function obtenerInstructores(int $limite = 5): array
    {
        $idRolInstructor = $this->obtenerOcrearIdRol('Instructor');
        $stmt = $this->conn->prepare("SELECT id_usuario, nombre, apellido, identificacion, nombre_usuario, telefono 
                                      FROM usuario WHERE fk_rol = ? ORDER BY id_usuario DESC LIMIT ?");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("ii", $idRolInstructor, $limite);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}