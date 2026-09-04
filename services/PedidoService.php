<?php

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/ClienteService.php';
require_once __DIR__ . '/ProductoService.php';

class PedidoService
{
    private $db;
    private $pedidoModel;
    private $clienteService;
    private $productoService;

    private $estadosValidos = [
        'PENDIENTE',
        'PREPARANDO',
        'ENTREGADO',
        'CANCELADO'
    ];

    public function __construct($db)
    {
        $this->db = $db;
        $this->pedidoModel = new Pedido($db);
        $this->clienteService = new ClienteService($db);
        $this->productoService = new ProductoService($db);
    }

    public function listarPedidos($estado = null)
    {
        if ($estado !== null) {
            $estado = strtoupper(trim($estado));

            if (!in_array($estado, $this->estadosValidos, true)) {
                return [
                    "error" => true,
                    "codigo" => 400,
                    "mensaje" => "Estado de pedido no válido"
                ];
            }
        }

        return [
            "error" => false,
            "codigo" => 200,
            "data" => $this->pedidoModel->listar($estado)
        ];
    }

    public function obtenerPedido($id)
    {
        $pedido = $this->pedidoModel->buscarPorId($id);

        if (!$pedido) {
            return [
                "error" => true,
                "codigo" => 404,
                "mensaje" => "Pedido no encontrado"
            ];
        }

        return [
            "error" => false,
            "codigo" => 200,
            "data" => $pedido
        ];
    }

    public function registrarPedido($datos)
    {
        if (
            empty($datos['cliente_id']) ||
            !isset($datos['productos']) ||
            !is_array($datos['productos']) ||
            count($datos['productos']) === 0
        ) {
            return [
                "error" => true,
                "codigo" => 400,
                "mensaje" => "cliente_id y productos son obligatorios"
            ];
        }

        $clienteId = filter_var(
            $datos['cliente_id'],
            FILTER_VALIDATE_INT
        );

        if ($clienteId === false || $clienteId <= 0) {
            return [
                "error" => true,
                "codigo" => 400,
                "mensaje" => "cliente_id no válido"
            ];
        }

        // Regla 5: cliente existente
        if (!$this->clienteService->clienteExiste($clienteId)) {
            return [
                "error" => true,
                "codigo" => 404,
                "mensaje" => "Cliente no encontrado"
            ];
        }

        /*
         * Agrupar productos repetidos.
         * Evita que el mismo producto aparezca dos veces y
         * permita superar el stock disponible.
         */
        $productosSolicitados = [];

        foreach ($datos['productos'] as $item) {

            if (
                !isset($item['producto_id']) ||
                !isset($item['cantidad'])
            ) {
                return [
                    "error" => true,
                    "codigo" => 400,
                    "mensaje" => "producto_id y cantidad son obligatorios"
                ];
            }

            $productoId = filter_var(
                $item['producto_id'],
                FILTER_VALIDATE_INT
            );

            $cantidad = filter_var(
                $item['cantidad'],
                FILTER_VALIDATE_INT
            );

            if ($productoId === false || $productoId <= 0) {
                return [
                    "error" => true,
                    "codigo" => 400,
                    "mensaje" => "producto_id no válido"
                ];
            }

            // Regla 6
            if ($cantidad === false || $cantidad <= 0) {
                return [
                    "error" => true,
                    "codigo" => 400,
                    "mensaje" => "La cantidad debe ser mayor a 0"
                ];
            }

            if (!isset($productosSolicitados[$productoId])) {
                $productosSolicitados[$productoId] = 0;
            }

            $productosSolicitados[$productoId] += $cantidad;
        }

        $detalles = [];
        $total = 0;

        foreach ($productosSolicitados as $productoId => $cantidad) {

            $resultadoProducto =
                $this->productoService->obtenerProducto($productoId);

            // Producto inexistente
            if ($resultadoProducto['error']) {
                return [
                    "error" => true,
                    "codigo" => 404,
                    "mensaje" => "Producto con ID $productoId no encontrado"
                ];
            }

            $producto = $resultadoProducto['data'];

            // Regla 3: producto activo
            if (!(bool) $producto['activo']) {
                return [
                    "error" => true,
                    "codigo" => 409,
                    "mensaje" => "El producto {$producto['nombre']} no está disponible"
                ];
            }

            // Regla 4: stock suficiente
            if ($cantidad > (int) $producto['stock']) {
                return [
                    "error" => true,
                    "codigo" => 409,
                    "mensaje" =>
                        "Stock insuficiente para {$producto['nombre']}"
                ];
            }

            $precio = (float) $producto['precio'];

            // Regla 7
            $subtotal = round($precio * $cantidad, 2);

            $total += $subtotal;

            $detalles[] = [
                "producto_id" => (int) $productoId,
                "cantidad" => $cantidad,
                "precio_unitario" => $precio,
                "subtotal" => $subtotal,
                "nuevo_stock" => (int) $producto['stock'] - $cantidad
            ];
        }

        $total = round($total, 2);

        try {

            $this->db->beginTransaction();

            $pedidoId = $this->pedidoModel->crear(
                $clienteId,
                $total
            );

            if (!$pedidoId) {
                throw new Exception("No se pudo crear el pedido");
            }

            foreach ($detalles as $detalle) {

                if (!$this->pedidoModel->crearDetalle(
                    $pedidoId,
                    $detalle['producto_id'],
                    $detalle['cantidad'],
                    $detalle['precio_unitario'],
                    $detalle['subtotal']
                )) {
                    throw new Exception(
                        "No se pudo guardar el detalle del pedido"
                    );
                }

                if (!$this->productoService->actualizarStockDesdePedido(
                    $detalle['producto_id'],
                    $detalle['nuevo_stock']
                )) {
                    throw new Exception(
                        "No se pudo actualizar el stock"
                    );
                }
            }

            $this->db->commit();

            return [
                "error" => false,
                "codigo" => 201,
                "mensaje" => "Pedido registrado correctamente",
                "pedido_id" => $pedidoId,
                "total" => $total
            ];

        } catch (Throwable $e) {

            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return [
                "error" => true,
                "codigo" => 500,
                "mensaje" => "Error interno al registrar el pedido"
            ];
        }
    }

    public function cambiarEstado($id, $datos)
    {
        $pedido = $this->pedidoModel->buscarPorId($id);

        if (!$pedido) {
            return [
                "error" => true,
                "codigo" => 404,
                "mensaje" => "Pedido no encontrado"
            ];
        }

        if (empty($datos['estado'])) {
            return [
                "error" => true,
                "codigo" => 400,
                "mensaje" => "El estado es obligatorio"
            ];
        }

        $estado = strtoupper(trim($datos['estado']));

        // Regla 8
        if (!in_array($estado, $this->estadosValidos, true)) {
            return [
                "error" => true,
                "codigo" => 400,
                "mensaje" => "Estado no válido"
            ];
        }

        if (!$this->pedidoModel->actualizarEstado($id, $estado)) {
            return [
                "error" => true,
                "codigo" => 500,
                "mensaje" => "No se pudo actualizar el pedido"
            ];
        }

        return [
            "error" => false,
            "codigo" => 200,
            "mensaje" => "Estado del pedido actualizado correctamente",
            "estado" => $estado
        ];
    }

    public function cancelarPedido($id)
    {
        return $this->cambiarEstado(
            $id,
            ["estado" => "CANCELADO"]
        );
    }
}