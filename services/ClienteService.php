<?php
require_once __DIR__ . '/../models/Cliente.php';

class ClienteService
{
    private $clienteModel;

    public function __construct($db)
    {
        $this->clienteModel = new Cliente($db);
    }

    public function listarClientes()
    {
        return $this->clienteModel->listar();
    }

    public function obtenerCliente($id)
    {
        $cliente = $this->clienteModel->buscarPorId($id);
        if (!$cliente) return ["error" => true, "codigo" => 404, "mensaje" => "Cliente no encontrado"];
        return ["error" => false, "codigo" => 200, "data" => $cliente];
    }

    // Erick y José la usarán para la Regla 5 (cliente existente) al crear un pedido
    public function clienteExiste($id)
    {
        return $this->clienteModel->buscarPorId($id) !== false;
    }

    public function registrarCliente($datos)
    {
        if (empty($datos['cedula']) || empty($datos['nombre']) || empty($datos['correo'])) {
            return ["error" => true, "codigo" => 400, "mensaje" => "Cédula, nombre y correo son obligatorios"];
        }
        if (!filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            return ["error" => true, "codigo" => 400, "mensaje" => "El correo no tiene un formato válido"];
        }
        if ($this->clienteModel->buscarPorCedula($datos['cedula'])) {
            return ["error" => true, "codigo" => 409, "mensaje" => "Ya existe un cliente con esa cédula"];
        }

        $this->clienteModel->cedula = $datos['cedula'];
        $this->clienteModel->nombre = $datos['nombre'];
        $this->clienteModel->correo = $datos['correo'];

        return $this->clienteModel->crear()
            ? ["error" => false, "codigo" => 201, "mensaje" => "Cliente registrado correctamente"]
            : ["error" => true, "codigo" => 500, "mensaje" => "No se pudo registrar el cliente"];
    }

    public function actualizarCliente($id, $datos)
    {
        $existente = $this->clienteModel->buscarPorId($id);
        if (!$existente) return ["error" => true, "codigo" => 404, "mensaje" => "Cliente no encontrado"];

        if (isset($datos['correo']) && !filter_var($datos['correo'], FILTER_VALIDATE_EMAIL)) {
            return ["error" => true, "codigo" => 400, "mensaje" => "El correo no tiene un formato válido"];
        }

        $this->clienteModel->id = $id;
        $this->clienteModel->nombre = $datos['nombre'] ?? $existente['nombre'];
        $this->clienteModel->correo = $datos['correo'] ?? $existente['correo'];

        return $this->clienteModel->actualizar()
            ? ["error" => false, "codigo" => 200, "mensaje" => "Cliente actualizado correctamente"]
            : ["error" => true, "codigo" => 500, "mensaje" => "No se pudo actualizar el cliente"];
    }

    public function eliminarCliente($id)
    {
        if (!$this->clienteModel->buscarPorId($id)) {
            return ["error" => true, "codigo" => 404, "mensaje" => "Cliente no encontrado"];
        }
        return $this->clienteModel->eliminar($id)
            ? ["error" => false, "codigo" => 200, "mensaje" => "Cliente eliminado correctamente"]
            : ["error" => true, "codigo" => 500, "mensaje" => "No se pudo eliminar el cliente"];
    }
}