# Plan de Migración VideoConf → Webflow AI Site Builder

---

## 1. Resumen Ejecutivo

Este prompt genera el paquete completo para migrar la UI del proyecto **VideoConf** (Laravel + Vue/Inertia) a **Webflow AI Site Builder**, manteniendo la lógica de negocio intacta. La vista se reconstruye en Webflow CMS con colecciones que imitan exactamente los modelos `Space` y `Reservation`, conectando mediante llamadas fetch a las rutas API existentes (`/api/spaces/{slug}/slots?date=`, POST `/reservations`) y usando Memberstack para auth. No se toca el backend Laravel; solo se sustituye el frontend.

---

## 2. Prompt Final para Webflow AI Site Builder

Reconstruye la interfaz de VideoConf — Sala de Videoconferencia en Webflow CMS sin romper la lógica del backend. Crea dos colecciones CMS: **Spaces** (name, slug, type, capacity, description, price_per_hour, is_active, image_url) y **Reservations** (space_name, user_name, user_email, start_time, end_time, status, notes, slug). Usa `{slug}` como clave URL en ambas. Diseño dark mode minimalista con personalidad propia, estilo Linear.app / Vercel Dashboard: fondo `#06080f`, superficies `#0d1117` y `#111827`, acento principal `#00dcff`, acento secundario `#c8ff00` solo para CTAs secundarios y badges activos, texto `#f0f4f8`, muted `#5a7080`. Sin gradientes llamativos, solo un subtle radial glow detrás del hero. Tipografía Inter una sola familia, con letter-spacing 0.08em uppercase en labels y badges. Grid base 8px, padding de secciones clamp(4rem, 8vw, 8rem), max-width 1200px centrado. Mobile-first, accesibilidad AA.

La página de listado de Spaces carga desde CMS collection list. La página de detalle de Space muestra un calendario de slots que llama a `GET /api/spaces/{slug}/slots?date=YYYY-MM-DD` mediante JS embebido y pinta franjas horarias clickeables con estado disabled/available. Al seleccionar una franja redirige al formulario que envía POST a `/reservations` con payload JSON vía fetch. Integra Memberstack para auth: zona pública sin login (ver slots, reservar con email), zona privada admin (dashboard con listado de reservas y cambio de estado). El admin dashboard consume lista de reservas y permite actualizar mediante PUT `/admin/reservations/{slug}`. Cards con border-radius 12px, border 1px solid rgba(255,255,255,0.06), sin sombras duras. Gaps entre cards 24px desktop / 16px mobile. Animaciones: fade-in con stagger 80ms en cards al cargar, hover cards translateY(-4px) + border cyan tenue, 200ms ease-out. Botón primario con glow pulse `box-shadow: 0 0 12px rgba(0,220,255,0.4)` en hover. Sin animaciones pesadas ni scroll-jacking. Skeleton loading mientras fetch resuelve. Incluye estados vacío y error en cada sección.

---

## 3. Estructura CMS Exacta

### Colección: Spaces

| Campo | Tipo Webflow | Notas |
|---|---|---|
| name | Plain text | Required |
| slug | Plain text (slug) | Usado como `{slug}` en URL |
| type | Plain text | Ej: "sala", "auditorio", "estudio" |
| capacity | Number | Entero |
| description | Rich text | Opcional |
| price_per_hour | Number | Decimal, ej: 25.00 |
| is_active | Switch | true/false |
| image_url | Image | Imagen principal del espacio |

**Ejemplo fila 1:**
| name | slug | type | capacity | description | price_per_hour | is_active | image_url |
|---|---|---|---|---|---|---|---|
| Sala Principal | sala-principal | sala | 10 | Sala principal con equipo 4K | 25.00 | true | https://images.unsplash.com/photo-sala1.jpg |

**Ejemplo fila 2:**
| name | slug | type | capacity | description | price_per_hour | is_active | image_url |
|---|---|---|---|---|---|---|---|
| Auditorio Central | auditorio-central | auditorio | 50 | Auditorio con sonido envolvente | 50.00 | true | https://images.unsplash.com/photo-auditorio1.jpg |

### Colección: Reservations

