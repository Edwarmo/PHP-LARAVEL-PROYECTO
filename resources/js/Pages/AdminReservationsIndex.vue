<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { gsap } from 'gsap'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps({
  reservations: Object, // Este será null inicialmente si es lazy
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

function applyFilters() {
  router.get('/admin/reservations', form.value, {
    preserveState: true,
    only: ['reservations'], // Solo recargar el prop lazy
  })
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
  gsap.from('.premium-card', { y: 20, opacity: 0, stagger: 0.05, duration: 0.8, ease: 'power3.out' })
})
</script>

<template>
  <Head title="Gestión de Reservas" />
  <PublicLayout>
    <div class="max-w-7xl mx-auto px-4 py-10">
      
      <!-- Premium Header Section -->
      <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
          <h1 class="text-4xl font-light mb-2" style="font-family: 'Cormorant Garamond', serif; color: var(--text-primary);">
            Gestión de Reservas
          </h1>
          <p class="font-mono text-xs uppercase tracking-[0.2em]" style="color: var(--text-dim);">
            Control administrativo y flujo de solicitudes
          </p>
        </div>
        
        <!-- Quick Filters Desktop -->
        <div class="flex flex-wrap items-center gap-3">
          <div v-for="status in statuses.slice(0, 3)" :key="status" 
            @click="form.status = status; applyFilters()"
            class="px-4 py-1.5 border text-[10px] uppercase tracking-widest cursor-pointer transition-all hover:bg-white/5"
            :style="{ 
              borderColor: form.status === status ? 'var(--cyan)' : 'var(--border)',
              color: form.status === status ? 'var(--cyan)' : 'var(--text-dim)'
            }"
          >
            {{ status }}
          </div>
        </div>
      </div>

      <!-- Advanced Filters Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-10 p-6 border" style="background: var(--bg-card); border-color: var(--border);">
        <div class="flex flex-col gap-1.5">
          <label class="font-mono text-[10px] uppercase text-dim ml-1">Espacio</label>
          <select v-model="form.space_id" class="custom-select">
            <option value="">Todos los espacios</option>
            <option v-for="space in spaces" :key="space.id" :value="space.id">{{ space.name }}</option>
          </select>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="font-mono text-[10px] uppercase text-dim ml-1">Estado</label>
          <select v-model="form.status" class="custom-select">
            <option value="">Todos los estados</option>
            <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
          </select>
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="font-mono text-[10px] uppercase text-dim ml-1">Desde</label>
          <input type="date" v-model="form.from" class="custom-input" />
        </div>

        <div class="flex flex-col gap-1.5">
          <label class="font-mono text-[10px] uppercase text-dim ml-1">Hasta</label>
          <input type="date" v-model="form.to" class="custom-input" />
        </div>

        <div class="flex items-end">
          <button @click="applyFilters" class="w-full py-2.5 bg-cyan text-black text-xs uppercase font-bold tracking-widest hover:brightness-110 transition-all">
            Filtrar Resultados
          </button>
        </div>
      </div>

      <!-- Content Area (Skeleton or Table) -->
      <div class="min-h-[400px]">
        
        <!-- Skeleton Loader -->
        <div v-if="!reservations" class="space-y-4">
          <div v-for="i in 5" :key="i" class="h-20 w-full animate-pulse border" style="background: rgba(255,255,255,0.03); border-color: var(--border);">
            <div class="flex items-center h-full px-6 gap-8">
              <div class="h-4 w-32 bg-white/10 rounded"></div>
              <div class="h-4 w-48 bg-white/10 rounded"></div>
              <div class="h-4 w-24 bg-white/10 rounded"></div>
              <div class="ml-auto h-8 w-24 bg-white/10 rounded"></div>
            </div>
          </div>
        </div>

        <!-- Real Table -->
        <div v-else class="space-y-4">
          <div v-if="!reservations.data.length" class="p-20 text-center border font-mono text-xs tracking-widest" style="border-color: var(--border); color: var(--text-dim);">
            — NO SE ENCONTRARON RESERVAS —
          </div>

          <div v-for="reservation in reservations.data" :key="reservation.slug" 
            class="premium-card group border p-6 flex flex-col md:grid md:grid-cols-6 gap-4 items-center transition-all hover:border-cyan/50"
            style="background: var(--bg-card); border-color: var(--border);"
          >
            <!-- Col 1: Space (Highlight) -->
            <div class="col-span-1 w-full">
              <div class="font-mono text-[10px] uppercase text-dim mb-1 md:hidden">Espacio</div>
              <div class="text-lg font-medium text-white group-hover:text-cyan transition-colors truncate">
                {{ reservation.space_name }}
              </div>
            </div>

            <!-- Col 2: User -->
            <div class="col-span-1 w-full">
              <div class="font-mono text-[10px] uppercase text-dim mb-1 md:hidden">Usuario</div>
              <div class="text-sm text-dim truncate">{{ reservation.user_name }}</div>
            </div>

            <!-- Col 3: Date -->
            <div class="col-span-1 w-full">
              <div class="font-mono text-[10px] uppercase text-dim mb-1 md:hidden">Fecha</div>
              <div class="text-sm text-white font-mono">{{ formatDate(reservation.start_time) }}</div>
            </div>

            <!-- Col 4: Time -->
            <div class="col-span-1 w-full text-center md:text-left">
              <div class="font-mono text-[10px] uppercase text-dim mb-1 md:hidden">Hora</div>
              <div class="text-xs text-lime font-mono tracking-tighter">{{ formatTime(reservation.start_time) }}</div>
            </div>

            <!-- Col 5: Status -->
            <div class="col-span-1 w-full flex justify-center md:justify-start">
              <StatusBadge :status="reservation.status" />
            </div>

            <!-- Col 6: Action -->
            <div class="col-span-1 w-full flex justify-end">
              <Link :href="`/admin/reservations/${reservation.slug}`" 
                class="px-6 py-2 border border-cyan/30 text-cyan text-[10px] uppercase tracking-[0.2em] hover:bg-cyan hover:text-black transition-all"
              >
                Ver Detalle
              </Link>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="reservations.last_page > 1" class="mt-10 flex justify-center items-center gap-6 font-mono text-[10px]">
            <Link v-if="reservations.prev_page_url" :href="reservations.prev_page_url" class="text-dim hover:text-white transition-colors">← ANTERIOR</Link>
            <span class="text-cyan">{{ reservations.current_page }} / {{ reservations.last_page }}</span>
            <Link v-if="reservations.next_page_url" :href="reservations.next_page_url" class="text-dim hover:text-white transition-colors">SIGUIENTE →</Link>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<style scoped>
.custom-select, .custom-input {
  width: 100%;
  background: transparent;
  border: none;
  border-bottom: 1px solid var(--border);
  padding: 8px 4px;
  color: var(--text-primary);
  font-family: 'DM Sans', sans-serif;
  font-size: 0.875rem;
  transition: all 0.3s ease;
}

.custom-select:focus, .custom-input:focus {
  outline: none;
  border-bottom-color: var(--cyan);
}

.custom-select option {
  background: #0c1018;
  color: white;
}

/* Chrome/Safari fixes for dates */
input[type="date"]::-webkit-calendar-picker-indicator {
  filter: invert(1);
  opacity: 0.5;
}
</style>
