<script setup>
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { useAdminDashboard } from '@/composables/useAdminDashboard'

const props = defineProps({
  metrics: Object,
  pendientes: Array,
  proximasReservas: Array,
  recientes: Array,
})

const {
  searchQuery, statusFilter,
  filteredReservations, exportCsv,
  metricLabels, metricColors, statusBadgeColors,
  formatDate, formatTime
} = useAdminDashboard()

const metricsKeys = ['pendientes', 'confirmadas', 'hoy', 'esta_semana']

function statusColor(status) {
  return statusBadgeColors[status] ?? '#9FB2D1'
}

function formatHour(iso) {
  return new Date(iso).toTimeString().slice(0, 5)
}

function statusText(status) {
  const map = {
    pendiente: 'Pendiente', confirmada: 'Confirmada',
    rechazada: 'Rechazada', cancelada: 'Cancelada', finalizada: 'Finalizada'
  }
  return map[status] ?? status
}
</script>

<template>
  <Head title="Panel de Control" />
  <PublicLayout>
    <div class="dashboard-container">
      <header class="topbar">
        <div class="brand">
          <div class="brand-mark">VC</div>
          <div>
            <div class="eyebrow">Control Center</div>
            <h1 class="brand-title">VideoConf Dashboard</h1>
            <p class="brand-sub">Panel de control con métricas en tiempo real y gestión de reservas</p>
          </div>
        </div>
        <div class="topbar-actions">
          <Link href="/admin/calendar" class="ghost-btn">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Calendario
          </Link>
          <Link href="/admin/reservations" class="ghost-btn">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Reservas
          </Link>
        </div>
      </header>

      <section class="metrics-grid">
        <article
          v-for="key in metricsKeys"
          :key="key"
          class="card metric-card"
          :style="{ '--metric-accent': metricColors[key] }"
        >
          <div class="metric-label">{{ metricLabels[key] }}</div>
          <div class="metric-value" :style="{ color: metricColors[key] }">{{ metrics[key] ?? 0 }}</div>
          <div class="metric-trend">{{ key === 'pendientes' ? 'Requieren atención' : key === 'confirmadas' ? 'Total confirmadas' : key === 'hoy' ? 'Programadas hoy' : 'Actividad semanal' }}</div>
        </article>
      </section>

      <section class="main-grid">
        <div class="stack">
          <article class="card section-card">
            <div class="section-header">
              <div>
                <h2 class="section-title">Reservas recientes</h2>
                <p class="section-subtitle">Busca, filtra y exporta el historial de reservas</p>
              </div>
            </div>

            <div class="table-controls">
              <input v-model="searchQuery" class="search-input" type="text" placeholder="Buscar por sala, usuario o fecha..." />
              <select v-model="statusFilter" class="filter-select" aria-label="Filtrar por estado">
                <option value="todas">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="confirmada">Confirmada</option>
                <option value="rechazada">Rechazada</option>
                <option value="cancelada">Cancelada</option>
                <option value="finalizada">Finalizada</option>
              </select>
              <button class="export-btn" @click="exportCsv(recientes)">Exportar CSV</button>
            </div>

            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Sala</th>
                    <th>Usuario</th>
                    <th>Fecha</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Estado</th>
                    <th></th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!filteredReservations(recientes).length">
                    <td colspan="7" class="empty-state">No hay reservas que coincidan con la búsqueda.</td>
                  </tr>
                  <tr v-for="res in filteredReservations(recientes)" :key="res.slug">
                    <td class="cell-room">{{ res.space_name }}</td>
                    <td>{{ res.user_name }}</td>
                    <td>{{ formatDate(res.start_time) }}</td>
                    <td>{{ formatTime(res.start_time) }}</td>
                    <td>{{ res.end_time ? formatTime(res.end_time) : '—' }}</td>
                    <td>
                      <span class="badge-custom" :style="{ background: statusColor(res.status) + '22', borderColor: statusColor(res.status) + '44', color: statusColor(res.status) }">
                        {{ statusText(res.status) }}
                      </span>
                    </td>
                    <td>
                      <Link :href="`/admin/reservations/${res.slug}`" class="action-link">Ver</Link>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="table-meta">{{ filteredReservations(recientes).length }} de {{ recientes.length }} reservas</div>
          </article>
        </div>

        <aside class="stack">
          <article class="card section-card">
            <div class="section-header">
              <div>
                <h2 class="section-title">Estado de reservas</h2>
                <p class="section-subtitle">Distribución por estado</p>
              </div>
            </div>
            <div class="donut-wrap">
              <div class="donut-chart" :style="{
                background: `conic-gradient(${['pendiente','confirmada','rechazada','cancelada','finalizada'].map((s,i) => {
                  const count = recientes.filter(r => r.status === s).length
                  const total = recientes.length || 1
                  const pct = (count / total) * 100
                  const colors = ['#F59E0B','#00C2CB','#EF4444','#8B5CF6','#22C55E']
                  const prev = ['pendiente','confirmada','rechazada','cancelada','finalizada'].slice(0,i).reduce((a,k) => a + (recientes.filter(r => r.status === k).length / total) * 100, 0)
                  return `${colors[i]} ${prev}% ${prev + pct}%`
                }).join(', ')})`
              }">
                <div class="donut-center">
                  <span class="donut-total">{{ recientes.length }}</span>
                  <span class="donut-label">reservas</span>
                </div>
              </div>
            </div>
            <div class="mini-legend">
              <div v-for="status in ['pendiente','confirmada','rechazada','cancelada','finalizada']" :key="status" class="legend-item">
                <div class="legend-left">
                  <span class="legend-dot" :style="{ background: statusColor(status), color: statusColor(status) }"></span>
                  <span>{{ statusText(status) }}</span>
                </div>
                <strong>{{ recientes.filter(r => r.status === status).length }}</strong>
              </div>
            </div>
          </article>

          <article class="card section-card">
            <div class="section-header">
              <div>
                <h2 class="section-title">Próximas reservas de hoy</h2>
                <p class="section-subtitle">Agenda operativa del día</p>
              </div>
            </div>
            <div v-if="!proximasReservas.length" class="empty-state">No hay reservas programadas para hoy.</div>
            <div v-for="res in proximasReservas" :key="res.slug" class="agenda-item">
              <div class="agenda-top">
                <div class="agenda-time">{{ formatHour(res.start_time) }}</div>
                <span class="badge-custom badge-sm" :style="{ background: statusColor(res.status) + '22', borderColor: statusColor(res.status) + '44', color: statusColor(res.status) }">{{ statusText(res.status) }}</span>
              </div>
              <div class="agenda-room">{{ res.space_name }}</div>
              <div class="agenda-meta">{{ res.user_name }}</div>
            </div>
          </article>

          <article class="card section-card">
            <div class="section-header">
              <div>
                <h2 class="section-title">Pendientes</h2>
                <p class="section-subtitle">Reservas que requieren acción</p>
              </div>
            </div>
            <div v-if="!pendientes.length" class="empty-state">— SIN RESERVAS PENDIENTES —</div>
            <div v-for="res in pendientes.slice(0, 5)" :key="res.slug" class="agenda-item">
              <div class="agenda-top">
                <div class="agenda-time">{{ formatHour(res.start_time) }}</div>
                <span class="badge-custom badge-sm" style="background:#F59E0B22;border-color:#F59E0B44;color:#F59E0B">Pendiente</span>
              </div>
              <div class="agenda-room">{{ res.space_name }}</div>
              <div class="agenda-meta">{{ res.user_name }}</div>
            </div>
            <div v-if="pendientes.length > 5" class="table-meta" style="margin-top:12px;text-align:center">
              <Link href="/admin/reservations" style="color:var(--accent,#00C2CB);font-weight:700">Ver todas ({{ pendientes.length }})</Link>
            </div>
          </article>
        </aside>
      </section>
    </div>
  </PublicLayout>
