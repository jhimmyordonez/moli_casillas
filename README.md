# MoliCasillas Backend API

Backend API para el sistema de Casilla Electrónica "MoliCasillas" de la Municipalidad de Lima. Implementado en Laravel 12 (API-only) con PostgreSQL y autenticación Sanctum.

## Requisitos

- PHP 8.2+
- Composer
- PostgreSQL 12+ (DB: `molicasillas` en puerto 5432)

## Instalación

1. **Clonar repositorio e instalar dependencias:**
   ```bash
   composer install
   ```

2. **Configurar entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   # Configurar DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD en .env
   ```

3. **Ejecutar migraciones y seeders:**
   ```bash
   php artisan migrate --seed
   ```
   
   El seeder crea:
   - 1 usuario de prueba (`auth_user_id`: `11111111-1111-1111-1111-111111111111`)
   - 1 versión de términos activos
   - 40 mensajes con estados variados (SIN LEER, NOTIFICADO, LEÍDO, ARCHIVADO)
   - Adjuntos de prueba

## Pruebas (Tests)

Ejecutar suite de pruebas (usa SQLite en memoria):
```bash
php artisan test --compact
```

## Uso de la API (Ejemplos cURL)

La API corre en `http://molicasillas.test` (o `http://localhost:8000`).
Todos los endpoints están bajo `/api/v1`.

### 1. Iniciar Sesión (Mock)

Obtener token Sanctum usando el ID de usuario externo.

```bash
curl -X POST http://molicasillas.test/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "auth_user_id": "11111111-1111-1111-1111-111111111111",
    "doc_type": "DNI",
    "doc_number": "12345678",
    "display_name": "Juan Pérez"
  }'
```

**Respuesta exitosa:**
```json
{
  "data": {
    "token": "1|AbCdEfGhIjKlMnOpQrStUvWxYz123456",
    "token_type": "Bearer",
    ...
  }
}
```

### 2. Aceptar Términos y Condiciones

Necesario para acceder a la bandeja.

```bash
# 1. Obtener ID de términos vigentes
curl -X GET http://molicasillas.test/api/v1/terms/current \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"

# 2. Aceptar términos
curl -X POST http://molicasillas.test/api/v1/terms/accept \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{ "terms_version_id": "<UUID_TERMS>" }'
```

### 3. Consultar Estados de Bandeja (Endpoint Principal)

Retorna conteo de mensajes por estado (SIN LEER, NOTIFICADO, LEÍDO, ARCHIVADO).

```bash
curl -X GET http://molicasillas.test/api/v1/casilla/messages/statuses \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

**Respuesta:**
```json
{
  "data": {
    "statuses": [
      { "code": "UNREAD", "label": "SIN LEER", "count": 15 },
      { "code": "NOTIFIED", "label": "NOTIFICADO", "count": 5 },
      { "code": "READ", "label": "LEÍDO", "count": 10 },
      { "code": "ARCHIVED", "label": "ARCHIVADO", "count": 10 }
    ],
    "unread_count": 20
  }
}
```

### 4. Listar Mensajes (Bandeja)

```bash
curl -X GET "http://molicasillas.test/api/v1/casilla/messages?page=1&per_page=10" \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

### 5. Marcar como Leído

```bash
curl -X PATCH http://molicasillas.test/api/v1/casilla/messages/<MESSAGE_UUID>/read \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

### 6. Ejecutar Descarga de Adjunto

```bash
curl -X GET http://molicasillas.test/api/v1/casilla/attachments/<ATTACHMENT_UUID>/download \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json" \
  -O
```

## Estructura del Proyecto

- `app/Models/CasillaAccount`: Usuario autenticado (extiende Authenticatable).
- `app/Models/Message`: Modelo principal con lógica de estados computados (`read_at`, `archived_at`).
- `app/Http/Controllers/Api/V1`: Controladores de la API.
- `app/Policies`: Policies para autorización (solo dueño accede a su casilla).
- `database/migrations`: Definición de esquema.

## Documentación API (Swagger UI)

La documentación interactiva de la API está disponible gracias a Swagger.

### Acceso a Swagger UI
1. Asegúrate de que el servidor esté corriendo (`php artisan serve` o Herd).
2. Visita: `/api/documentation`
   - Ejemplo: `http://molicasillas.test/api/documentation` o `http://localhost:8000/api/documentation`

### Cómo probar en Swagger
1. **Obtener Token**:
   - Usa el endpoint `POST /api/v1/auth/login`.
   - Copia el `token` de la respuesta.
2. **Autorizar**:
   - Haz clic en el botón verde **Authorize**.
   - Ingresa: `Bearer <token_copiado>` (asegúrate de incluir el prefijo `Bearer ` si Swagger no lo añade automáticamente).
   - Haz clic en **Authorize** y luego **Close**.
3. **Probar Endpoints**:
   - Accede a cualquier endpoint protegido (candado cerrado).
   - Haz clic en **Try it out**.
   - Llena parámetros si es necesario.
   - Haz clic en **Execute**.

### Comandos Útiles
- Generar documentación manualmente:
  ```bash
  php artisan l5-swagger:generate
  ```
