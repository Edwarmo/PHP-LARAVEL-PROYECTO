# Sistema de Reservas de Salas de Videoconferencia

Sistema web profesional para la gestión y reserva de espacios de videoconferencia.  
**Stack:** Laravel 13 · Vue 3 · Inertia.js · shadcn/ui · TailwindCSS v4 · Vite 8 · pnpm · PostgreSQL · Docker (nginx+fpm)

---

## Stack técnico

| Capa | Tecnología |
|------|-----------|
| Backend | Laravel 13 (PHP 8.4) |
| Frontend | Vue 3 + Inertia.js SPA + SSR |
| UI | shadcn/vue + TailwindCSS v4 + tw-animate-css |
| Build | Vite 8 + pnpm 10 + code splitting |
| BD | PostgreSQL 14+ (Supabase) |
| Cache | Redis (Upstash) vía middleware opcional |
| Contenedor | Docker multi-stage (node:22 → php:8.4-fpm-alpine + nginx) |

---

## Requisitos

- PHP 8.3+, Composer 2.x
- Node.js 22+, pnpm 10+
- PostgreSQL 14+ (o Supabase)
- Redis (opcional, para caché)
- Docker (opcional)

---

## Instalación local

```bash
# 1. Clonar
git clone https://github.com/Edwarmo/PHP-LARAVEL-PROYECTO.git
cd PHP-LARAVEL-PROYECTO

# 2. Entorno
cp .env.example .env

# 3. PHP deps
composer install

# 4. App key
php artisan key:generate

# 5. Configurar DB en .env
DB_CONNECTION=pgsql
DB_HOST=aws-0-us-east-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.tu_proyecto
DB_PASSWORD=tu_password
DB_SSLMODE=require

# 6. Migraciones + seeders
php artisan migrate --seed

# 7. Frontend (pnpm)
pnpm install
pnpm run build

# 8. Iniciar
php artisan serve
# → http://localhost:8000
```

---

## Docker

El proyecto usa **nginx + PHP-FPM** en Alpine (imagen final ~200MB).

### Build + run

```bash
# Build
docker build -t videoconf .

# Run
docker run -p 8080:8080 \
  -e APP_KEY=base64:$(php artisan key:generate --show) \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=aws-0-us-east-1.pooler.supabase.com \
  -e DB_PORT=6543 \
  -e DB_DATABASE=postgres \
  -e DB_USERNAME=postgres.tu_proyecto \
  -e DB_PASSWORD=tu_password \
  -e DB_SSLMODE=require \
  -e SESSION_DRIVER=cookie \
  --name videoconf-app videoconf
```

### Build rápido (con cache)

```bash
pnpm run deploy:fast
```

### Pre-deploy checks

```bash
pnpm run pre-deploy
# Valida: lockfile → build → typecheck → tests → secrets
```

---

## Scripts disponibles

| Comando | Descripción |
|---------|-------------|
| `pnpm dev` | Dev server Vite |
| `pnpm build` | Build producción |
| `pnpm test` | Tests Vitest |
| `pnpm pre-deploy` | Validación completa pre-push |
| `pnpm deploy` | Docker build desde cero |
| `pnpm deploy:fast` | Docker build con cache |

---

## Arquitectura (Clean Architecture — 4 capas)

```
app/
├── Application/
│   ├── Contracts/             # Interfaces de repositorios
│   ├── Mail/                  # Mailables (ShouldQueue)
│   └── UseCases/              # Lógica de negocio orquestada
│       ├── Admin/             #   Dashboard, Calendar, CRUD
│       ├── Api/               #   Slots API
│       ├── SpaceUseCase.php
│       ├── ReservationUseCase.php
│       └── FinalizeReservationsUseCase.php
├── Domain/
│   └── Models/                # Space, Reservation, User, etc.
├── Infrastructure/
│   ├── Cache/                 # RedisCacheMiddleware
│   ├── Repositories/          # EloquentSpaceRepository, etc.
│   └── Services/              # AvailabilityService
└── Http/
    ├── Controllers/           # Capa delgada (inyectan UseCases)
    └── Middleware/
        └── HandleInertiaRequests.php
```

```
resources/js/
├── app.js                     # Entry point Vue + Inertia
├── bootstrap.js               # Axios config
├── ziggy.js                   # Route definitions (Ziggy)
├── env.d.ts                   # TypeScript declarations
├── types/domain.ts            # Interfaces compartidas
├── lib/
│   ├── formatters.ts          # formatDate, formatCurrency, etc.
│   └── utils.js               # cn() helper (clsx + tailwind-merge)
├── composables/
│   ├── useSlots.ts            # Fetch slots por fecha
│   ├── useFilterNavigation.ts # Filtros con Inertia
│   └── useAnimatedNumber.ts   # Contador animado
├── Components/
│   ├── AnimatedBackground.vue
│   ├── SpaceCard.vue
│   ├── StatusBadge.vue
│   └── ui/                    # shadcn/vue components
│       ├── Button.vue
│       ├── Card.vue / CardHeader / CardTitle / etc.
│       ├── Input.vue / Label.vue
│       ├── Badge.vue
│       ├── Skeleton.vue
│       └── index.js           # Barrel exports
├── Layouts/
│   └── PublicLayout.vue       # Navbar, footer, flash messages
└── Pages/
    ├── Auth/                  # Login, Register
    ├── Spaces/                # Index, Show
    ├── Reservations/          # Create, Show, History
    └── Admin/                 # Dashboard, Calendar, CRUD
```

