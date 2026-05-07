<script setup>
import { ref, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { gsap } from 'gsap'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

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

function applyFilters() {
  router.get('/admin/reservations', form.value, {
    preserveState: true,
    onSuccess: () => {
      gsap.from('.table-row', { y: 20, opacity: 0, stagger: 0.05, duration: 0.5, ease: 'power3.out' })
    }
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
  gsap.from('.table-row', { y: 20, opacity: 0, stagger: 0.05, duration: 0.5, ease: 'power3.out' })
})
</script>

<template>
  <Head title="Reservas" />
  <PublicLayout>
    <div class="max-w-7xl mx-auto px-4 py-8">
      <!-- Filters -->
      <div class="flex flex-col sm:flex-row gap-4 mb-8 sm:flex-wrap">
        <select
          v-model="form.space_id"
          class="w-full sm:w-auto px-4 py-3 sm:py-2 border bg-transparent focus:outline-none"
          style="border-color: var(--border); color: var(--text-primary); border-radius: 0; font-family: 'DM Sans', sans-serif;"
        >
          <option value="">Todos los espacios</option>
          <option v-for="space in spaces" :key="space.id" :value="space.id">
            {{ space.name }}
          </option>
        </select>

        <select
          v-model="form.status"
          class="w-full sm:w-auto px-4 py-3 sm:py-2 border bg-transparent focus:outline-none"
          style="border-color: var(--border); color: var(--text-primary); border-radius: 0; font-family: 'DM Sans', sans-serif;"
        >
          <option value="">Todos los estados</option>
          <option v-for="status in statuses" :key="status" :value="status">
            {{ status }}
          </option>
        </select>

        <input
          v-model="form.from"
          type="date"
          placeholder="Desde"
          class="w-full sm:w-auto px-4 py-3 sm:py-2 border bg-transparent focus:outline-none"
          style="border-color: var(--border); color: var(--text-primary); border-radius: 0; font-family: 'DM Sans', sans-serif;"
        />

        <input
          v-model="form.to"
          type="date"
          placeholder="Hasta"
          class="w-full sm:w-auto px-4 py-3 sm:py-2 border bg-transparent focus:outline-none"
          style="border-color: var(--border); color: var(--text-primary); border-radius: 0; font-family: 'DM Sans', sans-serif;"
        />

        <button
          @click="applyFilters"
          class="w-full sm:w-auto px-6 py-3 sm:py-2 border text-xs uppercase font-medium tracking-wider transition-colors"
          style="border-color: var(--cyan); color: var(--cyan); background: transparent; border-radius: 0;"
          @mouseenter="$event.target.style.background = 'var(--cyan)'; $event.target.style.color = '#000'"
          @mouseleave="$event.target.style.background = 'transparent'; $event.target.style.color = 'var(--cyan)'"
        >
          Filtrar
        </button>
      </div>

      <!-- Table -->
      <div class="border" style="border-color: var(--border); border-radius: 0;">
        <!-- Header -->
        <div class="hidden md:grid grid-cols-6 gap-4 p-4 border-b font-mono text-xs uppercase" style="border-color: var(--border); color: var(--text-dim);">
          <div>Espacio</div>
          <div>Usuario</div>
          <div>Fecha</div>
          <div>Hora</div>
          <div>Estado</div>
          <div>Acciones</div>
        </div>

        <!-- Rows -->
        <div
          v-for="reservation in reservations.data"
          :key="reservation.slug"
          class="table-row flex flex-col md:grid md:grid-cols-6 gap-2 md:gap-4 p-4 border-b transition-colors hover:cursor-pointer"
          style="border-color: rgba(var(--border-hover), 0.3); font-family: 'DM Sans', sans-serif; font-size: 0.875rem;"
          @mouseenter="$event.currentTarget.style.background = 'var(--bg-card)'"
          @mouseleave="$event.currentTarget.style.background = 'transparent'"
        >
          <div class="flex justify-between md:block items-center">
            <span class="md:hidden font-mono text-xs" style="color: var(--text-dim);">Espacio:</span>
            <span style="color: var(--text-primary);">{{ reservation.space_name }}</span>
          </div>
          <div class="flex justify-between md:block items-center">
            <span class="md:hidden font-mono text-xs" style="color: var(--text-dim);">Usuario:</span>
            <span style="color: var(--text-primary);">{{ reservation.user_name }}</span>
          </div>
          <div class="flex justify-between md:block items-center">
            <span class="md:hidden font-mono text-xs" style="color: var(--text-dim);">Fecha:</span>
            <span style="color: var(--text-primary);">{{ formatDate(reservation.start_time) }}</span>
          </div>
          <div class="flex justify-between md:block items-center">
            <span class="md:hidden font-mono text-xs" style="color: var(--text-dim);">Hora:</span>
            <span class="font-mono text-xs" style="color: var(--lime);">{{ formatTime(reservation.start_time) }}</span>
          </div>
          <div class="flex justify-between md:block items-center">
            <span class="md:hidden font-mono text-xs" style="color: var(--text-dim);">Estado:</span>
            <StatusBadge :status="reservation.status" />
          </div>
          <div class="flex justify-end md:justify-start mt-2 md:mt-0">
            <Link
              :href="`/admin/reservations/${reservation.slug}`"
              class="px-2 py-1 border text-xs uppercase transition-colors"
              style="border-color: var(--border); color: var(--cyan); border-radius: 0;"
              @mouseenter="$event.target.style.borderColor = 'var(--cyan)'"
              @mouseleave="$event.target.style.borderColor = 'var(--border)'"
            >
              Ver
            </Link>
          </div>
        </div>
        
        <div v-if="!reservations.data.length" class="p-12 text-center font-mono text-xs" style="color: var(--text-dim);">
          — SIN RESULTADOS —
        </div>
      </div>

      <!-- Pagination -->
      <div class="mt-8 text-center font-mono text-xs" style="color: var(--text-muted);">
        <Link
          v-if="reservations.prev_page_url"
          :href="reservations.prev_page_url"
          class="hover:opacity-70"
        >
          ← ANTERIOR
        </Link>
        <span class="mx-4">{{ reservations.current_page }} / {{ reservations.last_page }}</span>
        <Link
          v-if="reservations.next_page_url"
          :href="reservations.next_page_url"
          class="hover:opacity-70"
        >
          SIGUIENTE →
        </Link>
      </div>
    </div>
  </PublicLayout>
</template>