| Campo | Tipo Webflow | Notas |
|---|---|---|
| space_name | Plain text | Nombre del espacio (denormalizado) |
| user_name | Plain text | Nombre del usuario |
| user_email | Email | Email del usuario |
| start_time | Date/Time | ISO 8601 |
| end_time | Date/Time | ISO 8601 |
| status | Plain text | Valores: pendiente, confirmada, rechazada, cancelada, finalizada |
| notes | Plain text | Opcional |
| slug | Plain text | UUID generado por backend |

**Ejemplo fila 1:**
| space_name | user_name | user_email | start_time | end_time | status | notes | slug |
|---|---|---|---|---|---|---|---|
| Sala Principal | Juan Pérez | juan@email.com | 2026-05-14T09:00:00Z | 2026-05-14T10:00:00Z | confirmada | -- | a1b2c3d4-e5f6-... |

**Ejemplo fila 2:**
| space_name | user_name | user_email | start_time | end_time | status | notes | slug |
|---|---|---|---|---|---|---|---|
| Auditorio Central | María López | maria@email.com | 2026-05-15T14:00:00Z | 2026-05-15T16:00:00Z | pendiente | Proyector necesario | e7f8g9h0-i1j2-... |

---

## 4. CSV para Importar

### spaces.csv

```csv
name,slug,type,capacity,description,price_per_hour,is_active,image_url
Sala Principal,sala-principal,sala,10,Sala principal con equipo 4K y micrófonos inalámbricos,25.00,true,https://images.unsplash.com/photo-1573164713714-d95e436ab8d6?w=800
Auditorio Central,auditorio-central,auditorio,50,Auditorio con sonido envolvente y pantalla gigante,50.00,true,https://images.unsplash.com/photo-1517457373958-b7bdd4587205?w=800
Estudio Creativo,estudio-creativo,estudio,4,Estudio insonorizado para grabación profesional,35.00,true,https://images.unsplash.com/photo-1598488035139-bdbb2231ce04?w=800
Sala Ejecutiva,sala-ejecutiva,sala,6,Sala privada para reuniones ejecutivas,40.00,true,https://images.unsplash.com/photo-1592078615290-033ee584e267?w=800
```

### reservations.csv

```csv
space_name,user_name,user_email,start_time,end_time,status,notes,slug
Sala Principal,Juan Pérez,juan@email.com,2026-05-14T09:00:00Z,2026-05-14T10:00:00Z,confirmada,,a1b2c3d4-e5f6-7890-abcd-ef1234567890
Auditorio Central,María López,maria@email.com,2026-05-15T14:00:00Z,2026-05-15T16:00:00Z,pendiente,Proyector necesario,b2c3d4e5-f6a7-8901-bcde-f12345678901
Estudio Creativo,Pedro García,pedro@email.com,2026-05-16T10:00:00Z,2026-05-16T12:00:00Z,pendiente,,c3d4e5f6-a7b8-9012-cdef-123456789012
Sala Ejecutiva,Ana Torres,ana@email.com,2026-05-17T15:00:00Z,2026-05-17T16:30:00Z,confirmada,Con café por favor,d4e5f6a7-b8c9-0123-defa-234567890123
```

---

## 5. Mapeo de Rutas y Eventos

### 5.1 Listado de Spaces
- **Ubicación:** Página CMS `/` (CMS Collection List de Spaces)
- **No requiere llamada API** — los datos vienen del CMS importado
- **Binding:** Webflow CMS collection list → `name`, `type`, `capacity`, `price_per_hour`, `image_url`

### 5.2 Detalle de Space + Calendario
- **Ubicación:** Página CMS `/spaces/{slug}` (CMS Collection Page)
- **Carga de slots:** JS embebido llama a `GET /api/spaces/{slug}/slots?date=YYYY-MM-DD`
- **Request:** `GET https://tudominio.com/api/spaces/{slug}/slots?date=2026-05-14`
- **Response esperado:**
  ```json
  {
    "slots": [
      { "label": "09:00 -- 10:00", "available": true },
      { "label": "10:00 -- 11:00", "available": false },
      { "label": "11:00 -- 12:00", "available": true }
    ]
  }
  ```
