<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  reservations: Array,
  spaces: Array,
  weekDays: Array,
  weekStart: String,
  weekEnd: String,
  prevWeek: String,
  nextWeek: String,
  selectedSpaceId: Number,
})

const selectedSpace = ref(props.selectedSpaceId || '')
const selectedReservation = ref(null)
const showPanel = ref(false)

const weekTitle = computed(() => {
  const start = new Date(props.weekStart)
  const end = new Date(props.weekEnd)
  const startDay = start.getDate().toString().padStart(2, '0')
  const endDay = end.getDate().toString().padStart(2, '0')
  const month = start.toLocaleDateString('es-CO', { month: 'short' }).toUpperCase()
  const year = start.getFullYear()
  return `${startDay} — ${endDay} ${month} ${year}`
})

const today = new Date().toISOString().split('T')[0]

const reservationsByDay = computed(() => {
  const byDay = Array(7).fill(null).map(() => [])
  props.reservations.forEach(r => {
    if (r.day_index >= 0 && r.day_index < 7) {
      byDay[r.day_index].push(r)
    }
  })
  return byDay
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

function navigateWeek(week) {
  router.get(`/admin/calendar?week=${week}&space_id=${selectedSpace.value || ''}`, {}, {
    preserveState: true
  })
}

function selectReservation(reservation) {
  selectedReservation.value = reservation
  showPanel.value = true
}

function closePanel() {
  showPanel.value = false
  selectedReservation.value = null
}

function formatTime(iso) {
  return new Date(iso).toTimeString().slice(0, 5)
}

function dayNumber(label) {
  return label.split(' ')[1]?.split('/')[0] || ''
}

function dayName(label) {
  return label.split(' ')[0] || ''
}

watch(selectedSpace, (val) => {
  router.get(`/admin/calendar?week=${props.weekStart}&space_id=${val || ''}`, {}, { preserveState: true })
})
</script>

<template>
  <Head title="Calendario Semanal" />
  <PublicLayout>
    <div class="dashboard-container">
      <header class="topbar">
        <div class="brand">
          <div class="brand-mark">CA</div>
          <div>
            <div class="eyebrow">Calendario</div>
            <h1 class="brand-title">Vista Semanal</h1>
            <p class="brand-sub">Programación de reservas por día y espacio</p>
          </div>
        </div>
        <div class="topbar-actions">
          <Link href="/admin" class="ghost-btn">← Volver al panel</Link>
        </div>
      </header>

      <!-- Week Navigator -->
      <article class="card section-card" style="margin-bottom:20px">
        <div class="section-header">
          <div>
            <h2 class="section-title">{{ weekTitle }}</h2>
            <p class="section-subtitle">Selecciona un espacio o navega entre semanas</p>
          </div>
          <div class="topbar-actions">
            <select v-model="selectedSpace" class="filter-select" aria-label="Filtrar por espacio">
              <option value="">Todos los espacios</option>
              <option v-for="space in spaces" :key="space.id" :value="space.id">{{ space.name }}</option>
            </select>
            <button class="ghost-btn" @click="navigateWeek(prevWeek)">← Semana anterior</button>
            <button class="ghost-btn" @click="navigateWeek(nextWeek)">Semana siguiente →</button>
          </div>
        </div>
      </article>

      <!-- Calendar Grid -->
      <div class="overflow-x-auto">
        <div class="min-w-[800px]">
          <!-- Day Headers -->
          <div class="grid grid-cols-7 gap-3 mb-3">
            <div
              v-for="(day, i) in weekDays"
              :key="i"
              class="card"
              :style="day.date === today ? 'box-shadow:0 0 0 1px rgba(0,194,203,.3), 0 0 24px rgba(0,194,203,.15)' : ''"
            >
              <div class="text-center py-3">
                <div class="font-mono text-xs uppercase tracking-wider" style="color:#9FB2D1">
                  {{ dayName(day.label) }}
                </div>
                <div
                  class="mt-1 text-2xl font-bold"
                  :style="{ color: day.date === today ? '#00C2CB' : '#EAF2FF' }"
                >
                  {{ dayNumber(day.label) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Slots Grid -->
          <div class="grid grid-cols-7 gap-3">
            <div
              v-for="(dayReservations, dayIndex) in reservationsByDay"
              :key="dayIndex"
              class="space-y-2"
            >
              <!-- Empty Slot -->
              <div
                v-if="!dayReservations.length"
                class="card p-4 text-center cursor-pointer transition-all hover:shadow-lg"
              >
                <span class="badge-custom" style="background:#00C2CB22;border-color:#00C2CB44;color:#00C2CB">
                  Disponible
                </span>
              </div>

              <!-- Reservations -->
              <div
                v-for="reservation in dayReservations"
                :key="reservation.slug"
                @click="selectReservation(reservation)"
                class="card p-4 cursor-pointer transition-all hover:shadow-lg"
                :style="{
                  borderColor: statusColor(reservation.status) + '44',
                  boxShadow: '0 0 0 1px ' + statusColor(reservation.status) + '22'
                }"
              >
                <div class="font-mono text-xs font-bold" style="color:#5AE6FF;text-shadow:0 0 14px rgba(90,230,255,.18)">
                  {{ formatTime(reservation.start_time) }}
                </div>
                <div class="mt-1 text-sm font-medium" style="color:#EAF2FF">
                  {{ reservation.user_name }}
                </div>
                <div class="mt-1">
                  <span class="badge-custom badge-sm" :style="{ background: statusColor(reservation.status) + '22', borderColor: statusColor(reservation.status) + '44', color: statusColor(reservation.status) }">
                    {{ statusText(reservation.status) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Side Panel -->
      <Transition
        enter-from-class="translate-x-full opacity-0"
        enter-active-class="transition-all duration-300"
        leave-to-class="translate-x-full opacity-0"
        leave-active-class="transition-all duration-300"
      >
        <div
          v-if="showPanel && selectedReservation"
          class="card fixed inset-y-0 right-0 z-50 w-full sm:w-96 border-l overflow-y-auto"
          style="border-radius:0;border-left:1px solid rgba(0,194,203,.2);box-shadow:-8px 0 40px rgba(0,0,0,.5)"
        >
          <div class="p-6">
            <div class="flex items-center justify-between mb-6">
              <div class="eyebrow">Detalle</div>
              <button @click="closePanel" class="ghost-btn" style="min-height:36px;padding:0 12px">×</button>
            </div>

            <div class="space-y-5">
              <div>
                <div class="filter-label mb-1">Espacio</div>
                <div class="text-lg font-bold" style="color:#00C2CB">{{ selectedReservation.space_name }}</div>
              </div>

              <div>
                <div class="filter-label mb-1">Usuario</div>
                <div style="color:#EAF2FF;font-weight:700">{{ selectedReservation.user_name }}</div>
              </div>

              <div>
                <div class="filter-label mb-1">Horario</div>
                <div class="font-mono text-sm font-bold" style="color:#c8ff00">
                  {{ formatTime(selectedReservation.start_time) }} — {{ formatTime(selectedReservation.end_time) }}
                </div>
              </div>

              <div>
                <div class="filter-label mb-1">Estado</div>
                <span class="badge-custom" :style="{ background: statusColor(selectedReservation.status) + '22', borderColor: statusColor(selectedReservation.status) + '44', color: statusColor(selectedReservation.status) }">
                  {{ statusText(selectedReservation.status) }}
                </span>
              </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 space-y-2">
              <button
                v-if="selectedReservation.status === 'pendiente'"
                class="export-btn" style="width:100%;min-height:44px"
              >
                Aceptar
              </button>
              <button
                v-if="selectedReservation.status === 'pendiente'"
                class="ghost-btn" style="width:100%;border-color:#EF444444;color:#EF4444"
              >
                Rechazar
              </button>
              <button
                v-if="selectedReservation.status === 'confirmada'"
                class="ghost-btn" style="width:100%"
              >
                Cancelar
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </PublicLayout>
</template>
