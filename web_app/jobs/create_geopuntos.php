<?php

// URL del GeoJSON (puede ser archivo local)
$url = "https://gist.githubusercontent.com/nestorandrespe/f2533e9bf810ee42ca9fa617ce138530/raw/b6bb3903e78c1a831ca442f94741fb4feef52921/municipios.json";

// Leer contenido
$json = file_get_contents($url);
$data = json_decode($json, true);

if (!$data) {
    die("No se pudo leer el GeoJSON");
}

// Función para calcular centroide de un polígono
function centroid($coords) {
    $area = 0;
    $cx = 0;
    $cy = 0;
    $points = $coords;
    $len = count($points) - 1; // último igual al primero en GeoJSON

    for ($i = 0; $i < $len; $i++) {
        $x1 = $points[$i][0];
        $y1 = $points[$i][1];
        $x2 = $points[$i+1][0];
        $y2 = $points[$i+1][1];

        $step = ($x1 * $y2) - ($x2 * $y1);
        $area += $step;
        $cx += ($x1 + $x2) * $step;
        $cy += ($y1 + $y2) * $step;
    }

    $area *= 0.5;

    if ($area == 0) {
        // Evita división por cero
        return [$points[0][1], $points[0][0]];
    }

    $cx /= (6 * $area);
    $cy /= (6 * $area);

    return [$cy, $cx]; // regresar (lat, lng)
}

echo "<pre>";

foreach ($data["features"] as $feature) {
    
    //print_r($feature);

    $municipio = strtoupper(trim($feature["properties"]["mpio_cnmbr"]));
   // $municipio = mb_convert_case($municipio, MB_CASE_TITLE, "UTF-8");
    $departamento = strtoupper(trim($feature["properties"]["dpto_cnmbr"]));
    $geometry = $feature["geometry"];

    $lat = 0;
    $lng = 0;

    // POLYGON
    if ($geometry["type"] === "Polygon") {
        $coords = $geometry["coordinates"][0]; 
        list($lat, $lng) = centroid($coords);
    }

    // MULTIPOLYGON
    elseif ($geometry["type"] === "MultiPolygon") {
        // tomar el polígono más grande
        $maxArea = 0;
        $maxCoords = [];

        foreach ($geometry["coordinates"] as $poly) {
            $coords = $poly[0];
            $area = 0;

            for ($i=0; $i < count($coords)-1; $i++) {
                $area += ($coords[$i][0] * $coords[$i+1][1]) - ($coords[$i+1][0] * $coords[$i][1]);
            }

            $area = abs($area);

            if ($area > $maxArea) {
                $maxArea = $area;
                $maxCoords = $coords;
            }
        }

        list($lat, $lng) = centroid($maxCoords);
    }

    // Si no hay geometría válida
    else {
        continue;
    }

    // Crear SQL
    echo "INSERT INTO geopuntos (municipio, departamento, lat, lng) VALUES ("
        . "'" . addslashes($municipio) . "', "
        . "'" . addslashes($departamento) . "', "
        . "$lat, $lng"
        . ");\n";
        

}

echo "</pre>";