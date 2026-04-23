<?php
function limpiarTextoOCR(string $texto): string
{
    $texto = str_replace(["\r\n", "\r"], "\n", $texto);
    $texto = preg_replace("/[ \t]+/", " ", $texto);
    $texto = preg_replace("/\n{2,}/", "\n", $texto);
    return trim($texto);
}

function detectarFecha(string $texto): ?string
{
    $patrones = [
        '/\b(\d{2})[\/\-](\d{2})[\/\-](\d{4})\b/',
        '/\b(\d{4})[\/\-](\d{2})[\/\-](\d{2})\b/'
    ];

    foreach ($patrones as $patron) {
        if (preg_match($patron, $texto, $m)) {
            if (strlen($m[1]) === 4) {
                return $m[1] . '-' . $m[2] . '-' . $m[3];
            }
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
    }

    return null;
}

function detectarTienda(string $texto): ?string
{
    $lineas = explode("\n", $texto);

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if ($linea === '') {
            continue;
        }

        if (mb_strlen($linea) < 3) {
            continue;
        }

        if (preg_match('/(fecha|ticket|total|iva|cif|nif|hora)/i', $linea)) {
            continue;
        }

        return mb_substr($linea, 0, 100);
    }

    return null;
}

function procesarOCRTicket(string $rutaArchivo): array
{
    // Ajusta esta ruta a tu instalación real
    $tesseract = 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe';

    if (!file_exists($tesseract)) {
        return [
            'ok' => false,
            'error' => 'No se ha encontrado Tesseract en la ruta configurada.'
        ];
    }

    if (!file_exists($rutaArchivo)) {
        return [
            'ok' => false,
            'error' => 'No se ha encontrado el archivo del ticket.'
        ];
    }

    $tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_' . uniqid();
    $salidaTxt = $tmpBase . '.txt';

    // OCR en español + modo de página automático
    $cmd = '"' . $tesseract . '" '
        . escapeshellarg($rutaArchivo) . ' '
        . escapeshellarg($tmpBase) . ' '
        . '-l spa '
        . '--psm 6 2>&1';

    exec($cmd, $output, $returnVar);

    if (!file_exists($salidaTxt)) {
        return [
            'ok' => false,
            'error' => 'No se pudo generar la salida OCR.',
            'debug' => implode("\n", $output)
        ];
    }

    $texto = file_get_contents($salidaTxt);
    @unlink($salidaTxt);

    $texto = limpiarTextoOCR($texto);

    return [
        'ok' => true,
        'texto' => $texto,
        'tienda' => detectarTienda($texto),
        'fecha_compra' => detectarFecha($texto)
    ];
}