<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import gsap from 'gsap'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps({
  reservations: { type: Array, default: () => [] },
  email: { type: String, default: '' },
})

const searchEmail = ref(props.email || '')

function search() {
  if (!searchEmail.value) return
  router.get('/historial', { email: searchEmail.value }, { preserveState: true })
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
  setTimeout(() => {
    const items = document.querySelectorAll('.history-item')
    if (items.length > 0) {
      gsap.from(items, { y: 20, opacity: 0, stagger: 0.08, duration: 0.6, ease: 'power3.out' })
    }
  }, 50)
})
</script>

<template>
  <Head title="Historial de Reservas" />
  <PublicLayout>
    <div class="max-w-4xl mx-auto px-4 py-12">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-4xl font-light mb-2" style="font-family: 'Cormorant Garamond', serif; color: var(--text-primary);">
          Historial de Reservas
        </h1>
        <p class="text-sm" style="font-family: 'DM Sans', sans-serif; color: var(--text-muted);">
          Consulta tus reservas ingresando tu correo electrónico
        </p>
      </div>

      <!-- Search Form -->
      <div class="mb-8">
        <form @submit.prevent="search" class="flex gap-4">
          <input
            v-model="searchEmail"
            type="email"
            placeholder="tu@email.com"
            required
            class="flex-1 px-4 py-3 bg-transparent border-b focus:outline-none transition-colors"
            style="border-color: var(--border); color: var(--text-primary); font-family: 'DM Sans', sans-serif; border-radius: 0;"
            @focus="$event.target.style.borderColor = 'var(--cyan)'"
            @blur="$event.target.style.borderColor = 'var(--border)'"
          />
          <button
            type="submit"
            class="px-6 py-3 border text-xs uppercase font-medium tracking-wider transition-colors"
            style="border-color: var(--cyan); color: var(--cyan); background: transparent; border-radius: 0;"
            @mouseenter="$event.target.style.background = 'var(--cyan)'; $event.target.style.color = '#000'"
            @mouseleave="$event.target.style.background = 'transparent'; $event.target.style.color = 'var(--cyan)'"
          >
            Buscar
          </button>
        </form>
      </div>

      <!-- Results -->
      <div v-if="email && reservations.length > 0" class="space-y-3">
        <div class="font-mono text-xs uppercase tracking-wide mb-4" style="color: var(--text-dim);">
          {{ reservations.length }} reserva{{ reservations.length !== 1 ? 's' : '' }} encontrada{{ reservations.length !== 1 ? 's' : '' }}
        </div>

        <a
          v-for="reservation in reservations"
          :key="reservation.slug"
          :href="`/reservations/${reservation.slug}`"
          class="history-item block border p-6 transition-colors"
          style="background: var(--bg-base); border-color: var(--border); border-radius: 0;"
          @mouseenter="$event.currentTarget.style.background = 'var(--bg-card)'"
          @mouseleave="$event.currentTarget.style.background = 'var(--bg-base)'"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="flex-1">
              <h3 class="text-xl font-light mb-1" style="font-family: 'Cormorant Garamond', serif; color: var(--text-primary);">
                {{ reservation.space_name }}
              </h3>
              <p class="text-sm" style="font-family: 'DM Sans', sans-serif; color: var(--text-muted);">
                {{ reservation.user_name }}
              </p>
            </div>
            <StatusBadge :status="reservation.status" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Fecha</div>
              <div class="text-sm" style="font-family: 'DM Sans', sans-serif; color: var(--text-primary);">
                {{ formatDate(reservation.start_time) }}
              </div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Horario</div>
              <div class="text-sm font-mono" style="color: var(--lime);">
                {{ formatTime(reservation.start_time) }} — {{ formatTime(reservation.end_time) }}
              </div>
            </div>
          </div>
        </a>
      </div>

      <!-- Empty State -->
      <div v-else-if="email && reservations.length === 0" class="text-center py-16">
        <p class="font-mono text-xs" style="color: var(--text-dim);">
          — NO SE ENCONTRARON RESERVAS PARA ESTE EMAIL —
        </p>
      </div>

      <div v-else class="text-center py-16">
        <p class="font-mono text-xs" style="color: var(--text-dim);">
          — INGRESA TU EMAIL PARA VER TUS RESERVAS —
        </p>
      </div>
    </div>
  </PublicLayout>
</template>
