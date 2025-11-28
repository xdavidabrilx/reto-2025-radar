<?php
header("Content-Type: application/json; charset=utf-8");

include_once "config.php"; // ajusta ruta si tu API está en /api/

if (!isset($_GET['departamento'])) {
    echo json_encode([]);
    exit;
}

$departamento = trim($_GET['departamento']);

try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT municipio 
        FROM delitos_resumen
        WHERE departamento = :dep
        ORDER BY municipio
    ");

    $stmt->execute([":dep" => $departamento]);

    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
