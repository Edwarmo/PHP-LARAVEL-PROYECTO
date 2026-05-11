<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Badge } from '@/Components/ui'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui'
import { Button } from '@/Components/ui'
import { Input } from '@/Components/ui'
import { Label } from '@/Components/ui'

const props = defineProps({
  reservation: Object,
})

const form = useForm({
  status: props.reservation.status,
  notes: props.reservation.notes || '',
  user_name: props.reservation.user_name,
  user_email: props.reservation.user_email,
})

const updateReservation = () => {
  form.put(`/admin/reservations/${props.reservation.slug}`)
}

const deleteReservation = () => {
  if (confirm('¿Estás seguro de que deseas eliminar permanentemente esta reserva?')) {
    form.delete(`/admin/reservations/${props.reservation.slug}`)
  }
}

function formatDate(iso) {
  const d = new Date(iso)
  return d.toLocaleDateString('es-CO', { day: '2-digit', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' })
}
</script>

<template>
  <Head :title="`Reserva - ${reservation.user_name}`" />
  <PublicLayout>
    <div class="mx-auto max-w-4xl px-4 py-8">
      <div class="mb-8 flex justify-between items-center">
        <div>
          <Link href="/admin/reservations" class="text-xs font-mono text-muted-foreground hover:text-foreground">← VOLVER AL LISTADO</Link>
          <h1 class="mt-2 text-3xl font-light text-foreground">Detalle de Reserva</h1>
        </div>
        <Button variant="destructive" size="sm" @click="deleteReservation">
          Eliminar Reserva
        </Button>
      </div>

      <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
        <!-- Sidebar: Info -->
        <div class="lg:col-span-1 space-y-6">
          <Card class="border border-cyan/20 bg-card/80">
            <CardHeader>
              <CardTitle>Información</CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div>
                <Label class="text-muted-foreground">Sala</Label>
                <div class="text-cyan">{{ reservation.space.name }}</div>
              </div>
              <div>
                <Label class="text-muted-foreground">Inicio</Label>
                <div class="text-foreground">{{ formatDate(reservation.start_time) }}</div>
              </div>
              <div>
                <Label class="text-muted-foreground">Fin</Label>
                <div class="text-foreground">{{ formatDate(reservation.end_time) }}</div>
              </div>
              <div>
                <Label class="text-muted-foreground">Solicitado el</Label>
                <div class="text-muted-foreground">{{ formatDate(reservation.created_at) }}</div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Main: Edit Form -->
        <div class="lg:col-span-2">
          <form @submit.prevent="updateReservation" class="space-y-6 border border-cyan/20 bg-card/80 p-8">
            <div class="font-mono text-xs uppercase tracking-wide text-muted-foreground">Gestión de la Reserva</div>
            
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
              <div class="space-y-2">
                <Label>Nombre del Usuario</Label>
                <Input v-model="form.user_name" />
              </div>
              <div class="space-y-2">
                <Label>Email del Usuario</Label>
                <Input v-model="form.user_email" type="email" />
              </div>
            </div>

            <div class="space-y-2">
              <Label>Estado de la Reserva</Label>
              <select
                v-model="form.status"
                class="w-full rounded-none border border-border bg-background py-2 px-3 text-xs text-foreground focus:outline-none focus:ring-1 focus:ring-ring"
              >
                <option value="pendiente">Pendiente</option>
                <option value="confirmada">Confirmada (Aceptada)</option>
                <option value="rechazada">Rechazada</option>
                <option value="cancelada">Cancelada</option>
                <option value="finalizada">Finalizada</option>
              </select>
              <div class="mt-2">
                <Badge :variant="form.status === 'pendiente' ? 'secondary' : 'default'">
                  {{ form.status }}
                </Badge>
              </div>
            </div>

            <div class="space-y-2">
              <Label>Notas Administrativas / Motivos</Label>
              <textarea
                v-model="form.notes"
                rows="4"
                class="w-full rounded-none border border-border bg-background py-2 px-3 text-xs text-foreground focus:outline-none focus:ring-1 focus:ring-ring resize-none"
                placeholder="Escribe aquí notas sobre el cambio de estado o instrucciones adicionales..."
              ></textarea>
            </div>

            <div class="flex items-center justify-between pt-4">
              <span v-if="form.recentlySuccessful" class="text-xs font-mono text-lime">✓ CAMBIOS GUARDADOS</span>
              <Button
                type="submit"
                class="bg-cyan text-black hover:bg-cyan/90"
                :disabled="form.processing"
              >
                {{ form.processing ? 'GUARDANDO...' : 'GUARDAR CAMBIOS' }}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>