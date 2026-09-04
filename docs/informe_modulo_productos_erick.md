# UNIVERSIDAD TÉCNICA DE AMBATO
## FACULTAD DE INGENIERÍA EN SISTEMAS ELECTRÓNICA E INDUSTRIAL
### CARRERA DE SOFTWARE — CICLO ACADÉMICO: JULIO 2026 – DICIEMBRE 2026
### INFORME DE GUÍA PRÁCTICA — GUÍA APE: API REST CAFETERÍA UNIVERSITARIA

---

**Integrante 3:** Erick Alexander López Yantalema  
**Rol asignado:** Módulo Productos (Actividades 2, 6, 9 [Reglas 1, 2, 3], 13 y Pruebas Postman del módulo)  
**Tema:** Desarrollo de una API REST multicapa para la gestión de pedidos de una cafetería universitaria utilizando PHP, MySQL, JSON y Postman.

---

## 1. ACTIVIDAD 2: DISEÑO DE LOS RECURSOS REST (PRODUCTOS)

Se identificó el recurso principal `productos` y se diseñaron los endpoints para dar cumplimiento a las operaciones CRUD y a los requerimientos de consulta, adaptados a la arquitectura sin reescritura de URLs mediante el parámetro `?resource=productos`:

| Método HTTP | Endpoint | Operación | Código HTTP Esperado |
|---|---|---|---|
| `GET` | `/cafeteria_api/?resource=productos` | Listar todos los productos registrados | `200 OK` |
| `GET` | `/cafeteria_api/?resource=productos&id=1` | Consultar producto específico por ID | `200 OK` / `404 Not Found` |
| `POST` | `/cafeteria_api/?resource=productos` | Registrar nuevo producto | `201 Created` / `400 Bad Request` |
| `PUT` | `/cafeteria_api/?resource=productos&id=1` | Actualizar datos de un producto | `200 OK` / `404 Not Found` / `400 Bad Request` |
| `DELETE` | `/cafeteria_api/?resource=productos&id=1` | Eliminar producto | `200 OK` / `404 Not Found` / `409 Conflict` |
| `GET` | `/cafeteria_api/?resource=productos&categoria=Bebidas` | Consulta adicional: filtrar por categoría en API | `200 OK` |
| `GET` | `/cafeteria_api/?resource=productos&nombre=cafe` | Consulta adicional: buscar por nombre en API | `200 OK` |

---

## 2. ACTIVIDAD 4: IMPLEMENTACIÓN MULTICAPA DEL MÓDULO PRODUCTOS

El módulo fue programado bajo estricta separación de responsabilidades:

1. **Modelo (`models/Producto.php`):**
   - Encargado exclusivo de la persistencia y ejecución de sentencias SQL seguras contra MySQL mediante PDO (Prepared Statements).
   - Métodos implementados: `listar()`, `buscarPorId($id)`, `buscarPorNombre($nombre)`, `crear()`, `actualizar()`, `actualizarStock($id, $nuevoStock)`, `eliminar($id)`.

2. **Servicio (`services/ProductoService.php`):**
   - Contiene la lógica del negocio, validaciones de integridad y reglas específicas.
   - Aplica las reglas 1, 2 y 3.
   - Resuelve el filtrado de consultas adicionales (categoría y nombre) en memoria mediante PHP.
   - Proporciona métodos de consulta para el Módulo de Pedidos: `productoExiste($id)`, `productoDisponible($id)`.

3. **Controlador (`controllers/ProductoController.php`):**
   - Recibe la petición HTTP desde `index.php`, analiza el verbo HTTP (`GET`, `POST`, `PUT`, `DELETE`), extrae los parámetros de la URL (`$_GET`) o el cuerpo JSON (`php://input`), invoca al servicio y formatea la respuesta en JSON con los códigos de estado HTTP correspondientes.

4. **Punto de entrada (`index.php`):**
   - Enrutador que delega la petición a `ProductoController` cuando `?resource=productos`.

---

## 3. ACTIVIDAD 6: IMPLEMENTACIÓN DEL CRUD DE PRODUCTOS

### 3.1 Crear Producto (`POST`)
- **Endpoint:** `POST /cafeteria_api/?resource=productos`
- **Petición (JSON Body):**
```json
{
  "nombre": "Mocaccino",
  "categoria": "Bebidas",
  "precio": 2.80,
  "stock": 18,
  "activo": true
}
```
- **Respuesta:**
```json
{
  "success": true,
  "mensaje": "Producto registrado correctamente"
}
```
- **Código HTTP:** `201 Created`

