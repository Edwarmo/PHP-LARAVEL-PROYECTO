<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { gsap } from 'gsap'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

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

function navigateWeek(week) {
  gsap.to('.calendar-grid', {
    x: week === props.prevWeek ? 40 : -40,
    opacity: 0,
    duration: 0.4,
    ease: 'power2.inOut',
    onComplete: () => {
      router.get(`/admin/calendar?week=${week}&space_id=${selectedSpace.value || ''}`, {}, {
        preserveState: true,
        onSuccess: () => {
          gsap.fromTo('.calendar-grid',
            { x: week === props.prevWeek ? -40 : 40, opacity: 0 },
            { x: 0, opacity: 1, duration: 0.4, ease: 'power2.out' }
          )
        }
      })
    }
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

watch(selectedSpace, (val) => {
  router.get(`/admin/calendar?week=${props.weekStart}&space_id=${val || ''}`, {}, { preserveState: true })
})
</script>

<template>
  <Head title="Calendario Semanal" />
  <PublicLayout>
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <select
          v-model="selectedSpace"
          class="px-4 py-2 border bg-transparent focus:outline-none transition-colors"
          style="border-color: var(--border); color: var(--text-primary); border-radius: 0; font-family: 'DM Sans', sans-serif;"
        >
          <option value="">Todos los espacios</option>
          <option v-for="space in spaces" :key="space.id" :value="space.id">
            {{ space.name }}
          </option>
        </select>

        <div class="flex items-center gap-6">
          <button
            @click="navigateWeek(prevWeek)"
            class="text-2xl transition-colors"
            style="color: var(--text-muted); font-family: 'DM Sans', sans-serif;"
            @mouseenter="$event.target.style.color = 'var(--cyan)'"
            @mouseleave="$event.target.style.color = 'var(--text-muted)'"
          >
            ←
          </button>
          <h1 class="text-2xl font-light" style="font-family: 'Cormorant Garamond', serif; color: var(--text-primary);">
            {{ weekTitle }}
          </h1>
          <button
            @click="navigateWeek(nextWeek)"
            class="text-2xl transition-colors"
            style="color: var(--text-muted); font-family: 'DM Sans', sans-serif;"
            @mouseenter="$event.target.style.color = 'var(--cyan)'"
            @mouseleave="$event.target.style.color = 'var(--text-muted)'"
          >
            →
          </button>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="calendar-grid">
        <!-- Day Headers -->
        <div class="grid grid-cols-7 gap-2 mb-4">
          <div
            v-for="(day, i) in weekDays"
            :key="i"
            class="text-center"
          >
            <div class="font-mono text-xs uppercase" style="color: var(--text-dim);">
              {{ day.label.split(' ')[0] }}
            </div>
            <div
              class="text-xl font-light mt-1"
              :class="{ 'border-b-2': day.date === today }"
              :style="{
                fontFamily: 'Cormorant Garamond, serif',
                color: 'var(--text-primary)',
                borderColor: day.date === today ? 'var(--cyan)' : 'transparent'
              }"
            >
              {{ day.label.split(' ')[1].split('/')[0] }}
            </div>
          </div>
        </div>

        <!-- Slots Grid -->
        <div class="grid grid-cols-7 gap-2">
          <div
            v-for="(dayReservations, dayIndex) in reservationsByDay"
            :key="dayIndex"
            class="space-y-2"
          >
            <!-- Empty Slot -->
            <div
              v-if="!dayReservations.length"
              class="border p-3 text-center transition-colors cursor-pointer"
              style="border-color: var(--border); background: transparent; border-radius: 0;"
              @mouseenter="$event.target.style.borderColor = 'var(--cyan)'"
              @mouseleave="$event.target.style.borderColor = 'var(--border)'"
            >
              <div class="font-mono text-xs" style="color: var(--cyan);">Disponible</div>
            </div>

            <!-- Reservations -->
            <div
              v-for="reservation in dayReservations"
              :key="reservation.slug"
              @click="selectReservation(reservation)"
              class="border p-3 cursor-pointer transition-colors"
              :style="{
                background: reservation.status === 'pendiente' ? 'rgba(240,192,64,0.1)' : 'rgba(0,220,255,0.08)',
                borderColor: reservation.status === 'pendiente' ? '#eab308' : 'var(--cyan)',
                borderRadius: 0
              }"
            >
              <div class="font-mono text-xs" style="color: var(--cyan);">
                {{ formatTime(reservation.start_time) }}
              </div>
              <div class="text-xs mt-1" style="font-family: 'DM Sans', sans-serif; color: var(--text-muted);">
                {{ reservation.user_name }}
              </div>
              <StatusBadge :status="reservation.status" class="mt-2" />
            </div>
          </div>
        </div>
      </div>

      <!-- Side Panel -->
      <Transition
        @enter="gsap.fromTo($el, { x: 300, opacity: 0 }, { x: 0, opacity: 1, duration: 0.3 })"
        @leave="gsap.to($el, { x: 300, opacity: 0, duration: 0.3 })"
      >
        <div
          v-if="showPanel && selectedReservation"
          class="fixed right-0 top-0 h-full w-96 border-l p-6 overflow-y-auto"
          style="background: var(--bg-base); border-color: var(--border);"
        >
          <button
            @click="closePanel"
            class="absolute top-4 right-4 text-2xl"
            style="color: var(--text-muted);"
          >
            ×
          </button>

          <div class="font-mono text-xs uppercase tracking-wide mb-4" style="color: var(--text-dim);">
            Detalle de reserva
          </div>

          <div class="space-y-4">
            <div>
              <div class="font-mono text-xs uppercase mb-1" style="color: var(--text-dim);">Espacio</div>
              <div style="color: var(--cyan); font-family: 'DM Sans', sans-serif;">
                {{ selectedReservation.space_name }}
              </div>
            </div>

            <div>
              <div class="font-mono text-xs uppercase mb-1" style="color: var(--text-dim);">Usuario</div>
              <div style="color: var(--text-primary); font-family: 'DM Sans', sans-serif;">
                {{ selectedReservation.user_name }}
              </div>
            </div>

            <div>
              <div class="font-mono text-xs uppercase mb-1" style="color: var(--text-dim);">Horario</div>
              <div class="font-mono text-sm" style="color: var(--lime);">
                {{ formatTime(selectedReservation.start_time) }} — {{ formatTime(selectedReservation.end_time) }}
              </div>
            </div>

            <div>
              <div class="font-mono text-xs uppercase mb-1" style="color: var(--text-dim);">Estado</div>
              <StatusBadge :status="selectedReservation.status" />
            </div>
          </div>

          <!-- Actions -->
          <div class="mt-8 space-y-2">
            <button
              v-if="selectedReservation.status === 'pendiente'"
              class="w-full py-2 border text-xs uppercase transition-colors"
              style="border-color: var(--cyan); color: var(--cyan); background: transparent; border-radius: 0;"
              @mouseenter="$event.target.style.background = 'rgba(0,220,255,0.1)'"
              @mouseleave="$event.target.style.background = 'transparent'"
            >
              Aceptar
            </button>
            <button
              v-if="selectedReservation.status === 'pendiente'"
              class="w-full py-2 border text-xs uppercase transition-colors"
              style="border-color: #ef4444; color: #ef4444; background: transparent; border-radius: 0;"
              @mouseenter="$event.target.style.background = 'rgba(239,68,68,0.1)'"
              @mouseleave="$event.target.style.background = 'transparent'"
            >
              Rechazar
            </button>
            <button
              v-if="selectedReservation.status === 'confirmada'"
              class="w-full py-2 border text-xs uppercase transition-colors"
              style="border-color: var(--text-muted); color: var(--text-muted); background: transparent; border-radius: 0;"
              @mouseenter="$event.target.style.background = 'rgba(90,112,128,0.1)'"
              @mouseleave="$event.target.style.background = 'transparent'"
            >
              Cancelar
            </button>
          </div>
        </div>
      </Transition>
    </div>
  </PublicLayout>
</template>
