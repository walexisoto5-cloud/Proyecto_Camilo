<?php
class Asistencia
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Registra o actualiza el marcaje diario (entrada o salida) por RFID o Documento.
     * Calcula automáticamente retardos y salidas anticipadas contra el horario de la ficha.
     */
    public function registrarAccesoPorRfId(string $codigo_rfid): array
    {
        $hoy = date('Y-m-d');
        $hora_actual = date('H:i:s');
        $ahora_datetime = date('Y-m-d H:i:s');

        // 1. Buscar al aprendiz y su ficha
        $sql = "SELECT ap.id_aprendiz, ap.fk_ficha, u.id_usuario, u.nombre, u.apellido, u.identificacion,
                       f.jornada, f.numero_ficha
                FROM aprendiz ap 
                INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario 
                LEFT JOIN ficha f ON ap.fk_ficha = f.id_ficha
                WHERE ap.codigo_rfid = ? OR u.identificacion = ? 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ss", $codigo_rfid, $codigo_rfid);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 0) {
            // Verificar si el usuario existe pero no como aprendiz
            $sqlUser = "SELECT nombre FROM usuario WHERE identificacion = ? OR nombre_usuario = ? LIMIT 1";
            $stmtUser = $this->db->prepare($sqlUser);
            $stmtUser->bind_param("ss", $codigo_rfid, $codigo_rfid);
            $stmtUser->execute();
            $resUser = $stmtUser->get_result();

            if ($resUser->num_rows > 0) {
                $uData = $resUser->fetch_assoc();
                return [
                    "status" => "warning",
                    "mensaje" => "El usuario " . htmlspecialchars($uData['nombre']) . " no tiene ficha de aprendiz asignada."
                ];
            }

            return [
                "status" => "error",
                "mensaje" => "Tarjeta RFID o documento (" . htmlspecialchars($codigo_rfid) . ") no registrado en el sistema."
            ];
        }

        $aprendiz = $resultado->fetch_assoc();
        $id_aprendiz = (int)$aprendiz['id_aprendiz'];
        $id_usuario = (int)$aprendiz['id_usuario'];
        $id_ficha = (int)($aprendiz['fk_ficha'] ?? 0);
        $nombreCompleto = trim($aprendiz['nombre'] . ' ' . $aprendiz['apellido']);
        $jornada = trim($aprendiz['jornada'] ?? 'Diurna');

        // 2. Determinar horario programado según las 3 jornadas oficiales del SENA
        // 1. Diurna: 06:00 AM - 12:00 PM
        // 2. Tarde:  12:00 PM - 06:00 PM
        // 3. Noche:  06:00 PM - 10:00 PM
        $horaProgramadaEntrada = '06:00:00';
        $horaProgramadaSalida = '12:00:00';

        if (stripos($jornada, 'tarde') !== false) {
            $horaProgramadaEntrada = '12:00:00';
            $horaProgramadaSalida = '18:00:00';
        } elseif (stripos($jornada, 'nocturna') !== false || stripos($jornada, 'noche') !== false) {
            $horaProgramadaEntrada = '18:00:00';
            $horaProgramadaSalida = '22:00:00';
        }

        if ($id_ficha > 0) {
            $diasSemana = [
                1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles',
                4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'
            ];
            $diaHoy = $diasSemana[(int)date('N')] ?? 'Lunes';
            $diaHoySinTilde = str_replace(['é', 'á'], ['e', 'a'], $diaHoy);

            $sqlHorario = "SELECT entrada, salida FROM horario 
                           WHERE fk_ficha = ? AND (dia_semana = ? OR dia_semana = ?) 
                           ORDER BY entrada ASC LIMIT 1";
            $stmtHorario = $this->db->prepare($sqlHorario);
            if ($stmtHorario) {
                $stmtHorario->bind_param("iss", $id_ficha, $diaHoy, $diaHoySinTilde);
                $stmtHorario->execute();
                $resHorario = $stmtHorario->get_result()->fetch_assoc();
                if ($resHorario && !empty($resHorario['entrada'])) {
                    $horaProgramadaEntrada = $resHorario['entrada'];
                    if (!empty($resHorario['salida'])) {
                        $horaProgramadaSalida = $resHorario['salida'];
                    }
                }
            }
        }

        // 3. Verificar si ya tiene asistencia hoy
        $sql_check = "SELECT * FROM asistencia WHERE fk_aprendiz = ? AND fecha_asistencia = ? LIMIT 1";
        $stmt_check = $this->db->prepare($sql_check);
        $stmt_check->bind_param("is", $id_aprendiz, $hoy);
        $stmt_check->execute();
        $asistencia_hoy = $stmt_check->get_result()->fetch_assoc();

        if (!$asistencia_hoy) {
            // --- REGISTRAR ENTRADA ---
            // Margen de tolerancia: 15 minutos posteriores a la hora programada
            $toleranciaSegundos = 15 * 60;
            $tsProgramado = strtotime($hoy . ' ' . $horaProgramadaEntrada);
            $tsLimite = $tsProgramado + $toleranciaSegundos;
            $tsActual = strtotime($ahora_datetime);

            $minutos_retardo = 0;
            if ($tsActual > $tsLimite) {
                $estado = "Retardo";
                $minutos_retardo = max(1, (int)round(($tsActual - $tsProgramado) / 60));
            } else {
                $estado = "A tiempo";
                $minutos_retardo = 0;
            }

            $sql_insert = "INSERT INTO asistencia (fecha_asistencia, entrada, estado_entrada, minutos_retardo, fk_aprendiz) 
                           VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $this->db->prepare($sql_insert);
            $stmt_insert->bind_param("sssii", $hoy, $ahora_datetime, $estado, $minutos_retardo, $id_aprendiz);
            $stmt_insert->execute();

            // Sincronizar tabla 'ingresos'
            $sql_ingreso = "INSERT INTO ingresos (id_usuario, fecha, hora_entrada, estado) 
                            VALUES (?, ?, ?, ?) 
                            ON DUPLICATE KEY UPDATE hora_entrada = VALUES(hora_entrada), estado = VALUES(estado)";
            $stmt_ingreso = $this->db->prepare($sql_ingreso);
            if ($stmt_ingreso) {
                $stmt_ingreso->bind_param("isss", $id_usuario, $hoy, $hora_actual, $estado);
                @$stmt_ingreso->execute();
            }

            $detalleRetardo = ($estado === 'Retardo') ? " (Retardo: {$minutos_retardo} min)" : " (A tiempo)";
            return [
                "status" => ($estado === 'Retardo') ? "warning" : "success",
                "tipo" => "entrada",
                "mensaje" => "¡Bienvenido(a), {$nombreCompleto}! Entrada registrada{$detalleRetardo} a las " . date('h:i A')
            ];

        } elseif (!empty($asistencia_hoy['entrada']) && (empty($asistencia_hoy['salida']) || $asistencia_hoy['salida'] === '0000-00-00 00:00:00')) {
            // --- REGISTRAR SALIDA ---
            $tsProgramadoSalida = strtotime($hoy . ' ' . $horaProgramadaSalida);
            $tsActual = strtotime($ahora_datetime);

            $minutos_salida_anticipada = 0;
            $estado_salida = "Salida normal";

            if ($tsActual < $tsProgramadoSalida) {
                $diferenciaMinutos = (int)round(($tsProgramadoSalida - $tsActual) / 60);
                if ($diferenciaMinutos > 15) {
                    $estado_salida = "Salida anticipada";
                    $minutos_salida_anticipada = $diferenciaMinutos;
                }
            }

            $sql_update = "UPDATE asistencia 
                           SET salida = ?, estado_salida = ?, minutos_salida_anticipada = ? 
                           WHERE id_asistencia = ?";
            $stmt_update = $this->db->prepare($sql_update);
            $stmt_update->bind_param("ssii", $ahora_datetime, $estado_salida, $minutos_salida_anticipada, $asistencia_hoy['id_asistencia']);
            $stmt_update->execute();

            // Sincronizar tabla ingresos
            $sql_ingreso_up = "UPDATE ingresos SET hora_salida = ? WHERE id_usuario = ? AND fecha = ?";
            $stmt_ingreso_up = $this->db->prepare($sql_ingreso_up);
            if ($stmt_ingreso_up) {
                $stmt_ingreso_up->bind_param("sis", $hora_actual, $id_usuario, $hoy);
                @$stmt_ingreso_up->execute();
            }

            $detalleSalida = ($estado_salida === 'Salida anticipada')
                ? " (Salida anticipada: {$minutos_salida_anticipada} min antes)"
                : " (Salida normal)";

            return [
                "status" => "success",
                "tipo" => "salida",
                "mensaje" => "¡Hasta luego, {$nombreCompleto}! Salida registrada{$detalleSalida} a las " . date('h:i A')
            ];
        } else {
            return [
                "status" => "warning",
                "mensaje" => "{$nombreCompleto} ya registró su entrada y salida por el día de hoy."
            ];
        }
    }
}
?>