---

## Code Splitting (Vite 8)

El build produce chunks separados para carga paralela:

| Chunk | Contenido | Tamaño | GZip |
|-------|-----------|--------|------|
| `vendor` | Vue 3 + Inertia.js | ~255 KB | ~90 KB |
| `gsap` | GSAP animations | ~70 KB | ~27 KB |
| `ui` | clsx + tailwind-merge | ~27 KB | ~8 KB |
| `app` | Código de aplicación | ~26 KB | ~9 KB |
| `PublicLayout` | Layout principal | ~16 KB | ~4 KB |
| Páginas (c/u) | Login, Register, etc. | 2-6 KB | ~1-2 KB |

---

## Diseño UI (shadcn/vue + glassmorphism)

- **Tema:** Dark mode por defecto con acentos cyan `#00dcff` y lima `#c8ff00`
- **Glassmorphism:** `backdrop-blur-xl bg-background/80` en navbar, cards con borde sutil
- **Animaciones:** GSAP (carga lazy, ~70KB solo cuando se necesita)
- **Componentes:** shadcn/vue adaptados: Button, Card, Input, Badge, Label, Skeleton
- **Tipografía:** JetBrains Mono (UI) + Cormorant Garamond (headings) + DM Sans (body)
- **Responsive:** Mobile-first con menú hamburguesa

---

## Cache con Redis (opcional)

Middleware `RedisCacheMiddleware` en `Infrastructure/Cache/` disponible para cachear respuestas GET:

```php
// En routes/web.php
Route::middleware(['redis-cache:dashboard_stats,300'])->group(function () {
    Route::get('/admin', [AdminController::class, 'dashboard']);
});
```

Tags y TTLs predefinidos:
| Tag | TTL | Uso |
|-----|-----|-----|
| `user_sessions` | 24h | Sesiones de usuario |
| `menu_cache` | 1h | Navegación |
| `recent_orders` | 30m | Reservas recientes |
| `dashboard_stats` | 5m | Métricas admin |

---

## Rutas

### Públicas
| Método | URL | Descripción |
|--------|-----|-------------|
| GET | `/` | Listado de salas |
| GET | `/spaces/{slug}` | Detalle + slots |
| GET | `/reservations/new` | Formulario reserva |
| POST | `/reservations` | Crear reserva |
| GET | `/reservations/{slug}` | Estado reserva |
| GET | `/historial` | Historial por email |
| GET | `/health` | Health check |

### Admin (requiere auth)
| Método | URL | Descripción |
|--------|-----|-------------|
| GET | `/admin` | Dashboard |
| GET | `/admin/reservations` | Gestionar reservas |
| GET | `/admin/calendar` | Calendario |
| GET | `/admin/spaces` | CRUD salas |
| POST | `/admin/reservations/{slug}/accept` | Aprobar |
| POST | `/admin/reservations/{slug}/reject` | Rechazar |
| POST | `/admin/reservations/{slug}/cancel` | Cancelar |

### API
| Método | URL | Descripción |
|--------|-----|-------------|
| GET | `/api/spaces/{slug}/slots` | Slots libres |

---

## Credenciales default

| Rol | Email | Password |
|-----|-------|----------|
| Admin | `admin@videoconfreservas.com` | `password` |

---

## Despliegue

### Render.com

1. Push a GitHub
2. Render → New Web Service → Docker
3. Variables de entorno (ver `.env.example`)
4. Deploy 🚀

### Cloudflare

- Cache de assets estáticos (nginx ya envía `Cache-Control: public, immutable`)
- Polish images (WebP/AVIF) vía Cloudflare

---

## Solución de problemas

| Error | Causa | Solución |
|-------|-------|----------|
| `ERR_PNPM_IGNORED_BUILDS` | esbuild bloqueado por pnpm | `pnpm approve-builds esbuild && pnpm rebuild esbuild` |
| Assets 404 en producción | Falta `pnpm run build` | Ejecutar antes del deploy |
| `No open ports detected` | Puerto incorrecto | Usar `PORT=8080` |
| `SQLSTATE[08006]` | Puerto DB incorrecto | Usar 6543 (pooler Supabase) |

---

## Licencia

MIT — [Edwarmo](https://github.com/Edwarmo)
