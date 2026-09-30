<?php
class Asistencia {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function registrarAccesoPorRfId($codigo_rfid) {
        // 1. Buscar al aprendiz por su código RFID y obtener su ficha/horario
        $sql = "SELECT u.id_usuario, u.nombre, u.apellido, f.hora_inicio, f.hora_fin 
                FROM usuario u 
                LEFT JOIN ficha f ON u.fk_ficha = f.id_ficha 
                WHERE u.codigo_rfid = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("s", $codigo_rfid);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            return ["status" => "error", "mensaje" => "Tarjeta RFID no registrada en el sistema."];
        }

        $aprendiz = $resultado->fetch_assoc();
        $id_usuario = $aprendiz['id_usuario'];
        $hoy = date('Y-m-d');
        $hora_actual = date('H:i:s');

        // 2. Verificar si ya tiene un registro de asistencia para el día de hoy
        $sql_check = "SELECT * FROM ingresos WHERE id_usuario = ? AND fecha = ?";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->bind_param("is", $id_usuario, $hoy);
        $stmt_check->execute();
        $asistencia_hoy = $stmt_check->get_result()->fetch_assoc();

        if (!$asistencia_hoy) {
            // --- REGISTRAR ENTRADA ---
            $estado = "A tiempo";
            // Validar retardo comparando con la hora de inicio de la ficha (ej. con 10 min de tolerancia)
            if ($horario_inicio = $aprendiz['hora_inicio']) {
                // Si la hora actual es mayor a la hora de entrada + tolerancia, se marca Retardo
                $tolerancia_minutos = 10;
                $hora_limite = date('H:i:s', strtotime($horario_inicio . " + $tolerancia_minutos minutes"));
                
                if ($hora_actual > $hora_limite) {
                    $estado = "Retardo";
                }
            }

            $sql_insert = "INSERT INTO ingresos (id_usuario, fecha, hora_entrada, estado) VALUES (?, ?, ?, ?)";
            $stmt_insert = $this->db->prepare($sql_insert);
            $stmt_insert->bind_param("isss", $id_usuario, $hoy, $hora_actual, $estado);
            $stmt_insert->execute();

            return [
                "status" => "success", 
                "tipo" => "entrada",
                "mensaje" => "¡Bienvenido, " . $aprendiz['nombre'] . "! Entrada registrada a las " . $hora_actual . ($estado == 'Retardo' ? ' (CON RETARDO)' : '')
            ];

        } else if ($asistencia_hoy && empty($asistencia_hoy['hora_salida'])) {
            // --- REGISTRAR SALIDA ---
            // Validar salida temprana si es necesario
            $estado = $asistencia_hoy['estado']; // Mantiene el estado si ya traía retardo
            $hora_fin_ficha = $aprendiz['hora_fin'] ?? '18:00:00';

            if ($hora_actual < $hora_fin_ficha) {
                $estado = "Salida temprana";
            }

            $sql_update = "UPDATE ingresos SET hora_salida = ?, estado = ? WHERE id_ingreso = ?";
            $stmt_update = $this->db->prepare($sql_update);
            $stmt_update->bind_param("ssi", $hora_actual, $estado, $asistencia_hoy['id_ingreso']);
            $stmt_update->execute();

            return [
                "status" => "success", 
                "tipo" => "salida",
                "mensaje" => "¡Hasta luego, " . $aprendiz['nombre'] . "! Salida registrada a las " . $hora_actual
            ];
        } else {
            return ["status" => "warning", "mensaje" => "El aprendiz ya registró su entrada y salida el día de hoy."];
        }
    }
}
?>