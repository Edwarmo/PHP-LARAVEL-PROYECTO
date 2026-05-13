<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Badge } from '@/Components/ui'
import { Card, CardContent } from '@/Components/ui'
import { Button } from '@/Components/ui'
import { Input } from '@/Components/ui'
import { Label } from '@/Components/ui'
import { useFilterNavigation } from '@/composables/useFilterNavigation'
import { formatDate, formatTime } from '@/lib/formatters'

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

const { navigate } = useFilterNavigation('/admin/reservations')

function applyFilters() {
  navigate(form.value)
}
</script>

<template>
  <Head title="Gestión de Reservas" />
  <PublicLayout>
    <div class="mx-auto max-w-7xl px-4 py-10">
      <!-- Header -->
      <div class="mb-10 flex flex-col justify-between gap-6 md:flex-row md:items-end">
        <div>
          <h1 class="text-4xl font-light text-foreground">Gestión de Reservas</h1>
          <p class="font-mono text-xs uppercase tracking-[0.2em] text-muted-foreground">
            Control administrativo y flujo de solicitudes
          </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
          <Button
            v-for="status in statuses.slice(0, 3)"
            :key="status"
            :variant="form.status === status ? 'default' : 'outline'"
            size="sm"
            @click="form.status = status; applyFilters()"
          >
            {{ status }}
          </Button>
        </div>
      </div>

      <!-- Filters -->
      <Card class="mb-10 border border-cyan/20 bg-card/50">
        <CardContent class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2 lg:grid-cols-5">
          <div class="flex flex-col gap-1.5">
            <Label>Tipo</Label>
            <select v-model="form.space_id" class="h-9 rounded-none border border-border bg-background px-3 text-xs">
              <option value="">Todos los espacios</option>
              <option v-for="space in spaces" :key="space.id" :value="space.id">{{ space.name }}</option>
            </select>
          </div>

          <div class="flex flex-col gap-1.5">
            <Label>Estado</Label>
            <select v-model="form.status" class="h-9 rounded-none border border-border bg-background px-3 text-xs">
              <option value="">Todos los estados</option>
              <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
            </select>
          </div>

          <div class="flex flex-col gap-1.5">
            <Label>Desde</Label>
            <Input type="date" v-model="form.from" class="h-9" />
          </div>

          <div class="flex flex-col gap-1.5">
            <Label>Hasta</Label>
            <Input type="date" v-model="form.to" class="h-9" />
          </div>

          <div class="flex items-end">
            <Button class="w-full bg-cyan text-black hover:bg-cyan/90" @click="applyFilters">
              Filtrar Resultados
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Content -->
      <div class="min-h-[400px]">
        <!-- Skeleton -->
        <div v-if="!reservations" class="space-y-4">
          <div v-for="i in 5" :key="i" class="h-20 w-full animate-pulse border border-cyan/20">
            <div class="flex h-full items-center gap-8 px-6">
              <div class="h-4 w-32 bg-white/10 rounded"></div>
              <div class="h-4 w-48 bg-white/10 rounded"></div>
              <div class="h-4 w-24 bg-white/10 rounded"></div>
              <div class="ml-auto h-8 w-24 bg-white/10 rounded"></div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div v-else class="space-y-4">
          <div v-if="!reservations.data.length" class="border border-cyan/20 p-20 text-center font-mono text-xs text-muted-foreground">
            — NO SE ENCONTRARON RESERVAS —
          </div>

          <Card
            v-for="reservation in reservations.data"
            :key="reservation.slug"
            class="border border-cyan/20 bg-card/50 transition-all hover:border-cyan/50"
          >
            <CardContent class="flex flex-col gap-4 p-6 md:grid md:grid-cols-6 md:items-center">
              <div class="w-full">
                <div class="md:hidden font-mono text-xs text-muted-foreground">Espacio</div>
                <div class="text-lg font-medium text-white truncate">{{ reservation.space_name }}</div>
              </div>

              <div class="w-full">
                <div class="md:hidden font-mono text-xs text-muted-foreground">Usuario</div>
                <div class="text-sm text-muted-foreground truncate">{{ reservation.user_name }}</div>
              </div>

              <div class="w-full">
                <div class="md:hidden font-mono text-xs text-muted-foreground">Fecha</div>
                <div class="font-mono text-sm">{{ formatDate(reservation.start_time) }}</div>
              </div>

              <div class="w-full text-center md:text-left">
                <div class="md:hidden font-mono text-xs text-muted-foreground">Hora</div>
                <div class="font-mono text-xs text-lime">{{ formatTime(reservation.start_time) }}</div>
              </div>

              <div class="flex w-full justify-center md:justify-start">
                <Badge :variant="reservation.status === 'pendiente' ? 'secondary' : 'default'">
                  {{ reservation.status }}
                </Badge>
              </div>

              <div class="flex w-full justify-end">
                <Link
                  :href="`/admin/reservations/${reservation.slug}`"
                  class="border border-cyan/30 px-6 py-2 text-xs uppercase tracking-[0.2em] text-cyan transition-all hover:bg-cyan hover:text-black"
                >
                  Ver Detalle
                </Link>
              </div>
            </CardContent>
          </Card>

          <!-- Pagination -->
          <div v-if="reservations.last_page > 1" class="mt-10 flex justify-center items-center gap-6 font-mono text-xs">
            <Link v-if="reservations.prev_page_url" :href="reservations.prev_page_url" class="text-muted-foreground hover:text-foreground">← ANTERIOR</Link>
            <span class="text-cyan">{{ reservations.current_page }} / {{ reservations.last_page }}</span>
            <Link v-if="reservations.next_page_url" :href="reservations.next_page_url" class="text-muted-foreground hover:text-foreground">SIGUIENTE →</Link>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>