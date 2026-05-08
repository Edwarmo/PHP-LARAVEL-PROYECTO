<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <Head title="Iniciar Sesión" />
  <PublicLayout>
    <div class="max-w-md mx-auto px-4 py-20">
      <div class="text-center mb-10">
        <div class="font-mono text-xs uppercase tracking-[0.3em]" style="color: var(--text-dim);">BIENVENIDO</div>
        <h1 class="text-4xl mt-2 font-light" style="font-family: 'Cormorant Garamond', serif; color: var(--text-primary);">
          Acceso Usuarios
        </h1>
      </div>

      <form @submit.prevent="submit" class="space-y-6 border p-8" style="background: var(--bg-card); border-color: var(--border); border-radius: 0;">
        <div>
          <label class="font-mono text-xs uppercase tracking-wider mb-1 block" style="color: var(--text-dim);">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            class="w-full bg-transparent border-b py-3 focus:outline-none transition-colors"
            style="border-color: var(--border); color: var(--text-primary); font-family: 'DM Sans', sans-serif; border-radius: 0;"
            @focus="$event.target.style.borderColor = 'var(--cyan)'"
            @blur="$event.target.style.borderColor = 'var(--border)'"
          />
          <p v-if="form.errors.email" class="mt-1 text-xs font-mono" style="color: #ef4444;">{{ form.errors.email }}</p>
        </div>

        <div>
          <label class="font-mono text-xs uppercase tracking-wider mb-1 block" style="color: var(--text-dim);">Contraseña</label>
          <input
            v-model="form.password"
            type="password"
            required
            class="w-full bg-transparent border-b py-3 focus:outline-none transition-colors"
            style="border-color: var(--border); color: var(--text-primary); font-family: 'DM Sans', sans-serif; border-radius: 0;"
            @focus="$event.target.style.borderColor = 'var(--cyan)'"
            @blur="$event.target.style.borderColor = 'var(--border)'"
          />
          <p v-if="form.errors.password" class="mt-1 text-xs font-mono" style="color: #ef4444;">{{ form.errors.password }}</p>
        </div>

        <div class="flex items-center">
          <input type="checkbox" v-model="form.remember" id="remember" class="accent-cyan-500" />
          <label for="remember" class="ml-2 text-xs font-mono" style="color: var(--text-muted);">Recordar sesión</label>
        </div>

        <button
          type="submit"
          :disabled="form.processing"
          class="w-full py-3 font-medium uppercase tracking-widest transition-all"
          style="background: var(--cyan); color: #000; font-family: 'DM Sans', sans-serif; border-radius: 0;"
          :style="{ opacity: form.processing ? 0.6 : 1 }"
        >
          {{ form.processing ? 'ENTRANDO...' : 'INICIAR SESIÓN' }}
        </button>

        <div class="text-center pt-4 border-t mt-6" style="border-color: var(--border);">
          <p class="text-xs font-mono" style="color: var(--text-dim);">
            ¿No tienes cuenta?
            <Link href="/register" class="text-white hover:text-cyan-400 underline ml-1">Regístrate aquí</Link>
          </p>
        </div>
      </form>
    </div>
  </PublicLayout>
</template>
