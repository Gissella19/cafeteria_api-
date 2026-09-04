<?php
require_once __DIR__ . '/../models/Producto.php';

class ProductoService
{
    private $productoModel;

    public function __construct($db)
    {
        $this->productoModel = new Producto($db);
    }

    /**
     * Listar productos con soporte de filtrado en la capa de la API (Actividad 13).
     * El filtrado se realiza directamente en memoria mediante PHP y no en la consulta SQL.
     */
    public function listarProductos($filtros = [])
    {
        $productos = $this->productoModel->listar();

        // Filtro por categoría (resuelto en la API)
        if (!empty($filtros['categoria'])) {
            $categoriaFiltro = mb_strtolower(trim($filtros['categoria']));
            $productos = array_filter($productos, function ($p) use ($categoriaFiltro) {
                return mb_strtolower(trim($p['categoria'])) === $categoriaFiltro;
            });
        }

        // Filtro por nombre (resuelto en la API)
        if (!empty($filtros['nombre'])) {
            $nombreFiltro = mb_strtolower(trim($filtros['nombre']));
            $productos = array_filter($productos, function ($p) use ($nombreFiltro) {
                return str_contains(mb_strtolower($p['nombre']), $nombreFiltro);
            });
        }

        return array_values($productos);
    }

    /**
     * Consultar un producto por su ID
     */
    public function obtenerProducto($id)
    {
        $producto = $this->productoModel->buscarPorId($id);
        if (!$producto) {
            return ["error" => true, "codigo" => 404, "mensaje" => "Producto no encontrado"];
        }
        return ["error" => false, "codigo" => 200, "data" => $producto];
    }

    /**
     * Métodos para integración con el módulo Pedidos (José)
     */
    public function productoExiste($id)
    {
        return $this->productoModel->buscarPorId($id) !== false;
    }

    // Regla 3: Producto disponible (no se puede agregar a pedidos si activo = false)
    public function productoDisponible($id)
    {
        $prod = $this->productoModel->buscarPorId($id);
        if (!$prod) return false;
        return (bool)$prod['activo'];
    }

    public function registrarProducto($datos)
    {
        // Validar campos obligatorios
        if (!isset($datos['nombre']) || trim($datos['nombre']) === '' ||
            !isset($datos['categoria']) || trim($datos['categoria']) === '' ||
            !isset($datos['precio']) || !isset($datos['stock'])) {
            return ["error" => true, "codigo" => 400, "mensaje" => "Nombre, categoría, precio y stock son obligatorios"];
        }

        // Regla 1: Precio válido (precio > 0)
        if (!is_numeric($datos['precio']) || floatval($datos['precio']) <= 0) {
            return ["error" => true, "codigo" => 400, "mensaje" => "El precio del producto debe ser mayor a 0"];
        }

        // Regla 2: Stock válido (stock >= 0)
        if (!is_numeric($datos['stock']) || intval($datos['stock']) < 0) {
            return ["error" => true, "codigo" => 400, "mensaje" => "El stock no puede ser negativo"];
        }

        // Regla 3: Manejo del campo activo (por defecto true si no se especifica)
        $activo = true;
        if (isset($datos['activo'])) {
            $activo = filter_var($datos['activo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($activo === null) {
                $activo = (bool)$datos['activo'];
            }
        }

        $this->productoModel->nombre = trim($datos['nombre']);
        $this->productoModel->categoria = trim($datos['categoria']);
        $this->productoModel->precio = floatval($datos['precio']);
        $this->productoModel->stock = intval($datos['stock']);
        $this->productoModel->activo = $activo ? 1 : 0;

        return $this->productoModel->crear()
            ? ["error" => false, "codigo" => 201, "mensaje" => "Producto registrado correctamente"]
            : ["error" => true, "codigo" => 500, "mensaje" => "No se pudo registrar el producto"];
    }

    public function actualizarProducto($id, $datos)
    {
        $existente = $this->productoModel->buscarPorId($id);
        if (!$existente) {
            return ["error" => true, "codigo" => 404, "mensaje" => "Producto no encontrado"];
        }

        // Regla 1: Validar precio si se proporciona
        if (isset($datos['precio'])) {
            if (!is_numeric($datos['precio']) || floatval($datos['precio']) <= 0) {
                return ["error" => true, "codigo" => 400, "mensaje" => "El precio del producto debe ser mayor a 0"];
            }
        }

        // Regla 2: Validar stock si se proporciona
        if (isset($datos['stock'])) {
            if (!is_numeric($datos['stock']) || intval($datos['stock']) < 0) {
                return ["error" => true, "codigo" => 400, "mensaje" => "El stock no puede ser negativo"];
            }
        }

        $this->productoModel->id = $id;
        $this->productoModel->nombre = isset($datos['nombre']) ? trim($datos['nombre']) : $existente['nombre'];
        $this->productoModel->categoria = isset($datos['categoria']) ? trim($datos['categoria']) : $existente['categoria'];
        $this->productoModel->precio = isset($datos['precio']) ? floatval($datos['precio']) : $existente['precio'];
        $this->productoModel->stock = isset($datos['stock']) ? intval($datos['stock']) : $existente['stock'];
        
        if (isset($datos['activo'])) {
            $activo = filter_var($datos['activo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $this->productoModel->activo = ($activo !== null ? $activo : (bool)$datos['activo']) ? 1 : 0;
        } else {
            $this->productoModel->activo = $existente['activo'];
        }

        return $this->productoModel->actualizar()
            ? ["error" => false, "codigo" => 200, "mensaje" => "Producto actualizado correctamente"]
            : ["error" => true, "codigo" => 500, "mensaje" => "No se pudo actualizar el producto"];
    }

    public function actualizarStockDesdePedido($id, $nuevoStock)
    {
        return $this->productoModel->actualizarStock($id, $nuevoStock);
    }

    public function eliminarProducto($id)
    {
        if (!$this->productoModel->buscarPorId($id)) {
            return ["error" => true, "codigo" => 404, "mensaje" => "Producto no encontrado"];
        }

        try {
            return $this->productoModel->eliminar($id)
                ? ["error" => false, "codigo" => 200, "mensaje" => "Producto eliminado correctamente"]
                : ["error" => true, "codigo" => 500, "mensaje" => "No se pudo eliminar el producto"];
        } catch (PDOException $e) {
            // Manejar restricción de integridad referencial si ya tiene pedidos asociados
            return ["error" => true, "codigo" => 409, "mensaje" => "No se puede eliminar el producto porque está referenciado en un pedido existente"];
        }
    }
}
