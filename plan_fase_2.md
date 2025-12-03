# Plan de Acción: Fase 2 - Reserva de Taules (SayianHub)

Este documento detalla el plan de ejecución para la implementación de las nuevas funcionalidades del proyecto, incluyendo la gestión de reservas, administración de usuarios y recursos.

## 1. Estrategia de Ramas (Branching Strategy)

Para mantener el código organizado y evitar conflictos, utilizaremos una estrategia basada en **Git Flow** simplificado.

*   **`main`**: Rama de producción. Contiene el código estable y funcional.
*   **`develop`**: Rama de desarrollo. Aquí se integran todas las nuevas funcionalidades antes de pasar a `main`.
*   **Ramas de Feature (`feature/...`)**: Ramas temporales para desarrollar funcionalidades específicas. Se crean a partir de `develop` y se fusionan (merge) de vuelta a `develop`.

### Ramas sugeridas para este proyecto:

1.  `feature/db-setup`: Modificación y ampliación de la base de datos.
2.  `feature/auth-roles`: Adaptación del login y gestión de sesiones por roles.
3.  `feature/crud-resources`: Gestión de recursos (salas, mesas, sillas) e imágenes.
4.  `feature/crud-users`: Gestión de usuarios (CRUD completo).
5.  `feature/reservations`: Lógica de reservas (frontend y backend).
6.  `feature/ui-ux`: Mejoras visuales, SweetAlerts y validaciones finales.

---

## 2. Plan de Ejecución (Fases)

El desarrollo se dividirá en fases lógicas para asegurar que las dependencias se resuelvan en orden.

### Fase 1: Base de Datos y Configuración (Est. 3h)
*   Diseñar el modelo E/R ampliado.
*   Crear scripts SQL para nuevas tablas (`resources`, `reservations`, `roles` si no existen).
*   Configurar conexión PDO robusta.

### Fase 2: Autenticación y Roles (Est. 4h)
*   Asegurar que el sistema de login distinga entre 'Admin' y 'Camarero'.
*   Crear middleware/lógica para proteger rutas (solo admin puede ver CRUDs).

### Fase 3: Administración de Recursos (CRUD) (Est. 8h)
*   Listar salas/mesas.
*   Crear/Editar/Eliminar recursos.
*   **Importante**: Subida de imágenes para las salas.

### Fase 4: Administración de Usuarios (CRUD) (Est. 6h)
*   Listar usuarios.
*   Crear/Editar/Eliminar usuarios y asignar roles.

### Fase 5: Sistema de Reservas (Est. 10h)
*   Interfaz para visualizar disponibilidad (calendario o lista por franjas).
*   Formulario de reserva (validación de fecha/hora).
*   Lógica de backend para evitar solapamientos (doble reserva).

### Fase 6: Refinamiento y Documentación (Est. 4h)
*   Implementar SweetAlerts para confirmaciones.
*   Validaciones JS en cliente.
*   README final y limpieza de código.

---

## 3. Listado de Issues para GitHub

A continuación, se presentan las tareas listas para ser creadas como Issues en GitHub, con sus etiquetas sugeridas.

### Configuración y Base de Datos

#### Issue 1: Diseño e implementación de la Base de Datos Fase 2
**Descripción**: 
Ampliar la base de datos actual para soportar las nuevas funcionalidades.
- Crear tabla `recursos` (id, nombre, tipo, capacidad, imagen, estado).
- Crear tabla `reservas` (id, id_recurso, id_usuario, fecha, franja_horaria, estado).
- Asegurar relaciones (FK) correctas.
**Etiquetas**: `database`, `backend`, `priority:high`

#### Issue 2: Configuración de conexión PDO y estructura de carpetas
**Descripción**: 
Revisar la conexión a BD para usar PDO estrictamente. Organizar estructura de carpetas para separar lógica de admin y pública si es necesario.
**Etiquetas**: `backend`, `configuration`

---

### Autenticación y Seguridad

#### Issue 3: Sistema de Roles y Protección de Rutas
**Descripción**: 
Implementar lógica para diferenciar usuarios.
- Admin: Acceso total (CRUDs + Reservas).
- Camarero: Acceso solo a Reservas.
- Redirigir si un usuario intenta entrar a una zona no autorizada.
**Etiquetas**: `security`, `backend`, `auth`

---

### Gestión de Recursos (Admin)

#### Issue 4: CRUD de Recursos - Listado y Lectura
**Descripción**: 
Crear página en panel de admin que muestre una tabla con todos los recursos (salas, mesas). Debe mostrar la imagen en miniatura.
**Etiquetas**: `frontend`, `backend`, `admin`

