# 🚀 Sprint 2 – Autenticación y Gestión de Productos

Este documento recoge los objetivos y entregables del **Sprint 2**, centrado en la carga inicial de productos y el sistema de autenticación de usuarios.

---

## 📌 Objetivos del Sprint

Los objetivos de esta iteración han sido los siguientes:

### **C1. Importación de productos (Excel → JSON Server)**

- Crear un script PHP capaz de leer un fichero Excel (`.xlsx`) subido por un usuario.
- Procesar los datos y validarlos (precios, stock, etc.).
- Generar un fichero `products.json` compatible con JSON Server.

### **C2. Registro e Inicio de Sesión (con JSON Server)**

- Implementar un formulario de **registro** que guarde usuarios en `users.json`, cifrando la contraseña con `password_hash()`.
- Implementar un formulario de **login** que valide el usuario y contraseña mediante `password_verify()`.
- Crear un sistema de **sesiones PHP** (`$_SESSION`) y **cookies** para mantener al usuario conectado.
- Desarrollar una página de **perfil** (`profile.php`) donde el usuario pueda ver y actualizar sus datos (excepto el nombre de usuario).
- Implementar el **cierre de sesión** (`logout.php`) destruyendo la sesión y las cookies.

---

## 🗨️ C3. Comentarios y Valoraciones de Productos

### 🎯 Objetivo

Fomentar la interacción entre los usuarios y los productos de la tienda mediante un sistema de **comentarios, valoraciones y “me gusta”** integrado en las fichas de producto.

Los usuarios **autenticados** podrán:

- Escribir comentarios.
- Asignar una puntuación (opcional).
- Indicar que un producto les gusta (“👍 Me gusta”).

Cada comentario o valoración estará **asociado al perfil del usuario** que lo ha creado y se mostrará **en tiempo real** en la página del producto, sin necesidad de recargarla.

La funcionalidad se implementará de forma **dinámica (AJAX / Fetch API)**, manteniendo coherencia visual con el sitio web y cumpliendo criterios de **usabilidad y accesibilidad**.

---

### ✅ Requisitos previos

- Sistema de **autenticación de usuarios activo** (Sprint 2 – C2).
- Base de datos o ficheros **JSON** para productos y comentarios.
- Soporte para **AJAX / Fetch API**.
- **JavaScript** habilitado en el cliente.
- Hojas de estilo CSS o framework (Bootstrap, Tailwind, etc.).

---

### 🔄 Flujo general de implementación

#### 1️⃣ Mostrar comentarios y valoraciones

- Carga de comentarios mediante una petición **AJAX (GET)** al backend.
- Visualización bajo la ficha del producto:
  - Usuario
  - Fecha
  - Comentario
  - Puntuación (si existe)

#### 2️⃣ Añadir un nuevo comentario

- Formulario con campo de texto y selector de puntuación (opcional).
- Envío de datos mediante **AJAX (POST)** sin recargar la página.
- Actualización inmediata de la lista de comentarios.

#### 3️⃣ Valorar un producto

- Botón interactivo **“👍 Me gusta”**.
- Registro de la interacción en la base de datos.
- Actualización dinámica del contador de “me gusta” o de la valoración media.

#### 4️⃣ Gestión de permisos

- Solo los usuarios autenticados pueden comentar o valorar.
- Cada usuario puede **editar o eliminar sus propios comentarios**.
- *(Opcional)* Moderación por parte de administradores.

---

## ✅ Entregables del Sprint 2

### 1. Código fuente (Importación C1)

- `frontend/formulario.html`  
  Formulario para subir el archivo Excel.

- `backend/procesar.php`  
  Script que recibe el Excel, lo valida con **PhpSpreadsheet** y genera `data/products.json`.

- `docker/php/Dockerfile`  
  Dockerfile actualizado con las librerías necesarias (`zip`, `gd`, etc.).

---

### 2. Código fuente (Autenticación C2)

- `backend/auth/register.php`  
  Gestión del registro de usuarios.

- `backend/auth/login.php`  
  Inicio de sesión y creación de sesiones.

- `backend/auth/profile.php`  
  Visualización y edición del perfil del usuario.

- `backend/auth/logout.php`  
  Cierre de sesión y destrucción de cookies.

- `backend/includes/json_connect.php`  
  Funciones de conexión con JSON Server (`getUserByUsername`, `createUser`, etc.).

---

### 3. Código fuente (Comentarios y Valoraciones – C3)

- `backend/api/comentarios.php`  
  API para obtener (**GET**) y crear (**POST**) comentarios.

- `backend/data/comentarios.json`  
  Almacenamiento de comentarios, valoraciones y “me gusta”.

- `frontend/js/comentarios.js`  
  Gestión dinámica de comentarios y valoraciones mediante AJAX.

- `frontend/js/detalle.js`  
  Vinculación de comentarios con el producto visualizado.

- `frontend/product.html`  
  Vista del producto con la sección de comentarios y valoraciones.

---

### 4. Planificación y Documentación

- `docs/sprint2.md`  
  Documento del Sprint 2.

- `docs/gantt-SA2.png`  
  Cronograma actualizado del Sprint.

- `docs/kanban-SA2.png`  
  Captura del tablero Kanban con las tareas finalizadas.
