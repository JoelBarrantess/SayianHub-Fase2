# SaiyanHub Fase 2

## Reservas

- Nueva rama: `feature/reservas`.
- Esquema: tabla `reservas` (ver `private/db/schema.sql`).
- Páginas:
	- `public/pages/reservas/reservar.php`: formulario para crear reservas (sala, mesa, fecha, hora inicio/fin).
	- `public/pages/reservas/listar.php`: listado básico de reservas.
- Backend:
	- `private/proc/reservas/crear_reserva.php`: inserta reserva y comprueba conflictos de franja para la misma mesa y fecha.

### Probar
1. Abrir `public/pages/reservas/reservar.php` y crear una reserva.
2. Ver en `public/pages/reservas/listar.php`.

### Notas
- Validación básica de solapamiento por franja horaria.
- Se usa PDO en todas las consultas.
- Mantiene coherencia con el proyecto original.
# Saiyan Hub — Gestión de ocupación de mesas

## Resumen del proyecto

Saiyan Hub es una aplicación web sencilla para la gestión de la ocupación de mesas en un restaurante. Permite a los camareros:

- Consultar salas y mesas.
- Marcar mesas como ocupadas y liberarlas (registrando fecha/hora de inicio y fin).
- Consultar un histórico de ocupaciones con filtros (sala, mesa, camarero y rango de fecha/hora).

El proyecto está pensado como una intranet y fue desarrollado como trabajo de grupo siguiendo el enunciado "PJ 01 Transversal".

## Instalación y funcionamiento (local)

Coloca la aplicación en tu localhost, importa la base de datos y abre en el navegador:

- Clona el repositorio:

```powershell
git clone https://github.com/Joserodrg/SaiyanHub.git SaiyanHub
cd SaiyanHub
```

- Importa el esquema y los datos de ejemplo (el archivo `private/db/schema.sql` ya incluye inserciones de ejemplo). Puedes hacerlo con phpMyAdmin o con el cliente MySQL:
- Asegúrate de configurar el puerto/credenciales en `private/db/db_conn.php` (por defecto este repo usa `'localhost'`).
- Abre en tu navegador: `http://localhost/SaiyanHub/`

Eso es todo: el esquema ya incluye datos de ejemplo, solo clonar, importar y ajustar `private/db/db_conn.php` si tu MySQL usa otro puerto o credenciales.

## Equipo

- José Rodríguez
- Joel Barrantes
- Junhao Xiang
- Aarón Suarez

---
