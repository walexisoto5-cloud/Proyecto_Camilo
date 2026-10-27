<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

class InstructorController
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

    private function verificarSesion()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['usuario_id']) && empty($_SESSION['id_usuario'])) {
            header("Location: index.php?action=login");
            exit();
        }
    }

    // Vista principal: Panel de toma de asistencia ágil
    public function portal()
    {
        $this->verificarSesion();

        $database = new Database();
        $db = $database->getConnection();

        $sqlFichas = "SELECT f.id_ficha, f.numero_ficha, f.nombre_programa, f.jornada, 
                             COUNT(ap.id_aprendiz) as total_aprendices 
                      FROM ficha f 
                      LEFT JOIN aprendiz ap ON f.id_ficha = ap.fk_ficha 
                      GROUP BY f.id_ficha, f.numero_ficha, f.nombre_programa, f.jornada 
                      ORDER BY total_aprendices DESC, f.id_ficha DESC";
        $resFichas = $db->query($sqlFichas);
        $fichas = $resFichas ? $resFichas->fetch_all(MYSQLI_ASSOC) : [];

        $idFichaSeleccionada = isset($_GET['ficha_id']) ? (int)$_GET['ficha_id'] : ($fichas[0]['id_ficha'] ?? 0);
        $fechaSeleccionada = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
        $competenciaActiva = isset($_GET['competencia']) ? trim($_GET['competencia']) : 'ADSO - Desarrollo y Programación';

        $aprendicesLista = [];
        require_once __DIR__ . '/../models/Comite.php';
        $comiteModel = new Comite($db);
        $aprendicesEnComite = $comiteModel->obtenerIdsAprendicesEnComiteActivo();

        if ($idFichaSeleccionada > 0) {
            $sqlAprendices = "SELECT ap.id_aprendiz, u.id_usuario, u.nombre, u.apellido, u.identificacion,
                                     a_hoy.id_asistencia, a_hoy.estado_entrada, a_hoy.estado_salida,
                                     COUNT(a_tot.id_asistencia) AS total_sesiones,
                                     SUM(CASE WHEN LOWER(a_tot.estado_entrada) LIKE '%ausente%' 
                                                OR LOWER(a_tot.estado_entrada) LIKE '%falta%' 
                                                OR LOWER(a_tot.estado_entrada) LIKE '%inasistencia%' 
                                              THEN 1 ELSE 0 END) AS total_faltas
                              FROM aprendiz ap
                              INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                              LEFT JOIN asistencia a_hoy ON a_hoy.fk_aprendiz = ap.id_aprendiz AND a_hoy.fecha_asistencia = ?
                              LEFT JOIN asistencia a_tot ON a_tot.fk_aprendiz = ap.id_aprendiz
                              WHERE ap.fk_ficha = ?
                              GROUP BY ap.id_aprendiz, u.id_usuario, u.nombre, u.apellido, u.identificacion, a_hoy.id_asistencia, a_hoy.estado_entrada, a_hoy.estado_salida
                              ORDER BY u.apellido ASC, u.nombre ASC";
            $stmtAp = $db->prepare($sqlAprendices);
            $stmtAp->bind_param("si", $fechaSeleccionada, $idFichaSeleccionada);
            $stmtAp->execute();
            $resAp = $stmtAp->get_result();

            while ($row = $resAp->fetch_assoc()) {
                $idAp = (int)$row['id_aprendiz'];

                $estadoActual = 'Presente';
                $est = strtolower($row['estado_entrada'] ?? '');
                if (strpos($est, 'retardo') !== false || strpos($est, 'tarde') !== false) {
                    $estadoActual = 'Tarde';
                } elseif (strpos($est, 'ausente') !== false || strpos($est, 'falta') !== false || strpos($est, 'inasistencia') !== false) {
                    $estadoActual = 'Ausente';
                }

                $totalSesiones = (int)($row['total_sesiones'] ?? 0);
                $totalFaltas = (int)($row['total_faltas'] ?? 0);
                $porcentajeFaltas = ($totalSesiones > 0) ? round(($totalFaltas / $totalSesiones) * 100) : 0;
                $alertaSena = ($porcentajeFaltas >= 15 || $totalFaltas >= 3);

                $row['estado_actual'] = $estadoActual;
                $row['asistencia_id'] = $row['id_asistencia'] ?? null;
                $row['total_faltas'] = $totalFaltas;
                $row['porcentaje_faltas'] = $porcentajeFaltas;
                $row['alerta_sena'] = $alertaSena;
                $row['en_comite'] = isset($aprendicesEnComite[$idAp]);

                $aprendicesLista[] = $row;
            }
        }

        $sqlExcusas = "SELECT e.*, u.nombre, u.apellido, u.identificacion, a.fecha_asistencia, f.numero_ficha,
                              u_rev.nombre AS revisor_nombre, u_rev.apellido AS revisor_apellido
                       FROM excusa e
                       INNER JOIN asistencia a ON e.fk_asistencia = a.id_asistencia
                       INNER JOIN aprendiz ap ON a.fk_aprendiz = ap.id_aprendiz
                       INNER JOIN ficha f ON ap.fk_ficha = f.id_ficha
                       INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                       LEFT JOIN usuario u_rev ON e.fk_usuario_instructor = u_rev.id_usuario
                       ORDER BY (CASE WHEN e.estado = 'Pendiente' THEN 1 ELSE 2 END), e.id_excusa DESC";
        $resExcusas = $db->query($sqlExcusas);
        $historialExcusas = $resExcusas ? $resExcusas->fetch_all(MYSQLI_ASSOC) : [];
        $excusasPendientes = array_values(array_filter($historialExcusas, fn($e) => $e['estado'] === 'Pendiente'));

        // Métricas de asistencia y monitoreo de aprendices para la portada del instructor
        require_once __DIR__ . '/../models/dashboard.php';
        $dashModel = new Dashboard($db);
        $totalAprendices = $dashModel->obtenerTotalAprendices();
        $asistenciasHoy = $dashModel->obtenerAsistenciasHoy();
        $retardosHoy = $dashModel->obtenerRetardosHoy();
        $totalFichas = count($fichas);
        $ultimosMarcajes = $dashModel->obtenerUltimasAsistencias(6);

        $nombreUsuario = $_SESSION['nombre_completo'] ?? $_SESSION['nombre_usuario'] ?? 'Instructor';
        $rolUsuario = $_SESSION['rol'] ?? 'Instructor';
        $seccionActiva = isset($_GET['seccion']) ? trim($_GET['seccion']) : 'resumen';
        $activo = ($seccionActiva === 'aprendices') ? 'aprendices' : 'dashboard';

        require_once __DIR__ . '/../views/instructor/dashboard.php';
    }

    // Guardar el llamado a lista masivo enviado desde el formulario
    public function guardarLlamadoLista()
    {
        $this->verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            $idFicha = (int)($_POST['ficha_id'] ?? 0);
            $fecha = $_POST['fecha'] ?? date('Y-m-d');
            $asistencias = $_POST['asistencia'] ?? [];
            $observaciones = $_POST['observacion'] ?? [];
            $ahora = date('Y-m-d H:i:s');

            foreach ($asistencias as $idAprendiz => $estado) {
                $idAprendiz = (int)$idAprendiz;
                $obs = trim($observaciones[$idAprendiz] ?? '');

                $estadoEntrada = 'A tiempo';
                if ($estado === 'Tarde') $estadoEntrada = 'Retardo';
                if ($estado === 'Ausente') $estadoEntrada = 'Inasistencia';

                $sqlCheck = "SELECT id_asistencia FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
                $stmtCheck = $db->prepare($sqlCheck);
                $stmtCheck->bind_param("is", $idAprendiz, $fecha);
                $stmtCheck->execute();
                $existe = $stmtCheck->get_result()->fetch_assoc();

                if ($existe) {
                    $idAsist = $existe['id_asistencia'];
                    $sqlUp = "UPDATE asistencia SET estado_entrada = ?, estado_salida = ? WHERE id_asistencia = ?";
                    $stmtUp = $db->prepare($sqlUp);
                    $stmtUp->bind_param("ssi", $estadoEntrada, $obs, $idAsist);
                    $stmtUp->execute();
                } else {
                    $sqlIns = "INSERT INTO asistencia (fecha_asistencia, entrada, estado_entrada, estado_salida, fk_aprendiz) 
                               VALUES (?, ?, ?, ?, ?)";
                    $stmtIns = $db->prepare($sqlIns);
                    $stmtIns->bind_param("ssssi", $fecha, $ahora, $estadoEntrada, $obs, $idAprendiz);
                    $stmtIns->execute();
                }
            }

            header("Location: index.php?action=portal_instructor&seccion=aprendices&ficha_id=" . $idFicha . "&fecha=" . urlencode($fecha) . "&guardado=1");
            exit();
        }
    }

    // Acción para Aprobar o Rechazar una Excusa Médica
    public function procesarExcusa()
    {
        $this->verificarSesion();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $database = new Database();
            $db = $database->getConnection();

            $idExcusa = (int)($_POST['id_excusa'] ?? 0);
            $nuevoEstado = ($_POST['estado'] === 'Aprobada') ? 'Aprobada' : 'Rechazada';
            $motivoRechazo = trim($_POST['motivo_rechazo'] ?? '');
            $idInstructor = (int)($_SESSION['usuario_id'] ?? $_SESSION['id_usuario'] ?? 0);

            if ($idExcusa > 0) {
                if ($nuevoEstado === 'Rechazada' && !empty($motivoRechazo)) {
                    $sql = "UPDATE excusa SET estado = ?, fecha_revision = CURDATE(), fk_usuario_instructor = ?, observacion = CONCAT(SUBSTRING(observacion, 1, 20), ' [Rech: ', ?, ']') WHERE id_excusa = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param("sisi", $nuevoEstado, $idInstructor, $motivoRechazo, $idExcusa);
                } else {
                    $sql = "UPDATE excusa SET estado = ?, fecha_revision = CURDATE(), fk_usuario_instructor = ? WHERE id_excusa = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->bind_param("sii", $nuevoEstado, $idInstructor, $idExcusa);
                }
                $stmt->execute();

                // Si fue Aprobada, actualizar la asistencia asociada para reflejar 'Justificada'
                if ($nuevoEstado === 'Aprobada') {
                    $sqlAsist = "UPDATE asistencia a 
                                 INNER JOIN excusa e ON a.id_asistencia = e.fk_asistencia 
                                 SET a.estado_salida = 'Justificada' 
                                 WHERE e.id_excusa = ?";
                    $stmtAsist = $db->prepare($sqlAsist);
                    if ($stmtAsist) {
                        $stmtAsist->bind_param("i", $idExcusa);
                        $stmtAsist->execute();
                    }
                }
            }
        }

        header("Location: index.php?action=portal_instructor&seccion=aprendices&msg=excusa_actualizada");
        exit();
    }

    // =========================================================================
    // GESTIÓN CRUD DE INSTRUCTORES (ADMINISTRADOR) - SRP
    // =========================================================================
    public function viewInstructores(): void
    {
        requiereRol('Administrador');
        $db = $this->conectar();
        $buscar = trim($_GET['buscar'] ?? '');
        $roles = $this->rolesTodos();
        $idRolInstructor = $roles['Instructor'] ?? 2;

        $sql = "SELECT u.id_usuario, u.nombre_usuario, u.nombre, u.apellido, u.identificacion, u.telefono,
                       IFNULL(u.estado, 'Activo') AS estado,
                       (SELECT GROUP_CONCAT(numero_ficha SEPARATOR ', ') FROM ficha WHERE fk_usuario = u.id_usuario) AS fichas_asignadas
                FROM usuario u
                WHERE u.fk_rol = ?";
        
        $params = [$idRolInstructor];
        $tipos = 'i';

        if ($buscar !== '') {
            $sql .= " AND (u.nombre LIKE ? OR u.apellido LIKE ? OR u.identificacion LIKE ? OR u.nombre_usuario LIKE ?)";
            $termino = "%{$buscar}%";
            $params = array_merge($params, [$termino, $termino, $termino, $termino]);
            $tipos .= 'ssss';
        }

        $sql .= " ORDER BY u.nombre, u.apellido";
        $stmt = $db->prepare($sql);
        $stmt->bind_param($tipos, ...$params);
        $stmt->execute();
        $instructores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $activo = 'gestion_instructores';
        $rol = $_SESSION['rol'] ?? 'Administrador';
        $nombreUsuario = $_SESSION['nombre_completo'] ?? ($_SESSION['nombre'] ?? 'Administrador');
        $rolUsuario = $rol;
        require_once __DIR__ . '/../views/gestion/instructores.php';
    }

    public function crearInstructor(): void
    {
        requiereRol('Administrador');
        $origen = trim($_POST['origen'] ?? '');
        $redirectOk = ($origen === 'dashboard') ? "index.php?action=dashboard&creado=1" : "index.php?action=gestion_instructores&creado=1";
        $redirectError = ($origen === 'dashboard') ? "index.php?action=dashboard&error=" : "index.php?action=gestion_instructores&error=";

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . $redirectOk);
            exit();
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $identificacion = trim($_POST['identificacion'] ?? $_POST['documento'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $nombreUsuario = trim($_POST['nombre_usuario'] ?? $_POST['correo'] ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');

        if ($nombre === '' || $apellido === '' || $identificacion === '' || $nombreUsuario === '' || $contrasena === '') {
            header("Location: " . $redirectError . urlencode("Todos los campos obligatorios deben ser diligenciados."));
            exit();
        }

        $db = $this->conectar();

        // Validar duplicado
        $check = $db->prepare("SELECT id_usuario FROM usuario WHERE nombre_usuario = ? OR identificacion = ?");
        $check->bind_param("ss", $nombreUsuario, $identificacion);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            header("Location: " . $redirectError . urlencode("El usuario o documento ya existe en el sistema."));
            exit();
        }

        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $roles = $this->rolesTodos();
        $fkRol = $roles['Instructor'] ?? 2;

        $stmt = $db->prepare("INSERT INTO usuario (nombre_usuario, contrasena, nombre, apellido, identificacion, telefono, fk_rol, estado)
                              VALUES (?, ?, ?, ?, ?, ?, ?, 'Activo')");
        $stmt->bind_param("ssssssi", $nombreUsuario, $hash, $nombre, $apellido, $identificacion, $telefono, $fkRol);
        
        if (!$stmt->execute()) {
            header("Location: " . $redirectError . urlencode($db->error));
            exit();
        }

        header("Location: " . $redirectOk);
        exit();
    }

    public function actualizarInstructor(): void
    {
        requiereRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=gestion_instructores");
            exit();
        }

        $idUsuario = (int)($_POST['id_usuario'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $apellido = trim($_POST['apellido'] ?? '');
        $identificacion = trim($_POST['identificacion'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $estado = ($_POST['estado'] ?? 'Activo') === 'Inactivo' ? 'Inactivo' : 'Activo';

        if ($idUsuario <= 0) {
            header("Location: index.php?action=gestion_instructores");
            exit();
        }

        $db = $this->conectar();
        $stmt = $db->prepare("UPDATE usuario SET nombre = ?, apellido = ?, identificacion = ?, telefono = ?, estado = ? WHERE id_usuario = ?");
        $stmt->bind_param("sssssi", $nombre, $apellido, $identificacion, $telefono, $estado, $idUsuario);
        $stmt->execute();

        header("Location: index.php?action=gestion_instructores&actualizado=1");
        exit();
    }

    public function eliminarInstructor(): void
    {
        requiereRol('Administrador');
        $id = (int)($_POST['id_usuario'] ?? $_GET['id'] ?? 0);
        if ($id > 0) {
            $db = $this->conectar();
            $db->query("SET FOREIGN_KEY_CHECKS = 0");
            $stmtF = $db->prepare("UPDATE ficha SET fk_usuario = NULL WHERE fk_usuario = ?");
            $stmtF->bind_param("i", $id);
            $stmtF->execute();

            $stmtH = $db->prepare("UPDATE horario SET fk_usuario_instructor = NULL WHERE fk_usuario_instructor = ?");
            $stmtH->bind_param("i", $id);
            $stmtH->execute();

            $stmt = $db->prepare("DELETE FROM usuario WHERE id_usuario = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $db->query("SET FOREIGN_KEY_CHECKS = 1");
        }
        header("Location: index.php?action=gestion_instructores&eliminado=1");
        exit();
    }
}
?>
