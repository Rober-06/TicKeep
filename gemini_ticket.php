<?php

function limpiarJsonGemini(string $texto): string
{
    $texto = trim($texto);
    $texto = str_replace("```json", "", $texto);
    $texto = str_replace("```", "", $texto);
    return trim($texto);
}

function procesarTicketGemini(string $rutaImagen): array
{
    $apiKey = "-PONER AQUI ";

    if (!file_exists($rutaImagen)) {
        return [
            'ok' => false,
            'error' => 'No existe la imagen del ticket.'
        ];
    }

    $mime = mime_content_type($rutaImagen);
    $base64 = base64_encode(file_get_contents($rutaImagen));

    $data = [
        "contents" => [
            [
                "parts" => [
                    [
                        "text" => "Analiza este ticket de compra y devuelve únicamente un objeto JSON puro con estas claves exactas: nombre_producto, tienda, fecha_compra. La fecha debe ir en formato YYYY-MM-DD. nombre_producto debe ser el artículo principal o el producto más probable sobre el que un usuario querría guardar una garantía, por ejemplo un móvil, auriculares, monitor, electrodoméstico, consola o accesorio tecnológico. Si hay varios productos, devuelve el más relevante. Si no puedes identificar ninguno con una confianza razonable, devuelve cadena vacía. No uses markdown, no uses ```json, no añadas explicaciones."
                    ],
                    [
                        "inline_data" => [
                            "mime_type" => $mime,
                            "data" => $base64
                        ]
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.1,
            "maxOutputTokens" => 800
        ]
    ];

    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $apiKey;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if ($response === false) {
        return [
            'ok' => false,
            'error' => 'Error cURL: ' . curl_error($ch)
        ];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $respuestaArray = json_decode($response, true);

    if ($httpCode !== 200) {
        return [
            'ok' => false,
            'error' => 'Error HTTP ' . $httpCode,
            'debug' => $respuestaArray
        ];
    }

    if (!isset($respuestaArray['candidates'][0]['content']['parts'][0]['text'])) {
        return [
            'ok' => false,
            'error' => 'Gemini no ha devuelto texto válido.',
            'debug' => $respuestaArray
        ];
    }

    $texto = $respuestaArray['candidates'][0]['content']['parts'][0]['text'];
    $jsonLimpio = limpiarJsonGemini($texto);
    $datos = json_decode($jsonLimpio, true);

    if (!is_array($datos)) {
        return [
            'ok' => false,
            'error' => 'No se pudo interpretar el JSON devuelto por Gemini.',
            'raw' => $texto
        ];
    }

    return [
        'ok' => true,
        'nombre_producto' => trim($datos['nombre_producto'] ?? ''),
        'tienda' => trim($datos['tienda'] ?? ''),
        'fecha_compra' => trim($datos['fecha_compra'] ?? '')
    ];
}