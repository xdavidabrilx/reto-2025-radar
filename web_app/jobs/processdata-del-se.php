<?php


function writeLog($message) {
    $logFile = __DIR__ . "/importacion-delito-sexual-".date("Y-m_d").".log"; // Ruta del log
    $date = date("Y-m-d H:i:s");
    file_put_contents(
        $logFile,
        "[$date] $message\n",
        FILE_APPEND
    );
}

function buscarGeopunto($departamento, $municipio, $geopuntos_index) {
    $key = trim($departamento) . "|" . trim($municipio);
    return isset($geopuntos_index[$key]) ? $geopuntos_index[$key] : null;
}

//Reporte Delito Violencia Intrafamiliar Policía Nacional
//$url = "https://datos.gov.co/resource/vuyt-mqpw.json";//"https://datos.gov.co/api/v3/views/vuyt-mqpw/query.json";

$api_base = "https://datos.gov.co/resource/fpe5-yrmw.json";
$app_token  = "";  // <-- cámbialo
$limit      = 5000;                 // tamaño de página recomendado
$offset     = 0;  

$headers = [
    "X-App-Token: ".$app_token
];




/******************************
 *  CONFIG PDO
 ******************************/
$host = "localhost";
$db   = '';
$user = '';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (Exception $e) {
    die("Error PDO: " . $e->getMessage());
}

/******************************
 *  LEER JSON DE ARCHIVO O API
 ******************************/
// Ej: desde un archivo en tu servidor
//$jsonString = file_get_contents("datos.json");

// También podría venir de una API:
// $jsonString = file_get_contents("https://tuservidor.com/datos.json");

//$data = json_decode($response, true);

/*if (!is_array($data)) {
    die("El JSON no tiene formato de array.");
}*/

/******************************
 * PREPARAR SENTENCIA PDO
 ******************************/

$stmtGeo = $pdo->query("SELECT * FROM geopuntos");
$geopuntos = $stmtGeo->fetchAll(PDO::FETCH_ASSOC);

// Crear un índice rápido en memoria (clave: depto+municipio)
$geopuntos_index = [];

foreach ($geopuntos as $g) {
    $key = trim($g["departamento"]) . "|" . trim($g["municipio"]);
    $geopuntos_index[$key] = $g;
}
 
$sql = "
INSERT INTO delitos (
    departamento, municipio, codigo_dane, armas_medios,
    fecha_hecho, genero, grupo_etario, cantidad, registro_hash, categoria
) VALUES (
    :departamento, :municipio, :codigo_dane, :armas_medios,
    :fecha_hecho, :genero, :grupo_etario, :cantidad, :registro_hash, :categoria
)
ON DUPLICATE KEY UPDATE 
    registro_hash = VALUES(registro_hash)"; 

$stmt = $pdo->prepare($sql);

while (true) {

    // URL CORRECTA:
    $url = $api_base .
           "?$"."limit=" . $limit .
           "&$"."offset=" . $offset .
           "&$$"."app_token=" . $app_token;
   writeLog($url."\n");
   writeLog("\nDescargando página offset = $offset ...\n");

    $response = file_get_contents($url);
    if (!$response) {
        die("Error al conectar con la API.\n");
    }

    $rows = json_decode($response, true);

    // Si la página viene vacía → terminar
    if (count($rows) === 0) {
        writeLog("\nNo hay más datos. Importación completa.\n");
        break;
    }


/******************************
 *  PROCESAR REGISTROS
 ******************************/
 foreach ($rows as $item) {
    $item["categoria"] = "SEXUAL";
    // Normalizar fecha dd/mm/yyyy → yyyy-mm-dd
    $fecha_parts = explode("/", $item["fecha_hecho"]);
    $fecha_mysql = "{$fecha_parts[2]}-{$fecha_parts[1]}-{$fecha_parts[0]}";

    // Crear hash único
    $hash = hash("sha256",
        $item["departamento"] . "|" .
        $item["municipio"] . "|" .
        $item["codigo_dane"] . "|" .
        $item["armas_medios"] . "|" .
        $fecha_mysql . "|" .
        $item["genero"] . "|" .
        $item["grupo_etario"] . "|" .
        $item["cantidad"]. "|" .
        $item["categoria"]
    );
    
    $item["municipio"] = mb_strtoupper($item["municipio"], 'UTF-8');
     $lat = "";
     $lng = "";
     
    $tmpObj=buscarGeopunto($item["departamento"], $item["municipio"], $geopuntos_index);
 
     if($tmpObj!=null){
          $lat = $tmpObj["lat"];
          $lng = $tmpObj["lng"];
     }

    // Ejecutar sentencia
    $stmt->execute([
        ":departamento"  => $item["departamento"],
        ":municipio"     => $item["municipio"],
        ":codigo_dane"   => $item["codigo_dane"],
        ":armas_medios"  => $item["armas_medios"],
        ":fecha_hecho"   => $fecha_mysql,
        ":genero"        => $item["genero"],
        ":grupo_etario"  => $item["grupo_etario"],
        ":cantidad"      => $item["cantidad"],
        ":registro_hash" => $hash,
        ":categoria" => $item["categoria"]
    ]);
 }
    
   writeLog("Insertados " . count($rows) . " registros.\n");

    // Avanzar a la siguiente página
    $offset += $limit;

    // Pequeña pausa opcional para no saturar servidores
    usleep(300000); // 0.3 segundos

}

writeLog("\nProceso finalizado.\n");