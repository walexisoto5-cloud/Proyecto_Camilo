<?php

/**
 * HorarioParser - Utilidad especializada en el procesamiento y extracción de bloques
 * formativos desde archivos Microsoft Excel (.xlsx) y archivos planos CSV.
 */
class HorarioParser
{
    /**
     * Extrae los bloques de formación a partir de la ruta y extensión del archivo
     */
    public static function extraerBloques(string $rutaArchivo, string $nombreOriginal): array
    {
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

        if ($ext === 'xlsx') {
            return self::parsearExcelXlsx($rutaArchivo);
        } elseif (in_array($ext, ['csv', 'txt'])) {
            return self::parsearCsv($rutaArchivo);
        }

        return [];
    }

    /**
     * Lector nativo de archivos .xlsx sin dependencias externas basado en ZipArchive y SimpleXML
     */
    public static function parsearExcelXlsx(string $filePath): array
    {
        if (!class_exists('ZipArchive')) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) {
            return [];
        }

        // 1. Obtener Shared Strings (textos compartidos de Excel)
        $sharedStrings = [];
        $xmlStrings = $zip->getFromName('xl/sharedStrings.xml');
        if ($xmlStrings === false) {
            $xmlStrings = $zip->getFromName('xl/SharedStrings.xml');
        }
        if ($xmlStrings !== false) {
            $sxml = simplexml_load_string($xmlStrings);
            if ($sxml) {
                foreach ($sxml->si as $val) {
                    $t = '';
                    if (isset($val->t)) {
                        $t = (string)$val->t;
                    } elseif (isset($val->r)) {
                        foreach ($val->r as $r) {
                            $t .= (string)$r->t;
                        }
                    }
                    $sharedStrings[] = trim($t);
                }
            }
        }

