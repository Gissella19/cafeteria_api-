<?php
require_once __DIR__ . '/../services/ClienteService.php';

class ClienteController
{
    private $service;

    public function __construct($db)
    {
        $this->service = new ClienteService($db);
    }

    public function manejarPeticion($metodo, $id = null)
    {
        switch ($metodo) {
            case 'GET':
                if ($id) {
                    $this->responder($this->service->obtenerCliente($id));
                } else {
                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $this->service->listarClientes()]);
                }
                break;
            case 'POST':
                $datos = json_decode(file_get_contents("php://input"), true);
                $this->responder($this->service->registrarCliente($datos));
                break;
            case 'PUT':
                $datos = json_decode(file_get_contents("php://input"), true);
                $this->responder($this->service->actualizarCliente($id, $datos));
                break;
            case 'DELETE':
                $this->responder($this->service->eliminarCliente($id));
                break;
            default:
                http_response_code(405);
                echo json_encode(["success" => false, "mensaje" => "Método no permitido"]);
        }
    }

    private function responder($resultado)
    {
        http_response_code($resultado['codigo']);
        $payload = ["success" => !$resultado['error']];
        if (isset($resultado['mensaje'])) $payload['mensaje'] = $resultado['mensaje'];
        if (isset($resultado['data'])) $payload['data'] = $resultado['data'];
        echo json_encode($payload);
    }
}