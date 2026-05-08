<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, useForm, router, usePage } from '@inertiajs/vue3'
import { gsap } from 'gsap'
import PublicLayout from '@/Layouts/PublicLayout.vue'

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
  console.log('Submitting with:', {
    user_name: form.user_name,
    user_email: form.user_email,
    notes: form.notes,
    space_id: props.space.id,
    start_time: props.start,
    duration: props.duration,
  })
  
  form.transform((data) => ({
    ...data,
    space_id: props.space.id,
    start_time: props.start,
    duration: props.duration,
  })).post('/reservations', {
    preserveScroll: true,
    onSuccess: (response) => { 
      console.log('Success:', response)
      success.value = true 
    },
    onError: (errors) => {
      console.error('Validation errors:', errors)
    }
  })
}

function handleSubmit(e) {
  gsap.to(e.target, { scale: 0.97, duration: 0.1, yoyo: true, repeat: 1 })
  submit()
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
  <Head title="Nueva Reserva" />
  <PublicLayout>
    <div class="max-w-2xl mx-auto px-4 py-12">
      <!-- Success -->
      <div v-if="success" class="text-center py-16">
        <h1 class="text-4xl font-light" style="font-family: 'Cormorant Garamond', serif; color: var(--text-primary);">
          Reserva recibida — Revisa tu correo
        </h1>
      </div>

      <!-- Form -->
      <form v-else-if="space && space.name" @submit.prevent="handleSubmit" class="space-y-8">
        <!-- Section 1: Summary -->
        <div class="reveal-block border p-6" style="background: var(--bg-card); border-color: var(--border); border-radius: 0;">
          <div class="font-mono text-xs uppercase tracking-wide mb-4" style="color: var(--text-dim);">Resumen</div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Espacio</div>
              <div class="text-sm" style="color: var(--cyan); font-family: 'DM Sans', sans-serif;">{{ space?.name || 'N/A' }}</div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Fecha</div>
              <div class="text-sm" style="color: var(--text-primary); font-family: 'DM Sans', sans-serif;">{{ formattedDate }}</div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Hora</div>
              <div class="text-sm font-mono" style="color: var(--lime);">{{ formattedTime }}</div>
            </div>
            <div>
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Duración</div>
              <div class="text-sm font-mono" style="color: var(--text-primary);">{{ duration }} min</div>
            </div>
            <div class="col-span-1 sm:col-span-2">
              <div class="font-mono text-xs uppercase tracking-wider mb-1" style="color: var(--text-dim);">Precio est.</div>
              <div class="text-sm font-mono" style="color: var(--lime);">${{ estimatedPrice.toLocaleString('es-CO') }}</div>
            </div>
          </div>
        </div>

        <!-- Section 2: User Data -->
        <div class="reveal-block">
          <div class="font-mono text-xs uppercase tracking-wide mb-4" style="color: var(--text-dim);">Tus datos</div>
          
          <div class="space-y-4">
            <div>
              <label class="font-mono text-xs uppercase tracking-wider mb-1 block" style="color: var(--text-dim);">Nombre completo</label>
              <input
                v-model="form.user_name"
                type="text"
                :readonly="user"
                class="w-full bg-transparent border-b py-3 focus:outline-none transition-colors"
                style="border-color: var(--border); color: var(--text-primary); font-family: 'DM Sans', sans-serif; border-radius: 0;"
                :style="{ opacity: user ? 0.6 : 1 }"
                @focus="$event.target.style.borderColor = 'var(--cyan)'"
                @blur="$event.target.style.borderColor = 'var(--border)'"
              />
              <p v-if="form.errors.user_name" class="mt-1 text-xs font-mono" style="color: var(--text-dim);">{{ form.errors.user_name }}</p>
            </div>

            <div>
              <label class="font-mono text-xs uppercase tracking-wider mb-1 block" style="color: var(--text-dim);">Email</label>
              <input
                v-model="form.user_email"
                type="email"
                :readonly="user"
                class="w-full bg-transparent border-b py-3 focus:outline-none transition-colors"
                style="border-color: var(--border); color: var(--text-primary); font-family: 'DM Sans', sans-serif; border-radius: 0;"
                :style="{ opacity: user ? 0.6 : 1 }"
                @focus="$event.target.style.borderColor = 'var(--cyan)'"
                @blur="$event.target.style.borderColor = 'var(--border)'"
              />
              <p v-if="form.errors.user_email" class="mt-1 text-xs font-mono" style="color: var(--text-dim);">{{ form.errors.user_email }}</p>
            </div>

            <div>
              <label class="font-mono text-xs uppercase tracking-wider mb-1 block" style="color: var(--text-dim);">Notas</label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full bg-transparent border-b py-3 focus:outline-none transition-colors resize-none"
                style="border-color: var(--border); color: var(--text-primary); font-family: 'DM Sans', sans-serif; border-radius: 0;"
                @focus="$event.target.style.borderColor = 'var(--cyan)'"
                @blur="$event.target.style.borderColor = 'var(--border)'"
              ></textarea>
              <p v-if="form.errors.notes" class="mt-1 text-xs font-mono" style="color: var(--text-dim);">{{ form.errors.notes }}</p>
            </div>
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full mt-6 py-3 font-medium uppercase tracking-widest transition-all"
            style="background: #c8ff00; color: #000; font-family: 'DM Sans', sans-serif; border-radius: 0;"
            :style="{ opacity: form.processing ? 0.6 : 1 }"
          >
            {{ form.processing ? 'ENVIANDO...' : 'CONFIRMAR RESERVA' }}
          </button>

          <!-- General Errors -->
          <div v-if="Object.keys(form.errors).length > 0" class="mt-4 p-3 border" style="border-color: #ef4444; background: rgba(239,68,68,0.1);">
            <p class="font-mono text-xs mb-2" style="color: #ef4444;">ERRORES DE VALIDACIÓN:</p>
            <ul class="space-y-1">
              <li v-for="(error, key) in form.errors" :key="key" class="text-xs" style="color: #ef4444; font-family: 'DM Sans', sans-serif;">
                • {{ error }}
              </li>
            </ul>
          </div>
        </div>
      </form>

      <!-- Error State -->
      <div v-else class="text-center py-16">
        <p class="font-mono text-xs" style="color: var(--text-dim);">— DATOS DE RESERVA NO DISPONIBLES —</p>
      </div>
    </div>
  </PublicLayout>
</template>