- **Error response:**
  ```json
  { "message": "No hay disponibilidad para esta fecha" }
  ```
- **Slug dinámico:** Se obtiene de `{wfm-cms-item-slug}` o del CMS binding

### 5.3 Crear Reserva
- **Ubicación:** Página estática `/reservar` con formulario
- **Método:** `POST /reservations`
- **Headers:** `Content-Type: application/json`, `Accept: application/json`
- **Request body:**
  ```json
  {
    "space_id": 1,
    "user_name": "Juan Pérez",
    "user_email": "juan@email.com",
    "start_time": "2026-05-14T09:00:00",
    "duration": 60,
    "notes": ""
  }
  ```
- **Response esperado (201):**
  ```json
  {
    "slug": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
    "message": "Reserva creada exitosamente"
  }
  ```
- **Error response (422):**
  ```json
  {
    "message": "Error de validación",
    "errors": {
      "user_name": ["El nombre es obligatorio"],
      "start_time": ["La fecha y hora no está disponible"]
    }
  }
  ```

### 5.4 Admin — Dashboard
- **Ubicación:** Página privada `/admin` (con Memberstack)
- **Carga de métricas:** `GET /admin` (si se expone como API) o embed desde CMS
- **Alternativa:** Usar CMS collection de Reservations filtrada por status

### 5.5 Admin — Actualizar Reserva
- **Ubicación:** Página privada `/admin/reservas/{slug}`
- **Método:** `PUT /admin/reservations/{slug}`
- **Request body:**
  ```json
  {
    "status": "confirmada",
    "notes": "Cambio de estado aprobado"
  }
  ```
- **Response esperado:**
  ```json
  {
    "message": "Reserva actualizada",
    "reservation": { "slug": "...", "status": "confirmada" }
  }
  ```

---

## 6. Snippets Listos

### 6a. JS para Obtener Slots y Pintar Calendario

Pegar en la página de detalle de Space dentro de `</body>` o en Page Settings → Custom Code → Footer.

```html
<script>
// VideoConf: Cargar slots del backend y pintar calendario
(async function() {
  const SLUG = '{wfm-cms-item-slug}'; // Webflow CMS slug binding
  const container = document.getElementById('slots-container');
  const datePicker = document.getElementById('slot-date-picker');
  if (!container) return;

  function getDate() {
    return datePicker ? datePicker.value : new Date().toISOString().split('T')[0];
  }

  async function loadSlots(date) {
    container.innerHTML = '<p class="text-secondary" aria-live="polite">Cargando disponibilidad...</p>';
    try {
      const res = await fetch(`/api/spaces/${SLUG}/slots?date=${date}`);
      if (!res.ok) throw new Error('Error al cargar disponibilidad');
      const data = await res.json();
      if (!data.slots || data.slots.length === 0) {
        container.innerHTML = '<p class="text-warning" role="alert">No hay disponibilidad para esta fecha.</p>';
        return;
      }
      container.innerHTML = '<ul class="slots-grid" role="listbox" aria-label="Franjas disponibles">' +
        data.slots.map(s => `
          <li role="option" aria-selected="false" class="slot ${s.available ? 'available' : 'occupied'}">
            ${s.available
              ? `<button class="slot-btn" data-label="${s.label}" aria-label="Reservar ${s.label}">${s.label}</button>`
              : `<span class="slot-occupied">${s.label} — ocupado</span>`
            }
          </li>
        `).join('') + '</ul>';
      // Click handler para slots disponibles
      container.querySelectorAll('.slot-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const start = this.dataset.label.split(' -- ')[0];
          window.location.href = `/reservar?space=${SLUG}&start=${date}T${start}:00&duration=60`;
        });
      });
    } catch (err) {
      container.innerHTML = `<p class="text-error" role="alert">Error: ${err.message}. Intente de nuevo.</p>`;
    }
  }

  await loadSlots(getDate());
  if (datePicker) {
    datePicker.addEventListener('change', function() { loadSlots(this.value); });
  }
})();
</script>
```

### 6b. Form Action para Enviar Reserva

Pegar en la página `/reservar` (formulario de creación de reserva).