</template>

<style scoped>
.dashboard-container {
  max-width: 1480px;
  margin: 0 auto;
  padding: 10px 24px 24px;
}

.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 24px;
  flex-wrap: wrap;
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
}

.brand-mark {
  width: 50px;
  height: 50px;
  border-radius: 16px;
  background: linear-gradient(135deg, #00C2CB, #5AE6FF);
  display: grid;
  place-items: center;
  color: #041018;
  font-weight: 900;
  font-size: 1.2rem;
  letter-spacing: .06em;
  box-shadow: 0 0 30px rgba(0,194,203,.24);
  flex-shrink: 0;
}

.eyebrow {
  display: inline-flex;
  padding: 4px 10px;
  border-radius: 999px;
  border: 1px solid rgba(0,194,203,.22);
  background: rgba(0,194,203,0.08);
  color: #5AE6FF;
  font-size: .72rem;
  letter-spacing: .08em;
  text-transform: uppercase;
  font-weight: 700;
  margin-bottom: 6px;
}

.brand-title {
  margin: 0;
  font-size: clamp(1.3rem, 2vw, 1.8rem);
  letter-spacing: .3px;
  color: #EAF2FF;
}

.brand-sub {
  margin: 4px 0 0;
  color: #9FB2D1;
  font-size: .9rem;
}

.topbar-actions {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  align-items: center;
}

.ghost-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(20, 29, 47, 0.88);
  color: #EAF2FF;
  border-radius: 14px;
  min-height: 44px;
  padding: 0 16px;
  font-size: .85rem;
  font-weight: 700;
  backdrop-filter: blur(12px);
  cursor: pointer;
  text-decoration: none;
  transition: border-color .2s;
}
.ghost-btn:hover {
  border-color: rgba(0,194,203,.3);
}

