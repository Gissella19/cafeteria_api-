<?php

require_once __DIR__ . '/../services/PedidoService.php';

class PedidoController
{
    private $service;

    public function __construct($db)
    {
        $this->service = new PedidoService($db);
    }

    public function manejarPeticion($metodo, $id = null)
    {
        switch ($metodo) {

            case 'GET':

                if ($id) {
                    $this->responder(
                        $this->service->obtenerPedido($id)
                    );
                } else {
                    $estado = $_GET['estado'] ?? null;

                    $this->responder(
                        $this->service->listarPedidos($estado)
                    );
                }

                break;

            case 'POST':

                $datos = $this->leerJson();

                if ($datos === null) {
                    return;
                }

                $this->responder(
                    $this->service->registrarPedido($datos)
                );

                break;

            case 'PUT':

                if (!$id) {
                    $this->responder([
                        "error" => true,
                        "codigo" => 400,
                        "mensaje" => "Se requiere el ID del pedido"
                    ]);
                    return;
                }

                $datos = $this->leerJson();

                if ($datos === null) {
                    return;
                }

                $this->responder(
                    $this->service->cambiarEstado($id, $datos)
                );

                break;

            case 'DELETE':

                if (!$id) {
                    $this->responder([
                        "error" => true,
                        "codigo" => 400,
                        "mensaje" => "Se requiere el ID del pedido"
                    ]);
                    return;
                }

                /*
                 * Se realiza cancelación lógica en lugar de
                 * eliminar físicamente el pedido.
                 */
                $this->responder(
                    $this->service->cancelarPedido($id)
                );

                break;

            default:

                http_response_code(405);

                echo json_encode([
                    "success" => false,
                    "mensaje" => "Método no permitido"
                ]);
        }
    }

    private function leerJson()
    {
        $input = file_get_contents("php://input");

        $datos = json_decode($input, true);

        if (
            $input !== '' &&
            $datos === null &&
            json_last_error() !== JSON_ERROR_NONE
        ) {
            $this->responder([
                "error" => true,
                "codigo" => 400,
                "mensaje" => "Formato JSON inválido"
            ]);

            return null;
        }

        return $datos ?? [];
    }

    private function responder($resultado)
    {
        http_response_code($resultado['codigo']);

        $payload = [
            "success" => !$resultado['error']
        ];

        foreach ($resultado as $clave => $valor) {

            if ($clave !== 'error' && $clave !== 'codigo') {
                $payload[$clave] = $valor;
            }
        }

        echo json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
        );
    }
}