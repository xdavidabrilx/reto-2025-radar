<?php

$csv_file = __DIR__ . '/150_Información_delictiva_del_municipio_de_Bucaramanga_20251128.csv';  // Cambia por tu CSV
$json_file = __DIR__ . '/150_bucaramanga.json'; // Archivo JSON de salida

if (!file_exists($csv_file)) {
    die("El archivo CSV no existe: $csv_file");
}

$rows = [];
if (($handle = fopen($csv_file, 'r')) !== false) {
    $headers = fgetcsv($handle); // Leer encabezados

    while (($data = fgetcsv($handle)) !== false) {
        $rows[] = array_combine($headers, $data);
    }

    fclose($handle);
}

// Guardar JSON
file_put_contents($json_file, json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "✔ CSV convertido a JSON: $json_file\n";