```html
<script>
// VideoConf: Enviar reserva al backend
document.getElementById('reservation-form').addEventListener('submit', async function(e) {
  e.preventDefault();
  const btn = this.querySelector('button[type="submit"]');
  const msg = document.getElementById('reservation-message');
  btn.disabled = true;
  btn.textContent = 'Enviando...';
  msg.innerHTML = '';

  const payload = {
    space_id: parseInt(document.getElementById('space-id').value),
    user_name: document.getElementById('user-name').value.trim(),
    user_email: document.getElementById('user-email').value.trim(),
    start_time: document.getElementById('start-time').value,
    duration: parseInt(document.getElementById('duration').value),
    notes: document.getElementById('notes').value.trim()
  };

  try {
    const res = await fetch('/reservations', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!res.ok) {
      const errors = data.errors ? Object.values(data.errors).flat().join('<br>') : data.message;
      msg.innerHTML = `<div class="alert-error" role="alert">${errors}</div>`;
      btn.disabled = false;
      btn.textContent = 'Reservar';
      return;
    }
    msg.innerHTML = `<div class="alert-success" role="alert">✓ Reserva creada. ID: ${data.slug}</div>`;
    this.reset();
    setTimeout(() => { window.location.href = `/reservas/${data.slug}`; }, 1500);
  } catch (err) {
    msg.innerHTML = `<div class="alert-error" role="alert">Error de conexión: ${err.message}</div>`;
    btn.disabled = false;
    btn.textContent = 'Reservar';
  }
});
</script>
```

HTML del formulario (estructura mínima requerida):
```html
<form id="reservation-form" novalidate>
  <input type="hidden" id="space-id" value="1">
  <label for="user-name">Nombre</label>
  <input type="text" id="user-name" required aria-required="true">
  <label for="user-email">Email</label>
  <input type="email" id="user-email" required aria-required="true">
  <label for="start-time">Fecha y hora inicio</label>
  <input type="datetime-local" id="start-time" required aria-required="true">
  <label for="duration">Duración (minutos)</label>
  <select id="duration">
    <option value="30">30 min</option>
    <option value="60" selected>1 hora</option>
    <option value="120">2 horas</option>
    <option value="240">4 horas</option>
  </select>
  <label for="notes">Notas</label>
  <textarea id="notes"></textarea>
  <button type="submit">Reservar</button>
  <div id="reservation-message"></div>
</form>
```

### 6c. Snippet para Dashboard Admin con Webhook

```html
<script>
// VideoConf: Dashboard admin — refrescar cada 30s vía webhook simulado
(function() {
  const list = document.getElementById('admin-reservations-list');
  if (!list) return;

  async function refreshDashboard() {
    list.setAttribute('aria-busy', 'true');
    try {
      const res = await fetch('/admin/reservations?status=pendiente&per_page=10', {
        headers: { 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error('Error al cargar reservas');
      const data = await res.json();
      // data.data debe ser un array de reservas
      if (!data.data || data.data.length === 0) {
        list.innerHTML = '<tr><td colspan="5" class="empty-state">No hay reservas pendientes</td></tr>';
        return;
      }
      list.innerHTML = data.data.map(r => `
        <tr>
          <td>${r.user_name}</td>
          <td>${r.space_name}</td>
          <td>${new Date(r.start_time).toLocaleString()}</td>
          <td><span class="status-badge status-${r.status}">${r.status}</span></td>
          <td><a href="/admin/reservas/${r.slug}" class="btn-sm">Ver</a></td>
        </tr>
      `).join('');
    } catch (err) {
      list.innerHTML = `<tr><td colspan="5" class="text-error" role="alert">${err.message}</td></tr>`;
    } finally {
      list.removeAttribute('aria-busy');
    }
  }

  refreshDashboard();
  setInterval(refreshDashboard, 30000); // Refrescar cada 30s
})();
</script>
```

---

## 7. Guía de Diseño

### Paleta de Colores (Dark Mode por defecto)

