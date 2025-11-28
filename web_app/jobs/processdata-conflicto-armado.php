<?php

function writeLog($message) {
    $logFile = __DIR__ . "/importacion-conflicto-".date("Y-m_d").".log"; // Ruta del log
    $date = date("Y-m-d H:i:s");
    file_put_contents(
        $logFile,
        "[$date] $message\n",
        FILE_APPEND
    );
}

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


$endpoint = "https://serviciosgiscnmh.centrodememoriahistorica.gov.co/agccnmh/rest/services/OMC/Acciones_Belicas/MapServer/1/query";

$limit = 10000; // ArcGIS permite hasta 2000 normalmente
$offset = 0;

$sql = "
INSERT INTO delitos (
    departamento, municipio, codigo_dane, armas_medios,
    fecha_hecho, genero, grupo_etario, cantidad,
    registro_hash, categoria, lat, lng
) VALUES (
    :departamento, :municipio, :codigo_dane, :armas_medios,
    :fecha_hecho, :genero, :grupo_etario, :cantidad,
    :registro_hash, :categoria, :lat, :lng
)
ON DUPLICATE KEY UPDATE registro_hash = VALUES(registro_hash)
";

$stmt = $pdo->prepare($sql);


  

while (true) {

    $url = $endpoint . "?" . http_build_query([
        "where"             => "Fecha_Hecho >= DATE '2010-01-01'",
        "outFields"         => "*",
        "returnGeometry"    => "false",
        "f"                 => "json",
        "resultOffset"      => $offset,
        "resultRecordCount" => $limit
    ]);
    writeLog($url."\n");
    writeLog("\nDescargando página offset = $offset ...\n");
   
    $json = file_get_contents($url);
    if (!$json) {
        die("Error descargando datos\n");
    }

    $data = json_decode($json, true);

    if (!isset($data["features"]) || count($data["features"]) === 0) {
        echo "No hay más datos. Fin.\n";
        break;
    }
    $registro = 0;
    // PROCESAR CADA REGISTRO
    
    function debugSQL($query, $params) {
    foreach ($params as $key => $value) {

        // NULL → NULL
        if ($value === null) {
            $value = 'NULL';

        // Números → sin comillas
        } elseif (is_numeric($value)) {
            $value = $value;

        // Strings → con comillas escapadas
        } else {
            $value = "'" . addslashes($value) . "'";
        }

        // Reemplazar :param por valor
        $query = str_replace($key, $value, $query);
    }
    return $query;
}
    
    foreach ($data["features"] as $f) {

        $attr = $f["attributes"];
        

        // Normalizar municipio
        $municipio = mb_strtoupper(trim($attr["Nombre_Municipio"]), 'UTF-8');

        // Categoría fija que mencionaste
        $categoria = "CONFLICTO_ARMADO";
        $attr["categoria"] =  $categoria;
        
        if(intval($attr["dia_hecho"])<=9){
            $attr["dia_hecho"]="0".$attr["dia_hecho"];
        }
        
        if(intval($attr["mes_hecho"])<=9){
            $attr["mes_hecho"]="0".$attr["mes_hecho"];
        }
        
        $attr["FECHA_HECHO_1"] = $attr["Anio_hecho"]."-".$attr["mes_hecho"]."-".$attr["dia_hecho"];
        $attr["LAT"] = $attr["Latitud"];
        $attr["LON"] = $attr["Longitud"];
        $attr["CANTIDAD"] = $attr["Total_victimas"];
        $attr["MUNICIPIO"] = $municipio;
        $attr["DEPARTAMENTO"] = $attr["Nombre_Departamento"];
        $attr["ARMAS_MEDIOS"] = $attr["Nombre_Tipo"];
        $attr["GRUPO_ETARIO"] = "MIXTO";
        $attr["GENERO"] = "MIXTO";
        $attr["CODIGO_DANE"] = -1;
        	

        
        // Crear HASH único
        $hash = hash("sha256",
            $attr["DEPARTAMENTO"] . "|" .
            $attr["MUNICIPIO"] . "|" .
            $attr["CODIGO_DANE"] . "|" .
            $attr["ARMAS_MEDIOS"] . "|" .
            $attr["FECHA_HECHO_1"] . "|" .
            $attr["GENERO"] . "|" .
            $attr["GRUPO_ETARIO"] . "|" .
            $attr["CANTIDAD"] . "|" .
            $categoria
        );
        
        $params = [
            ":departamento"  => $attr["DEPARTAMENTO"],
            ":municipio"     =>$attr["MUNICIPIO"],
            ":codigo_dane"   => $attr["CODIGO_DANE"],
            ":armas_medios"  => $attr["ARMAS_MEDIOS"],
            ":fecha_hecho"   => $attr["FECHA_HECHO_1"],
            ":genero"        =>  $attr["GENERO"],
            ":grupo_etario"  => $attr["GRUPO_ETARIO"],
            ":cantidad"      => $attr["CANTIDAD"],
            ":registro_hash" => $hash,
            ":categoria"     => $attr["categoria"],
            ":lat"           => $attr["LAT"],
            ":lng"           => $attr["LON"]
        ];
      
        // Ejecutar INSERT
        $stmt->execute($params);
        //echo debugSQL($sql, $params);
        
        //print_r($attr);
     
        // Pequeña pausa opcional para no saturar servidores
        //usleep(300000); // 0.3 segundos
        $registro++;
    }


    writeLog("Insertados " . count($data) . " registros.\n");

    // Avanzar a la siguiente página
    $offset += $limit;

   
}

writeLog("\nProceso finalizado.\n");