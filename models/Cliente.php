<?php
class Cliente
{
    private $conn;
    private $table = "clientes";

    public $id;
    public $cedula;
    public $nombre;
    public $correo;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listar()
    {
        $stmt = $this->conn->prepare("SELECT id, cedula, nombre, correo FROM {$this->table} ORDER BY id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $stmt = $this->conn->prepare("SELECT id, cedula, nombre, correo FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorCedula($cedula)
    {
        $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE cedula = :cedula LIMIT 1");
        $stmt->bindParam(":cedula", $cedula);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear()
    {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (cedula, nombre, correo) VALUES (:cedula, :nombre, :correo)");
        $stmt->bindParam(":cedula", $this->cedula);
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":correo", $this->correo);
        return $stmt->execute();
    }

    public function actualizar()
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET nombre = :nombre, correo = :correo WHERE id = :id");
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":correo", $this->correo);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminar($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}