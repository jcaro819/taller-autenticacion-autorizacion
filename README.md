# EventHub API · Taller Laravel – Autenticación y Autorización

API RESTful en **Laravel 12** para **EventHub Solutions** (gestión de eventos y conferencias tecnológicas), con:

- 🔑 **Autenticación sin estado con Laravel Sanctum**: tokens Bearer en lugar de sesiones y cookies.
- 🛡️ **Policy** (`EventoPolicy`): sólo el organizador dueño de un evento puede modificarlo.
- 🚦 **Gate** (`ver-metricas`): sólo el Administrador Global ve las métricas financieras.
- 📦 Respuestas **siempre en JSON** con códigos HTTP estándar (200, 401, 403, 404, 422).

---

## Requisitos del taller y dónde están

| Requisito | Implementación |
|---|---|
| **RF 1.1** `POST /api/login` → token Bearer o 401 | `app/Http/Controllers/Api/AuthController.php` (`createToken()` de Sanctum) |
| **RF 1.2** `GET /api/perfil` | `app/Http/Controllers/Api/UserController.php` |
| **RF 2.1** `GET /api/eventos` público con el nombre del organizador | `EventoController@index` + `EventoResource` (`organizador.name`) |
| **RF 2.2** `PUT /api/eventos/{id}` con validación por campo | `EventoController@update` + `UpdateEventoRequest::rules()` |
| **RF 2.3** Sólo el creador (`user_id`) puede modificar | `app/Policies/EventoPolicy.php` → `update()`, invocada en `UpdateEventoRequest::authorize()` |
| **RF 3.1** `GET /api/admin/metricas` | `app/Http/Controllers/Api/MetricaController.php` |
| **RF 3.2** Sólo rol admin | Gate `ver-metricas` + `Gate::authorize()` |
| **RNF 1.1** Tokens con Sanctum | `laravel/sanctum`, trait `HasApiTokens` en `User`, tabla `personal_access_tokens` |
| **RNF 1.2** Contraseñas con hash | cast `'password' => 'hashed'` (bcrypt) en `User`, `Hash::check()` en el login |
| **RNF 1.3** Rutas protegidas con middleware de Sanctum | `routes/api.php` → `Route::middleware('auth:sanctum')` |
| **RNF 2.1** Autorización del modelo en una Policy, sin `if` en el modelo | `EventoPolicy`; el modelo `Evento` no tiene lógica de autorización |
| **RNF 2.2** Gate registrado en `AppServiceProvider` | `app/Providers/AppServiceProvider.php` → `Gate::define('ver-metricas', ...)` |
| **RNF 3.1** JSON + códigos 200/401/403/422 | `bootstrap/app.php` (manejo de excepciones de la API) |
| Modelo de datos `users` y `eventos` | `database/migrations/` |
| Datos semilla (1 admin, 2 organizadores, 1 asistente, eventos del Organizador A) | `database/seeders/DatabaseSeeder.php` |

---

## Instalación

Necesitas **PHP 8.2+** y **Composer**.

```bash
git clone https://github.com/jcaro819/taller-autenticacion-autorizacion.git
cd taller-autenticacion-autorizacion
composer install
cp .env.example .env              # en Windows (CMD/PowerShell): copy .env.example .env
php artisan key:generate
```

### Base de datos

**Opción A: SQLite (por defecto, sin instalar nada):**

```bash
touch database/database.sqlite    # en Windows: type nul > database\database.sqlite
php artisan migrate --seed
```

**Opción B: MySQL.** Crea la base de datos `eventhub` y en `.env` pon:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eventhub
DB_USERNAME=root
DB_PASSWORD=
```

Luego ejecuta `php artisan migrate --seed`.

Para empezar de cero en cualquier momento: `php artisan migrate:fresh --seed`.

### Ejecutar

```bash
php artisan serve
```

La API queda en `http://127.0.0.1:8000/api`.

### Usuarios de prueba (seeder)

