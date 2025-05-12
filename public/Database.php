<?php

function connectToDB(){
    $host = getenv("DB_HOST");
    $port = getenv("DB_PORT");
    $db   = getenv("DB_DATABASE");
    $user = getenv("DB_USERNAME");
    $pass = getenv("DB_PASSWORD");

    $conn = pg_connect("host=$host port=$port dbname=$db user=$user password=$pass");

    if (!$conn) {
        http_response_code(500);
        echo json_encode(["error" => "Error al conectar a la base de datos"]);
        exit;
    }

    return $conn;
}