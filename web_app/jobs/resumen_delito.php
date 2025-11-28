<?php

function writeLog($message) {
    $logFile = __DIR__ . "/resumen-creacion-2-".date("Y-m_d").".log"; // Ruta del log
    $date = date("Y-m-d H:i:s");
    file_put_contents(
        $logFile,
        "[$date] $message\n",
        FILE_APPEND
    );
}

writeLog("Inicio...\n");

$mysqli = new mysqli("localhost", "", "", "");
  
if ($mysqli->connect_errno) {
    die("Error de conexión: " . $mysqli->connect_error);
}


// 1. Crear tabla delitos_resumen si no existe
$create = "
CREATE TABLE IF NOT EXISTS delitos_resumen (
    departamento VARCHAR(255),
    municipio VARCHAR(255),
    zona VARCHAR(255),
    anio INT,
    mes INT,
    semana INT,
    lat DECIMAL(10,7),
    lng DECIMAL(10,7),
    delitos_sexuales INT,
    violencia INT,
    hurto INT,
    conflicto_armado INT
);
";
$mysqli->query($create);

$mysqli->query("TRUNCATE TABLE delitos_resumen");

// 1. Leer todo sin agrupar (NO usa /tmp)
$res = $mysqli->query("SELECT MIN(id) AS min_id, MAX(id) AS max_id FROM delitos");
$row = $res->fetch_assoc();
$min_id = intval($row['min_id']);
$max_id = intval($row['max_id']);

$chunk_size = 100000; // filas por bloque
$current_id = $min_id;

while ($current_id <= $max_id) {
    $end_id = $current_id + $chunk_size - 1;

    echo "Procesando IDs $current_id a $end_id...\n";

    $sql = "
    SELECT id, departamento, municipio, zona,
           YEAR(fecha_hecho) AS anio,
           MONTH(fecha_hecho) AS mes,
           WEEK(fecha_hecho, 3) AS semana,
           lat, lng, categoria, cantidad
    FROM delitos
    WHERE id BETWEEN $current_id AND $end_id
    ORDER BY id ASC
    ";

    $result = $mysqli->query($sql);
    if (!$result) {
        die("Error: " . $mysqli->error);
    }

    $grupo = []; // reiniciamos array para no saturar memoria

    while ($row = $result->fetch_assoc()) {
        $key = $row['departamento'] . "|" . $row['municipio'] . "|" . $row['zona'] . "|" .
               $row['anio'] . "|" . $row['mes'] . "|" . $row['semana'];

        if (!isset($grupo[$key])) {
            $grupo[$key] = [
                'departamento' => $row['departamento'],
                'municipio' => $row['municipio'],
                'zona' => $row['zona'],
                'anio' => $row['anio'],
                'mes' => $row['mes'],
                'semana' => $row['semana'],
                'lat' => $row['lat'],
                'lng' => $row['lng'],
                'delitos_sexuales' => 0,
                'violencia' => 0,
                'hurto' => 0,
                'conflicto_armado' => 0
            ];
        }

        switch ($row['categoria']) {
            case 'SEXUAL': $grupo[$key]['delitos_sexuales'] += $row['cantidad']; break;
            case 'VIOLENCIA_INTRAFAMILIAR': $grupo[$key]['violencia'] += $row['cantidad']; break;
            case 'HURTO': $grupo[$key]['hurto'] += $row['cantidad']; break;
            case 'CONFLICTO_ARMADO': $grupo[$key]['conflicto_armado'] += $row['cantidad']; break;
        }
    }
     $insert_sql = 0;
    // Insertar resultados de este bloque
    foreach ($grupo as $g) {
        $stmt = $mysqli->prepare("
            INSERT INTO delitos_resumen
            (departamento, municipio, zona, anio, mes, semana, lat, lng, delitos_sexuales, violencia, hurto, conflicto_armado)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "sssiiiddiiii",
            $g['departamento'], $g['municipio'], $g['zona'], 
            $g['anio'], $g['mes'], $g['semana'],
            $g['lat'], $g['lng'],
            $g['delitos_sexuales'], $g['violencia'], $g['hurto'], $g['conflicto_armado']
        );

    $stmt->execute();
        
        
        /*$insert_sql = "
        INSERT INTO delitos_resumen
        (departamento, municipio, zona, anio, mes, semana, lat, lng, delitos_sexuales, violencia, hurto, conflicto_armado)
        VALUES (
            '{$g['departamento']}',
            '{$g['municipio']}',
            '{$g['zona']}',
            {$g['anio']},
            {$g['mes']},
            {$g['semana']},
            {$g['lat']},
            {$g['lng']},
            {$g['delitos_sexuales']},
            {$g['violencia']},
            {$g['hurto']},
            {$g['conflicto_armado']}
        );
        ";*/
        
         writeLog($insert_sql . "\n");
         $insert_sql = $insert_sql +1;
    }

    // Limpiar memoria
    $grupo = [];
    unset($result);

    $current_id += $chunk_size;
}


writeLog("✔ Proceso completado sin usar GROUP BY en MySQL.\n");