### 3.2 Consultar Productos (`GET`)
- **Endpoint:** `GET /cafeteria_api/?resource=productos`
- **Respuesta:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "nombre": "Capuchino",
      "categoria": "Bebidas",
      "precio": "2.50",
      "stock": 20,
      "activo": 1
    },
    {
      "id": 2,
      "nombre": "Café Americano",
      "categoria": "Bebidas",
      "precio": "1.75",
      "stock": 30,
      "activo": 1
    }
  ]
}
```
- **Código HTTP:** `200 OK`

### 3.3 Consultar Producto por ID (`GET`)
- **Endpoint:** `GET /cafeteria_api/?resource=productos&id=1`
- **Respuesta:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "nombre": "Capuchino",
    "categoria": "Bebidas",
    "precio": "2.50",
    "stock": 20,
    "activo": 1
  }
}
```
- **Código HTTP:** `200 OK`

### 3.4 Actualizar Producto (`PUT`)
- **Endpoint:** `PUT /cafeteria_api/?resource=productos&id=1`
- **Petición (JSON Body):**
```json
{
  "precio": 2.75,
  "stock": 25
}
```
- **Respuesta:**
```json
{
  "success": true,
  "mensaje": "Producto actualizado correctamente"
}
```
- **Código HTTP:** `200 OK`

### 3.5 Eliminar Producto (`DELETE`)
- **Endpoint:** `DELETE /cafeteria_api/?resource=productos&id=11`
- **Respuesta:**
```json
{
  "success": true,
  "mensaje": "Producto eliminado correctamente"
}
```
- **Código HTTP:** `200 OK`

---

## 4. ACTIVIDAD 9: APLICACIÓN DE REGLAS DE NEGOCIO

Las reglas fueron programadas dentro de la capa `services/ProductoService.php`:

### Regla 1: Precio Válido
* **Condición:** El precio debe ser un número estrictamente mayor a 0 (`precio > 0`).
* **Comportamiento ante incumplimiento:** Se rechaza la petición con código `400 Bad Request` y mensaje `"El precio del producto debe ser mayor a 0"`.
* **Prueba:** Se envía un JSON con `precio: -1.50` o `precio: 0`.

### Regla 2: Stock Válido
* **Condición:** El stock inicial o actualizado no puede ser negativo (`stock >= 0`).
* **Comportamiento ante incumplimiento:** Se rechaza la solicitud retornando `400 Bad Request` y mensaje `"El stock no puede ser negativo"`.
* **Prueba:** Se envía un JSON con `stock: -5`.

### Regla 3: Producto Disponible
* **Condición:** No se puede incluir en un pedido un producto con `activo = false` (o `0`).
* **Implementación:**
  - Al registrar o editar el producto se almacena su estado booleano `activo`.
  - Se implementó en `ProductoService` el método:
    ```php
    public function productoDisponible($id) {
        $prod = $this->productoModel->buscarPorId($id);
        if (!$prod) return false;
        return (bool)$prod['activo'];
    }
    ```
  - Este método es consumido directamente por el servicio de pedidos (`PedidoService`) para rechazar compras de productos inactivos o dados de baja.

---

## 5. ACTIVIDAD 13: CONSULTA ADICIONAL DESDE LA API

* **Requerimiento:** Realizar al menos una consulta adicional (por ejemplo, filtrar por categoría o por nombre) procesada en la API y no directamente en MySQL.
* **Justificación técnica:** Para evidenciar el desacoplamiento de capas y el procesamiento en la capa de servicio, `ProductoService::listarProductos($filtros)` solicita todos los productos a la base de datos y aplica funciones de orden superior en PHP (`array_filter`) comparando de forma case-insensitive las cadenas recibidas.
* **Endpoints:**
  - `GET /cafeteria_api/?resource=productos&categoria=Bebidas`
  - `GET /cafeteria_api/?resource=productos&nombre=cafe`
* **Ejemplo de Resultado (`GET ?resource=productos&categoria=Postres`):**
```json
{
  "success": true,
  "data": [
    {
      "id": 10,
      "nombre": "Torta de Chocolate",
      "categoria": "Postres",
      "precio": "2.75",
      "stock": 10,
      "activo": 1
    }
  ]
}
```