| Token | Hex | Uso |
|---|---|---|
| `--bg-base` | `#06080f` | Fondo principal de página |
| `--bg-elevated` | `#0d1117` | Superficies de cards y secciones |
| `--bg-hover` | `#111827` | Hover de cards y filas de tabla |
| `--border-subtle` | `rgba(255,255,255,0.06)` | Bordes de cards y contenedores |
| `--border-hover` | `rgba(0,220,255,0.2)` | Borde en hover de cards |
| `--text-primary` | `#f0f4f8` | Texto principal (body, headings) |
| `--text-muted` | `#5a7080` | Texto secundario, labels, placeholders |
| `--accent-cyan` | `#00dcff` | Acción primaria, links, focus rings, hover borders |
| `--accent-lime` | `#c8ff00` | Badges confirmada, CTAs secundarios, highlights |
| `--error` | `#ff4444` | Slots ocupados, errores, badge rechazada |
| `--warning` | `#ffaa00` | Badge pendiente, advertencias |
| `--hero-glow` | `radial-gradient(ellipse at 50% 0%, rgba(0,220,255,0.08) 0%, transparent 70%)` | Glow sutil detrás del hero |

### Tipografía

| Elemento | Font | Size | Weight | Line-height | Letter-spacing |
|---|---|---|---|---|---|
| H1 | Inter, sans-serif | `clamp(2rem, 5vw, 3.5rem)` | 700 | 1.15 | normal |
| H2 | Inter, sans-serif | `clamp(1.5rem, 3vw, 2rem)` | 600 | 1.25 | normal |
| H3 | Inter, sans-serif | `1.125rem` | 600 | 1.4 | normal |
| Body | Inter, sans-serif | `1rem` | 300 | 1.6 | normal |
| Body small | Inter, sans-serif | `0.875rem` | 300 | 1.6 | normal |
| Label / Badge | Inter, sans-serif | `0.75rem` | 500 | 1.4 | `0.08em` uppercase |
| Button | Inter, sans-serif | `0.875rem` | 500 | 1 | normal |

Solo una familia tipográfica: **Inter** (o Geist si está disponible en Webflow). Sin mezclar.

### Layout y Espaciado

```css
:root {
  --grid-base: 8px;
  --section-padding: clamp(4rem, 8vw, 8rem);
  --content-max: 1200px;
  --card-radius: 12px;
  --card-gap-desktop: 24px;
  --card-gap-mobile: 16px;
}

.container {
  max-width: var(--content-max);
  margin: 0 auto;
  padding: 0 var(--grid-base);
}

section {
  padding: var(--section-padding) 0;
}
```

### Cards

```css
.card {
  background: var(--bg-elevated);
  border: 1px solid var(--border-subtle);
  border-radius: var(--card-radius);
  padding: 1.5rem;
  transition: transform 200ms ease-out, border-color 200ms ease-out;
}
.card:hover {
  transform: translateY(-4px);
  border-color: var(--border-hover);
}
```

### Botones

```css
.btn-primary {
  background: var(--accent-cyan);
  color: #06080f;
  border: none;
  padding: 0.625rem 1.25rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: box-shadow 200ms ease-out, opacity 200ms ease-out;
}
.btn-primary:hover {
  box-shadow: 0 0 12px rgba(0, 220, 255, 0.4);
  opacity: 0.9;
}
.btn-primary:focus-visible {
  outline: 2px solid var(--accent-cyan);
  outline-offset: 2px;
}
.btn-primary:disabled {
  opacity: 0.35;
  cursor: not-allowed;
  box-shadow: none;
}

.btn-secondary {
  background: transparent;
  color: var(--accent-cyan);
  border: 1px solid rgba(0, 220, 255, 0.3);
  padding: 0.625rem 1.25rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: border-color 200ms ease-out, background 200ms ease-out;
}
.btn-secondary:hover {
  border-color: var(--accent-cyan);
  background: rgba(0, 220, 255, 0.06);
}

.btn-ghost {
  background: transparent;
  color: var(--text-muted);
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 400;
  cursor: pointer;
  transition: color 200ms ease-out, background 200ms ease-out;
}
.btn-ghost:hover {
  color: var(--text-primary);
  background: rgba(255, 255, 255, 0.04);
}
```

### Slots Grid