.metrics-grid {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 18px;
  margin-bottom: 24px;
}

.card {
  position: relative;
  background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(255,255,255,0.01)), rgba(20, 29, 47, 0.88);
  border: 1px solid rgba(255,255,255,0.08);
  border-radius: 24px;
  box-shadow: 0 20px 48px rgba(0,0,0,.34);
  overflow: hidden;
  backdrop-filter: blur(14px);
}

.card::before {
  content: "";
  position: absolute;
  inset: 0;
  pointer-events: none;
  border-radius: inherit;
  padding: 1px;
  background: linear-gradient(135deg, rgba(0,194,203,.25), rgba(90,230,255,.04), rgba(139,92,246,.18));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite: xor;
  mask-composite: exclude;
}

.metric-card {
  padding: 20px;
  min-height: 130px;
}

.metric-card::after {
  content: "";
  position: absolute;
  right: -22px;
  bottom: -22px;
  width: 110px;
  height: 110px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(0,194,203,.22), transparent 70%);
  filter: blur(6px);
}

.metric-label {
  color: #9FB2D1;
  font-size: .88rem;
  margin-bottom: 12px;
  position: relative;
  z-index: 1;
}

.metric-value {
  font-size: clamp(1.8rem, 2vw, 2.5rem);
  font-weight: 900;
  letter-spacing: .3px;
  margin-bottom: 8px;
  position: relative;
  z-index: 1;
}

.metric-trend {
  font-size: .85rem;
  font-weight: 600;
  color: #9FB2D1;
  position: relative;
  z-index: 1;
}

.main-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.65fr) minmax(320px, .95fr);
  gap: 20px;
  align-items: start;
}

.stack {
  display: grid;
  gap: 20px;
}

.section-card {
  padding: 20px;
  box-shadow: 0 20px 48px rgba(0,0,0,.34), 0 0 0 1px rgba(0,194,203,.14), 0 0 32px rgba(0,194,203,.12);
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 18px;
  flex-wrap: wrap;
}

.section-title {
  margin: 0;
  font-size: 1.05rem;
  font-weight: 800;
  letter-spacing: .01em;
  color: #EAF2FF;
}

.section-subtitle {
  margin: 4px 0 0;
  color: #9FB2D1;
  font-size: .88rem;
}

.table-controls {
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
  margin-bottom: 16px;
}

.search-input, .filter-select, .export-btn {
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(13, 20, 35, 0.9);
  color: #EAF2FF;
  border-radius: 14px;
  min-height: 44px;
  padding: 0 14px;
  outline: none;
  backdrop-filter: blur(12px);
  font-size: .88rem;
}

