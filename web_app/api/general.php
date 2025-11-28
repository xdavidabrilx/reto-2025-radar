<?php

header("Content-Type: application/json; charset=utf-8");

include_once "config.php"; // ajusta ruta si tu API está en /api/

function normalizar_fecha($f) {
    // Si viene en blanco
    if ($f == "" || $f == null) return null;

    // Si ya viene en formato YYYY-MM-DD
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $f)) {
        return $f;
    }

    // Si viene en formato DD/MM/YYYY
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $f)) {
        list($d, $m, $y) = explode("/", $f);
        return $y . "-" . $m . "-" . $d;
    }

    return null; // formato desconocido
}


$desde        = isset($_GET["desde"]) ? $_GET["desde"] : date('Y')."-01-01";
$hasta        = isset($_GET["hasta"]) ? $_GET["hasta"] : date('Y-m-d');
$desde_ts = strtotime($desde);
$hasta_ts = strtotime($hasta);

$desde_anio = date('Y', $desde_ts);
$desde_mes  = date('n', $desde_ts);
$desde_sem  = date('W', $desde_ts); // semana ISO, lunes primer día

$hasta_anio = date('Y', $hasta_ts);
$hasta_mes  = date('n', $hasta_ts);
$hasta_sem  = date('W', $hasta_ts);


$where = [];
$params = [];

// Caso años diferentes
if ($desde_anio == $hasta_anio) {
    $where[] = "anio = :anio AND ((mes > :desde_mes) OR (mes = :desde_mes AND semana >= :desde_sem)) AND ((mes < :hasta_mes) OR (mes = :hasta_mes AND semana <= :hasta_sem))";
    $params = [
        ":anio" => $desde_anio,
        ":desde_mes" => $desde_mes,
        ":desde_sem" => $desde_sem,
        ":hasta_mes" => $hasta_mes,
        ":hasta_sem" => $hasta_sem
    ];
} else {
    // Si quieres manejar varios años, debes dividir en dos o más rangos
    $where[] = "(
        (anio = :desde_anio AND ((mes > :desde_mes) OR (mes = :desde_mes AND semana >= :desde_sem)))
        OR
        (anio = :hasta_anio AND ((mes < :hasta_mes) OR (mes = :hasta_mes AND semana <= :hasta_sem)))
        OR
        (anio > :desde_anio AND anio < :hasta_anio)
    )";
    $params = [
        ":desde_anio" => $desde_anio,
        ":desde_mes"  => $desde_mes,
        ":desde_sem"  => $desde_sem,
        ":hasta_anio" => $hasta_anio,
        ":hasta_mes"  => $hasta_mes,
        ":hasta_sem"  => $hasta_sem
    ];
}



$table="vista_delitos_tiempo_departamento";

 $sql = "
    SELECT departamento,delitos_sexuales,violencia,hurto,conflicto_armado
    FROM ".$table." ";

if(count($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " GROUP BY  departamento ORDER BY  departamento asc";


try {

    // ================================
    // Consulta principal
    // ================================
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    // ================================
    // Respuesta final
    // ================================
    echo json_encode($data);


} catch (Exception $e) {
    echo json_encode(array(
        "error" => true,
        "message" => $e->getMessage()
    ));
}