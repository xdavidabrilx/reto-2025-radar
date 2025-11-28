<?php
header("Content-Type: application/json; charset=utf-8");

include_once "config.php"; // ajusta ruta si tu API está en /api/


try {
    $stmt = $pdo->prepare("
        SELECT DISTINCT departamento 
        FROM delitos 
        ORDER BY departamento asc
    ");

    $stmt->execute();

    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode([
        "error" => true,
        "message" => $e->getMessage()
    ]);
}
