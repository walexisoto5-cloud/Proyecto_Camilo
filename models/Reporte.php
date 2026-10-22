<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Modelo Reporte - Responsable de la consulta y preparación de datos para reportes oficiales.
 */
class Reporte
{
    private $db;

    public function __construct($db = null)
    {
        if ($db) {
            $this->db = $db;
        } else {
            $database = new Database();
            $this->db = $database->getConnection();
        }
    }

    /**
     * Obtiene los datos informativos de una ficha específica o la última registrada si no se indica ID.
     */
    public function obtenerFicha(int $idFicha): ?array
    {
        if ($idFicha <= 0) {
            $res = $this->db->query("SELECT id_ficha FROM ficha ORDER BY id_ficha DESC LIMIT 1");
            $fila = $res ? $res->fetch_assoc() : null;
            $idFicha = $fila ? (int)$fila['id_ficha'] : 0;
        }

        if ($idFicha <= 0) {
            return null;
        }

        $stmt = $this->db->prepare("SELECT id_ficha, numero_ficha, nombre_programa, jornada FROM ficha WHERE id_ficha = ? LIMIT 1");
        $stmt->bind_param("i", $idFicha);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res ? $res->fetch_assoc() : null;
    }

    /**
     * Obtiene el listado de aprendices de la ficha, su asistencia del día y contadores acumulados de faltas.
     */
    public function obtenerAsistenciaSesion(int $idFicha, string $fecha): array
    {
        $sql = "SELECT ap.id_aprendiz, u.id_usuario, u.nombre, u.apellido, u.identificacion,
                       a.id_asistencia, a.entrada, a.salida, a.estado_entrada, a.estado_salida
                FROM aprendiz ap
                INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                LEFT JOIN asistencia a ON a.fk_aprendiz = ap.id_aprendiz AND a.fecha_asistencia = ?
                WHERE ap.fk_ficha = ?
                ORDER BY u.apellido ASC, u.nombre ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("si", $fecha, $idFicha);
        $stmt->execute();
        $aprendices = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $totalPresentes = 0;
        $totalRetardos = 0;
        $totalInasistencias = 0;
        $totalSinRegistro = 0;

        foreach ($aprendices as &$ap) {
            $stmtF = $this->db->prepare("SELECT COUNT(*) as faltas FROM asistencia WHERE fk_aprendiz = ? AND (LOWER(estado_entrada) LIKE '%ausente%' OR LOWER(estado_entrada) LIKE '%falta%' OR LOWER(estado_entrada) LIKE '%inasistencia%')");
            $stmtF->bind_param("i", $ap['id_aprendiz']);
            $stmtF->execute();
            $ap['total_faltas'] = (int)($stmtF->get_result()->fetch_assoc()['faltas'] ?? 0);

            $est = strtolower($ap['estado_entrada'] ?? '');
            if (empty($ap['id_asistencia'])) {
                $totalSinRegistro++;
            } elseif (strpos($est, 'retardo') !== false || strpos($est, 'tarde') !== false) {
                $totalRetardos++;
            } elseif (strpos($est, 'inasistencia') !== false || strpos($est, 'ausente') !== false || strpos($est, 'falta') !== false) {
                $totalInasistencias++;
            } else {
                $totalPresentes++;
            }
        }
        unset($ap);

        return [
            'aprendices' => $aprendices,
            'totales' => [
                'presentes' => $totalPresentes,
                'retardos' => $totalRetardos,
                'inasistencias' => $totalInasistencias,
                'sin_registro' => $totalSinRegistro,
                'total' => count($aprendices)
            ]
        ];
    }

    /**
     * Obtiene la sábana de asistencia de todos los días del mes para los aprendices de la ficha.
     */
    public function obtenerAsistenciaMensual(int $idFicha, int $mes, int $anio): array
    {
        // 1. Obtener aprendices de la ficha
        $sqlAp = "SELECT ap.id_aprendiz, u.identificacion, u.nombre, u.apellido
                  FROM aprendiz ap
                  INNER JOIN usuario u ON ap.fk_usuario = u.id_usuario
                  WHERE ap.fk_ficha = ?
                  ORDER BY u.apellido ASC, u.nombre ASC";
        $stmtAp = $this->db->prepare($sqlAp);
        $stmtAp->bind_param("i", $idFicha);
        $stmtAp->execute();
        $aprendices = $stmtAp->get_result()->fetch_all(MYSQLI_ASSOC);

        // 2. Obtener todos los marcajes del mes para esa ficha
        $sqlAsist = "SELECT a.fk_aprendiz, DAY(a.fecha_asistencia) AS dia, a.estado_entrada, a.estado_salida
                     FROM asistencia a
                     INNER JOIN aprendiz ap ON a.fk_aprendiz = ap.id_aprendiz
                     WHERE ap.fk_ficha = ? AND MONTH(a.fecha_asistencia) = ? AND YEAR(a.fecha_asistencia) = ?";
        $stmtAsist = $this->db->prepare($sqlAsist);
        $stmtAsist->bind_param("iii", $idFicha, $mes, $anio);
        $stmtAsist->execute();
        $marcajes = $stmtAsist->get_result()->fetch_all(MYSQLI_ASSOC);

        $mapa = [];
        foreach ($marcajes as $m) {
            $idAp = (int)$m['fk_aprendiz'];
            $dia = (int)$m['dia'];
            $estEnt = strtolower($m['estado_entrada'] ?? '');
            $estSal = strtolower($m['estado_salida'] ?? '');

            $cod = 'P';
            if (strpos($estSal, 'justificada') !== false || strpos($estEnt, 'excusa') !== false) {
                $cod = 'E';
            } elseif (strpos($estEnt, 'inasistencia') !== false || strpos($estEnt, 'falta') !== false || strpos($estEnt, 'ausente') !== false) {
                $cod = 'F';
            } elseif (strpos($estEnt, 'retardo') !== false || strpos($estEnt, 'tarde') !== false) {
                $cod = 'R';
            } else {
                $cod = 'P';
            }

            $mapa[$idAp][$dia] = $cod;
        }

        $diasEnMes = (int)date('t', strtotime("$anio-$mes-01"));

        foreach ($aprendices as &$ap) {
            $id = (int)$ap['id_aprendiz'];
            $ap['dias'] = [];
            $p = 0; $r = 0; $f = 0; $e = 0;

            for ($d = 1; $d <= $diasEnMes; $d++) {
                $val = $mapa[$id][$d] ?? '-';
                $ap['dias'][$d] = $val;
                if ($val === 'P') $p++;
                elseif ($val === 'R') $r++;
                elseif ($val === 'F') $f++;
                elseif ($val === 'E') $e++;
            }

            $totalSesiones = $p + $r + $f + $e;
            $asistEfectiva = $p + $r + $e;
            $porcentaje = ($totalSesiones > 0) ? round(($asistEfectiva / $totalSesiones) * 100, 1) : 100.0;

            $ap['totales'] = [
                'presentes' => $p,
                'retardos' => $r,
                'faltas' => $f,
                'excusas' => $e,
                'total_sesiones' => $totalSesiones,
                'porcentaje' => $porcentaje
            ];
        }
        unset($ap);

        return [
            'dias_en_mes' => $diasEnMes,
            'mes' => $mes,
            'anio' => $anio,
            'aprendices' => $aprendices
        ];
    }
}
