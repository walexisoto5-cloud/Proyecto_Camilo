<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../models/ReglasAsistencia.php';

use PHPUnit\Framework\TestCase;

class ReglasAsistenciaTest extends TestCase {

    /**
     * Prueba 1: Tolerancia de 15 minutos.
     * Si el aprendiz llega dentro de los 15 minutos, debe quedar como 'Presente'.
     */
    public function testLlegadaDentroDeToleranciaEsPresente() {
        // Arrange (Preparación)
        $horaInicio = '06:00';
        $horaLlegada = '06:12'; // 12 minutos después

        // Act (Ejecución)
        $resultado = ReglasAsistencia::evaluarLlegada($horaInicio, $horaLlegada);

        // Assert (Verificación)
        $this->assertEquals('Presente', $resultado);
    }

    /**
     * Prueba 2: Superar la tolerancia de 15 minutos.
     * Si el aprendiz llega más de 15 minutos tarde, debe quedar como 'Retardo'.
     */
    public function testLlegadaDespuesDeToleranciaEsRetardo() {
        // Arrange
        $horaInicio = '06:00';
        $horaLlegada = '06:16'; // 22 minutos después

        // Act
        $resultado = ReglasAsistencia::evaluarLlegada($horaInicio, $horaLlegada);

        // Assert
        $this->assertEquals('Retardo', $resultado);
    }

    /**
     * Prueba 3: Umbral de Comité por Inasistencia.
     * Si supera o iguala el 15% de inasistencias, debe ameritar Comité.
     */
    public function testAmeritaComiteAlAlcanzarQuincePorCiento() {
        $totalSesiones = 100;
        $inasistencias = 15; // 15% exacto

        $amerita = ReglasAsistencia::ameritaComite($totalSesiones, $inasistencias);

        $this->assertTrue($amerita, 'Con 15% de inasistencias debe remitir a Comité.');
    }

    /**
     * Prueba 4: Por debajo del umbral de Comité.
     * Con 10% de inasistencias NO debe remitir a Comité.
     */
    public function testNoAmeritaComitePorDebajoDelUmbral() {
        $totalSesiones = 100;
        $inasistencias = 10; // 10%

        $amerita = ReglasAsistencia::ameritaComite($totalSesiones, $inasistencias);

        $this->assertFalse($amerita, 'Con 10% de inasistencias NO debe remitir a Comité.');
    }

    /**
     * Prueba 5: Cálculo matemático exacto de porcentajes.
     */
    public function testCalculoPorcentajeInasistenciasExacto() {
        $totalSesiones = 80;
        $inasistencias = 12; // 12 / 80 = 15%

        $porcentaje = ReglasAsistencia::calcularPorcentajeInasistencias($totalSesiones, $inasistencias);

        $this->assertEquals(15.0, $porcentaje);
    }

    /**
     * Prueba 6: Validación de formato de Ficha SENA.
     */
    public function testValidarFormatoDeFichaValidaEInvalida() {
        // Ficha válida (números de 7 dígitos)
        $this->assertTrue(ReglasAsistencia::validarNumeroFicha('2698745'));

        // Ficha inválida (texto o símbolos)
        $this->assertFalse(ReglasAsistencia::validarNumeroFicha('FICHA-2024'));
        $this->assertFalse(ReglasAsistencia::validarNumeroFicha('123')); // Demasiado corta
    }
}
