# API REST - Cafetería Universitaria

API REST desarrollada en PHP y MySQL con arquitectura multicapa para gestionar productos, clientes y pedidos de una cafetería universitaria, utilizando Postman para las pruebas de endpoints[cite: 2].

## 👥 Equipo de Trabajo
1. **Espín Caiza Camila Gisselle** — Arquitectura, Base de Datos y Documentación general[cite: 1]
2. **Guevara López Oscar Mauricio** — Módulo Clientes[cite: 1]
3. **López Yantalema Erick Alexander** — Módulo Productos[cite: 1]
4. **Sánchez Sánchez José Esteban** — Módulo Pedidos, Códigos HTTP y Pruebas finales[cite: 1]

---

## 🛠️ Tecnologías Utilizadas
* PHP (v8.2+)[cite: 2]
* MySQL / phpMyAdmin[cite: 2]
* Apache (XAMPP)[cite: 2]
* Postman[cite: 2]

---

## 🚀 Instalación y Configuración
1. Clonar o descargar este repositorio dentro de la carpeta `htdocs` de XAMPP.
2. Iniciar **Apache** y **MySQL** desde el panel de control de XAMPP[cite: 2].
3. Importar el script SQL ubicado en `docs/script_bd.sql` utilizando phpMyAdmin para crear la base de datos `cafeteria_api` con sus datos iniciales[cite: 2].
4. Verificar la conexión ingresando a la URL del proyecto:
   `http://localhost/cafeteria_api/`

---

## 📂 Estructura del Proyecto
```text
cafeteria_api/
├── config/          # Conexión a la base de datos (PDO)
├── models/          # Operaciones directas con MySQL (CRUD)
├── services/        # Reglas de negocio, validaciones y cálculos
├── controllers/     # Recepción de peticiones HTTP
├── docs/            # Diagramas, scripts SQL y capturas de evidencia
├── index.php        # Punto de entrada de la API
└── README.md        # Documentación general del proyecto
```

---

## ☕ Módulo Productos (Erick Alexander López Yantalema)

### 📌 Endpoints Diseñados e Implementados
| Método | Endpoint | Descripción | Código HTTP |
|---|---|---|---|
| `GET` | `/cafeteria_api/?resource=productos` | Listar todos los productos | 200 OK |
| `GET` | `/cafeteria_api/?resource=productos&id={id}` | Consultar producto específico | 200 OK / 404 Not Found |
| `POST` | `/cafeteria_api/?resource=productos` | Registrar un nuevo producto | 201 Created / 400 Bad Request |
| `PUT` | `/cafeteria_api/?resource=productos&id={id}` | Actualizar un producto existente | 200 OK / 404 Not Found / 400 Bad Request |
| `DELETE` | `/cafeteria_api/?resource=productos&id={id}` | Eliminar un producto | 200 OK / 404 Not Found / 409 Conflict |
| `GET` | `/cafeteria_api/?resource=productos&categoria={categoria}` | Consulta adicional: filtrar por categoría (API) | 200 OK |
| `GET` | `/cafeteria_api/?resource=productos&nombre={nombre}` | Consulta adicional: filtrar por nombre (API) | 200 OK |

---

### ⚖️ Reglas de Negocio Aplicadas en la Capa de Servicio
* **Regla 1 (Precio Válido):** El precio de todo producto debe ser mayor a 0 (`precio > 0`). Si se envía un valor menor o igual a 0, la API responde con código `400 Bad Request` y el mensaje `"El precio del producto debe ser mayor a 0"`.
* **Regla 2 (Stock Válido):** El stock no puede ser negativo (`stock >= 0`). En caso de recibir un número negativo, se retorna `400 Bad Request` con el mensaje `"El stock no puede ser negativo"`.
* **Regla 3 (Producto Disponible):** La entidad maneja el atributo `activo` (booleano). Los productos dados de baja (`activo = false`) no podrán ser agregados a nuevos pedidos. El servicio expone el método `productoDisponible($id)` para que la capa de pedidos verifique su disponibilidad.
* **Actividad 13 (Consulta Adicional en la API):** El filtrado por `categoria` o `nombre` se resuelve en la capa de servicio de la API en PHP mediante filtrado en memoria sobre los datos obtenidos del modelo, cumpliendo el requisito de no realizar la consulta directamente con un `WHERE` en MySQL.

---

### 📝 Ejemplos JSON de Petición y Respuesta

#### 1. Crear Producto (`POST`)
* **Headers:** `Content-Type: application/json`
* **Body:**
```json
{
  "nombre": "Capuchino Vainilla",
  "categoria": "Bebidas",
  "precio": 2.75,
  "stock": 25,
  "activo": true
}
```
* **Respuesta (`201 Created`):**
```json
{
  "success": true,
  "mensaje": "Producto registrado correctamente"
}
```

#### 2. Consultar Productos (`GET`)
* **Respuesta (`200 OK`):**
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
    }
  ]
}
```

#### 3. Actualizar Producto (`PUT /cafeteria_api/?resource=productos&id=1`)
* **Body:**
```json
{
  "precio": 2.85,
  "stock": 35
}
```
* **Respuesta (`200 OK`):**
```json
{
  "success": true,
  "mensaje": "Producto actualizado correctamente"
}
```

#### 4. Consulta Adicional por Categoría (`GET /cafeteria_api/?resource=productos&categoria=Bebidas`)
* **Respuesta (`200 OK`):**
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