```css
.slots-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
  gap: 12px;
  list-style: none;
  padding: 0;
}
.slot-btn {
  width: 100%;
  padding: 0.75rem 0.5rem;
  border: 1px solid rgba(0, 220, 255, 0.2);
  border-radius: 8px;
  background: rgba(0, 220, 255, 0.04);
  color: var(--accent-cyan);
  cursor: pointer;
  font-size: 0.8125rem;
  font-weight: 400;
  transition: background 200ms ease-out, border-color 200ms ease-out, color 200ms ease-out;
}
.slot-btn:hover {
  background: var(--accent-cyan);
  border-color: var(--accent-cyan);
  color: #06080f;
}
.slot-btn:focus-visible {
  outline: 2px solid var(--accent-lime);
  outline-offset: 2px;
}
.slot-occupied {
  display: block;
  padding: 0.75rem 0.5rem;
  border: 1px solid rgba(255, 68, 68, 0.2);
  border-radius: 8px;
  color: var(--error);
  opacity: 0.5;
  font-size: 0.8125rem;
  cursor: not-allowed;
  text-align: center;
}
```

### Status Badges (uppercase, letter-spaced)

```css
.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.2rem 0.625rem;
  border-radius: 999px;
  font-size: 0.6875rem;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}
.status-pendiente { background: rgba(255,170,0,0.12); color: #ffaa00; }
.status-confirmada { background: rgba(200,255,0,0.12); color: #c8ff00; }
.status-rechazada { background: rgba(255,68,68,0.12); color: #ff4444; }
.status-cancelada { background: rgba(90,112,128,0.15); color: #5a7080; }
.status-finalizada { background: rgba(0,220,255,0.1); color: #00dcff; }
```

### Hero Glow (subtle radial)

```css
.hero-section {
  position: relative;
  background: var(--bg-base);
}
.hero-section::before {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0;
  height: 60vh;
  background: radial-gradient(ellipse at 50% 0%, rgba(0,220,255,0.08) 0%, transparent 70%);
  pointer-events: none;
  z-index: 0;
}
```

### Animaciones

```css
/* Fade-in con stagger para listado de cards */
.card {
  opacity: 0;
  transform: translateY(8px);
  animation: cardFadeIn 400ms ease-out forwards;
}
.card:nth-child(1) { animation-delay: 0ms; }
.card:nth-child(2) { animation-delay: 80ms; }
.card:nth-child(3) { animation-delay: 160ms; }
.card:nth-child(4) { animation-delay: 240ms; }
.card:nth-child(5) { animation-delay: 320ms; }
.card:nth-child(6) { animation-delay: 400ms; }
/* Extiende el patrón para más items si es necesario */
.card:nth-child(n+7) { animation-delay: 480ms; }

@keyframes cardFadeIn {
  to { opacity: 1; transform: translateY(0); }
}

/* Skeleton loading */
@keyframes skeletonPulse {
  0%, 100% { opacity: 0.3; }
  50% { opacity: 0.6; }
}
.skeleton {
  background: var(--bg-elevated);
  border-radius: 8px;
  animation: skeletonPulse 1.5s ease-in-out infinite;
}

/* Glow pulse solo en botón primario */
.btn-primary {
  animation: btnGlowPulse 3s ease-in-out infinite;
}
@keyframes btnGlowPulse {
  0%, 100% { box-shadow: 0 0 8px rgba(0,220,255,0.2); }
  50% { box-shadow: 0 0 16px rgba(0,220,255,0.5); }
}
/* Quitar glow pulse si está disabled */
.btn-primary:disabled { animation: none; }
```

### Inputs y Formularios

```css
.input-field {
  width: 100%;
  padding: 0.625rem 0.875rem;
  background: var(--bg-elevated);
  border: 1px solid var(--border-subtle);
  border-radius: 8px;
  color: var(--text-primary);
  font-size: 0.875rem;
  font-weight: 300;
  line-height: 1.6;
  transition: border-color 200ms ease-out;
}
.input-field::placeholder { color: var(--text-muted); }
.input-field:focus {
  outline: none;
  border-color: var(--accent-cyan);
  box-shadow: 0 0 0 3px rgba(0, 220, 255, 0.1);
}
.input-field:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

label {
  display: block;
  font-size: 0.75rem;
  font-weight: 500;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  color: var(--text-muted);
  margin-bottom: 6px;
}

select.input-field {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%235a7080'%3E%3Cpath d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 0.75rem center;
  padding-right: 2.5rem;
}
```