.search-input { flex: 1 1 240px; }
.filter-select { min-width: 170px; cursor: pointer; }
.filter-select option { background: #0A0E1A; color: #EAF2FF; }

.export-btn {
  background: linear-gradient(135deg, #00C2CB, #5AE6FF);
  color: #041018;
  border-color: transparent;
  font-weight: 900;
  cursor: pointer;
  box-shadow: 0 0 24px rgba(0,194,203,.22);
  min-width: 140px;
}

.table-wrap {
  width: 100%;
  overflow: auto;
  border-radius: 18px;
  border: 1px solid rgba(255,255,255,0.08);
  background: rgba(27, 39, 64, 0.92);
}

table {
  width: 100%;
  border-collapse: collapse;
  min-width: 760px;
}

thead th {
  text-align: left;
  font-size: .78rem;
  color: #9FB2D1;
  font-weight: 800;
  padding: 14px 16px;
  text-transform: uppercase;
  letter-spacing: .06em;
  border-bottom: 1px solid rgba(255,255,255,0.08);
  background: rgba(255,255,255,0.02);
}

tbody td {
  padding: 12px 16px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
  font-size: .9rem;
  color: #EAF2FF;
}

tbody tr:hover {
  background: rgba(255,255,255,0.025);
}

.cell-room {
  font-weight: 700;
}

.badge-custom {
  display: inline-flex;
  align-items: center;
  padding: 5px 10px;
  border-radius: 999px;
  font-size: .76rem;
  font-weight: 800;
  letter-spacing: .02em;
  text-transform: capitalize;
  border: 1px solid;
}
.badge-sm {
  padding: 3px 8px;
  font-size: .7rem;
}

.action-link {
  color: #00C2CB;
  font-weight: 700;
  font-size: .82rem;
  text-decoration: none;
  transition: opacity .2s;
}
.action-link:hover { opacity: .7; }

.empty-state {
  padding: 24px;
  text-align: center;
  color: #9FB2D1;
  font-size: .9rem;
}

.table-meta {
  margin-top: 12px;
  color: #9FB2D1;
  font-size: .85rem;
}

.donut-wrap {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.donut-chart {
  width: 180px;
  height: 180px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  flex-shrink: 0;
}

.donut-center {
  display: flex;
  flex-direction: column;
  align-items: center;
  background: rgba(20, 29, 47, 0.88);
  width: 100px;
  height: 100px;
  border-radius: 50%;
  justify-content: center;
}

.donut-total {
  font-size: 1.8rem;
  font-weight: 900;
  color: #EAF2FF;
  line-height: 1;
}

.donut-label {
  font-size: .72rem;
  color: #9FB2D1;
  text-transform: uppercase;
  letter-spacing: .06em;
  margin-top: 2px;
}

.mini-legend {
  display: grid;
  gap: 8px;
  margin-top: 14px;
  width: 100%;
}

.legend-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
  padding: 8px 12px;
  border-radius: 14px;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.08);
}

.legend-left {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  font-weight: 700;
  font-size: .85rem;
  color: #EAF2FF;
}

.legend-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  display: inline-block;
  box-shadow: 0 0 10px currentColor;
  flex-shrink: 0;
}

.agenda-item {
  padding: 12px 0;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}

.agenda-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.agenda-top {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 8px;
}

.agenda-time {
  min-width: 52px;
  font-weight: 900;
  font-size: .95rem;
  color: #5AE6FF;
  text-shadow: 0 0 14px rgba(90,230,255,.22);
}

.agenda-room {
  font-weight: 800;
  font-size: .9rem;
  margin-bottom: 3px;
  color: #EAF2FF;
}

.agenda-meta {
  color: #9FB2D1;
  font-size: .82rem;
}

@media (max-width: 1240px) {
  .metrics-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .main-grid { grid-template-columns: 1fr; }
}

@media (max-width: 720px) {
  .dashboard-container { padding: 8px 16px 16px; }
  .metrics-grid { grid-template-columns: 1fr; }
  .topbar { align-items: stretch; }
  .topbar-actions { width: 100%; }
  .section-card { padding: 16px; }
  .table-controls { flex-direction: column; }
  .filter-select, .export-btn, .search-input { width: 100%; }
  .brand-mark { width: 40px; height: 40px; font-size: 1rem; }
}
</style>
