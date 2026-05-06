<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { gsap } from 'gsap'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps({
  metrics: Object,
  proximasReservas: Array,
  pendientes: Array,
})

const animatedMetrics = ref({
  pendientes: 0,
  confirmadas: 0,
  hoy: 0,
  esta_semana: 0,
})

const currentDate = new Date().toLocaleDateString('es-CO', { 
  weekday: 'long', 
  year: 'numeric', 
  month: 'long', 
  day: 'numeric' 
})

const metricColors = {
  pendientes: '#eab308',
  confirmadas: 'var(--cyan)',
  hoy: 'var(--lime)',
  esta_semana: 'var(--text-muted)',
}

const metricLabels = {
  pendientes: 'Pendientes',
  confirmadas: 'Confirmadas',
  hoy: 'Próximas',
  esta_semana: 'Finalizadas',
}

function formatDate(iso) {
  const d = new Date(iso)
  return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'short', year: 'numeric' })
}

function formatTime(iso) {
  const d = new Date(iso)
  return d.toTimeString().slice(0, 5)
}

onMounted(() => {
  Object.keys(props.metrics).forEach((key) => {
    if (animatedMetrics.value.hasOwnProperty(key)) {
      gsap.to(animatedMetrics.value, {
        [key]: props.metrics[key],
        duration: 1.5,
        ease: 'power2.out',
        snap: { [key]: 1 },
      })
    }
  })
})
</script>

<template>
  <Head title="Panel de Control" />
  <PublicLayout>
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="font-mono text-xs uppercase tracking-wide" style="color: var(--text-dim);">Panel de control</div>
        <div class="font-mono text-xs mt-1" style="color: var(--text-muted);">{{ currentDate }}</div>
      </div>

      <!-- Metrics Grid -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-px mb-12" style="background: var(--border);">
        <div
          v-for="(value, key) in animatedMetrics"
          :key="key"
          class="p-6"
          style="background: var(--bg-base);"
        >
          <div class="text-5xl font-light" style="font-family: 'Cormorant Garamond', serif;" :style="{ color: metricColors[key] }">
            {{ Math.round(value) }}
          </div>
          <div class="font-mono text-xs uppercase mt-2" style="color: var(--text-dim);">
            {{ metricLabels[key] }}
          </div>
        </div>
      </div>

      <!-- Recent Reservations Table -->
      <div class="mt-12">
        <div class="font-mono text-xs uppercase tracking-wide mb-4" style="color: var(--text-dim);">Reservas recientes</div>
        
        <div class="border" style="border-color: var(--border); border-radius: 0;">
          <!-- Header -->
          <div class="grid grid-cols-5 gap-4 p-4 border-b font-mono text-xs uppercase" style="border-color: var(--border); color: var(--text-dim);">
            <div>Espacio</div>
            <div>Usuario</div>
            <div>Fecha</div>
            <div>Estado</div>
            <div>Acción</div>
          </div>

          <!-- Rows -->
          <div
            v-for="reserva in pendientes"
            :key="reserva.slug"
            class="grid grid-cols-5 gap-4 p-4 border-b transition-colors hover:cursor-pointer"
            style="border-color: rgba(var(--border-hover), 0.3); font-family: 'DM Sans', sans-serif; font-size: 0.875rem;"
            @mouseenter="$event.currentTarget.style.background = 'var(--bg-card)'"
            @mouseleave="$event.currentTarget.style.background = 'transparent'"
          >
            <div style="color: var(--text-primary);">{{ reserva.space_name }}</div>
            <div style="color: var(--text-muted);">{{ reserva.user_name }}</div>
            <div class="font-mono text-xs" style="color: var(--text-muted);">
              {{ formatDate(reserva.start_time) }} {{ formatTime(reserva.start_time) }}
            </div>
            <div>
              <StatusBadge status="pendiente" />
            </div>
            <div class="flex gap-2">
              <Link
                :href="`/admin/reservations/${reserva.slug}`"
                class="px-3 py-1 border text-xs uppercase transition-colors"
                style="border-color: var(--border); color: var(--cyan); border-radius: 0;"
                @mouseenter="$event.target.style.borderColor = 'var(--cyan)'"
                @mouseleave="$event.target.style.borderColor = 'var(--border)'"
              >
                Ver
              </Link>
            </div>
          </div>

          <!-- Empty State -->
          <div v-if="!pendientes.length" class="p-12 text-center font-mono text-xs" style="color: var(--text-dim);">
            — SIN RESERVAS PENDIENTES —
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
