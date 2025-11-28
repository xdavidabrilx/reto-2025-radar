<?php
header("Content-Type: application/json");

$apiKey = "";  

$vector_store_id_general = "vs_6929f..."; 
$vector_store_id_santander = "vs_6929..."; 

function classify_intent($question, $api_key) {

    $payload = [
        "model" => "gpt-4o-mini",
        "input" => "
Clasifica la pregunta SOLO en una de estas categorías:

1. BUCARAMANGA
2. SANTANDER
3. GENERAL

Responde SOLO el nombre en mayúsculas, sin texto extra.

Pregunta: \"$question\"
"
    ];

    $ch = curl_init("https://api.openai.com/v1/responses");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $api_key",
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    $classification = trim($data["output"][0]["content"][0]["text"]);

    return $classification;
}

function select_vector_store($classification) {
    $vector_store_id_general = "vs_6929...."; 
    $vector_store_id_santander = "vs_692....";
    
    $map = [
        "BUCARAMANGA" => $vector_store_id_santander,
        "DELINCUENCIA" => $vector_store_id_santander,
        "GENERAL" => $vector_store_id_general
    ];

    if (isset($map[$classification])) {
        return $map[$classification];
    }

    // default fallback
    return $vector_store_id_general;
}

$input = file_get_contents("php://input");
$data = json_decode($input, true);

// Ahora si puedes acceder a 'mensaje'
$user_question = isset($data["mensaje"]) ? $data["mensaje"] : "";

if ($user_question == "") {
    echo json_encode(["output" => ["respuesta" => "Por favor agrega una pregunta"]]);
    exit;
}



// Lista de keywords y vector stores (usando array clásico)
$keywords = array(
    "bucaramanga" => $vector_store_id_santander,
    "santander"   => $vector_store_id_santander
);

$question_lower = strtolower($user_question);
$best_match = "";
$best_score = 0;

// Comparar cada keyword usando similar_text()
foreach ($keywords as $key => $vs_id) {
    $percent = 0;
    similar_text($question_lower, $key, $percent);
    if ($percent > $best_score) {
        $best_score = $percent;
        $best_match = $key;
    }
}


// Umbral mínimo para considerar coincidencia
$threshold = 20; // porcentaje mínimo
if ($best_score >= $threshold) {
    $classification = classify_intent($question_lower, $apiKey);
    $vector_store_id = select_vector_store($classification);
} else {
    $vector_store_id = $vector_store_id_general;
}


$response = "Puedes darme más contexto por favor?";

if($user_question){
    
    $payload = array(
    "model" => "gpt-4o-mini",
    "input" => "Responde siempre en español, sin importar el idioma de la pregunta del usuario.Pregunta: ".$user_question,
    "tools" => array(
        array(
            "type" => "file_search",
            "vector_store_ids" => array($vector_store_id)
        )
    ),
    "include" => array("file_search_call.results")
);
    

    $ch = curl_init("https://api.openai.com/v1/responses");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Authorization: Bearer " . $apiKey,
        "Content-Type: application/json"
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    
    curl_close($ch);
    
    // Parsear respuesta
    $data = json_decode($response, true);
    $output = $data["output"];
    //print_r($output);
    $reply = "Disculpame, no poseo información";
    
    foreach($output as $result){
    
        if(isset($result["type"]) && $result["type"]=="message"){
            $reply =  $result["content"][0]["text"];
        }
    }

    $response = $reply;

}

echo json_encode(["output" => ["respuesta" => $response]]);

