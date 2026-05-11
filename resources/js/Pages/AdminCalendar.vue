<script setup>
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'
import { Card, CardContent } from '@/Components/ui'
import { Button } from '@/Components/ui'
import { Badge } from '@/Components/ui'

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

watch(selectedSpace, (val) => {
  router.get(`/admin/calendar?week=${props.weekStart}&space_id=${val || ''}`, {}, { preserveState: true })
})
</script>

<template>
  <Head title="Calendario Semanal" />
  <PublicLayout>
    <div class="mx-auto max-w-7xl px-4 py-8">
      <!-- Header -->
      <div class="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-center">
        <select
          v-model="selectedSpace"
          class="w-full border border-border bg-background px-4 py-3 text-sm md:w-auto md:py-2"
        >
          <option value="">Todos los espacios</option>
          <option v-for="space in spaces" :key="space.id" :value="space.id">
            {{ space.name }}
          </option>
        </select>

        <div class="flex items-center justify-between gap-6 md:justify-start">
          <Button variant="ghost" size="icon" @click="navigateWeek(prevWeek)">←</Button>
          <h1 class="text-center text-xl font-light sm:text-2xl text-foreground">
            {{ weekTitle }}
          </h1>
          <Button variant="ghost" size="icon" @click="navigateWeek(nextWeek)">→</Button>
        </div>
      </div>

      <!-- Calendar Grid -->
      <div class="overflow-x-auto">
        <div class="min-w-[700px]">
          <!-- Day Headers -->
          <div class="mb-4 grid grid-cols-7 gap-2">
            <div
              v-for="(day, i) in weekDays"
              :key="i"
              class="text-center"
            >
              <div class="font-mono text-xs uppercase text-muted-foreground">
                {{ day.label.split(' ')[0] }}
              </div>
              <div
                class="mt-1 text-xl font-light"
                :class="{ 'border-b-2 border-cyan': day.date === today }"
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
                class="cursor-pointer border border-border p-3 text-center transition-colors hover:border-cyan"
              >
                <Badge variant="outline" class="text-cyan">Disponible</Badge>
              </div>

              <!-- Reservations -->
              <div
                v-for="reservation in dayReservations"
                :key="reservation.slug"
                @click="selectReservation(reservation)"
                class="cursor-pointer border p-3 transition-colors"
                :class="{
                  'border-yellow-500/50 bg-yellow-500/10': reservation.status === 'pendiente',
                  'border-cyan/50 bg-cyan/10': reservation.status !== 'pendiente'
                }"
              >
                <div class="font-mono text-xs text-cyan">
                  {{ formatTime(reservation.start_time) }}
                </div>
                <div class="mt-1 text-sm text-muted-foreground">
                  {{ reservation.user_name }}
                </div>
                <StatusBadge :status="reservation.status" class="mt-2" />
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
        <Card
          v-if="showPanel && selectedReservation"
          class="fixed inset-y-0 right-0 z-50 w-full border-l sm:w-96"
        >
          <Button
            variant="ghost"
            size="icon"
            class="absolute top-4 right-4"
            @click="closePanel"
          >
            ×
          </Button>

          <div class="p-6">
            <div class="mb-4 font-mono text-xs uppercase tracking-wide text-muted-foreground">
              Detalle de reserva
            </div>

            <div class="space-y-4">
              <div>
                <div class="mb-1 font-mono text-xs text-muted-foreground">Espacio</div>
                <div class="text-cyan">{{ selectedReservation.space_name }}</div>
              </div>

              <div>
                <div class="mb-1 font-mono text-xs text-muted-foreground">Usuario</div>
                <div class="text-foreground">{{ selectedReservation.user_name }}</div>
              </div>

              <div>
                <div class="mb-1 font-mono text-xs text-muted-foreground">Horario</div>
                <div class="font-mono text-sm text-lime">
                  {{ formatTime(selectedReservation.start_time) }} — {{ formatTime(selectedReservation.end_time) }}
                </div>
              </div>

              <div>
                <div class="mb-1 font-mono text-xs text-muted-foreground">Estado</div>
                <StatusBadge :status="selectedReservation.status" />
              </div>
            </div>

            <!-- Actions -->
            <div class="mt-8 space-y-2">
              <Button
                v-if="selectedReservation.status === 'pendiente'"
                variant="outline"
                class="w-full border-cyan text-cyan hover:bg-cyan/10"
              >
                Aceptar
              </Button>
              <Button
                v-if="selectedReservation.status === 'pendiente'"
                variant="outline"
                class="w-full border-destructive text-destructive hover:bg-destructive/10"
              >
                Rechazar
              </Button>
              <Button
                v-if="selectedReservation.status === 'confirmada'"
                variant="outline"
                class="w-full"
              >
                Cancelar
              </Button>
            </div>
          </div>
        </Card>
      </Transition>
    </div>
  </PublicLayout>
</template>