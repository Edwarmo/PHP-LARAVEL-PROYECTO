<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import StatusBadge from '@/Components/StatusBadge.vue'

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
    <div class="max-w-4xl mx-auto px-4 py-8">
      <div class="mb-8 flex justify-between items-center">
        <div>
          <Link href="/admin/reservations" class="text-xs font-mono text-dim hover:text-white transition-colors">← VOLVER AL LISTADO</Link>
          <h1 class="text-3xl mt-2 font-light" style="font-family: 'Cormorant Garamond', serif; color: var(--text-primary);">
            Detalle de Reserva
          </h1>
        </div>
        <button 
          @click="deleteReservation"
          class="px-4 py-2 border border-red-500/50 text-red-500 text-xs uppercase tracking-widest hover:bg-red-500/10 transition-colors"
        >
          Eliminar Reserva
        </button>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Sidebar: Info -->
        <div class="lg:col-span-1 space-y-6">
          <div class="border p-6" style="background: var(--bg-card); border-color: var(--border); border-radius: 0;">
            <div class="font-mono text-xs uppercase tracking-wide mb-4" style="color: var(--text-dim);">Información</div>
            <div class="space-y-4">
              <div>
                <label class="font-mono text-[10px] uppercase text-dim block mb-1">Sala</label>
                <div class="text-sm font-medium text-cyan">{{ reservation.space.name }}</div>
              </div>
              <div>
                <label class="font-mono text-[10px] uppercase text-dim block mb-1">Inicio</label>
                <div class="text-sm text-primary">{{ formatDate(reservation.start_time) }}</div>
              </div>
              <div>
                <label class="font-mono text-[10px] uppercase text-dim block mb-1">Fin</label>
                <div class="text-sm text-primary">{{ formatDate(reservation.end_time) }}</div>
              </div>
              <div>
                <label class="font-mono text-[10px] uppercase text-dim block mb-1">Solicitado el</label>
                <div class="text-sm text-dim">{{ formatDate(reservation.created_at) }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main: Edit Form -->
        <div class="lg:col-span-2">
          <form @submit.prevent="updateReservation" class="border p-8 space-y-6" style="background: var(--bg-card); border-color: var(--border); border-radius: 0;">
            <div class="font-mono text-xs uppercase tracking-wide mb-6" style="color: var(--text-dim);">Gestión de la Reserva</div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label class="font-mono text-xs uppercase tracking-wider mb-2 block" style="color: var(--text-dim);">Nombre del Usuario</label>
                <input
                  v-model="form.user_name"
                  type="text"
                  class="w-full bg-transparent border-b py-2 focus:outline-none focus:border-cyan transition-colors"
                  style="border-color: var(--border); color: var(--text-primary); border-radius: 0;"
                />
              </div>
              <div>
                <label class="font-mono text-xs uppercase tracking-wider mb-2 block" style="color: var(--text-dim);">Email del Usuario</label>
                <input
                  v-model="form.user_email"
                  type="email"
                  class="w-full bg-transparent border-b py-2 focus:outline-none focus:border-cyan transition-colors"
                  style="border-color: var(--border); color: var(--text-primary); border-radius: 0;"
                />
              </div>
            </div>

            <div>
              <label class="font-mono text-xs uppercase tracking-wider mb-2 block" style="color: var(--text-dim);">Estado de la Reserva</label>
              <select
                v-model="form.status"
                class="w-full bg-transparent border-b py-2 focus:outline-none focus:border-cyan transition-colors"
                style="border-color: var(--border); color: var(--text-primary); border-radius: 0; appearance: none;"
              >
                <option value="pendiente">Pendiente</option>
                <option value="confirmada">Confirmada (Aceptada)</option>
                <option value="rechazada">Rechazada</option>
                <option value="cancelada">Cancelada</option>
                <option value="finalizada">Finalizada</option>
              </select>
              <div class="mt-2">
                <StatusBadge :status="form.status" />
              </div>
            </div>

            <div>
              <label class="font-mono text-xs uppercase tracking-wider mb-2 block" style="color: var(--text-dim);">Notas Administrativas / Motivos</label>
              <textarea
                v-model="form.notes"
                rows="4"
                class="w-full bg-transparent border-b py-2 focus:outline-none focus:border-cyan transition-colors resize-none"
                style="border-color: var(--border); color: var(--text-primary); border-radius: 0;"
                placeholder="Escribe aquí notas sobre el cambio de estado o instrucciones adicionales..."
              ></textarea>
            </div>

            <div class="flex items-center justify-between pt-4">
              <span v-if="form.recentlySuccessful" class="text-xs font-mono text-lime">✓ CAMBIOS GUARDADOS</span>
              <button
                type="submit"
                :disabled="form.processing"
                class="px-8 py-3 font-medium uppercase tracking-widest transition-all"
                style="background: var(--cyan); color: #000; border-radius: 0;"
                :style="{ opacity: form.processing ? 0.6 : 1 }"
              >
                {{ form.processing ? 'GUARDANDO...' : 'GUARDAR CAMBIOS' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>

<style scoped>
select {
  background-color: #0c1018 !important;
}
select option {
  background: #0c1018;
  color: white;
}
</style>
