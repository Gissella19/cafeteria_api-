<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");

require_once 'config/database.php';
require_once 'controllers/ClienteController.php';
require_once 'controllers/ProductoController.php'; // Erick
// require_once 'controllers/PedidoController.php';   // José

$database = new Database();
$db = $database->getConnection();

$recurso = $_GET['resource'] ?? '';
$id = $_GET['id'] ?? null;

switch ($recurso) {
    case 'clientes':
        (new ClienteController($db))->manejarPeticion($_SERVER['REQUEST_METHOD'], $id);
        break;
    case 'productos':
        (new ProductoController($db))->manejarPeticion($_SERVER['REQUEST_METHOD'], $id);
        break;
    default:
        http_response_code(404);
        echo json_encode(["success" => false, "mensaje" => "Usa ?resource=clientes|productos|pedidos"]);
}