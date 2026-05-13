<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Button } from '@/Components/ui'
import { Input } from '@/Components/ui'
import { Label } from '@/Components/ui'

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
      <div class="border border-cyan/10 bg-background/20 backdrop-blur-2xl shadow-lg shadow-cyan/5">
        <div class="flex flex-col gap-4 p-6">
          <div class="space-y-1.5">
            <h3 class="text-2xl font-light text-foreground">Acceso Usuarios</h3>
            <p class="text-xs text-muted-foreground font-mono">BIENVENIDO — Ingresa tus credenciales</p>
          </div>

          <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2">
              <Label for="email">Email</Label>
              <Input
                id="email"
                v-model="form.email"
                type="email"
                placeholder="tu@email.com"
                required
                :disabled="form.processing"
              />
              <p v-if="form.errors.email" class="text-xs text-rose-400">{{ form.errors.email }}</p>
            </div>

            <div class="space-y-2">
              <Label for="password">Contraseña</Label>
              <Input
                id="password"
                v-model="form.password"
                type="password"
                placeholder="••••••••"
                required
                :disabled="form.processing"
              />
              <p v-if="form.errors.password" class="text-xs text-rose-400">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center space-x-2">
              <input
                type="checkbox"
                id="remember"
                v-model="form.remember"
                class="h-4 w-4 rounded-none border border-cyan/20 bg-background/60 text-cyan"
              />
              <Label for="remember" class="font-normal">Recordar sesión</Label>
            </div>

            <div class="flex flex-col space-y-4 pt-2">
              <Button
                type="submit"
                class="w-full bg-cyan text-black hover:bg-cyan/90"
                :disabled="form.processing"
              >
                {{ form.processing ? 'ENTRANDO...' : 'INICIAR SESIÓN' }}
              </Button>

              <p class="text-center text-xs text-muted-foreground">
                ¿No tienes cuenta?
                <Link href="/register" class="text-cyan hover:underline">Regístrate aquí</Link>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>