        // 2. Localizar hoja de cálculo principal
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (preg_match('#^xl/worksheets/sheet\d+\.xml$#i', $stat['name'])) {
                    $sheetXml = $zip->getFromIndex($i);
                    break;
                }
            }
        }
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $sheet = simplexml_load_string($sheetXml);
        if (!$sheet || !isset($sheet->sheetData->row)) {
            return [];
        }

        $filas = [];
        foreach ($sheet->sheetData->row as $r) {
            $fila = [];
            foreach ($r->c as $c) {
                $attr = $c->attributes();
                $tipo = isset($attr['t']) ? (string)$attr['t'] : '';
                $ref = (string)$attr['r'];
                preg_match('/([A-Z]+)/', $ref, $mCol);
                $colIndex = isset($mCol[1]) ? self::columnaAIndice($mCol[1]) : count($fila);

                $valor = '';
                if ($tipo === 's' && isset($sharedStrings[(int)$c->v])) {
                    $valor = $sharedStrings[(int)$c->v];
                } elseif ($tipo === 'inlineStr' && isset($c->is->t)) {
                    $valor = (string)$c->is->t;
                } elseif (isset($c->v)) {
                    $valor = (string)$c->v;
                }

                $fila[$colIndex] = trim($valor);
            }
            if (!empty($fila)) {
                $filas[] = $fila;
            }
        }

        return self::mapearFilasABloques($filas);
    }

    /**
     * Convierte columnas alfabéticas de Excel (A, B, C...) a índice numérico base 0
     */
    private static function columnaAIndice(string $col): int
    {
        $index = 0;
        $len = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - 64);
        }
        return $index - 1;
    }

    /**
     * Lector de archivos CSV con autodetección de delimitador (, o ;)
     */
    public static function parsearCsv(string $filePath): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) return [];

        $delimitador = (substr_count($content, ';') > substr_count($content, ',')) ? ';' : ',';

        $handle = fopen($filePath, 'r');
        if (!$handle) return [];

        $filas = [];
        while (($row = fgetcsv($handle, 1000, $delimitador)) !== false) {
            $filas[] = array_map('trim', $row);
        }
        fclose($handle);

        return self::mapearFilasABloques($filas);
    }

    /**
     * Mapea filas leídas al formato estructurado de bloques de formación
     */
    private static function mapearFilasABloques(array $filas): array
    {
        // 1. Intentar analizar como Matriz Oficial SENA
        $bloquesMatriz = self::parsearMatrizHorarioSena($filas);
        if (!empty($bloquesMatriz)) {
            return $bloquesMatriz;
        }

        // 2. Si no es matriz SENA, analizar formato plano
        $bloques = [];
        $diasValidos = ['LUNES', 'MARTES', 'MIERCOLES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SABADO', 'SÁBADO', 'DOMINGO'];

        foreach ($filas as $fila) {
            if (empty($fila)) continue;

            $celda0 = strtoupper(trim($fila[0] ?? ''));

            if (strpos($celda0, 'DIA') !== false || strpos($celda0, 'DÍA') !== false) {
                continue;
            }

            if (in_array($celda0, $diasValidos)) {
                $dia = ucfirst(strtolower($celda0));
                if ($dia === 'Miercoles') $dia = 'Miércoles';
                if ($dia === 'Sabado') $dia = 'Sábado';

                $entradaRaw = trim($fila[1] ?? '06:00');
                $salidaRaw = trim($fila[2] ?? '08:45');
                $instructor = trim($fila[3] ?? '');
                $competencia = trim($fila[4] ?? '');
                $ambiente = trim($fila[5] ?? '');

                $entrada = self::normalizarHora($entradaRaw, '06:00:00');
                $salida = self::normalizarHora($salidaRaw, '08:45:00');

                if (stripos($instructor, 'DESCANSO') !== false || stripos($instructor, 'RECESO') !== false ||
                    stripos($competencia, 'DESCANSO') !== false || stripos($competencia, 'RECESO') !== false) {
                    continue;
                }

                $bloques[] = [
                    'dia_semana' => $dia,
                    'entrada' => $entrada,
                    'salida' => $salida,
                    'instructor' => $instructor,
                    'competencia' => $competencia,
                    'ambiente' => $ambiente
                ];
            }
        }

        return $bloques;
    }

    /**
     * Parser especializado para la cuadrícula institucional del SENA
     */
    private static function parsearMatrizHorarioSena(array $filas): array
    {
        $diasMapa = [
            'LUNES' => 'Lunes',
            'MARTES' => 'Martes',
            'MIERCOLES' => 'Miércoles',
            'MIÉRCOLES' => 'Miércoles',
            'JUEVES' => 'Jueves',
            'VIERNES' => 'Viernes',
            'SABADO' => 'Sábado',
            'SÁBADO' => 'Sábado'
        ];

        $columnasDiasActuales = [];
        $horarioPorDia = [];

        foreach ($filas as $fila) {
            if (empty($fila)) continue;

            $colsEncontradas = [];
            foreach ($fila as $cIdx => $valor) {
                $valLimpio = strtoupper(trim((string)$valor));
                $valLimpio = str_replace(['Á','É','Í','Ó','Ú'], ['A','E','I','O','U'], $valLimpio);
                if (isset($diasMapa[$valLimpio])) {
                    $colsEncontradas[$cIdx] = $diasMapa[$valLimpio];
                }
            }

            if (count($colsEncontradas) >= 3) {
                $columnasDiasActuales = $colsEncontradas;
                continue;
            }

            if (empty($columnasDiasActuales)) {
                continue;
            }

            $horaInicio = null;
            $maxColCheck = min(6, count($fila));
            for ($c = 0; $c < $maxColCheck; $c++) {
                $val = trim((string)($fila[$c] ?? ''));
                if ($val === '') continue;

                if (is_numeric($val) && (int)$val >= 5 && (int)$val <= 23) {
                    for ($k = $c + 1; $k <= min($c + 2, count($fila) - 1); $k++) {
                        $valNext = trim((string)($fila[$k] ?? ''));
                        if (is_numeric($valNext) && ((int)$valNext == ((int)$val + 1) || (int)$valNext == ((int)$val + 2))) {
                            $horaInicio = (int)$val;
                            break 2;
                        }
                    }
                    $horaInicio = (int)$val;
                    break;
                } elseif (preg_match('/^(\d{1,2})\s*[-:]\s*(\d{1,2})/i', $val, $mH)) {
                    $horaInicio = (int)$mH[1];
                    break;
                }
            }

            if ($horaInicio === null) {
                continue;
            }

            $esBloque1 = ($horaInicio >= 6 && $horaInicio < 9);
            $esBloque2 = ($horaInicio >= 9 && $horaInicio < 12);

            if (!$esBloque1 && !$esBloque2) {
                continue;
            }

            foreach ($columnasDiasActuales as $cIdx => $nombreDia) {
                $contenido = trim((string)($fila[$cIdx] ?? ''));
                if (empty($contenido) && isset($fila[$cIdx + 1])) {
                    $contenido = trim((string)$fila[$cIdx + 1]);
                }

                if (empty($contenido) || stripos($contenido, 'FESTIVO') !== false) {
                    continue;
                }

                $lineas = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $contenido)))));
                $instructorNombre = $lineas[0] ?? '';
                $competenciaNombre = $lineas[1] ?? 'Formación Técnica ADSO';

                if (empty($instructorNombre) || is_numeric($instructorNombre) || mb_strlen($instructorNombre) < 3 || stripos($instructorNombre, 'DESCANSO') !== false || stripos($instructorNombre, 'RECESO') !== false) {
                    continue;
                }

                $tipoBloque = $esBloque1 ? 'bloque1' : 'bloque2';

                if (!isset($horarioPorDia[$nombreDia][$tipoBloque])) {
                    $horarioPorDia[$nombreDia][$tipoBloque] = [
                        'dia_semana' => $nombreDia,
                        'entrada' => $esBloque1 ? '06:00:00' : '09:15:00',
                        'salida' => $esBloque1 ? '08:45:00' : '12:00:00',
                        'instructor' => $instructorNombre,
                        'competencia' => $competenciaNombre,
                        'ambiente' => 'Ambiente 204'
                    ];
                }
            }
        }

        $resultado = [];
        $ordenDias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        foreach ($ordenDias as $dia) {
            if (isset($horarioPorDia[$dia]['bloque1'])) {
                $resultado[] = $horarioPorDia[$dia]['bloque1'];
            }
            if (isset($horarioPorDia[$dia]['bloque2'])) {
                $resultado[] = $horarioPorDia[$dia]['bloque2'];
            }
        }

        return $resultado;
    }

    /**
     * Normaliza formatos de hora a formato estandarizado HH:MM:SS
     */
    public static function normalizarHora(string $horaStr, string $default = '06:00:00'): string
    {
        $horaStr = trim($horaStr);
        if (empty($horaStr)) return $default;

        if (is_numeric($horaStr) && (float)$horaStr < 1.0) {
            $totalSegundos = round((float)$horaStr * 86400);
            $h = floor($totalSegundos / 3600);
            $m = floor(($totalSegundos % 3600) / 60);
            $s = $totalSegundos % 60;
            return sprintf('%02d:%02d:%02d', $h, $m, $s);
        }

        $time = strtotime($horaStr);
        if ($time !== false) {
            return date('H:i:s', $time);
        }

        return $default;
    }

    /**
     * Genera el contenido de plantilla CSV para descarga
     */
    public static function generarPlantillaCsv(): string
    {
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $lineas = [];
        $lineas[] = "DIA_SEMANA,HORA_ENTRADA,HORA_SALIDA,INSTRUCTOR,COMPETENCIA,AMBIENTE";

        foreach ($dias as $d) {
            $lineas[] = "{$d},06:00,08:45,Juan Camilo Vanegas,Desarrollo de Software ADSO,Ambiente 204";
            $lineas[] = "{$d},08:45,09:15,RECESO,Descanso de Formación,Cafetería";
            $lineas[] = "{$d},09:15,12:00,Maria Alejandra Lopez,Bases de Datos Relacionales,Ambiente 204";
        }

        return "\xEF\xBB\xBF" . implode("\r\n", $lineas);
    }
}