Todos tienen la contraseña **`password`**.

| Rol | Email |
|---|---|
| admin | `admin@eventhub.com` |
| organizador | `organizador.a@eventhub.com` (dueño de los eventos 1 y 2) |
| organizador | `organizador.b@eventhub.com` (dueño del evento 3) |
| asistente | `asistente@eventhub.com` |

---

## Endpoints

| Método | URL | Autenticación | Quién puede | Respuestas |
|---|---|---|---|---|
| POST | `/api/login` | Pública | Todos | 200 token · 401 credenciales · 422 validación |
| GET | `/api/eventos` | Pública | Todos | 200 |
| GET | `/api/perfil` | Bearer | Cualquier usuario autenticado | 200 · 401 |
| POST | `/api/logout` | Bearer | Cualquier usuario autenticado | 200 (revoca el token) · 401 |
| PUT | `/api/eventos/{id}` | Bearer | **Sólo el organizador dueño** (Policy) | 200 · 401 · 403 · 404 · 422 |
| GET | `/api/admin/metricas` | Bearer | **Sólo admin** (Gate) | 200 · 401 · 403 |

### Probar con Postman (recomendado)

1. Importa `docs/EventHub.postman_collection.json` (*Import → File*).
2. Ejecuta la carpeta **"1. Login"**: cada login guarda su token automáticamente (`token_admin`, `token_org_a`, `token_org_b`, `token_asistente`).
3. Ejecuta las demás peticiones; el nombre de cada una indica el código esperado.

### Probar con curl

```bash
# 1) Login → copia el "token" de la respuesta
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Accept: application/json" -H "Content-Type: application/json" \
  -d '{"email":"organizador.a@eventhub.com","password":"password"}'

# 2) Catálogo público
curl http://127.0.0.1:8000/api/eventos -H "Accept: application/json"

# 3) Perfil (reemplaza TOKEN)
curl http://127.0.0.1:8000/api/perfil -H "Accept: application/json" -H "Authorization: Bearer TOKEN"

# 4) Modificar el evento 1 (con el token del Organizador A → 200; con el del B → 403)
curl -X PUT http://127.0.0.1:8000/api/eventos/1 \
  -H "Accept: application/json" -H "Content-Type: application/json" -H "Authorization: Bearer TOKEN" \
  -d '{"titulo":"Laravel Conf 2026","descripcion":"Nueva sinopsis","cupo_maximo":350}'

# 5) Métricas (sólo admin → 200; otros → 403)
curl http://127.0.0.1:8000/api/admin/metricas -H "Accept: application/json" -H "Authorization: Bearer TOKEN"
```

### Ejemplos de respuesta

`POST /api/login` → **200**
```json
{
  "message": "Inicio de sesión exitoso.",
  "token": "1|tKQBu6pnc0Q4Lnq2Xq4Pa9WTNFLgPmQjqJBUhvi629ee5d27",
  "token_type": "Bearer",
  "user": { "id": 2, "name": "Organizador A", "email": "organizador.a@eventhub.com", "role": "organizador" }
}
```

`PUT /api/eventos/1` con el token del Organizador B → **403**
```json
{ "message": "No tienes permiso para modificar este evento: sólo su organizador puede hacerlo." }
```

`PUT /api/eventos/1` con datos inválidos → **422**
```json
{
  "message": "El campo título es obligatorio. (and 1 more error)",
  "errors": {
    "titulo": ["El campo título es obligatorio."],
    "cupo_maximo": ["El campo cupo máximo debe ser al menos 1."]
  }
}
```

`GET /api/admin/metricas` como admin → **200**
```json
{
  "total_eventos": 3,
  "cupo_total_ofertado": 490,
  "total_organizadores": 2,
  "total_usuarios": 4,
  "monto_global_recaudado": 12500000,
  "moneda": "COP"
}
```

