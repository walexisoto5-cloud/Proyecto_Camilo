<?php
class Asistencia {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function registrarAccesoPorRfId($codigo_rfid) {
        // 1. Buscar al aprendiz por su código RFID vinculando aprendiz y usuario
        $sql = "SELECT ap.id_aprendiz, u.id_usuario, u.nombre, u.apellido, ap.fk_ficha 
                FROM aprendiz ap 
                INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario 
                WHERE ap.codigo_rfid = ? OR u.identificacion = ? 
                LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $codigo_rfid, $codigo_rfid);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            return ["status" => "error", "mensaje" => "Tarjeta RFID no registrada en el sistema."];
        }

        $aprendiz = $resultado->fetch_assoc();
        $id_aprendiz = (int)$aprendiz['id_aprendiz'];
        $id_usuario = (int)$aprendiz['id_usuario'];
        $nombreCompleto = trim($aprendiz['nombre'] . ' ' . $aprendiz['apellido']);
        $hoy = date('Y-m-d');
        $hora_actual = date('H:i:s');
        $ahora_datetime = date('Y-m-d H:i:s');

        // 2. Verificar si ya tiene un registro de asistencia para el día de hoy
        $sql_check = "SELECT * FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->bind_param("is", $id_aprendiz, $hoy);
        $stmt_check->execute();
        $asistencia_hoy = $stmt_check->get_result()->fetch_assoc();

        if (!$asistencia_hoy) {
            // --- REGISTRAR ENTRADA ---
            $estado = "A tiempo";
            if ($hora_actual > '08:15:00') {
                $estado = "Retardo";
            }

            $sql_insert = "INSERT INTO asistencia (fecha_asistencia, entrada, estado_entrada, fk_aprendiz) VALUES (?, ?, ?, ?)";
            $stmt_insert = $this->db->prepare($sql_insert);
            $stmt_insert->bind_param("sssi", $hoy, $ahora_datetime, $estado, $id_aprendiz);
            $stmt_insert->execute();

            return [
                "status" => "success", 
                "tipo" => "entrada",
                "mensaje" => "¡Bienvenido, " . $nombreCompleto . "! Entrada registrada a las " . date('h:i A') . ($estado == 'Retardo' ? ' (CON RETARDO)' : '')
            ];

        } else if ($asistencia_hoy && (empty($asistencia_hoy['salida']) || $asistencia_hoy['salida'] === '0000-00-00 00:00:00')) {
            // --- REGISTRAR SALIDA ---
            $estado_salida = "Salida normal";

            $sql_update = "UPDATE asistencia SET salida = ?, estado_salida = ? WHERE id_asistencia = ?";
            $stmt_update = $this->db->prepare($sql_update);
            $stmt_update->bind_param("ssi", $ahora_datetime, $estado_salida, $asistencia_hoy['id_asistencia']);
            $stmt_update->execute();

            return [
                "status" => "success", 
                "tipo" => "salida",
                "mensaje" => "¡Hasta luego, " . $nombreCompleto . "! Salida registrada a las " . date('h:i A')
            ];
        } else {
            return ["status" => "warning", "mensaje" => $nombreCompleto . " ya registró su entrada y salida el día de hoy."];
        }
    }
}
?>