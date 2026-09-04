<?php
require_once __DIR__ . '/../services/ProductoService.php';

class ProductoController
{
    private $service;

    public function __construct($db)
    {
        $this->service = new ProductoService($db);
    }

    public function manejarPeticion($metodo, $id = null)
    {
        switch ($metodo) {
            case 'GET':
                if ($id) {
                    $this->responder($this->service->obtenerProducto($id));
                } else {
                    $filtros = [];
                    if (!empty($_GET['categoria'])) {
                        $filtros['categoria'] = $_GET['categoria'];
                    }
                    if (!empty($_GET['nombre'])) {
                        $filtros['nombre'] = $_GET['nombre'];
                    }

                    $productos = $this->service->listarProductos($filtros);
                    http_response_code(200);
                    echo json_encode(["success" => true, "data" => $productos]);
                }
                break;

            case 'POST':
                $input = file_get_contents("php://input");
                $datos = json_decode($input, true);
                if ($input && $datos === null && json_last_error() !== JSON_ERROR_NONE) {
                    $this->responder(["error" => true, "codigo" => 400, "mensaje" => "Formato JSON inválido"]);
                    return;
                }
                $this->responder($this->service->registrarProducto($datos ?? []));
                break;

            case 'PUT':
                if (!$id) {
                    $this->responder(["error" => true, "codigo" => 400, "mensaje" => "Se requiere el ID del producto a actualizar"]);
                    return;
                }
                $input = file_get_contents("php://input");
                $datos = json_decode($input, true);
                if ($input && $datos === null && json_last_error() !== JSON_ERROR_NONE) {
                    $this->responder(["error" => true, "codigo" => 400, "mensaje" => "Formato JSON inválido"]);
                    return;
                }
                $this->responder($this->service->actualizarProducto($id, $datos ?? []));
                break;

            case 'DELETE':
                if (!$id) {
                    $this->responder(["error" => true, "codigo" => 400, "mensaje" => "Se requiere el ID del producto a eliminar"]);
                    return;
                }
                $this->responder($this->service->eliminarProducto($id));
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
