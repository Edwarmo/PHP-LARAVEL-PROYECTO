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
      <div class="flex gap-4 mb-8 flex-wrap">
        <select
          v-model="form.space_id"
          class="px-4 py-2 border bg-transparent focus:outline-none"
          style="border-color: var(--border); color: var(--text-primary); border-radius: 0; font-family: 'DM Sans', sans-serif;"
        >
          <option value="">Todos los espacios</option>
          <option v-for="space in spaces" :key="space.id" :value="space.id">
            {{ space.name }}
          </option>
        </select>

        <select
          v-model="form.status"
          class="px-4 py-2 border bg-transparent focus:outline-none"
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
          class="px-4 py-2 border bg-transparent focus:outline-none"
          style="border-color: var(--border); color: var(--text-primary); border-radius: 0; font-family: 'DM Sans', sans-serif;"
        />

        <input
          v-model="form.to"
          type="date"
          placeholder="Hasta"
          class="px-4 py-2 border bg-transparent focus:outline-none"
          style="border-color: var(--border); color: var(--text-primary); border-radius: 0; font-family: 'DM Sans', sans-serif;"
        />

        <button
          @click="applyFilters"
          class="px-6 py-2 border text-xs uppercase font-medium tracking-wider transition-colors"
          style="border-color: var(--cyan); color: var(--cyan); background: transparent; border-radius: 0;"
          @mouseenter="$event.target.style.background = 'var(--cyan)'; $event.target.style.color = '#000'"
          @mouseleave="$event.target.style.background = 'transparent'; $event.target.style.color = 'var(--cyan)'"
        >
          Filtrar
        </button>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="w-full border-t" style="border-collapse: collapse; border-color: var(--border);">
          <thead>
            <tr>
              <th class="text-left py-3 px-4 font-mono text-xs uppercase border-b" style="color: var(--text-dim); border-color: var(--border);">
                Espacio
              </th>
              <th class="text-left py-3 px-4 font-mono text-xs uppercase border-b" style="color: var(--text-dim); border-color: var(--border);">
                Usuario
              </th>
              <th class="text-left py-3 px-4 font-mono text-xs uppercase border-b" style="color: var(--text-dim); border-color: var(--border);">
                Fecha
              </th>
              <th class="text-left py-3 px-4 font-mono text-xs uppercase border-b" style="color: var(--text-dim); border-color: var(--border);">
                Hora
              </th>
              <th class="text-left py-3 px-4 font-mono text-xs uppercase border-b" style="color: var(--text-dim); border-color: var(--border);">
                Estado
              </th>
              <th class="text-left py-3 px-4 font-mono text-xs uppercase border-b" style="color: var(--text-dim); border-color: var(--border);">
                Acciones
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="reservation in reservations.data"
              :key="reservation.slug"
              class="table-row transition-colors"
              style="font-family: 'DM Sans', sans-serif; font-size: 0.875rem; color: var(--text-primary);"
              @mouseenter="$event.currentTarget.style.background = 'var(--bg-card)'"
              @mouseleave="$event.currentTarget.style.background = 'transparent'"
            >
              <td class="py-4 px-4 border-b" style="border-color: rgba(0,220,255,0.06);">
                {{ reservation.space_name }}
              </td>
              <td class="py-4 px-4 border-b" style="border-color: rgba(0,220,255,0.06);">
                {{ reservation.user_name }}
              </td>
              <td class="py-4 px-4 border-b" style="border-color: rgba(0,220,255,0.06);">
                {{ formatDate(reservation.start_time) }}
              </td>
              <td class="py-4 px-4 border-b font-mono text-xs" style="border-color: rgba(0,220,255,0.06); color: var(--lime);">
                {{ formatTime(reservation.start_time) }}
              </td>
              <td class="py-4 px-4 border-b" style="border-color: rgba(0,220,255,0.06);">
                <StatusBadge :status="reservation.status" />
              </td>
              <td class="py-4 px-4 border-b" style="border-color: rgba(0,220,255,0.06);">
                <div class="flex gap-2">
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
              </td>
            </tr>
          </tbody>
        </table>
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
