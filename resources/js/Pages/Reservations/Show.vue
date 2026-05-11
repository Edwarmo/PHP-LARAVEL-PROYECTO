<script setup>
import { onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Badge } from '@/Components/ui'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui'
import { Button } from '@/Components/ui'

const props = defineProps({
  reservation: Object,
  userReservations: { type: Array, default: () => [] },
})

const statusMessages = {
  pendiente: 'Tu solicitud está siendo revisada por nuestro equipo. Te notificaremos pronto.',
  confirmada: '¡Tu reserva está confirmada! Recuerda llegar 5 minutos antes.',
  rechazada: 'Lamentamos informarte que tu solicitud no pudo ser procesada.',
  cancelada: 'Tu reserva ha sido cancelada. Puedes solicitar un nuevo horario.',
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
</script>

<template>
  <Head title="Estado de tu Reserva" />
  <PublicLayout>
    <div class="mx-auto max-w-4xl px-4 py-12">
      <!-- Current Reservation -->
      <div class="mb-12">
        <div class="mb-4 font-mono text-xs uppercase tracking-wide text-muted-foreground">Reserva actual</div>
        
        <Card class="border border-cyan/20 bg-card/80 backdrop-blur-sm">
          <CardHeader class="flex flex-row items-start justify-between">
            <div>
              <CardTitle class="text-3xl font-light break-words md:text-4xl lg:text-5xl">
                {{ reservation.space.name }}
              </CardTitle>
              <p class="text-sm text-muted-foreground">
                {{ statusMessages[reservation.status] }}
              </p>
            </div>
            <Badge :variant="reservation.status === 'pendiente' ? 'secondary' : 'default'">
              {{ reservation.status }}
            </Badge>
          </CardHeader>
          
          <CardContent class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <div class="font-mono text-xs uppercase tracking-wider text-muted-foreground">Fecha</div>
              <div class="text-foreground">{{ formatDate(reservation.start_time) }}</div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider text-muted-foreground">Horario</div>
              <div class="font-mono text-lime">{{ formatTime(reservation.start_time) }} — {{ formatTime(reservation.end_time) }}</div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider text-muted-foreground">Solicitante</div>
              <div class="text-foreground">{{ reservation.user_name }}</div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider text-muted-foreground">Email</div>
              <div class="text-foreground">{{ reservation.user_email }}</div>
            </div>
            <div v-if="reservation.notes" class="col-span-1 sm:col-span-2">
              <div class="font-mono text-xs uppercase tracking-wider text-muted-foreground">Notas</div>
              <div class="text-muted-foreground">{{ reservation.notes }}</div>
            </div>
          </CardContent>

          <CardFooter>
            <p class="font-mono text-xs text-muted-foreground">Ref: {{ reservation.slug }}</p>
          </CardFooter>
        </Card>
      </div>

      <!-- Reservation History -->
      <div v-if="userReservations.length > 0">
        <div class="mb-4 font-mono text-xs uppercase tracking-wide text-muted-foreground">
          Historial de reservas
        </div>
        
        <div class="space-y-3">
          <Link
            v-for="res in userReservations"
            :key="res.slug"
            :href="`/reservations/${res.slug}`"
            class="block border border-cyan/20 bg-card/50 p-6 transition-colors hover:bg-card/80"
          >
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row">
              <div class="flex-1">
                <h3 class="mb-1 text-xl font-light text-foreground">
                  {{ res.space_name }}
                </h3>
                <p class="text-sm text-muted-foreground">
                  {{ res.user_name }}
                </p>
              </div>
              <Badge :variant="res.status === 'pendiente' ? 'secondary' : 'default'">
                {{ res.status }}
              </Badge>
            </div>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <div class="font-mono text-xs uppercase tracking-wider text-muted-foreground">Fecha</div>
                <div class="text-foreground">{{ formatDate(res.start_time) }}</div>
              </div>
              <div>
                <div class="font-mono text-xs uppercase tracking-wider text-muted-foreground">Horario</div>
                <div class="font-mono text-lime">{{ formatTime(res.start_time) }} — {{ formatTime(res.end_time) }}</div>
              </div>
            </div>
          </Link>
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-8 text-center">
        <Link
          href="/"
          class="inline-block border border-cyan px-6 py-3 text-xs uppercase tracking-wider text-cyan transition-colors hover:bg-cyan hover:text-black"
        >
          Ver otras salas disponibles
        </Link>
      </div>
    </div>
  </PublicLayout>
</template>