# Diagrama de Arquitectura Cliente-Servidor (API REST Multicapa)

## 1. Esquema del Flujo
[ Postman / Cliente ] 
       │ (Petición HTTP JSON)
       ▼
[ index.php (Punto de Entrada) ]
       │
       ▼
[ Controllers (Controladores) ] -> Reciben la petición y llaman al servicio.
       │
       ▼
[ Services (Lógica de Negocio) ] -> Validan reglas, stock, precios y cálculos.
       │
       ▼
[ Models (Modelos) ] -> Ejecutan las consultas SQL en la base de datos.
       │
       ▼
[ Base de Datos MySQL (cafeteria_api) ] -> Almacena la información persistente.

---

## 2. Explicación detallada de cada elemento
* **Cliente (Postman):** Es el programa externo que simula a la aplicación cliente enviando solicitudes HTTP (`GET`, `POST`, `PUT`, `DELETE`) con datos en formato JSON.
* **Servidor (Apache / PHP):** Es el entorno local (XAMPP) que recibe las peticiones web en el puerto 80.
* **Punto de Entrada (`index.php`):** Recibe todas las peticiones globales y las redirige al controlador correspondiente.
* **Capa de Controladores (`controllers/`):** Funcionan como "intermediarios"; recogen los datos que envía el usuario y piden al servicio que los procese.
* **Capa de Servicios (`services/`):** Aquí vive la inteligencia y las reglas del negocio de la cafetería (validar que el stock no sea negativo, calcular subtotales, verificar si un cliente existe, etc.).
* **Capa de Modelos (`models/`):** Se encarga exclusivamente de hablar con la base de datos MySQL mediante consultas seguras (PDO).
* **Base de Datos (`MySQL`):** Almacena de forma permanente las 4 tablas principales del sistema (`clientes`, `productos`, `pedidos`, `detalle_pedido`).