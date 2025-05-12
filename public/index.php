<?php
require_once __DIR__ . '/Database.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");


$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$conn = connectToDB();

if ($method === 'POST' && strcmp($uri, '/votar')) {
    votar($conn);
} elseif ($method === 'GET' && strcmp($uri, '/top3')) {
    obtenerTop3($conn);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Endpoint no encontrado"]);
    exit;
}

function votar($conn) {
    $input = json_decode(file_get_contents("php://input"), true);
    $name = $input['name'];

    if (!$name) {
        http_response_code(400);
        echo json_encode(["error" => "Nombre requerido"]);
        exit;
    }

    $query = "UPDATE personajes SET votos = COALESCE(votos, 0) + 1 WHERE name = $1";
    $result = pg_query_params($conn, $query, [$name]);

    if (!$result) {
        http_response_code(500);
        echo json_encode(["error" => "Error al registrar voto"]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "Voto registrado para $name"]);
}

function obtenerTop3($conn) {
    $query = "SELECT name, votos FROM personajes ORDER BY votos DESC NULLS LAST LIMIT 3";
    $result = pg_query($conn, $query);

    if (!$result) {
        http_response_code(500);
        echo json_encode(["error" => "Error al obtener top 3"]);
        exit;
    }

    $top = [];
    while ($row = pg_fetch_assoc($result)) {
        $top[] = $row;
    }

    echo json_encode($top);
}