> El modelo de datos del enunciado no tiene ventas ni inscripciones, así que `monto_global_recaudado` se toma de `config/eventhub.php` (variable `EVENTHUB_MONTO_RECAUDADO`), igual que en el ejemplo de clase. `total_eventos` y `cupo_total_ofertado` sí se calculan desde la base de datos.

`GET /api/perfil` sin token → **401**
```json
{ "message": "No autenticado: envía un token Bearer válido." }
```

---

## Cómo funciona

### Autenticación (¿quién eres?): Sanctum

```
Cliente (web/móvil)                         API Laravel
  │ POST /api/login {email, password} ──▶  Hash::check(password, hash guardado)
  │ ◀── 200 { token: "1|abc..." }          createToken() guarda el HASH del token en personal_access_tokens
  │
  │ GET /api/perfil
  │ Authorization: Bearer 1|abc... ──────▶  middleware auth:sanctum busca el token → identifica al usuario
  │ ◀── 200 { datos del usuario }           (sin token o token inválido → 401)
```

- **Sin estado:** el servidor no guarda una sesión. Cada petición trae su token en el header `Authorization`, por eso sirve igual para web y para apps móviles.
- La base de datos guarda **el hash** del token y **el hash** de la contraseña (bcrypt): si alguien roba la base de datos, no obtiene ni contraseñas ni tokens utilizables.
- `POST /api/logout` borra el token actual, que deja de servir de inmediato.

### Autorización (¿qué puedes hacer?): Gate vs. Policy

| | **Gate** `ver-metricas` | **Policy** `EventoPolicy::update` |
|---|---|---|
| Pregunta | "¿Este **usuario** puede hacer X?" | "¿Este usuario puede hacer X **sobre este recurso**?" |
| Depende de | El rol (`admin`) | El dueño del evento (`user_id`) |
| Se registra en | `AppServiceProvider::boot()` | `app/Policies/EventoPolicy.php` (Laravel la descubre por convención) |
| Se usa con | `Gate::authorize('ver-metricas')` | `Gate::authorize('update', $evento)` |
| Si falla | **403** | **403** |

Orden en `PUT /api/eventos/{id}`:

1. `auth:sanctum` → ¿hay token válido? Si no, **401**.
2. *Route model binding* → ¿existe el evento? Si no, **404**.
3. `UpdateEventoRequest::authorize()` → **Policy**: ¿eres el dueño? Si no, **403**. Va antes que la validación, así quien no tiene permiso no obtiene información.
4. `UpdateEventoRequest::rules()` → ¿datos válidos? Si no, **422**.
5. Se actualiza → **200**.

Además, `user_id` **no** está en `$fillable` del modelo `Evento`: aunque alguien lo envíe en el JSON, no puede cambiar el dueño de un evento.

---

## Tests

```bash
php artisan test
```

23 tests (70 aserciones): login correcto e incorrecto, contraseñas hasheadas, perfil con y sin token, logout que revoca el token, listado público, el dueño modifica su evento (200), otro organizador / admin / asistente no pueden (403), autorización antes que validación, 422, `user_id` no modificable, 404, métricas para admin (200) y para el resto (403/401), y los datos del seeder.

---

## Estructura

```
app/
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php      # login / logout (Sanctum)
│   │   ├── UserController.php      # perfil
│   │   ├── EventoController.php    # listado público / actualización
│   │   └── MetricaController.php   # métricas (Gate)
│   ├── Requests/
│   │   ├── LoginRequest.php
│   │   └── UpdateEventoRequest.php # authorize() = Policy, rules() = validación
│   └── Resources/                  # formato JSON de User y Evento
├── Models/ (User, Evento)
├── Policies/EventoPolicy.php
└── Providers/AppServiceProvider.php  # Gate ver-metricas
bootstrap/app.php                   # respuestas JSON para 401/403/404/422
config/eventhub.php                 # monto recaudado de referencia
database/migrations/                # users (con role), eventos, personal_access_tokens
database/seeders/DatabaseSeeder.php
docs/EventHub.postman_collection.json
routes/api.php
tests/Feature/
```
