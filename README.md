# PokeApp

Aplicacion web tipo SPA construida con Laravel + Vue usando una API propia en Laravel como proxy hacia PokeAPI.

El proyecto evoluciono por fases, incorporando desacoplamiento backend, cache, manejo de errores, proteccion de API, testing y despliegue con contenedores.

## Demo

- Buscar por nombre de Pokemon (ejemplo: pikachu, charizard, gengar).
- Endpoint consumido por frontend: /api/pokemon/{name}

## Que demuestra este proyecto

- Consumo HTTP con Axios desde componente Vue contra API propia.
- API proxy en Laravel hacia PokeAPI con contrato desacoplado.
- Cache de respuestas en Redis con TTL configurable.
- Manejo de errores 404 / 502 / 504.
- Rate limiting por IP para proteger el endpoint.
- Logs estructurados para observabilidad de errores upstream.
- Estados de interfaz claros: loading, error y success.
- Render dinamico de datos remotos (nombre, sprite, altura, peso).
- Integracion Laravel + Vite + Vue SFC.
- Base limpia y facil de extender con rutas, tests o nuevas vistas.

## Historial por fases

### Fase 1 - Activacion de API interna

- Se habilitaron rutas API en bootstrap para exponer /api/*.
- Se creo routes/api.php con el endpoint GET /api/pokemon/{name}.
- Se implemento PokemonController como base inicial del flujo backend.

### Fase 2 - Proxy robusto + cache

- Normalizacion del parametro name (trim + lowercase) y validacion de formato.
- Integracion con PokeAPI mediante Http Client de Laravel.
- Retry y timeout configurables para resiliencia.
- Cache::remember con TTL configurable (acotado entre 60 y 300 segundos).
- Mapeo de errores:
	- 404 externo -> 404 interno con mensaje consistente.
	- timeout/conexion -> 504.
	- error upstream no-404 -> 502.
- Contrato desacoplado de salida para frontend:
	- name, height, weight, sprite, types.

### Fase 3 - Frontend migrado a API propia

- El componente Vue dejo de consumir pokeapi.co directamente.
- Axios ahora consulta /api/pokemon/{name}.
- Se mantuvieron estados loading/error/success.
- Se adaptaron mensajes de error segun codigos del backend (404, 422, 502, 504).
- Se ajusto el render para usar el nuevo contrato (pokemon.sprite).

### Fase 4 - Proteccion y observabilidad

- Rate limiter dedicado pokemon-api por IP.
- Middleware throttle:pokemon-api aplicado al endpoint.
- Logging estructurado en backend para:
	- parametro invalido,
	- no encontrado,
	- timeout upstream,
	- error upstream,
	- excepcion no controlada.

### Fase 5 - Cobertura de pruebas del endpoint

- Se agrego suite de tests de Feature para la API de Pokemon.
- Casos cubiertos:
	- 200 con campos mapeados,
	- 404 con mensaje consistente,
	- 504 por timeout,
	- 502 por error upstream,
	- cache hit evita segunda llamada upstream,
	- 429 por rate limit.

### Fase 6 - Infra y despliegue base

- Cache cambiada a Redis para entorno local/prod.
- Docker basico agregado:
	- php-fpm,
	- nginx,
	- redis,
	- mysql.
- Health check operativo en /up.
- Configuracion base de Railway con healthcheck.
- Documentacion de despliegue en Railway y VPS.

## Stack

- PHP 8+
- Laravel 12
- Vue 3
- Axios
- Vite 7
- Tailwind CSS 4
- Redis
- Docker (php-fpm + nginx + redis + mysql)

## Arquitectura resumida

- Backend Laravel sirve la vista raiz.
- Frontend Vue se monta sobre un nodo unico en la pagina.
- El componente principal consume /api/pokemon/{name}.
- Laravel consulta PokeAPI, aplica cache y devuelve solo los campos necesarios al cliente.
- Redis almacena respuestas de Pokemon por TTL para reducir latencia y llamadas a terceros.

Archivos clave:

- resources/js/app.js
- resources/js/components/PokemonSearch.vue
- resources/views/welcome.blade.php
- routes/web.php
- routes/api.php
- app/Http/Controllers/PokemonController.php
- app/Providers/AppServiceProvider.php
- docker-compose.yml
- Dockerfile
- railway.json

## Instalacion y ejecucion

### 1) Clonar e instalar dependencias

```bash
composer install
npm install
```

### 2) Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

En Windows PowerShell puedes usar:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

### 3) Levantar en desarrollo

En una terminal:

```bash
php artisan serve
```

En otra terminal:

```bash
npm run dev
```

Abrir en navegador:

- http://127.0.0.1:8000

## Docker (entorno local/prod base)

Este repo incluye stack Docker base con:

- app: php-fpm
- nginx: servidor web
- redis: cache
- db: mysql

### Levantar contenedores

```bash
docker compose up -d --build
```

### Preparar Laravel dentro del contenedor

```bash
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
```

### Acceso y health check

- App: http://localhost:8080
- Health check: http://localhost:8080/up

## Variables de entorno recomendadas

Estas variables ya estan preparadas para el proxy y rendimiento:

- CACHE_STORE=redis
- REDIS_HOST=redis (Docker) o 127.0.0.1 (local sin Docker)
- POKEAPI_BASE_URL=https://pokeapi.co/api/v2
- POKEAPI_TIMEOUT=5
- POKEAPI_RETRY_TIMES=2
- POKEAPI_RETRY_SLEEP_MS=200
- POKEMON_CACHE_TTL_SECONDS=180
- POKEMON_API_RATE_LIMIT_PER_MINUTE=30

## Deploy en Railway o VPS

### Railway

- Opcion simple: desplegar contenedores con Dockerfile y configurar servicio Redis/MySQL administrados en Railway.
- Variables minimas: APP_ENV, APP_DEBUG=false, APP_KEY, APP_URL, DB_*, CACHE_STORE=redis, REDIS_*, POKEAPI_*.
- Health check path recomendado: /up.

### VPS (Ubuntu ejemplo)

- Instalar Docker y Docker Compose.
- Clonar repo y configurar .env de produccion.
- Levantar servicios con docker compose up -d --build.
- Ejecutar migraciones: docker compose exec app php artisan migrate --force.
- Configurar reverse proxy/TLS (Nginx host o Cloudflare Tunnel) apuntando a puerto 8080.
- Monitorear estado via /up y logs de contenedores.

## Decisiones tecnicas

- API proxy en Laravel: evita exponer la API externa en cliente y permite controlar contrato de respuesta.
- Contrato desacoplado: frontend consume solo name, height, weight, sprite, types.
- Cache Redis: menor latencia y menos dependencia del upstream para consultas repetidas.
- TTL acotado (60-300s): equilibrio entre frescura de datos y performance.
- Manejo de errores normalizado: 404 (no encontrado), 502 (upstream error), 504 (timeout).
- Rate limit por IP: proteccion basica ante abuso y picos.
- Logs estructurados: facilita troubleshooting y observabilidad en produccion.

## Build de produccion

```bash
npm run build
```

## Testing

Ejecutar pruebas del endpoint de Pokemon:

```bash
php artisan test tests/Feature/PokemonApiTest.php
```

## Proximas mejoras (roadmap)

- Mostrar tipos y estadisticas base del Pokemon.
- Agregar historial de busquedas recientes.
- Implementar tests unitarios del componente.
- Crear pagina detalle por Pokemon con Vue Router.
- CI/CD para test + build + deploy.
- Metricas de latencia y tasa de aciertos de cache.

## Licencia

Proyecto de uso educativo.
