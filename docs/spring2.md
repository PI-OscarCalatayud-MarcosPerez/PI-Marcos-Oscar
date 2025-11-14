# 🚀 Sprint 2 – Autenticación y Gestión de Productos

Este documento recoge los objetivos y entregables del **Sprint 2**, centrado en la carga inicial de productos y el sistema de autenticación de usuarios.

---

## 📌 Objetivos del Sprint

Los objetivos de esta iteración han sido dos:

1.  **C1. Importación de productos (Excel → JSON Server):**
    * Crear un script PHP capaz de leer un fichero Excel (`.xlsx`) subido por un usuario.
    * Procesar los datos, validarlos (precios, stock, etc.).
    * Generar un fichero `products.json` compatible con JSON Server.

2.  **C2. Registro e Inicio de Sesión (con JSON Server):**
    * Implementar un formulario de **registro** que guarde usuarios en `users.json`, cifrando la contraseña con `password_hash()`.
    * Implementar un formulario de **login** que valide el usuario y contraseña (con `password_verify()`).
    * Crear un sistema de **sesiones PHP** (`$_SESSION`) y **cookies** para mantener al usuario conectado.
    * Desarrollar una página de **perfil** (`profile.php`) donde el usuario pueda ver y actualizar sus datos (excepto el nombre de usuario).
    * Implementar el **cierre de sesión** (`logout.php`) destruyendo la sesión y las cookies.

---

## ✅ Entregables del Sprint 2

### 1. Código fuente (Importación C1)

* `frontend/formulario.html`: Formulario para subir el archivo Excel.
* `backend/procesar.php`: Script principal que recibe el Excel, lo valida con `PhpSpreadsheet` y genera el `data/products.json`.
* `docker/php/Dockerfile`: Actualizado para incluir las librerías de PHP necesarias (como `zip`, `gd`, etc.).

### 2. Código fuente (Autenticación C2)

* `backend/auth/register.php`: Gestiona el registro de nuevos usuarios.
* `backend/auth/login.php`: Gestiona el inicio de sesión y la creación de sesiones.
* `backend/auth/profile.php`: Muestra y permite actualizar el perfil del usuario.
* `backend/auth/logout.php`: Cierra la sesión del usuario.
* `backend/includes/json_connect.php`: (DEBERÍAS TENERLO) Fichero con las funciones para conectar con el JSON Server (getUserByUsername, createUser, etc.). *NOTA: Tu código lo incluye (`require_once`), asegúrate de que existe.*

### 3. Planificación y Documentación

* `docs/sprint2.md`: Este mismo documento.
* `docs/gantt-SA2.png`: (AÑADIR CAPTURA) Cronograma actualizado con las tareas del Sprint 2.
* `docs/kanban-SA2.png`: (AÑADIR CAPTURA) Captura del tablero Kanban (Trello o GitHub Projects) al finalizar el Sprint 2, mostrando las tareas completadas.

---