---

## 6. ACTIVIDAD 11: EVIDENCIAS DE PRUEBAS EN POSTMAN (MÓDULO PRODUCTOS)

A continuación se presentan las tablas de evidencias con el formato exigido por la guía práctica para ser anexadas al informe final:

### Prueba P1. Listar Productos
| Campo | Detalle |
|---|---|
| **Método HTTP** | `GET` |
| **Endpoint** | `http://localhost/cafeteria_api/?resource=productos` |
| **Datos enviados** | Ninguno (no requiere body) |
| **Código HTTP esperado** | `200` |
| **Código HTTP obtenido** | `200 OK` |
| **Resultado esperado** | Devolver el listado completo de productos registrados |
| **Resultado obtenido** | Lista de 10 productos iniciales en formato JSON con status 200 |
| **Evidencia** | *(Pegar captura de Postman aquí)* |

---

### Prueba P2. Consultar un Producto Existente
| Campo | Detalle |
|---|---|
| **Método HTTP** | `GET` |
| **Endpoint** | `http://localhost/cafeteria_api/?resource=productos&id=1` |
| **Datos enviados** | Ninguno |
| **Código HTTP esperado** | `200` |
| **Código HTTP obtenido** | `200 OK` |
| **JSON recibido** | `{"success": true, "data": {"id": 1, "nombre": "Capuchino", "categoria": "Bebidas", "precio": "2.50", "stock": 20, "activo": 1}}` |
| **Resultado esperado** | Devolver los datos del producto con id=1 |
| **Resultado obtenido** | Objeto JSON con los atributos completos del producto 1 |
| **Evidencia** | *(Pegar captura de Postman aquí)* |

---

### Prueba P3. Consultar un Producto Inexistente
| Campo | Detalle |
|---|---|
| **Método HTTP** | `GET` |
| **Endpoint** | `http://localhost/cafeteria_api/?resource=productos&id=999` |
| **Datos enviados** | Ninguno |
| **Código HTTP esperado** | `404` |
| **Código HTTP obtenido** | `404 Not Found` |
| **JSON recibido** | `{"success": false, "mensaje": "Producto no encontrado"}` |
| **Resultado esperado** | Responder con error 404 informando que no existe |
| **Resultado obtenido** | Mensaje de error 404 devuelto correctamente |
| **Evidencia** | *(Pegar captura de Postman aquí)* |

---

### Prueba P4. Registrar Producto Válido
| Campo | Detalle |
|---|---|
| **Método HTTP** | `POST` |
| **Endpoint** | `http://localhost/cafeteria_api/?resource=productos` |
| **Headers** | `Content-Type: application/json` |
| **Datos enviados** | `{"nombre": "Mocaccino", "categoria": "Bebidas", "precio": 2.80, "stock": 15, "activo": true}` |
| **Código HTTP esperado** | `201` |
| **Código HTTP obtenido** | `201 Created` |
| **JSON recibido** | `{"success": true, "mensaje": "Producto registrado correctamente"}` |
| **Resultado esperado** | Crear el producto y devolver 201 Created |
| **Resultado obtenido** | Registro insertado en la base de datos satisfactoriamente |
| **Evidencia** | *(Pegar captura de Postman aquí)* |

---

### Prueba P5. Registrar Producto con Precio Negativo (Regla 1)
| Campo | Detalle |
|---|---|
| **Método HTTP** | `POST` |
| **Endpoint** | `http://localhost/cafeteria_api/?resource=productos` |
| **Headers** | `Content-Type: application/json` |
| **Datos enviados** | `{"nombre": "Galleta Avena", "categoria": "Postres", "precio": -1.50, "stock": 10, "activo": true}` |
| **Código HTTP esperado** | `400` |
| **Código HTTP obtenido** | `400 Bad Request` |
| **JSON recibido** | `{"success": false, "mensaje": "El precio del producto debe ser mayor a 0"}` |
| **Resultado esperado** | Rechazar la inserción por regla de negocio de precio |
| **Resultado obtenido** | Petición denegada con status 400 Bad Request |
| **Evidencia** | *(Pegar captura de Postman aquí)* |

---

