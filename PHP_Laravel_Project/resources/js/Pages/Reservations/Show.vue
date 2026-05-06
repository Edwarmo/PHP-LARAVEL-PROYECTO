<script setup>
import { onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { gsap } from 'gsap'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

const props = defineProps({
  reservation: Object,
  userReservations: { type: Array, default: () => [] },
})

const statusMessages = {
  pendiente:  'Tu solicitud está siendo revisada por nuestro equipo. Te notificaremos pronto.',
  confirmada: '¡Tu reserva está confirmada! Recuerda llegar 5 minutos antes.',
  rechazada:  'Lamentamos informarte que tu solicitud no pudo ser procesada.',
  cancelada:  'Tu reserva ha sido cancelada. Puedes solicitar un nuevo horario.',
  finalizada: 'Tu reserva ha concluido. ¡Esperamos que haya sido una gran experiencia!',
}

function formatDate(isoString) {
  return new Date(isoString).toLocaleDateString('es-CO', {
    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
  })
}
function formatTime(isoString) {
  return new Date(isoString).toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  setTimeout(() => {
    const blocks = document.querySelectorAll('.reveal-block')
    if (blocks.length > 0) {
      gsap.from(blocks, { y: 30, opacity: 0, stagger: 0.15, duration: 0.8, ease: 'power3.out' })
    }
  }, 50)
})
</script>

<template>
  <Head title="Estado de tu Reserva" />
  <PublicLayout>
    <div class="max-w-4xl mx-auto px-4 py-12">
      <!-- Current Reservation -->
      <div class="reveal-block mb-12">
        <div class="font-mono text-xs uppercase tracking-wide mb-4" style="color: var(--text-dim);">Reserva actual</div>
        
        <div class="border p-6" style="background: var(--bg-card); border-color: var(--border); border-radius: 0;">
          <div class="flex items-start justify-between mb-6">
            <div>
              <h1 class="text-3xl font-light mb-2" style="font-family: 'Cormorant Garamond', serif; color: var(--text-primary);">
                {{ reservation.space.name }}
              </h1>
              <p class="text-sm" style="font-family: 'DM Sans', sans-serif; color: var(--text-muted);">
                {{ statusMessages[reservation.status] }}
              </p>
            </div>
            <StatusBadge :status="reservation.status" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Fecha</div>
              <div class="text-sm" style="font-family: 'DM Sans', sans-serif; color: var(--text-primary);">{{ formatDate(reservation.start_time) }}</div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Horario</div>
              <div class="text-sm font-mono" style="color: var(--lime);">{{ formatTime(reservation.start_time) }} — {{ formatTime(reservation.end_time) }}</div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Solicitante</div>
              <div class="text-sm" style="font-family: 'DM Sans', sans-serif; color: var(--text-primary);">{{ reservation.user_name }}</div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Email</div>
              <div class="text-sm" style="font-family: 'DM Sans', sans-serif; color: var(--text-primary);">{{ reservation.user_email }}</div>
            </div>
            <div v-if="reservation.notes" class="col-span-2">
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Notas</div>
              <div class="text-sm" style="font-family: 'DM Sans', sans-serif; color: var(--text-muted);">{{ reservation.notes }}</div>
            </div>
          </div>

          <div class="mt-6 pt-4" style="border-top: 1px solid var(--border);">
            <p class="font-mono text-xs" style="color: var(--text-dim);">Ref: {{ reservation.slug }}</p>
          </div>
        </div>
      </div>

      <!-- Reservation History -->
      <div v-if="userReservations.length > 0" class="reveal-block">
        <div class="font-mono text-xs uppercase tracking-wide mb-4" style="color: var(--text-dim);">Historial de reservas</div>
        
        <div class="space-y-3">
          <Link
            v-for="res in userReservations"
            :key="res.slug"
            :href="`/reservations/${res.slug}`"
            class="block border p-4 transition-colors"
            style="background: var(--bg-base); border-color: var(--border); border-radius: 0;"
            @mouseenter="$event.currentTarget.style.background = 'var(--bg-card)'"
            @mouseleave="$event.currentTarget.style.background = 'var(--bg-base)'"
          >
            <div class="flex items-center justify-between">
              <div class="flex-1">
                <div class="text-sm font-medium mb-1" style="font-family: 'DM Sans', sans-serif; color: var(--text-primary);">
                  {{ res.space_name }}
                </div>
                <div class="font-mono text-xs" style="color: var(--text-muted);">
                  {{ formatDate(res.start_time) }} • {{ formatTime(res.start_time) }}
                </div>
              </div>
              <StatusBadge :status="res.status" />
            </div>
          </Link>
        </div>
      </div>

      <!-- Actions -->
      <div class="reveal-block mt-8 text-center">
        <Link
          href="/"
          class="inline-block px-6 py-3 border text-xs uppercase font-medium tracking-wider transition-colors"
          style="border-color: var(--cyan); color: var(--cyan); background: transparent; border-radius: 0;"
          @mouseenter="$event.target.style.background = 'var(--cyan)'; $event.target.style.color = '#000'"
          @mouseleave="$event.target.style.background = 'transparent'; $event.target.style.color = 'var(--cyan)'"
        >
          Ver otras salas disponibles
        </Link>
      </div>
    </div>
  </PublicLayout>
</template>
