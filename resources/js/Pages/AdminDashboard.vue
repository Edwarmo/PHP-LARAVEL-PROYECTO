<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Badge } from '@/Components/ui'
import { Card, CardContent } from '@/Components/ui'
import { Button } from '@/Components/ui'

const props = defineProps({
  metrics: Object,
  pendientes: Array,
})

const animatedMetrics = ref({
  pendientes: 0,
  confirmadas: 0,
  hoy: 0,
  esta_semana: 0,
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
  if (props.metrics) {
    Object.keys(props.metrics).forEach((key) => {
      if (animatedMetrics.value.hasOwnProperty(key)) {
        const target = Number(props.metrics[key]) || 0
        const start = 0
        const duration = 1500
        const startTime = performance.now()
        
        const animate = (currentTime) => {
          const elapsed = currentTime - startTime
          const progress = Math.min(elapsed / duration, 1)
          animatedMetrics.value[key] = Math.floor(start + (target - start) * progress)
          if (progress < 1) requestAnimationFrame(animate)
        }
        requestAnimationFrame(animate)
      }
    })
  }
})
</script>

<template>
  <Head title="Panel de Control" />
  <PublicLayout>
    <div class="mx-auto max-w-7xl px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <div class="font-mono text-xs uppercase tracking-wide text-muted-foreground">Panel de control</div>
        <div class="font-mono text-xs text-muted-foreground">
          {{ new Date().toLocaleDateString('es-CO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
        </div>
      </div>

      <!-- Metrics Grid -->
      <div class="mb-12 grid grid-cols-2 gap-px border border-cyan/20 lg:grid-cols-4">
        <Card
          v-for="(value, key) in animatedMetrics"
          :key="key"
          class="border-0 rounded-none bg-card/50"
        >
          <CardContent class="p-6">
            <div class="text-5xl font-light" :style="{ color: metricColors[key] }">
              {{ Math.round(value) }}
            </div>
            <div class="mt-2 font-mono text-xs uppercase text-muted-foreground">
              {{ metricLabels[key] }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Reservations Table -->
      <div class="mt-12">
        <div class="mb-4 font-mono text-xs uppercase tracking-wide text-muted-foreground">
          Reservas recientes
        </div>
        
        <Card class="border border-cyan/20 bg-card/50">
          <div class="hidden border-b border-border md:grid grid-cols-5 gap-4 p-4 font-mono text-xs uppercase text-muted-foreground">
            <div>Espacio</div>
            <div>Usuario</div>
            <div>Fecha</div>
            <div>Estado</div>
            <div>Acción</div>
          </div>

          <div
            v-for="reserva in pendientes"
            :key="reserva.slug"
            class="flex flex-col md:grid md:grid-cols-5 gap-2 md:gap-4 p-4 border-b transition-colors hover:bg-card/80"
          >
            <div class="flex justify-between md:block items-center">
              <span class="md:hidden font-mono text-xs text-muted-foreground">Espacio:</span>
              <span class="text-foreground">{{ reserva.space_name }}</span>
            </div>
            <div class="flex justify-between md:block items-center">
              <span class="md:hidden font-mono text-xs text-muted-foreground">Usuario:</span>
              <span class="text-muted-foreground">{{ reserva.user_name }}</span>
            </div>
            <div class="flex justify-between md:block items-center font-mono text-xs text-muted-foreground">
              <span class="md:hidden font-mono text-xs text-muted-foreground">Fecha:</span>
              <span>{{ formatDate(reserva.start_time) }} {{ formatTime(reserva.start_time) }}</span>
            </div>
            <div class="flex justify-between md:block items-center">
              <span class="md:hidden font-mono text-xs text-muted-foreground">Estado:</span>
              <Badge :variant="reserva.status === 'pendiente' ? 'secondary' : 'default'">
                {{ reserva.status }}
              </Badge>
            </div>
            <div class="flex gap-2 mt-2 md:mt-0 justify-end md:justify-start">
              <Link
                :href="`/admin/reservations/${reserva.slug}`"
                class="px-3 py-1 border text-xs uppercase transition-colors border-cyan/50 text-cyan hover:bg-cyan hover:text-black"
              >
                Ver
              </Link>
            </div>
          </div>

          <div v-if="!pendientes.length" class="p-12 text-center font-mono text-xs text-muted-foreground">
            — SIN RESERVAS PENDIENTES —
          </div>
        </Card>
      </div>
    </div>
  </PublicLayout>
</template>