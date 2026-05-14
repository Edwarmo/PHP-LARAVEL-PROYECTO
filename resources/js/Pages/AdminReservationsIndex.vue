<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Button } from '@/Components/ui'
import { useFilterNavigation } from '@/composables/useFilterNavigation'
import { formatDate, formatTime } from '@/lib/formatters'
import { exportCsv } from '@/lib/csv'

const props = defineProps({
  reservations: Object,
  spaces: Array,
  filters: Object,
  statuses: Array,
})

const form = ref({
  space_id: props.filters.space_id || '',
  status: props.filters.status || '',
  from: props.filters.from || '',
  to: props.filters.to || '',
})

const statusColors = {
  pendiente: '#F59E0B',
  confirmada: '#00C2CB',
  rechazada: '#EF4444',
  cancelada: '#8B5CF6',
  finalizada: '#22C55E',
}

function statusColor(s) { return statusColors[s] ?? '#9FB2D1' }
function statusText(s) {
  const m = { pendiente: 'Pendiente', confirmada: 'Confirmada', rechazada: 'Rechazada', cancelada: 'Cancelada', finalizada: 'Finalizada' }
  return m[s] ?? s
}

const { navigate } = useFilterNavigation('/admin/reservations')

function applyFilters() { navigate(form.value) }

function downloadCsv() {
  const items = reservations?.data ?? []
  exportCsv(items,
    ['Sala', 'Usuario', 'Fecha', 'Inicio', 'Fin', 'Estado'],
    [
      r => r.space_name, r => r.user_name,
      r => formatDate(r.start_time), r => formatTime(r.start_time),
      r => r.end_time ? formatTime(r.end_time) : '—',
      r => r.status,
    ], 'reservas.csv'
  )
}
</script>

<template>
  <Head title="Gestión de Reservas" />
  <PublicLayout>
    <div class="dashboard-container">
      <header class="topbar">
        <div class="brand">
          <div class="brand-mark">R</div>
          <div>
            <div class="eyebrow">Reservas</div>
            <h1 class="brand-title">Gestión de Reservas</h1>
            <p class="brand-sub">Control administrativo y flujo de solicitudes</p>
          </div>
        </div>
        <div class="topbar-actions">
          <Link href="/admin" class="ghost-btn">← Volver al panel</Link>
        </div>
      </header>

      <article class="card section-card">
        <div class="section-header">
          <div>
            <h2 class="section-title">Filtros de búsqueda</h2>
            <p class="section-subtitle">Espacio, estado y rango de fechas</p>
          </div>
        </div>

        <div class="filter-grid">
          <div class="filter-group">
            <label class="filter-label">Espacio</label>
            <select v-model="form.space_id" class="filter-select">
              <option value="">Todos los espacios</option>
              <option v-for="space in spaces" :key="space.id" :value="space.id">{{ space.name }}</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="filter-label">Estado</label>
            <select v-model="form.status" class="filter-select">
              <option value="">Todos los estados</option>
              <option v-for="s in statuses" :key="s" :value="s">{{ statusText(s) }}</option>
            </select>
          </div>
          <div class="filter-group">
            <label class="filter-label">Desde</label>
            <input type="date" v-model="form.from" class="filter-input" />
          </div>
          <div class="filter-group">
            <label class="filter-label">Hasta</label>
            <input type="date" v-model="form.to" class="filter-input" />
          </div>
          <div class="filter-actions">
            <button class="export-btn" @click="applyFilters">Filtrar</button>
            <button class="ghost-btn" @click="downloadCsv">Exportar CSV</button>
          </div>
        </div>
      </article>

      <div style="height:20px"></div>

      <article class="card section-card">
        <div class="section-header">
          <div>
            <h2 class="section-title">Reservas</h2>
            <p class="section-subtitle">{{ reservations?.data?.length ?? 0 }} resultados</p>
          </div>
          <div v-if="reservations?.last_page > 1" class="flex items-center gap-3 font-mono text-xs">
            <Link v-if="reservations.prev_page_url" :href="reservations.prev_page_url" class="pagination-link">← Anterior</Link>
            <span style="color:#5AE6FF">{{ reservations.current_page }} / {{ reservations.last_page }}</span>
            <Link v-if="reservations.next_page_url" :href="reservations.next_page_url" class="pagination-link">Siguiente →</Link>
          </div>
        </div>

        <!-- Skeleton -->
        <div v-if="!reservations" class="space-y-3">
          <div v-for="i in 5" :key="i" class="skeleton-row"></div>
        </div>

        <div v-else-if="!reservations.data.length" class="empty-state">— NO SE ENCONTRARON RESERVAS —</div>

        <div v-else class="table-wrap">
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
              <tr v-for="r in reservations.data" :key="r.slug">
                <td class="cell-room">{{ r.space_name }}</td>
                <td>{{ r.user_name }}</td>
                <td>{{ formatDate(r.start_time) }}</td>
                <td>{{ formatTime(r.start_time) }}</td>
                <td>{{ r.end_time ? formatTime(r.end_time) : '—' }}</td>
                <td>
                  <span class="badge-custom" :style="{ background: statusColor(r.status) + '22', borderColor: statusColor(r.status) + '44', color: statusColor(r.status) }">{{ statusText(r.status) }}</span>
                </td>
                <td>
                  <Link :href="`/admin/reservations/${r.slug}`" class="action-link">Ver</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="reservations?.last_page > 1" class="table-meta" style="margin-top:16px;text-align:center">
          <Link v-if="reservations.prev_page_url" :href="reservations.prev_page_url" class="pagination-link">← Anterior</Link>
          <span style="color:#5AE6FF;margin:0 16px">{{ reservations.current_page }} / {{ reservations.last_page }}</span>
          <Link v-if="reservations.next_page_url" :href="reservations.next_page_url" class="pagination-link">Siguiente →</Link>
        </div>
      </article>
    </div>
  </PublicLayout>
</template>

<style scoped>
.skeleton-row { height:60px; border-radius:14px; background:rgba(255,255,255,0.03); animation:pulse 1.5s ease infinite; }
@keyframes pulse { 0%,100%{opacity:.4} 50%{opacity:.2} }
</style>
