-- Creación de la Base de Datos
CREATE DATABASE IF NOT EXISTS cafeteria_api;
USE cafeteria_api;

-- Tabla de Clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(10) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL
);

-- Tabla de Productos
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    categoria VARCHAR(50) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    activo BOOLEAN NOT NULL DEFAULT TRUE
);

-- Tabla de Pedidos
CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',
    total DECIMAL(10,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id)
);

-- Tabla de Detalle del Pedido
CREATE TABLE IF NOT EXISTS detalle_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
    FOREIGN KEY (producto_id) REFERENCES productos(id)
);

-- Inserción de 5 Clientes de prueba
INSERT INTO clientes (cedula, nombre, correo) VALUES 
('1801111111', 'Ana Pérez', 'ana@correo.com'),
('1802222222', 'Carlos Gómez', 'carlos@correo.com'),
('1803333333', 'María Torres', 'maria@correo.com'),
('1804444444', 'José Ruiz', 'jose@correo.com'),
('1805555555', 'Lucía Minda', 'lucia@correo.com');

-- Inserción de 10 Productos de prueba
INSERT INTO productos (nombre, categoria, precio, stock, activo) VALUES 
('Capuchino', 'Bebidas', 2.50, 20, TRUE),
('Café Americano', 'Bebidas', 1.75, 30, TRUE),
('Jugo de Naranja', 'Bebidas', 2.00, 15, TRUE),
('Empanada de Queso', 'Aperitivos', 1.25, 25, TRUE),
('Bolón de Verde', 'Aperitivos', 2.25, 10, TRUE),
('Sanduche de Pollo', 'Comida', 3.50, 12, TRUE),
('Salchipapa', 'Comida', 3.00, 8, TRUE),
('Humedas de Choclo', 'Aperitivos', 1.00, 40, TRUE),
('Te Frio', 'Bebidas', 1.50, 25, TRUE),
('Torta de Chocolate', 'Postres', 2.75, 10, TRUE);