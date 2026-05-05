# PokeApp CV

Aplicacion web tipo SPA construida con Laravel + Vue que consume PokeAPI en tiempo real.

Este proyecto esta pensado para portfolio: demuestra integracion frontend con API REST externa, manejo de estado asincrono y UI reactiva.

## Demo

- Buscar por nombre de Pokemon (ejemplo: pikachu, charizard, gengar).
- Endpoint consumido: https://pokeapi.co/api/v2/pokemon/{name}

## Que demuestra este proyecto

- Consumo HTTP con Axios desde componente Vue.
- Estados de interfaz claros: loading, error y success.
- Render dinamico de datos remotos (nombre, sprite, altura, peso).
- Integracion Laravel + Vite + Vue SFC.
- Base limpia y facil de extender con rutas, tests o nuevas vistas.

## Stack

- PHP 8+
- Laravel 12
- Vue 3
- Axios
- Vite 7
- Tailwind CSS 4

## Arquitectura resumida

- Backend Laravel sirve la vista raiz.
- Frontend Vue se monta sobre un nodo unico en la pagina.
- El componente principal realiza la llamada a PokeAPI y actualiza estado local.

Archivos clave:

- resources/js/app.js
- resources/js/components/PokemonSearch.vue
- resources/views/welcome.blade.php
- routes/web.php

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

## Build de produccion

```bash
npm run build
```

## Proximas mejoras (roadmap)

- Mostrar tipos y estadisticas base del Pokemon.
- Agregar historial de busquedas recientes.
- Implementar tests unitarios del componente.
- Crear pagina detalle por Pokemon con Vue Router.

## Texto para CV / LinkedIn

Desarrolle una SPA con Vue 3 integrada en Laravel que consume una API REST externa (PokeAPI). Implemente manejo de estado asincrono (loading/error/success), peticiones HTTP con Axios y render dinamico de datos en tiempo real.

## Licencia

Proyecto de uso educativo y de portfolio.