### Prueba P6. Registrar Producto con Stock Negativo (Regla 2)
| Campo | Detalle |
|---|---|
| **Método HTTP** | `POST` |
| **Endpoint** | `http://localhost/cafeteria_api/?resource=productos` |
| **Headers** | `Content-Type: application/json` |
| **Datos enviados** | `{"nombre": "Jugo de Fresa", "categoria": "Bebidas", "precio": 2.00, "stock": -4, "activo": true}` |
| **Código HTTP esperado** | `400` |
| **Código HTTP obtenido** | `400 Bad Request` |
| **JSON recibido** | `{"success": false, "mensaje": "El stock no puede ser negativo"}` |
| **Resultado esperado** | Rechazar la inserción por stock inválido |
| **Resultado obtenido** | Status 400 Bad Request y mensaje descriptivo |
| **Evidencia** | *(Pegar captura de Postman aquí)* |

---

### Prueba P7. Actualizar Producto Existente
| Campo | Detalle |
|---|---|
| **Método HTTP** | `PUT` |
| **Endpoint** | `http://localhost/cafeteria_api/?resource=productos&id=1` |
| **Headers** | `Content-Type: application/json` |
| **Datos enviados** | `{"precio": 2.75, "stock": 28}` |
| **Código HTTP esperado** | `200` |
| **Código HTTP obtenido** | `200 OK` |
| **JSON recibido** | `{"success": true, "mensaje": "Producto actualizado correctamente"}` |
| **Resultado esperado** | Actualizar precio y stock del producto 1 |
| **Resultado obtenido** | Producto modificado exitosamente en la base de datos |
| **Evidencia** | *(Pegar captura de Postman aquí)* |

---

### Prueba P8. Eliminar Producto
| Campo | Detalle |
|---|---|
| **Método HTTP** | `DELETE` |
| **Endpoint** | `http://localhost/cafeteria_api/?resource=productos&id=11` |
| **Datos enviados** | Ninguno |
| **Código HTTP esperado** | `200` |
| **Código HTTP obtenido** | `200 OK` |
| **JSON recibido** | `{"success": true, "mensaje": "Producto eliminado correctamente"}` |
| **Resultado esperado** | Eliminar el registro de prueba creado |
| **Resultado obtenido** | Registro eliminado de MySQL con confirmación 200 OK |
| **Evidencia** | *(Pegar captura de Postman aquí)* |

---

### Prueba P9. Consulta Adicional por Categoría en la API (Actividad 13)
| Campo | Detalle |
|---|---|
| **Método HTTP** | `GET` |
| **Endpoint** | `http://localhost/cafeteria_api/?resource=productos&categoria=Bebidas` |
| **Datos enviados** | Ninguno |
| **Código HTTP esperado** | `200` |
| **Código HTTP obtenido** | `200 OK` |
| **JSON recibido** | Arreglo JSON conteniendo únicamente los productos con categoría "Bebidas" |
| **Resultado esperado** | Filtrado exitoso resuelto por la API en PHP |
| **Resultado obtenido** | Lista filtrada de bebidas devuelta correctamente con código 200 |
| **Evidencia** | *(Pegar captura de Postman aquí)* |

---

## 7. CONCLUSIONES (MÓDULO PRODUCTOS)
1. Se estructuró el módulo de productos respetando el patrón multicapa, lo que permitió aislar las sentencias SQL en el modelo (`Producto.php`), asegurando que las reglas del negocio (precio positivo, stock no negativo y disponibilidad activa) se validen de forma centralizada en la capa de servicios (`ProductoService.php`).
2. La implementación de filtros en la capa de la API (Actividad 13) demostró la versatilidad de la arquitectura orientada a servicios, permitiendo transformar y depurar conjuntos de datos en memoria sin sobrecargar el motor de base de datos con sentencias condicionales acopladas.
3. El uso estricto de códigos HTTP semánticos (`200 OK`, `201 Created`, `400 Bad Request`, `404 Not Found`, `409 Conflict`) garantiza que cualquier cliente consumidor (como Postman o una futura aplicación móvil/web) interprete de manera inequívoca el estado de cada transacción.

---

## 8. RECOMENDACIONES (MÓDULO PRODUCTOS)
1. Para futuros despliegues en producción con altos volúmenes de productos, se recomienda combinar el filtrado en API con paginación (`limit` y `offset`) para evitar consumos excesivos de memoria en el servidor PHP.
2. Implementar un borrado lógico (`soft delete`) mediante el campo `activo = 0` en lugar de un `DELETE` físico de MySQL, preservando así la integridad histórica de los pedidos previamente despachados.
