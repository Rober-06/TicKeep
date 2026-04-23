<?php

function limpiarTextoOCR(string $texto): string
{
    $texto = str_replace(["\r\n", "\r"], "\n", $texto);
    $texto = preg_replace('/[ \t]+/', ' ', $texto);
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

function esLineaNoValida(string $linea): bool
{
    $linea = trim($linea);

    if ($linea === '') {
        return true;
    }

    if (mb_strlen($linea) < 3) {
        return true;
    }

    if (mb_strlen($linea) > 60) {
        return true;
    }

    if (preg_match('/(fecha|ticket|total|iva|cif|nif|hora|euros|importe|tarjeta|efectivo|cambio|gracias|www|http|tel|telefono|direccion|dat[aá]fono)/i', $linea)) {
        return true;
    }

    if (preg_match('/^\d+[.,]?\d*\s?€?$/', $linea)) {
        return true;
    }

    return false;
}

function detectarTienda(string $texto): ?string
{
    $lineas = explode("\n", $texto);

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if (esLineaNoValida($linea)) {
            continue;
        }

        return mb_substr($linea, 0, 100);
    }

    return null;
}

function detectarProducto(string $texto, ?string $tienda = null): ?string
{
    $lineas = explode("\n", $texto);

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        if (esLineaNoValida($linea)) {
            continue;
        }

        if ($tienda !== null && mb_strtolower($linea) === mb_strtolower(trim($tienda))) {
            continue;
        }

        // Evitar líneas que son solo mayúsculas largas tipo cabecera
        if (preg_match('/^[A-Z0-9\s\-\.]+$/u', $linea) && mb_strlen($linea) > 25) {
            continue;
        }

        return mb_substr($linea, 0, 150);
    }

    return null;
}

function crearResumenOCR(string $texto, ?string $tienda, ?string $fecha, ?string $producto): string
{
    $partes = [];

    if ($tienda) {
        $partes[] = "Tienda detectada: " . $tienda;
    }

    if ($fecha) {
        $partes[] = "Fecha detectada: " . $fecha;
    }

    if ($producto) {
        $partes[] = "Producto detectado: " . $producto;
    }

    return implode(" | ", $partes);
}

function procesarOCRTicket(string $rutaArchivo): array
{
    $tesseract = 'C:\\Program Files\\Tesseract-OCR\\tesseract.exe';

    if (!file_exists($tesseract)) {
        return [
            'ok' => false,
            'error' => 'No se ha encontrado Tesseract en la ruta configurada: ' . $tesseract
        ];
    }

    if (!file_exists($rutaArchivo)) {
        return [
            'ok' => false,
            'error' => 'No se ha encontrado el archivo subido: ' . $rutaArchivo
        ];
    }

    $tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'ocr_' . uniqid();
    $salidaTxt = $tmpBase . '.txt';

    $cmd = '"' . $tesseract . '" '
        . escapeshellarg($rutaArchivo) . ' '
        . escapeshellarg($tmpBase) . ' '
        . '-l eng --psm 6 2>&1';

    exec($cmd, $output, $returnVar);

    if (!file_exists($salidaTxt)) {
        return [
            'ok' => false,
            'error' => 'Tesseract no ha generado ningún archivo de salida.',
            'debug' => implode("\n", $output),
            'cmd' => $cmd
        ];
    }

    $texto = file_get_contents($salidaTxt);
    @unlink($salidaTxt);

    $texto = limpiarTextoOCR($texto);

    if ($texto === '') {
        return [
            'ok' => false,
            'error' => 'El OCR se ejecutó, pero no detectó texto.',
            'cmd' => $cmd
        ];
    }

    $tienda = detectarTienda($texto);
    $fecha = detectarFecha($texto);
    $producto = detectarProducto($texto, $tienda);
    $resumen = crearResumenOCR($texto, $tienda, $fecha, $producto);

    return [
        'ok' => true,
        'texto' => $texto,
        'tienda' => $tienda,
        'fecha_compra' => $fecha,
        'producto' => $producto,
        'resumen' => $resumen,
        'cmd' => $cmd
    ];
}