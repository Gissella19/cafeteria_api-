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