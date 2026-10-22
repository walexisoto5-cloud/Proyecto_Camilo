<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

class AprendizController
{
    private function conectar()
    {
        $database = new Database();
        return $database->getConnection();
    }

    private function rolesTodos(): array
    {
        $db = $this->conectar();
        $res = $db->query("SELECT id_rol, nombre_rol FROM rol");
        $roles = [];
        if ($res) {
            while ($r = $res->fetch_assoc()) {
                $roles[$r['nombre_rol']] = (int)$r['id_rol'];
            }
        }
        return $roles;
    }

    // Verifica que haya una sesión iniciada
    private function verificarSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id'])) {
            header("Location: index.php?action=login");
            exit();
        }
    }

    
    public function portal()
    {
        $this->verificarSesion();

        $database = new Database();
        $db = $database->getConnection();
        $idUsuario = (int)$_SESSION['usuario_id'];

        //Obtener los datos del aprendiz y la ficha a la que pertenece
        $sqlAprendiz = "SELECT ap.id_aprendiz, ap.fk_ficha, ap.codigo_rfid, 
                               f.numero_ficha, f.nombre_programa, f.jornada,
                               u.nombre, u.apellido, u.identificacion, u.nombre_usuario
                        FROM aprendiz ap
                        INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                        LEFT JOIN ficha f ON ap.fk_ficha = f.id_ficha
                        WHERE u.id_usuario = ? LIMIT 1";

        $stmtAp = $db->prepare($sqlAprendiz);
        $stmtAp->bind_param("i", $idUsuario);
        $stmtAp->execute();
        $datosAprendiz = $stmtAp->get_result()->fetch_assoc();

        if (!$datosAprendiz) {
            $nombreSesion = $_SESSION['nombre_completo'] ?? 'Usuario Demo';
            $partes = explode(' ', $nombreSesion, 2);

            $datosAprendiz = [
                'id_aprendiz' => 0,
                'nombre' => $partes[0] ?? 'Aprendiz',
                'apellido' => $partes[1] ?? 'Demo',
                'identificacion' => '---',
                'numero_ficha' => '3234082',
                'nombre_programa' => 'ADSO - Análisis y Desarrollo de Software',
                'jornada' => 'Diurna'
            ];
            $mensajeError = "Modo Previsualización: Tu usuario actual (" . htmlspecialchars($_SESSION['rol'] ?? 'Administrador') . ") no tiene registro como aprendiz. Mostrando datos de prueba para visualización.";
            $porcentajeAsistencia = 100;
            $totalAsistencias = 0;
            $totalRetardos = 0;
            $totalFaltas = 0;
            $asistenciaHoy = null;
            $historial = [];
            $misExcusas = [];
            $comiteAprendiz = null;

            require_once __DIR__ . '/../views/aprendiz/dashboard.php';
            return;
        }

        $idAprendiz = (int)$datosAprendiz['id_aprendiz'];

        $sqlTotal = "SELECT COUNT(*) as total FROM asistencia WHERE fk_aprendiz = ?";
        $stmtTotal = $db->prepare($sqlTotal);
        $stmtTotal->bind_param("i", $idAprendiz);
        $stmtTotal->execute();
        $totalSesiones = (int)($stmtTotal->get_result()->fetch_assoc()['total'] ?? 0);

        $sqlAsistencias = "SELECT COUNT(*) as total FROM asistencia 
                           WHERE fk_aprendiz = ? AND (LOWER(estado_entrada) = 'a tiempo' OR LOWER(estado_entrada) = 'presente')";
        $stmtAsist = $db->prepare($sqlAsistencias);
        $stmtAsist->bind_param("i", $idAprendiz);
        $stmtAsist->execute();
        $totalAsistencias = (int)($stmtAsist->get_result()->fetch_assoc()['total'] ?? 0);

        $sqlRetardos = "SELECT COUNT(*) as total FROM asistencia 
                        WHERE fk_aprendiz = ? AND LOWER(estado_entrada) LIKE '%retardo%'";
        $stmtRet = $db->prepare($sqlRetardos);
        $stmtRet->bind_param("i", $idAprendiz);
        $stmtRet->execute();
        $totalRetardos = (int)($stmtRet->get_result()->fetch_assoc()['total'] ?? 0);

        $sqlFaltas = "SELECT COUNT(*) as total FROM asistencia 
                      WHERE fk_aprendiz = ? AND (LOWER(estado_entrada) LIKE '%inasistencia%' OR LOWER(estado_entrada) LIKE '%ausente%' OR LOWER(estado_entrada) LIKE '%falta%')";
        $stmtFaltas = $db->prepare($sqlFaltas);
        $stmtFaltas->bind_param("i", $idAprendiz);
        $stmtFaltas->execute();
        $totalFaltas = (int)($stmtFaltas->get_result()->fetch_assoc()['total'] ?? 0);

        $asistenciasValidas = $totalAsistencias + $totalRetardos;
        if ($totalSesiones > 0) {
            $porcentajeAsistencia = round(($asistenciasValidas / $totalSesiones) * 100);
        } else {
            $porcentajeAsistencia = 100;
        }

        $hoy = date('Y-m-d');
        $sqlHoy = "SELECT * FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
        $stmtHoy = $db->prepare($sqlHoy);
        $stmtHoy->bind_param("is", $idAprendiz, $hoy);
        $stmtHoy->execute();
        $asistenciaHoy = $stmtHoy->get_result()->fetch_assoc();

        $sqlHistorial = "SELECT a.*, f.nombre_programa, 
                                u_inst.nombre AS nombre_instructor, u_inst.apellido AS apellido_instructor
                         FROM asistencia a
                         LEFT JOIN aprendiz ap ON a.fk_aprendiz = ap.id_aprendiz
                         LEFT JOIN ficha f ON ap.fk_ficha = f.id_ficha
                         LEFT JOIN usuario u_inst ON f.fk_usuario = u_inst.id_usuario
                         WHERE a.fk_aprendiz = ?
                         ORDER BY a.fecha_asistencia DESC, a.entrada DESC";
        $stmtHist = $db->prepare($sqlHistorial);
        $stmtHist->bind_param("i", $idAprendiz);
        $stmtHist->execute();
        $historial = $stmtHist->get_result()->fetch_all(MYSQLI_ASSOC);

        $sqlExcusas = "SELECT e.*, a.fecha_asistencia, 
                              u_rev.nombre AS revisor_nombre, u_rev.apellido AS revisor_apellido
                       FROM excusa e
                       INNER JOIN asistencia a ON e.fk_asistencia = a.id_asistencia
                       LEFT JOIN usuario u_rev ON e.fk_usuario_instructor = u_rev.id_usuario
                       WHERE a.fk_aprendiz = ?
                       ORDER BY (CASE WHEN e.estado = 'Pendiente' THEN 1 ELSE 2 END), e.id_excusa DESC";
        $stmtExc = $db->prepare($sqlExcusas);
        $stmtExc->bind_param("i", $idAprendiz);
        $stmtExc->execute();
        $misExcusas = $stmtExc->get_result()->fetch_all(MYSQLI_ASSOC);

        require_once __DIR__ . '/../models/Comite.php';
        $comiteModel = new Comite($db);
        $comiteAprendiz = $comiteModel->obtenerPorAprendiz($idAprendiz);

        require_once __DIR__ . '/../views/aprendiz/dashboard.php';
    }

    public function marcarAsistencia()
    {
        $this->verificarSesion();

        $database = new Database();
        $db = $database->getConnection();
        $idUsuario = (int)$_SESSION['usuario_id'];

        $sql = "SELECT id_aprendiz FROM aprendiz WHERE fk_usuario = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $aprendiz = $stmt->get_result()->fetch_assoc();

        if (!$aprendiz) {
            header("Location: index.php?action=portal_aprendiz");
            exit();
        }

        $idAprendiz = (int)$aprendiz['id_aprendiz'];
        $hoy = date('Y-m-d');
        $ahora = date('Y-m-d H:i:s');
        $horaActual = date('H:i:s');

        $sqlCheck = "SELECT id_asistencia, entrada, salida FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->bind_param("is", $idAprendiz, $hoy);
        $stmtCheck->execute();
        $asist = $stmtCheck->get_result()->fetch_assoc();

        if (!$asist) {
            $estado = ($horaActual > '08:15:00') ? 'Retardo' : 'A tiempo';
            $sqlIns = "INSERT INTO asistencia (fecha_asistencia, entrada, estado_entrada, fk_aprendiz) VALUES (?, ?, ?, ?)";
            $stmtIns = $db->prepare($sqlIns);
            $stmtIns->bind_param("sssi", $hoy, $ahora, $estado, $idAprendiz);
            $stmtIns->execute();
        } else if (empty($asist['salida']) || $asist['salida'] === '0000-00-00 00:00:00') {
            $sqlUp = "UPDATE asistencia SET salida = ?, estado_salida = 'Salida normal' WHERE id_asistencia = ?";
            $stmtUp = $db->prepare($sqlUp);
            $stmtUp->bind_param("si", $ahora, $asist['id_asistencia']);
            $stmtUp->execute();
        }

        header("Location: index.php?action=portal_aprendiz");
        exit();
    }

    // =========================================================================
    // GESTIÓN CRUD DE APRENDICES (ADMINISTRADOR) - SRP
    // =========================================================================
    public function viewAprendices(): void
    {
        requiereRol('Administrador');
        $db = $this->conectar();
        $buscar = trim($_GET['buscar'] ?? '');
        $estado = trim($_GET['estado'] ?? '');

        $sql = "SELECT ap.id_aprendiz, ap.codigo_rfid, ap.fk_ficha,
                       u.id_usuario, u.nombre_usuario, u.nombre, u.apellido, u.identificacion, u.telefono,
                       IFNULL(u.estado, 'Activo') AS estado,
                       f.numero_ficha, f.nombre_programa
                FROM aprendiz ap
                INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                LEFT JOIN ficha f ON ap.fk_ficha = f.id_ficha
                WHERE 1 = 1";
        
        $params = [];
        $tipos = '';

        if ($buscar !== '') {
            $sql .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.identificacion LIKE ? OR u.nombre_usuario LIKE ? OR ap.codigo_rfid LIKE ?)";
            $termino = "%{$buscar}%";
            $params = [$termino, $termino, $termino, $termino, $termino];
            $tipos .= 'sssss';
        }

        if ($estado !== '') {
            $sql .= " AND IFNULL(u.estado, 'Activo') = ?";
            $params[] = $estado;
            $tipos .= 's';
        }

        $sql .= " ORDER BY u.nombre, u.apellido";

        $stmt = $db->prepare($sql);
        if ($tipos !== '') {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        $aprendices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $fichasRes = $db->query("SELECT id_ficha, numero_ficha, nombre_programa, jornada FROM ficha ORDER BY numero_ficha");
        $fichas = $fichasRes ? $fichasRes->fetch_all(MYSQLI_ASSOC) : [];

        $activo = 'gestion_aprendices';
        $rol = $_SESSION['rol'] ?? 'Administrador';
        $nombreUsuario = $_SESSION['nombre_completo'] ?? ($_SESSION['nombre'] ?? 'Administrador');
        $rolUsuario = $rol;
        require_once __DIR__ . '/../views/gestion/aprendices.php';
    }

    public function crearAprendiz(): void
    {
        requiereRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=gestion_aprendices");
            exit();
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $identificacion = trim($_POST['identificacion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');
        $rfid = trim($_POST['codigo_rfid'] ?? '');
        $idFicha = (int)($_POST['fk_ficha'] ?? 0);

        if ($nombre === '' || $apellido === '' || $identificacion === '' || $nombreUsuario === '' || $contrasena === '') {
            header("Location: index.php?action=gestion_aprendices&error=" . urlencode("Todos los campos obligatorios deben ser diligenciados."));
            exit();
        }

        if ($idFicha <= 0) {
            header("Location: index.php?action=gestion_aprendices&error=" . urlencode("Debes asignar una ficha de formación al aprendiz."));
            exit();
        }

        $db = $this->conectar();

        // Verificar si ya existe usuario o identificación
        $checkStmt = $db->prepare("SELECT id_usuario FROM usuario WHERE nombre_usuario = ? OR identificacion = ?");
        $checkStmt->bind_param("ss", $nombreUsuario, $identificacion);
        $checkStmt->execute();
        if ($checkStmt->get_result()->num_rows > 0) {
            header("Location: index.php?action=gestion_aprendices&error=" . urlencode("El nombre de usuario o documento ya se encuentra registrado."));
            exit();
        }

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $roles = $this->rolesTodos();
        $fkRolAprendiz = $roles['Aprendiz'] ?? 3;
        $rfidValor = ($rfid === '') ? null : $rfid;

        $stmt = $db->prepare("INSERT INTO usuario (nombre_usuario, contrasena, nombre, apellido, identificacion, telefono, fk_rol, estado)
                              VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo')");
        $stmt->bind_param("ssssssi", $nombreUsuario, $hash, $nombre, $apellido, $identificacion, $telefono, $fkRolAprendiz);
        
        if (!$stmt->execute()) {
            header("Location: index.php?action=gestion_aprendices&error=" . urlencode($db->error));
            exit();
        }
        $idUsuario = (int)$db->insert_id;

        $stmtAp = $db->prepare("INSERT INTO aprendiz (codigo_rfid, fk_ficha, fk_usuario) VALUES (?, ?, ?)");
        $stmtAp->bind_param("sii", $rfidValor, $idFicha, $idUsuario);
        $stmtAp->execute();

        header("Location: index.php?action=gestion_aprendices&creado=1");
        exit();
    }

    public function importarAprendicesCsv(): void
    {
        requiereRol('Administrador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=gestion_aprendices");
            exit();
        }

        $idFicha = (int)($_POST['fk_ficha'] ?? 0);
        if ($idFicha <= 0) {
            header("Location: index.php?action=gestion_aprendices&error=" . urlencode("Debes seleccionar una ficha de formación válida."));
            exit();
        }

        if (!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) {
            header("Location: index.php?action=gestion_aprendices&error=" . urlencode("Por favor selecciona un archivo CSV válido."));
            exit();
        }

        $rutaTmp = $_FILES['archivo_csv']['tmp_name'];
        $lineas = file($rutaTmp, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (empty($lineas)) {
            header("Location: index.php?action=gestion_aprendices&error=" . urlencode("El archivo CSV se encuentra vacío."));
            exit();
        }

        $db = $this->conectar();
        $roles = $this->rolesTodos();
        $fkRolAprendiz = $roles['Aprendiz'] ?? 3;

        $importados = 0;
        $omitidos = 0;

        foreach ($lineas as $idx => $linea) {
            $delim = (strpos($linea, ';') !== false) ? ';' : ',';
            $datos = str_getcsv(trim($linea), $delim);

            if (empty($datos) || count($datos) < 3) {
                continue;
            }

            if ($idx === 0 && (stripos($datos[0], 'identificacion') !== false || stripos($datos[0], 'documento') !== false || stripos($datos[1], 'nombre') !== false)) {
                continue;
            }

            $identificacion = trim($datos[0] ?? '');
            $nombre = trim($datos[1] ?? '');
            $apellido = trim($datos[2] ?? '');
            $usuario = trim($datos[3] ?? '');
            $telefono = trim($datos[4] ?? '');
            $rfid = trim($datos[5] ?? '');

            if ($identificacion === '' || $nombre === '' || $apellido === '') {
                $omitidos++;
                continue;
            }

            if ($usuario === '') {
                $usuario = $identificacion;
            }

            $rfidValor = ($rfid === '') ? null : $rfid;

            $checkUser = $db->prepare("SELECT id_usuario FROM usuario WHERE identificacion = ? OR nombre_usuario = ?");
            $checkUser->bind_param("ss", $identificacion, $usuario);
            $checkUser->execute();
            $userRes = $checkUser->get_result()->fetch_assoc();

            if ($userRes) {
                $idUsuario = (int)$userRes['id_usuario'];
                $checkAp = $db->prepare("SELECT id_aprendiz FROM aprendiz WHERE fk_usuario = ?");
                $checkAp->bind_param("i", $idUsuario);
                $checkAp->execute();
                $apRes = $checkAp->get_result()->fetch_assoc();

                if (!$apRes) {
                    $insAp = $db->prepare("INSERT INTO aprendiz (codigo_rfid, fk_ficha, fk_usuario) VALUES (?, ?, ?)");
                    $insAp->bind_param("sii", $rfidValor, $idFicha, $idUsuario);
                    $insAp->execute();
                    $importados++;
                } else {
                    $omitidos++;
                }
            } else {
                $hash = password_hash($identificacion, PASSWORD_DEFAULT);
                $insUser = $db->prepare("INSERT INTO usuario (nombre_usuario, contrasena, nombre, apellido, identificacion, telefono, fk_rol, estado)
                                         VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo')");
                $insUser->bind_param("ssssssi", $usuario, $hash, $nombre, $apellido, $identificacion, $telefono, $fkRolAprendiz);
                if ($insUser->execute()) {
                    $idUsuario = (int)$db->insert_id;
                    $insAp = $db->prepare("INSERT INTO aprendiz (codigo_rfid, fk_ficha, fk_usuario) VALUES (?, ?, ?)");
                    $insAp->bind_param("sii", $rfidValor, $idFicha, $idUsuario);
                    $insAp->execute();
                    $importados++;
                } else {
                    $omitidos++;
                }
            }
        }

        header("Location: index.php?action=gestion_aprendices&creado=1&msg=" . urlencode("Se importaron {$importados} aprendices correctamente. ({$omitidos} omitidos o ya existentes)."));
        exit();
    }

    public function actualizarAprendiz(): void
    {
        requiereRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=gestion_aprendices");
            exit();
        }

        $idAprendiz = (int)($_POST['id_aprendiz'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $identificacion = trim($_POST['identificacion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $rfid = trim($_POST['codigo_rfid'] ?? '');
        $idFicha = (int)($_POST['fk_ficha'] ?? 0);
        $estado = ($_POST['estado'] ?? 'Activo') === 'Inactivo' ? 'Inactivo' : 'Activo';

        if ($idAprendiz <= 0) {
            header("Location: index.php?action=gestion_aprendices");
            exit();
        }

        $db = $this->conectar();

        // Buscar usuario vinculado
        $stmtU = $db->prepare("SELECT fk_usuario FROM aprendiz WHERE id_aprendiz = ?");
        $stmtU->bind_param("i", $idAprendiz);
        $stmtU->execute();
        $fila = $stmtU->get_result()->fetch_assoc();
        if (!$fila) {
            header("Location: index.php?action=gestion_aprendices&error=" . urlencode("Aprendiz no encontrado"));
            exit();
        }
        $idUsuario = (int)$fila['fk_usuario'];

        // Actualizar datos del usuario
        $stmtUs = $db->prepare("UPDATE usuario SET nombre = ?, apellido = ?, identificacion = ?, telefono = ?, estado = ? WHERE id_usuario = ?");
        $stmtUs->bind_param("sssssi", $nombre, $apellido, $identificacion, $telefono, $estado, $idUsuario);
        $stmtUs->execute();

        // Actualizar aprendiz
        $rfidValor = ($rfid === '') ? null : $rfid;
        if ($idFicha > 0) {
            $stmtAp = $db->prepare("UPDATE aprendiz SET codigo_rfid = ?, fk_ficha = ? WHERE id_aprendiz = ?");
            $stmtAp->bind_param("sii", $rfidValor, $idFicha, $idAprendiz);
            $stmtAp->execute();
        } else {
            $stmtAp = $db->prepare("UPDATE aprendiz SET codigo_rfid = ? WHERE id_aprendiz = ?");
            $stmtAp->bind_param("si", $rfidValor, $idAprendiz);
            $stmtAp->execute();
        }

        header("Location: index.php?action=gestion_aprendices&actualizado=1");
        exit();
    }

    public function eliminarAprendiz(): void
    {
        requiereRol('Administrador');
        $id = (int)($_POST['id_aprendiz'] ?? $_GET['id'] ?? 0);
        if ($id > 0) {
            $db = $this->conectar();
            $stmtU = $db->prepare("SELECT fk_usuario FROM aprendiz WHERE id_aprendiz = ?");
            $stmtU->bind_param("i", $id);
            $stmtU->execute();
            $fila = $stmtU->get_result()->fetch_assoc();

            $db->query("SET FOREIGN_KEY_CHECKS = 0");
            $stmtA = $db->prepare("DELETE FROM aprendiz WHERE id_aprendiz = ?");
            $stmtA->bind_param("i", $id);
            $stmtA->execute();

            if ($fila) {
                $stmtUs = $db->prepare("DELETE FROM usuario WHERE id_usuario = ?");
                $stmtUs->bind_param("i", $fila['fk_usuario']);
                $stmtUs->execute();
            }
            $db->query("SET FOREIGN_KEY_CHECKS = 1");
        }
        header("Location: index.php?action=gestion_aprendices&eliminado=1");
        exit();
    }
}
?>
