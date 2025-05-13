<?php
require_once __DIR__ . '/Database.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type");


$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$conn = connectToDB();

if ($method === 'POST' && $uri === '/votarPersonaje') {
    votar($conn, $uri);
} elseif ($method === 'POST' && $uri === '/votarLibro') {
    votar($conn, $uri);
} elseif ($method === 'POST' && $uri === '/votarPelicula') {
    votar($conn, $uri);
} elseif ($method === 'GET' && $uri === '/top3') {
    obtenerTop3($conn);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Endpoint no encontrado"]);
    exit;
}


function votar($conn, $uri)
{
    $input = json_decode(file_get_contents("php://input"), true);
    $name = $input['name'];

    if (!$name) {
        http_response_code(400);
        echo json_encode(["error" => "Nombre requerido"]);
        exit;
    }

    if ($uri === '/votarPersonaje') {
        $query = "UPDATE votos_personajes SET num_votos = COALESCE(num_votos, 0) + 1 WHERE nombre = $1";
        $result = pg_query_params($conn, $query, [$name]);

        if (!$result) {
            http_response_code(500);
            echo json_encode(["error" => "Error al registrar voto"]);
            exit;
        }

        if (pg_affected_rows($result) === 0) {
            $insertQuery = "INSERT INTO votos_personajes (nombre, num_votos) VALUES ($1, 1)";
            $insertResult = pg_query_params($conn, $insertQuery, [$name]);

            if (!$insertResult) {
                http_response_code(500);
                echo json_encode(["error" => "Error al insertar nuevo personaje"]);
                exit;
            }
        }
    } elseif ($uri === '/votarLibro') {
        $query = "UPDATE votos_libros SET num_votos = COALESCE(num_votos, 0) + 1 WHERE nombre = $1";
        $result = pg_query_params($conn, $query, [$name]);

        if (!$result) {
            http_response_code(500);
            echo json_encode(["error" => "Error al registrar voto"]);
            exit;
        }

        if (pg_affected_rows($result) === 0) {
            $insertQuery = "INSERT INTO votos_libros (nombre, num_votos) VALUES ($1, 1)";
            $insertResult = pg_query_params($conn, $insertQuery, [$name]);

            if (!$insertResult) {
                http_response_code(500);
                echo json_encode(["error" => "Error al insertar nuevo personaje"]);
                exit;
            }
        }
    } elseif ($uri === '/votarPelicula') {
        $query = "UPDATE votos_peliculas SET num_votos = COALESCE(num_votos, 0) + 1 WHERE nombre = $1";
        $result = pg_query_params($conn, $query, [$name]);

        if (!$result) {
            http_response_code(500);
            echo json_encode(["error" => "Error al registrar voto"]);
            exit;
        }

        if (pg_affected_rows($result) === 0) {
            $insertQuery = "INSERT INTO votos_peliculas (nombre, num_votos) VALUES ($1, 1)";
            $insertResult = pg_query_params($conn, $insertQuery, [$name]);

            if (!$insertResult) {
                http_response_code(500);
                echo json_encode(["error" => "Error al insertar nuevo personaje"]);
                exit;
            }
        }
    }
    echo json_encode(["success" => true, "message" => "Voto registrado para $name"]);
}


function obtenerTop3($conn)
{
    $query = "SELECT nombre, num_votos FROM votos_personajes ORDER BY num_votos DESC NULLS LAST LIMIT 3";
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
