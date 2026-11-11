<?php

class ReglasAsistencia {

    /**
     * Evalúa el estado de asistencia según la hora programada y la hora de llegada.
     * Regla SENA:
     * - Hasta 15 minutos de tolerancia: 'Presente'
     * - Más de 15 minutos: 'Retardo'
     */
    public static function evaluarLlegada(string $horaProgramada, string $horaLlegada): string {
        $tsProgramada = strtotime($horaProgramada);
        $tsLlegada = strtotime($horaLlegada);

        $diferenciaMinutos = ($tsLlegada - $tsProgramada) / 60;

        if ($diferenciaMinutos >= -15 && $diferenciaMinutos <= 15) {
            return 'Presente';
        }

        return 'Retardo';
    }

    /**
     * Determina si un aprendiz supera el umbral de inasistencias (15%) para remitir a Comité.
     * Regla SENA:
     * - >= 15% de inasistencias injustificadas amerita Comité de Evaluación y Seguimiento.
     */
    public static function ameritaComite(int $totalSesiones, int $inasistencias): bool {
        if ($totalSesiones <= 0) {
            return false;
        }

        $porcentaje = ($inasistencias / $totalSesiones) * 100;
        return $porcentaje >= 15.0;
    }

    /**
     * Calcula el porcentaje exacto de inasistencias.
     */
    public static function calcularPorcentajeInasistencias(int $totalSesiones, int $inasistencias): float {
        if ($totalSesiones <= 0) {
            return 0.0;
        }

        return round(($inasistencias / $totalSesiones) * 100, 2);
    }

    /**
     * Valida si el número de ficha cumple con el formato estándar SENA (numérico de 6 a 9 dígitos).
     */
    public static function validarNumeroFicha($ficha): bool {
        return (bool) preg_match('/^[0-9]{6,9}$/', (string) $ficha);
    }
}
