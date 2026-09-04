<?php
class Producto
{
    private $conn;
    private $table = "productos";

    public $id;
    public $nombre;
    public $categoria;
    public $precio;
    public $stock;
    public $activo;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function listar()
    {
        $stmt = $this->conn->prepare("SELECT id, nombre, categoria, precio, stock, activo FROM {$this->table} ORDER BY id");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id)
    {
        $stmt = $this->conn->prepare("SELECT id, nombre, categoria, precio, stock, activo FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarPorNombre($nombre)
    {
        $stmt = $this->conn->prepare("SELECT id, nombre, categoria, precio, stock, activo FROM {$this->table} WHERE LOWER(nombre) = LOWER(:nombre) LIMIT 1");
        $stmt->bindParam(":nombre", $nombre);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear()
    {
        $stmt = $this->conn->prepare("INSERT INTO {$this->table} (nombre, categoria, precio, stock, activo) VALUES (:nombre, :categoria, :precio, :stock, :activo)");
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":stock", $this->stock, PDO::PARAM_INT);
        $stmt->bindParam(":activo", $this->activo, PDO::PARAM_BOOL);
        return $stmt->execute();
    }

    public function actualizar()
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET nombre = :nombre, categoria = :categoria, precio = :precio, stock = :stock, activo = :activo WHERE id = :id");
        $stmt->bindParam(":nombre", $this->nombre);
        $stmt->bindParam(":categoria", $this->categoria);
        $stmt->bindParam(":precio", $this->precio);
        $stmt->bindParam(":stock", $this->stock, PDO::PARAM_INT);
        $stmt->bindParam(":activo", $this->activo, PDO::PARAM_BOOL);
        $stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function actualizarStock($id, $nuevoStock)
    {
        $stmt = $this->conn->prepare("UPDATE {$this->table} SET stock = :stock WHERE id = :id");
        $stmt->bindParam(":stock", $nuevoStock, PDO::PARAM_INT);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminar($id)
    {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
