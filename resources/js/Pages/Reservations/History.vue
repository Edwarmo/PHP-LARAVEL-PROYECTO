<script setup>
import { ref, onMounted } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Badge } from '@/Components/ui'
import { Card, CardContent } from '@/Components/ui'
import { Button } from '@/Components/ui'
import { Input } from '@/Components/ui'

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
</script>

<template>
  <Head title="Historial de Reservas" />
  <PublicLayout>
    <div class="mx-auto max-w-4xl px-4 py-12">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-7xl font-light break-words text-foreground">Historial de Reservas</h1>
        <p class="text-sm text-muted-foreground">
          Consulta tus reservas ingresando tu correo electrónico
        </p>
      </div>

      <!-- Search Form -->
      <div class="mb-8">
        <form @submit.prevent="search" class="flex flex-col gap-4 sm:flex-row">
          <Input
            v-model="searchEmail"
            type="email"
            placeholder="tu@email.com"
            required
            class="flex-1"
          />
          <Button type="submit" class="w-full sm:w-auto border border-cyan text-cyan hover:bg-cyan hover:text-black">
            Buscar
          </Button>
        </form>
      </div>

      <!-- Results -->
      <div v-if="email && reservations.length > 0" class="space-y-3">
        <div class="mb-4 font-mono text-xs uppercase tracking-wide text-muted-foreground">
          {{ reservations.length }} reserva{{ reservations.length !== 1 ? 's' : '' }} encontrada{{ reservations.length !== 1 ? 's' : '' }}
        </div>

        <a
          v-for="reservation in reservations"
          :key="reservation.slug"
          :href="`/reservations/${reservation.slug}`"
          class="block border border-cyan/20 bg-card/50 p-6 transition-colors hover:bg-card/80"
        >
          <div class="flex flex-col items-start justify-between gap-4 sm:flex-row">
            <div class="flex-1">
              <h3 class="mb-1 text-xl font-light text-foreground">
                {{ reservation.space_name }}
              </h3>
              <p class="text-sm text-muted-foreground">
                {{ reservation.user_name }}
              </p>
            </div>
            <Badge :variant="reservation.status === 'pendiente' ? 'secondary' : 'default'">
              {{ reservation.status }}
            </Badge>
          </div>

          <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <div class="font-mono text-xs uppercase tracking-wider text-muted-foreground">Fecha</div>
              <div class="text-foreground">{{ formatDate(reservation.start_time) }}</div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider text-muted-foreground">Horario</div>
              <div class="font-mono text-lime">{{ formatTime(reservation.start_time) }} — {{ formatTime(reservation.end_time) }}</div>
            </div>
          </div>
        </a>
      </div>

      <!-- Empty States -->
      <div v-else-if="email && reservations.length === 0" class="py-16 text-center">
        <p class="font-mono text-xs text-muted-foreground">
          — NO SE ENCONTRARON RESERVAS PARA ESTE EMAIL —
        </p>
      </div>

      <div v-else class="py-16 text-center">
        <p class="font-mono text-xs text-muted-foreground">
          — INGRESA TU EMAIL PARA VER TUS RESERVAS —
        </p>
      </div>
    </div>
  </PublicLayout>
</template>