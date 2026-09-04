<?php

class Pedido
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listar($estado = null)
    {
        $sql = "
            SELECT
                p.id,
                p.cliente_id,
                c.nombre AS cliente,
                p.fecha,
                p.estado,
                p.total
            FROM pedidos p
            INNER JOIN clientes c ON c.id = p.cliente_id
        ";

        if ($estado !== null) {
            $sql .= " WHERE p.estado = :estado";
        }

        $sql .= " ORDER BY p.id DESC";

        $stmt = $this->conn->prepare($sql);

        if ($estado !== null) {
            $stmt->bindParam(':estado', $estado);
        }

        $stmt->execute();

        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pedidos as &$pedido) {
            $pedido['detalle'] = $this->listarDetalle($pedido['id']);
        }

        return $pedidos;
    }

    public function buscarPorId($id)
    {
        $sql = "
            SELECT
                p.id,
                p.cliente_id,
                c.nombre AS cliente,
                p.fecha,
                p.estado,
                p.total
            FROM pedidos p
            INNER JOIN clientes c ON c.id = p.cliente_id
            WHERE p.id = :id
            LIMIT 1
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            return false;
        }

        $pedido['detalle'] = $this->listarDetalle($id);

        return $pedido;
    }

    public function listarDetalle($pedidoId)
    {
        $sql = "
            SELECT
                d.id,
                d.producto_id,
                p.nombre AS producto,
                d.cantidad,
                d.precio_unitario,
                d.subtotal
            FROM detalle_pedido d
            INNER JOIN productos p ON p.id = d.producto_id
            WHERE d.pedido_id = :pedido_id
            ORDER BY d.id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':pedido_id', $pedidoId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crear($clienteId, $total)
    {
        $sql = "
            INSERT INTO pedidos (cliente_id, estado, total)
            VALUES (:cliente_id, 'PENDIENTE', :total)
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':cliente_id', $clienteId, PDO::PARAM_INT);
        $stmt->bindParam(':total', $total);

        if (!$stmt->execute()) {
            return false;
        }

        return (int) $this->conn->lastInsertId();
    }

    public function crearDetalle(
        $pedidoId,
        $productoId,
        $cantidad,
        $precioUnitario,
        $subtotal
    ) {
        $sql = "
            INSERT INTO detalle_pedido
            (pedido_id, producto_id, cantidad, precio_unitario, subtotal)
            VALUES
            (:pedido_id, :producto_id, :cantidad, :precio_unitario, :subtotal)
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':pedido_id', $pedidoId, PDO::PARAM_INT);
        $stmt->bindParam(':producto_id', $productoId, PDO::PARAM_INT);
        $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(':precio_unitario', $precioUnitario);
        $stmt->bindParam(':subtotal', $subtotal);

        return $stmt->execute();
    }

    public function actualizarEstado($id, $estado)
    {
        $sql = "
            UPDATE pedidos
            SET estado = :estado
            WHERE id = :id
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }
}