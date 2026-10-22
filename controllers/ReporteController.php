<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Reporte.php';

/**
 * ReporteController - Controlador especializado en la exportación e impresión de reportes oficiales en PDF
 */
class ReporteController
{
    private function verificarSesion(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['usuario_id'])) {
            header("Location: index.php");
            exit();
        }
    }

    private function toIso(string $str): string
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', $str);
    }

    /**
     * Genera y descarga el reporte oficial de asistencia en formato PDF utilizando FPDF.
     */
    public function generarReporteAsistenciaPdf(): void
    {
        $this->verificarSesion();

        $idFicha = isset($_GET['ficha_id']) ? (int)$_GET['ficha_id'] : 0;
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : date('Y-m-d');
        $competencia = isset($_GET['competencia']) ? trim($_GET['competencia']) : 'Formación Profesional Integral';

        $modeloReporte = new Reporte();
        $ficha = $modeloReporte->obtenerFicha($idFicha);

        if (!$ficha) {
            die("La ficha seleccionada no existe o no tiene datos registrados.");
        }

        $idFichaReal = (int)$ficha['id_ficha'];
        $datosSesion = $modeloReporte->obtenerAsistenciaSesion($idFichaReal, $fecha);
        $aprendices = $datosSesion['aprendices'];
        $totales = $datosSesion['totales'];

        $nombreInstructor = $_SESSION['nombre_completo'] ?? ($_SESSION['nombre'] ?? 'Instructor Responsable');

        // Carga de la librería FPDF
        require_once __DIR__ . '/../librerias/fpdf/fpdf.php';

        $pdf = new FPDF('P', 'mm', 'A4');
        $pdf->AliasNbPages();
        $pdf->AddPage();

        // 1. Banda Verde Institucional Superior
        $pdf->SetFillColor(22, 163, 74);
        $pdf->Rect(10, 8, 190, 3, 'F');

        // 2. Encabezado Institucional
        $pdf->SetY(14);
        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor(22, 163, 74);
        $pdf->Cell(0, 6, $this->toIso('SERVICIO NACIONAL DE APRENDIZAJE - SENA'), 0, 1, 'C');

        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->Cell(0, 5, $this->toIso('REPORTE OFICIAL DE ASISTENCIA Y CONTROL FORMATIVO'), 0, 1, 'C');

        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(100, 116, 139);
        $pdf->Cell(0, 4, $this->toIso('Sistema de Gestión de Asistencia Biometría / RFID'), 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetDrawColor(203, 213, 225);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(4);

        // 3. Ficha Técnica / Metadatos de la Formación
        $pdf->SetFillColor(248, 250, 252);
        $pdf->SetDrawColor(203, 213, 225);
        $posYInfo = $pdf->GetY();
        $pdf->Rect(10, $posYInfo, 190, 24, 'DF');

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetTextColor(15, 23, 42);

        $pdf->Cell(25, 5, 'Ficha:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(70, 5, $this->toIso($ficha['numero_ficha'] . ' - ' . ($ficha['nombre_programa'] ?? 'ADSO')), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 5, 'Fecha Sesion:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(70, 5, $fecha, 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 5, 'Jornada:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(70, 5, $this->toIso($ficha['jornada'] ?? 'Diurna'), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 5, 'Generado el:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(70, 5, date('d/m/Y h:i A'), 0, 1, 'L');

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 5, 'Instructor:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(70, 5, $this->toIso($nombreInstructor), 0, 0, 'L');

        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(25, 5, 'Competencia:', 0, 0, 'L');
        $pdf->SetFont('Arial', '', 8.5);
        $pdf->Cell(70, 5, $this->toIso($competencia), 0, 1, 'L');
        $pdf->Ln(6);

        // 4. Barra de Resumen de Métricas de Asistencia
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(22, 163, 74);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(38, 7, 'PRESENTES: ' . $totales['presentes'], 1, 0, 'C', true);

        $pdf->SetFillColor(234, 179, 8);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->Cell(38, 7, 'RETARDOS: ' . $totales['retardos'], 1, 0, 'C', true);

        $pdf->SetFillColor(239, 68, 68);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(38, 7, 'INASISTENCIAS: ' . $totales['inasistencias'], 1, 0, 'C', true);

        $pdf->SetFillColor(100, 116, 139);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(38, 7, 'SIN REGISTRO: ' . $totales['sin_registro'], 1, 0, 'C', true);

        $pdf->SetFillColor(15, 23, 42);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(38, 7, 'TOTAL: ' . $totales['total'], 1, 1, 'C', true);
        $pdf->Ln(4);

        // 5. Encabezados de la Tabla de Aprendices
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetFillColor(30, 41, 59);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(8, 7, '#', 1, 0, 'C', true);
        $pdf->Cell(26, 7, 'DOCUMENTO', 1, 0, 'C', true);
        $pdf->Cell(68, 7, $this->toIso('APRENDIZ (APELLIDOS Y NOMBRES)'), 1, 0, 'L', true);
        $pdf->Cell(24, 7, 'HORA ENTRADA', 1, 0, 'C', true);
        $pdf->Cell(30, 7, $this->toIso('ESTADO SESIÓN'), 1, 0, 'C', true);
        $pdf->Cell(16, 7, 'FALTAS', 1, 0, 'C', true);
        $pdf->Cell(18, 7, 'FIRMA', 1, 1, 'C', true);

        // 6. Filas de Aprendices
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(0, 0, 0);

        $num = 1;
        foreach ($aprendices as $ap) {
            $fill = ($num % 2 === 0);
            $pdf->SetFillColor(248, 250, 252);

            $nombreCompleto = trim($ap['apellido'] . ', ' . $ap['nombre']);
            if (strlen($nombreCompleto) > 38) {
                $nombreCompleto = substr($nombreCompleto, 0, 36) . '..';
            }

            $horaEntrada = !empty($ap['entrada']) ? date('h:i A', strtotime($ap['entrada'])) : '---';
            $estadoStr = !empty($ap['estado_entrada']) ? $ap['estado_entrada'] : 'Sin registro';

            $pdf->Cell(8, 6, $num, 1, 0, 'C', $fill);
            $pdf->Cell(26, 6, $ap['identificacion'], 1, 0, 'C', $fill);
            $pdf->Cell(68, 6, $this->toIso($nombreCompleto), 1, 0, 'L', $fill);
            $pdf->Cell(24, 6, $horaEntrada, 1, 0, 'C', $fill);

            // Resaltado de color según el estado
            if (stripos($estadoStr, 'retardo') !== false || stripos($estadoStr, 'tarde') !== false) {
                $pdf->SetTextColor(180, 83, 9);
            } elseif (stripos($estadoStr, 'inasistencia') !== false || stripos($estadoStr, 'falta') !== false || stripos($estadoStr, 'ausente') !== false) {
                $pdf->SetTextColor(220, 38, 38);
            } elseif (stripos($estadoStr, 'tiempo') !== false || stripos($estadoStr, 'presente') !== false) {
                $pdf->SetTextColor(22, 101, 52);
            } else {
                $pdf->SetTextColor(100, 116, 139);
            }

            $pdf->Cell(30, 6, $this->toIso($estadoStr), 1, 0, 'C', $fill);
            $pdf->SetTextColor(0, 0, 0);

            // Alerta visual si tiene 3 o más faltas
            if ($ap['total_faltas'] >= 3) {
                $pdf->SetTextColor(220, 38, 38);
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->Cell(16, 6, $ap['total_faltas'] . ' (!)', 1, 0, 'C', $fill);
                $pdf->SetFont('Arial', '', 8);
                $pdf->SetTextColor(0, 0, 0);
            } else {
                $pdf->Cell(16, 6, (string)$ap['total_faltas'], 1, 0, 'C', $fill);
            }

            $pdf->Cell(18, 6, '', 1, 1, 'C', $fill);
            $num++;
        }

        // 7. Espacio para Firma del Instructor Responsable
        $pdf->Ln(12);
        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $pdf->SetDrawColor(100, 116, 139);
        $posYFirma = $pdf->GetY() + 8;
        $pdf->Line(20, $posYFirma, 95, $posYFirma);

        $pdf->SetY($posYFirma + 2);
        $pdf->SetX(20);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->Cell(75, 4, $this->toIso('Firma Instructor(a) Responsable'), 0, 1, 'L');
        $pdf->SetX(20);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(75, 4, $this->toIso($nombreInstructor), 0, 1, 'L');

        // 8. Salida al navegador
        $nomArchivo = "Reporte_Asistencia_Ficha_{$ficha['numero_ficha']}_{$fecha}.pdf";
        $pdf->Output('I', $nomArchivo);
        exit();
    }

    /**
     * Genera y descarga la sábana mensual consolidada o la sesión diaria en formato Excel (.xlsx) nativo
     */
    public function generarReporteAsistenciaExcel(): void
    {
        $this->verificarSesion();

        $idFicha = isset($_GET['ficha_id']) ? (int)$_GET['ficha_id'] : 0;
        $tipo = isset($_GET['tipo']) ? trim($_GET['tipo']) : 'sesion';
        $fecha = isset($_GET['fecha']) ? trim($_GET['fecha']) : date('Y-m-d');
        $mes = isset($_GET['mes']) ? (int)$_GET['mes'] : (int)date('m', strtotime($fecha));
        $anio = isset($_GET['anio']) ? (int)$_GET['anio'] : (int)date('Y', strtotime($fecha));

        $modeloReporte = new Reporte();
        $ficha = $modeloReporte->obtenerFicha($idFicha);

        if (!$ficha) {
            die("La ficha seleccionada no existe o no tiene datos registrados.");
        }

        $idFichaReal = (int)$ficha['id_ficha'];
        $numeroFicha = $ficha['numero_ficha'];
        $nombrePrograma = $ficha['nombre_programa'] ?? 'ADSO';
        $jornada = $ficha['jornada'] ?? 'Diurna';
        $nombreInstructor = $_SESSION['nombre_completo'] ?? ($_SESSION['nombre'] ?? 'Instructor Responsable');

        require_once __DIR__ . '/../librerias/SimpleXLSXGen.php';
        $xlsxGenClass = '\Shuchkin\SimpleXLSXGen';

        if (!class_exists($xlsxGenClass)) {
            die("Error: No se encontró la librería SimpleXLSXGen para la generación de Excel.");
        }

        $filas = [];

        // Encabezado Institucional SENA
        $filas[] = ['<style bgcolor="#16a34a" color="#ffffff"><b>SERVICIO NACIONAL DE APRENDIZAJE - SENA</b></style>'];
        $filas[] = ['<style bgcolor="#22c55e" color="#ffffff"><b>SISTEMA DE GESTIÓN Y CONTROL DE ASISTENCIA</b></style>'];
        $filas[] = [];

        if ($tipo === 'mensual') {
            // REPORTE SÁBANA MENSUAL CONSOLIDADA
            $mesesNombres = [1=>'Enero', 2=>'Febrero', 3=>'Marzo', 4=>'Abril', 5=>'Mayo', 6=>'Junio', 7=>'Julio', 8=>'Agosto', 9=>'Septiembre', 10=>'Octubre', 11=>'Noviembre', 12=>'Diciembre'];
            $nombreMes = $mesesNombres[$mes] ?? 'Mes';

            $filas[] = ['<b>Tipo de Reporte:</b>', 'Sábana Mensual Consolidada de Asistencia', '<b>Período:</b>', "$nombreMes de $anio"];
            $filas[] = ['<b>Ficha:</b>', "$numeroFicha - $nombrePrograma", '<b>Jornada:</b>', $jornada];
            $filas[] = ['<b>Instructor Responsable:</b>', $nombreInstructor, '<b>Fecha de Emisión:</b>', date('d/m/Y h:i A')];
            $filas[] = [];

            $datosMensuales = $modeloReporte->obtenerAsistenciaMensual($idFichaReal, $mes, $anio);
            $diasEnMes = $datosMensuales['dias_en_mes'];
            $aprendices = $datosMensuales['aprendices'];

            // Cabecera de la tabla
            $cabecera = ['<b>#</b>', '<b>Documento</b>', '<b>Aprendiz (Apellidos y Nombres)</b>'];
            for ($d = 1; $d <= $diasEnMes; $d++) {
                $cabecera[] = "<b>$d</b>";
            }
            $cabecera[] = '<style bgcolor="#16a34a" color="#ffffff"><b>Presentes (P)</b></style>';
            $cabecera[] = '<style bgcolor="#eab308" color="#000000"><b>Retardos (R)</b></style>';
            $cabecera[] = '<style bgcolor="#ef4444" color="#ffffff"><b>Faltas (F)</b></style>';
            $cabecera[] = '<style bgcolor="#3b82f6" color="#ffffff"><b>Excusas (E)</b></style>';
            $cabecera[] = '<style bgcolor="#1e293b" color="#ffffff"><b>% Asistencia</b></style>';
            $filas[] = $cabecera;

            // Filas por aprendiz
            $num = 1;
            foreach ($aprendices as $ap) {
                $fila = [
                    $num,
                    $ap['identificacion'],
                    trim($ap['apellido'] . ', ' . $ap['nombre'])
                ];

                for ($d = 1; $d <= $diasEnMes; $d++) {
                    $cod = $ap['dias'][$d] ?? '-';
                    if ($cod === 'P') {
                        $fila[] = '<style color="#16a34a"><b>P</b></style>';
                    } elseif ($cod === 'R') {
                        $fila[] = '<style color="#d97706"><b>R</b></style>';
                    } elseif ($cod === 'F') {
                        $fila[] = '<style color="#dc2626"><b>F</b></style>';
                    } elseif ($cod === 'E') {
                        $fila[] = '<style color="#2563eb"><b>E</b></style>';
                    } else {
                        $fila[] = '-';
                    }
                }

                $t = $ap['totales'];
                $fila[] = $t['presentes'];
                $fila[] = $t['retardos'];
                $fila[] = $t['faltas'];
                $fila[] = $t['excusas'];
                $fila[] = $t['porcentaje'] . '%';

                $filas[] = $fila;
                $num++;
            }

            $filas[] = [];
            $filas[] = ['<b>Convenciones:</b>', 'P = Presente | R = Retardo | F = Inasistencia Injustificada | E = Excusa Justificada | - = Sin sesión formativa'];

            $nombreArchivo = "Sabana_Asistencia_Ficha_{$numeroFicha}_{$nombreMes}_{$anio}.xlsx";
        } else {
            // REPORTE DE SESIÓN DEL DÍA
            $filas[] = ['<b>Tipo de Reporte:</b>', 'Planilla de Asistencia de Sesión Formativa', '<b>Fecha de Sesión:</b>', $fecha];
            $filas[] = ['<b>Ficha:</b>', "$numeroFicha - $nombrePrograma", '<b>Jornada:</b>', $jornada];
            $filas[] = ['<b>Instructor:</b>', $nombreInstructor, '<b>Fecha de Emisión:</b>', date('d/m/Y h:i A')];
            $filas[] = [];

            $datosSesion = $modeloReporte->obtenerAsistenciaSesion($idFichaReal, $fecha);
            $aprendices = $datosSesion['aprendices'];
            $totales = $datosSesion['totales'];

            // Resumen de Métricas
            $filas[] = ['<b>RESUMEN DE ASISTENCIA:</b>'];
            $filas[] = [
                'Presentes:', $totales['presentes'],
                'Retardos:', $totales['retardos'],
                'Inasistencias:', $totales['inasistencias'],
                'Sin Registro:', $totales['sin_registro'],
                'Total Ficha:', $totales['total']
            ];
            $filas[] = [];

            // Encabezados
            $filas[] = [
                '<style bgcolor="#1e293b" color="#ffffff"><b>#</b></style>',
                '<style bgcolor="#1e293b" color="#ffffff"><b>Documento</b></style>',
                '<style bgcolor="#1e293b" color="#ffffff"><b>Aprendiz (Apellidos y Nombres)</b></style>',
                '<style bgcolor="#1e293b" color="#ffffff"><b>Hora Entrada</b></style>',
                '<style bgcolor="#1e293b" color="#ffffff"><b>Hora Salida</b></style>',
                '<style bgcolor="#1e293b" color="#ffffff"><b>Estado Sesión</b></style>',
                '<style bgcolor="#1e293b" color="#ffffff"><b>Faltas Acumuladas</b></style>'
            ];

            $num = 1;
            foreach ($aprendices as $ap) {
                $horaEntrada = !empty($ap['entrada']) ? date('h:i A', strtotime($ap['entrada'])) : '---';
                $horaSalida = !empty($ap['salida']) ? date('h:i A', strtotime($ap['salida'])) : '---';
                $estado = !empty($ap['estado_entrada']) ? $ap['estado_entrada'] : 'Sin registro';

                $filas[] = [
                    $num,
                    $ap['identificacion'],
                    trim($ap['apellido'] . ', ' . $ap['nombre']),
                    $horaEntrada,
                    $horaSalida,
                    $estado,
                    $ap['total_faltas']
                ];
                $num++;
            }

            $nombreArchivo = "Asistencia_Sesion_Ficha_{$numeroFicha}_{$fecha}.xlsx";
        }

        $xlsx = $xlsxGenClass::fromArray($filas);
        $xlsx->downloadAs($nombreArchivo);
        exit();
    }
}
