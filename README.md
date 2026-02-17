# MoliCasillas Backend API

Backend API para el sistema de Casilla Electrónica "MoliCasillas" de la Municipalidad de Lima. Implementado en Laravel 12 (API-only) con PostgreSQL y autenticación Sanctum. Toda la nomenclatura del sistema (base de datos, modelos y respuestas de API) ha sido refactorizada a español.

## Requisitos

- PHP 8.4+
- Composer
- PostgreSQL 12+ (DB: `moli_casillas` en puerto 5432)
- Laravel Herd (Recomendado para entorno local `.test`)

## Instalación

1. **Clonar repositorio e instalar dependencias:**
   ```bash
   composer install
   ```

2. **Configurar entorno:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   # Configurar DB_HOST, DB_PORT, DB_DATABASE (moli_casillas), DB_USERNAME, DB_PASSWORD en .env
   ```

3. **Ejecutar migraciones y seeders:**
   ```bash
   php artisan migrate:fresh --seed
   ```
   
   El seeder crea:
   - 1 usuario de prueba (`usuario_auth_id`: `11111111-1111-1111-1111-111111111111`)
   - 1 versión de términos activos (automáticamente aceptados para el usuario de prueba)
   - 40 mensajes con estados variados (SIN LEER, NOTIFICADO, LEÍDO, ARCHIVADO)
   - Adjuntos de prueba generados aleatoriamente.

## Pruebas (Tests)

Ejecutar suite de pruebas (usa PostgreSQL configurado o SQLite según `phpunit.xml`):
```bash
php artisan test --compact
```
Actualmente el proyecto cuenta con **42 tests de integración** que validan todo el flujo de negocio.

## Uso de la API (Ejemplos cURL)

La API corre por defecto en `http://molicasillas.test`. Todos los endpoints están bajo `/api/v1`.

### 1. Iniciar Sesión
Obtener token Sanctum usando el ID de usuario externo.

```bash
curl -X POST http://molicasillas.test/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "usuario_auth_id": "11111111-1111-1111-1111-111111111111",
    "tipo_documento": "DNI",
    "numero_documento": "12345678",
    "nombre_mostrar": "Juan Pérez",
    "email": "juan@example.com"
  }'
```

**Respuesta exitosa:**
```json
{
  "data": {
    "token": "1|AbCdEf...",
    "tipo_token": "Bearer",
    "cuenta": { ... }
  }
}
```

### 2. Consultar Estados de Bandeja
Retorna conteo de mensajes por estado.

```bash
curl -X GET http://molicasillas.test/api/v1/casilla/messages/statuses \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

**Respuesta:**
```json
{
  "data": {
    "estados": [
      { "codigo": "UNREAD", "etiqueta": "SIN LEER", "cantidad": 15 },
      { "codigo": "READ", "etiqueta": "LEÍDO", "cantidad": 10 },
      ...
    ],
    "cantidad_no_leidos": 20
  }
}
```

### 3. Ejecutar Descarga de Adjunto
```bash
curl -X GET http://molicasillas.test/api/v1/casilla/attachments/<ADJUNTO_UUID>/download \
  -H "Authorization: Bearer <TOKEN>" \
  -H "Accept: application/json"
```

## Estructura del Proyecto (Nomenclatura Español)

- `app/Models/Casilla`: Representa la cuenta de la casilla del usuario.
- `app/Models/Mensaje`: Gestión de comunicaciones y sus estados.
- `app/Models/Adjunto`: Archivos asociados a los mensajes.
- `app/Models/VersionTerminos`: Control de versiones de contratos/términos.
- `app/Http/Controllers/Api/V1`: Controladores (`AuthController`, `MensajeController`, `AdjuntoController`, `TerminosController`).

## Documentación API (Swagger UI)

La documentación interactiva está disponible en:
`http://molicasillas.test/api/documentation`

### Consideraciones de Swagger
1. **Host**: Asegúrate de que `L5_SWAGGER_CONST_HOST` en `config/l5-swagger.php` apunte a tu dominio local.
2. **Autorización**: Usa el botón **Authorize** e ingresa el token con el formato `Bearer <token>`.
3. **CSRF**: Las rutas de la API están excluidas de la verificación CSRF para facilitar las pruebas desde Swagger.

---
**Desarrollado para la Municipalidad de Lima.**