---

## 8. Checklist QA Técnico (10 items)

- [ ] **Rutas CMS vs API:** Verificar que `{slug}` en URLs de Webflow coincida exactamente con el slug del backend (minúsculas, guiones). Probar con "Sala Principal" → "sala-principal".
- [ ] **Import CSV:** Confirmar que los CSV de Spaces y Reservations se importaron correctamente en Webflow CMS. Revisar que `is_active` en Spaces sea booleano y `price_per_hour` sea número decimal.
- [ ] **CORS:** Asegurar que el backend Laravel tiene habilitado CORS para el dominio de Webflow. En `config/cors.php` agregar `'allowed_origins' => ['https://tudominio.webflow.io']`.
- [ ] **Auth Memberstack:** Verificar que las páginas admin (`/admin`, `/admin/reservas/*`) estén protegidas con Memberstack. Probar flujo: login → ver dashboard → logout → redirigido a login.
- [ ] **Forms y payload:** Probar POST `/reservations` desde Webflow. Verificar que `space_id` se mapea correctamente (usar un hidden field con el ID real de la DB, no el slug).
- [ ] **CMS Bindings:** En la página de detalle de Space, confirmar que `{wfm-cms-item-slug}` resuelve al slug correcto. Probar con 3 spaces diferentes.
- [ ] **Responsive:** Probar en 320px, 768px, 1024px, 1440px. El slots grid debe pasar a 1 columna en mobile, 2 en tablet, 3+ en desktop.
- [ ] **ARIA:** Verificar roles (`role="listbox"`, `role="option"`, `aria-selected`, `aria-live="polite"`, `role="alert"`). Navegación por teclado (Tab, Enter, Escape) en slots y formularios.
- [ ] **Performance:** JS snippets deben cargarse con `defer` o al final del body. Slots fetch timeout de 10s. No debe haber render blocking.
- [ ] **Estados vacío/error:** Probar: fecha sin slots → mensaje "No hay disponibilidad". API caída → mensaje "Error de conexión". Lista de reservas vacía → "No hay reservas".

---

## 9. Plan de Rollback

### Si algo falla después de publicar:

1. **Revertir el publish en Webflow:** Ir a Webflow → Settings → Publishing → "Revert to last published backup" para restaurar la versión anterior completa.
2. **Restaurar DNS:** Si cambiaste DNS, reestablecer los registros apuntando al hosting anterior (Laravel con frontend Vue). TTL estimado: 5-10 min.
3. **Desactivar CORS temporal:** Comentar la entrada del dominio Webflow en `config/cors.php` y hacer deploy del backend (`git revert` + `git push`).
4. **Volver al frontend Vue:** Si el frontend Vue/Inertia sigue en el servidor (no se borró), solo DNS apunta a Webflow. Revertir DNS resuelve. Si se borró, restaurar desde `git checkout` y recompilar con `npm run build`.
5. **Restaurar importación CSV:** Si se modificaron datos durante la prueba, reimportar los CSV originales desde el backup de la DB Laravel (usar `php artisan db:seed` o backup SQL).
6. **Checklist post-rollback:** Repetir el checklist QA en la versión restaurada antes de cualquier nuevo intento.

---

## 10. Entregables

| Archivo | Descripción |
|---|---|
| `prompt-webflow-final.txt` | Prompt listo para pegar en Webflow AI Site Builder |
| `spaces.csv` | Datos de ejemplo para importar colección Spaces |
| `reservations.csv` | Datos de ejemplo para importar colección Reservations |
| `snippet-slots.js` | JS para cargar slots del backend |
| `snippet-reservation-form.js` | JS para enviar formulario de reserva |
| `snippet-admin-dashboard.js` | JS para dashboard admin con auto-refresh |
| `design-tokens.css` | Variables CSS, estilos de botones, glassmorphism, badges |
| `README-deploy.md` | Instrucciones de deploy, orden de pasos, verificación |
