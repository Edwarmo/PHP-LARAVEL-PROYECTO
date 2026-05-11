<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/Components/ui'
import { Button } from '@/Components/ui'
import { Input } from '@/Components/ui'
import { Label } from '@/Components/ui'

const props = defineProps({
  space: { type: Object, default: () => ({}) },
  start: { type: String, default: '' },
  duration: { type: Number, default: 60 },
})

const page = usePage()
const user = computed(() => page.props.auth?.user)

const form = useForm({
  user_name: user.value?.name || '',
  user_email: user.value?.email || '',
  notes: '',
})

const success = ref(false)

const estimatedPrice = computed(() => {
  if (!props.space?.price_per_hour) return 0
  return (props.space.price_per_hour * props.duration) / 60
})

const formattedDate = computed(() => {
  if (!props.start) return ''
  const d = new Date(props.start)
  return d.toLocaleDateString('es-CO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })
})

const formattedTime = computed(() => {
  if (!props.start) return ''
  const d = new Date(props.start)
  const end = new Date(d.getTime() + props.duration * 60000)
  return `${d.toTimeString().slice(0, 5)} — ${end.toTimeString().slice(0, 5)}`
})

function submit() {
  form.transform((data) => ({
    ...data,
    space_id: props.space.id,
    start_time: props.start,
    duration: props.duration,
  })).post('/reservations', {
    preserveScroll: true,
    onSuccess: () => { success.value = true },
  })
}
</script>

<template>
  <Head title="Nueva Reserva" />
  <PublicLayout>
    <div class="mx-auto max-w-2xl px-4 py-12">
      <!-- Success -->
      <div v-if="success" class="py-16 text-center">
        <h1 class="text-4xl font-light text-foreground">
          Reserva recibida — Revisa tu correo
        </h1>
      </div>

      <!-- Form -->
      <form v-else-if="space && space.name" @submit.prevent="submit" class="space-y-8">
        <!-- Section 1: Summary -->
        <Card class="border border-cyan/20 bg-card/80 backdrop-blur-sm">
          <CardHeader>
            <CardTitle>Resumen</CardTitle>
          </CardHeader>
          <CardContent class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <Label class="text-muted-foreground">Espacio</Label>
              <div class="text-cyan">{{ space?.name || 'N/A' }}</div>
            </div>
            <div>
              <Label class="text-muted-foreground">Fecha</Label>
              <div class="text-foreground">{{ formattedDate }}</div>
            </div>
            <div>
              <Label class="text-muted-foreground">Hora</Label>
              <div class="font-mono text-lime">{{ formattedTime }}</div>
            </div>
            <div>
              <Label class="text-muted-foreground">Duración</Label>
              <div class="font-mono text-foreground">{{ duration }} min</div>
            </div>
            <div class="col-span-1 sm:col-span-2">
              <Label class="text-muted-foreground">Precio est.</Label>
              <div class="font-mono text-lime">${{ estimatedPrice.toLocaleString('es-CO') }}</div>
            </div>
          </CardContent>
        </Card>

        <!-- Section 2: User Data -->
        <Card class="border border-cyan/20 bg-card/80 backdrop-blur-sm">
          <CardHeader>
            <CardTitle>Tus datos</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-2">
              <Label for="user_name">Nombre completo</Label>
              <Input
                id="user_name"
                v-model="form.user_name"
                type="text"
                :readonly="user"
                :style="{ opacity: user ? 0.6 : 1 }"
              />
              <p v-if="form.errors.user_name" class="text-xs text-destructive">{{ form.errors.user_name }}</p>
            </div>

            <div class="space-y-2">
              <Label for="user_email">Email</Label>
              <Input
                id="user_email"
                v-model="form.user_email"
                type="email"
                :readonly="user"
                :style="{ opacity: user ? 0.6 : 1 }"
              />
              <p v-if="form.errors.user_email" class="text-xs text-destructive">{{ form.errors.user_email }}</p>
            </div>

            <div class="space-y-2">
              <Label for="notes">Notas</Label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="w-full resize-none rounded-none border border-border bg-background px-3 py-2 text-xs text-foreground focus:outline-none focus:ring-1 focus:ring-ring"
              ></textarea>
              <p v-if="form.errors.notes" class="text-xs text-destructive">{{ form.errors.notes }}</p>
            </div>
          </CardContent>
          <CardFooter class="flex flex-col space-y-4">
            <Button
              type="submit"
              class="w-full bg-lime text-black hover:bg-lime/90"
              :disabled="form.processing"
            >
              {{ form.processing ? 'ENVIANDO...' : 'CONFIRMAR RESERVA' }}
            </Button>

            <div v-if="Object.keys(form.errors).length > 0" class="border border-destructive/50 bg-destructive/10 p-3">
              <p class="mb-2 font-mono text-xs text-destructive">ERRORES DE VALIDACIÓN:</p>
              <ul class="space-y-1">
                <li v-for="(error, key) in form.errors" :key="key" class="text-xs text-destructive">
                  • {{ error }}
                </li>
              </ul>
            </div>
          </CardFooter>
        </Card>
      </form>

      <!-- Error State -->
      <div v-else class="py-16 text-center">
        <p class="font-mono text-xs text-muted-foreground">— DATOS DE RESERVA NO DISPONIBLES —</p>
      </div>
    </div>
  </PublicLayout>
</template>