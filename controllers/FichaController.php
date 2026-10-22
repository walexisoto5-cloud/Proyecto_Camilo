<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

/**
 * FichaController - Responsable exclusivo de la gestión de Fichas de formación (SRP)
 */
class FichaController
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

    public function buscarInstructores(): array
    {
        $db = $this->conectar();
        $roles = $this->rolesTodos();
        $idRolInstructor = $roles['Instructor'] ?? 2;

        $stmt = $db->prepare("SELECT id_usuario, CONCAT(nombre, ' ', apellido) AS nombre_completo, identificacion, nombre_usuario
                              FROM usuario WHERE fk_rol = ? ORDER BY nombre, apellido");
        $stmt->bind_param("i", $idRolInstructor);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function viewFichas(): void
    {
        requiereRol('Administrador');
        $db = $this->conectar();
        $buscar = trim($_GET['buscar'] ?? '');

        $sql = "SELECT f.id_ficha, f.numero_ficha, f.nombre_programa, f.jornada, f.fk_usuario,
                       CONCAT(u.nombre, ' ', u.apellido) AS instructor,
                       (SELECT COUNT(*) FROM aprendiz WHERE fk_ficha = f.id_ficha) AS total_aprendices
                FROM ficha f
                LEFT JOIN usuario u ON f.fk_usuario = u.id_usuario
                WHERE 1 = 1";
        
        $params = [];
        $tipos = '';
        if ($buscar !== '') {
            $sql .= " AND (f.numero_ficha LIKE ? OR f.nombre_programa LIKE ?)";
            $termino = "%{$buscar}%";
            $params = [$termino, $termino];
            $tipos .= 'ss';
        }
        $sql .= " ORDER BY f.numero_ficha";

        $stmt = $db->prepare($sql);
        if ($tipos !== '') {
            $stmt->bind_param($tipos, ...$params);
        }
        $stmt->execute();
        $fichas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $instructores = $this->buscarInstructores();
        $activo = 'gestion_fichas';
        $rol = $_SESSION['rol'] ?? 'Administrador';
        $nombreUsuario = $_SESSION['nombre_completo'] ?? ($_SESSION['nombre'] ?? 'Administrador');
        $rolUsuario = $rol;
        require_once __DIR__ . '/../views/gestion/fichas.php';
    }

    public function crearFicha(): void
    {
        requiereRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=gestion_fichas");
            exit();
        }

        $numeroFicha = trim($_POST['numero_ficha'] ?? '');
        $programa = trim($_POST['nombre_programa'] ?? $_POST['programa_formacion'] ?? '');
        $jornada = trim($_POST['jornada'] ?? 'Diurna');
        $fkInstructor = (int)($_POST['fk_usuario'] ?? 0);
        $origen = trim($_POST['origen'] ?? '');
        $redirectError = ($origen === 'dashboard') ? "index.php?action=dashboard&error=" : "index.php?action=gestion_fichas&error=";
        $redirectOk = ($origen === 'dashboard') ? "index.php?action=dashboard&creado=1" : "index.php?action=gestion_fichas&creado=1";

        if ($numeroFicha === '' || $programa === '') {
            header("Location: " . $redirectError . urlencode("El número de ficha y programa son requeridos."));
            exit();
        }

        $db = $this->conectar();

        // Validar si la ficha ya existe
        $check = $db->prepare("SELECT id_ficha FROM ficha WHERE numero_ficha = ? LIMIT 1");
        $check->bind_param("s", $numeroFicha);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            header("Location: " . $redirectError . urlencode("La ficha N° " . $numeroFicha . " ya existe en el sistema. No se permiten números de ficha duplicados."));
            exit();
        }

        $fk = $fkInstructor > 0 ? $fkInstructor : null;

        $stmt = $db->prepare("INSERT INTO ficha (numero_ficha, nombre_programa, jornada, fk_usuario) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $numeroFicha, $programa, $jornada, $fk);
        
        if (!$stmt->execute()) {
            $msgError = ($db->errno === 1062) 
                ? "La ficha N° " . $numeroFicha . " ya existe en el sistema." 
                : $db->error;
            header("Location: " . $redirectError . urlencode($msgError));
            exit();
        }

        header("Location: " . $redirectOk);
        exit();
    }

    public function actualizarFicha(): void
    {
        requiereRol('Administrador');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?action=gestion_fichas");
            exit();
        }

        $idFicha = (int)($_POST['id_ficha'] ?? 0);
        $numeroFicha = trim($_POST['numero_ficha'] ?? '');
        $programa = trim($_POST['nombre_programa'] ?? '');
        $jornada = trim($_POST['jornada'] ?? 'Diurna');
        $fkInstructor = (int)($_POST['fk_usuario'] ?? 0);
        $origen = trim($_POST['origen'] ?? '');
        $redirectError = ($origen === 'dashboard') ? "index.php?action=dashboard&error=" : "index.php?action=gestion_fichas&error=";
        $redirectOk = ($origen === 'dashboard') ? "index.php?action=dashboard&actualizado=1" : "index.php?action=gestion_fichas&actualizado=1";

        if ($idFicha <= 0 || $numeroFicha === '' || $programa === '') {
            header("Location: " . $redirectError . urlencode("Datos incompletos para actualizar la ficha."));
            exit();
        }

        $db = $this->conectar();

        // Validar si el nuevo número de ficha ya le pertenece a otra ficha
        $check = $db->prepare("SELECT id_ficha FROM ficha WHERE numero_ficha = ? AND id_ficha != ? LIMIT 1");
        $check->bind_param("si", $numeroFicha, $idFicha);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            header("Location: " . $redirectError . urlencode("El número de ficha " . $numeroFicha . " ya está registrado en otra ficha."));
            exit();
        }

        $fk = $fkInstructor > 0 ? $fkInstructor : null;

        $stmt = $db->prepare("UPDATE ficha SET numero_ficha = ?, nombre_programa = ?, jornada = ?, fk_usuario = ? WHERE id_ficha = ?");
        $stmt->bind_param("sssii", $numeroFicha, $programa, $jornada, $fk, $idFicha);
        
        if (!$stmt->execute()) {
            $msgError = ($db->errno === 1062) 
                ? "El número de ficha " . $numeroFicha . " ya está registrado en otra ficha." 
                : $db->error;
            header("Location: " . $redirectError . urlencode($msgError));
            exit();
        }

        header("Location: " . $redirectOk);
        exit();
    }

    public function eliminarFicha(): void
    {
        requiereRol('Administrador');
        $id = (int)($_POST['id_ficha'] ?? $_GET['id'] ?? 0);
        if ($id > 0) {
            $db = $this->conectar();
            $db->query("SET FOREIGN_KEY_CHECKS = 0");
            $stmtA = $db->prepare("UPDATE aprendiz SET fk_ficha = NULL WHERE fk_ficha = ?");
            $stmtA->bind_param("i", $id);
            $stmtA->execute();

            $stmtH = $db->prepare("DELETE FROM horario WHERE fk_ficha = ?");
            $stmtH->bind_param("i", $id);
            $stmtH->execute();

            $stmt = $db->prepare("DELETE FROM ficha WHERE id_ficha = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $db->query("SET FOREIGN_KEY_CHECKS = 1");
        }
        header("Location: index.php?action=gestion_fichas&eliminado=1");
        exit();
    }
}
