<?php
// Permitir solicitudes de cualquier origen (CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Incluir la conexión a la base de datos
require_once 'config/database.php';

// Instanciar la base de datos
$database = new Database();
$db = $database->getConnection();

if ($db) {
    // Si la conexión es exitosa
    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "mensaje" => "¡Conexión exitosa a la base de datos cafeteria_api!"
    ));
} else {
    // Si falla la conexión
    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "mensaje" => "Error de conexión a la base de datos."
    ));
}
?>