#### Issue 5: CRUD de Recursos - Creación y Subida de Imágenes
**Descripción**: 
Formulario para añadir nuevos recursos.
- Campos: Nombre, Tipo, Capacidad.
- **Input File**: Permitir subir una imagen, guardarla en servidor y guardar la ruta en BD.
**Etiquetas**: `frontend`, `backend`, `admin`, `feature`

#### Issue 6: CRUD de Recursos - Edición y Borrado
**Descripción**: 
Permitir editar datos de un recurso (incluyendo cambiar imagen) y eliminarlo (soft delete o hard delete según decisión).
**Etiquetas**: `frontend`, `backend`, `admin`

---

### Gestión de Usuarios (Admin)

#### Issue 7: CRUD de Usuarios - Gestión Completa
**Descripción**: 
Panel para que el administrador gestione la plantilla.
- Listar usuarios.
- Crear usuario con rol (Select: Gerente, Camarero, Mantenimiento).
- Editar/Eliminar usuarios.
**Etiquetas**: `frontend`, `backend`, `admin`

---

### Sistema de Reservas (Camareros)

#### Issue 8: Interfaz de Disponibilidad de Recursos
**Descripción**: 
Pantalla principal para camareros.
- Seleccionar fecha.
- Seleccionar franja horaria.
- Mostrar qué recursos están libres y cuáles ocupados visualmente.
**Etiquetas**: `frontend`, `ui`

#### Issue 9: Lógica de Creación de Reservas
**Descripción**: 
Backend para procesar la reserva.
- Validar que el recurso no esté ocupado en esa fecha/hora.
- Insertar reserva en BD.
- Feedback al usuario (Éxito/Error).
**Etiquetas**: `backend`, `core`

#### Issue 10: Visualización de Mis Reservas / Historial
**Descripción**: 
(Opcional pero recomendado) Ver lista de reservas activas para poder cancelarlas o consultarlas.
**Etiquetas**: `frontend`, `backend`

---

### UI/UX y Frontend

#### Issue 11: Implementación de SweetAlerts y Validaciones JS
**Descripción**: 
Sustituir `alert()` nativos por SweetAlert2.
- Confirmación antes de borrar (CRUD).
- Mensajes de éxito al reservar.
- Validación de formularios en cliente (campos vacíos, fechas pasadas).
**Etiquetas**: `frontend`, `ux`, `javascript`

#### Issue 12: Diseño Responsive y Homogéneo
**Descripción**: 
Asegurar que tanto el panel de admin como la vista de reservas usen Bootstrap 5 y mantengan una estética coherente (mismo header, footer, estilos de botones).
**Etiquetas**: `css`, `design`

---

### Documentación y Despliegue

#### Issue 13: Documentación README y Limpieza
**Descripción**: 
Crear `README.md` con:
- Instrucciones de instalación.
- Usuarios de prueba (Admin/Camarero).
- Explicación de funcionamiento.
- Sincronizar repo final.
**Etiquetas**: `documentation`

---

## 4. Etiquetas de GitHub (Labels)

Para organizar visualmente los issues, crea las siguientes etiquetas en tu repositorio de GitHub con sus respectivos colores:

| Etiqueta | Color (Hex) | Descripción |
| :--- | :--- | :--- |
| `database` | `#0e8a16` (Verde oscuro) | Cambios en base de datos o SQL |
| `backend` | `#c5def5` (Azul claro) | Lógica de servidor (PHP) |
| `frontend` | `#1d76db` (Azul) | HTML, vistas y presentación |
| `priority:high` | `#d93f0b` (Rojo intenso) | Tareas urgentes o bloqueantes |
| `configuration` | `#fbca04` (Amarillo) | Configuración de entorno, conexión BD |
| `security` | `#b60205` (Rojo oscuro) | Temas de seguridad y permisos |
| `auth` | `#5319e7` (Violeta) | Login, registro y roles |
| `admin` | `#bfd4f2` (Azul pálido) | Funcionalidades del panel de administración |
| `feature` | `#a2eeef` (Cian) | Nuevas funcionalidades |
| `ui` | `#d4c5f9` (Lavanda) | Interfaz de usuario |
| `ux` | `#006b75` (Verde azulado) | Experiencia de usuario |
| `core` | `#000000` (Negro) | Funcionalidad núcleo del sistema |
| `javascript` | `#f1e05a` (Amarillo JS) | Código JavaScript |
| `css` | `#563d7c` (Morado) | Estilos y diseño |
| `design` | `#e99695` (Salmón) | Aspectos visuales y maquetación |
| `documentation` | `#0075ca` (Azul medio) | README y documentación |

