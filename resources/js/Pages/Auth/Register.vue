<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Button } from '@/Components/ui'
import { Input } from '@/Components/ui'
import { Label } from '@/Components/ui'

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const submit = () => {
  form.post('/register', {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="Registro" />
  <PublicLayout>
    <div class="max-w-md mx-auto px-4 py-20">
      <div class="border border-cyan/10 bg-background/20 backdrop-blur-2xl shadow-lg shadow-cyan/5">
        <div class="flex flex-col gap-4 p-6">
          <div class="space-y-1.5">
            <h3 class="text-2xl font-light text-foreground">Únete a nosotros</h3>
            <p class="text-xs text-muted-foreground font-mono">NUEVA CUENTA — Crea tu cuenta ahora</p>
          </div>

          <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-2">
              <Label for="name">Nombre Completo</Label>
              <Input
                id="name"
                v-model="form.name"
                type="text"
                placeholder="Tu nombre"
                required
                :disabled="form.processing"
              />
              <p v-if="form.errors.name" class="text-xs text-rose-400">{{ form.errors.name }}</p>
            </div>

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

            <div class="space-y-2">
              <Label for="password_confirmation">Confirmar Contraseña</Label>
              <Input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                placeholder="••••••••"
                required
                :disabled="form.processing"
              />
            </div>

            <div class="flex flex-col space-y-4 pt-2">
              <Button
                type="submit"
                class="w-full bg-lime text-black hover:bg-lime/90"
                :disabled="form.processing"
              >
                {{ form.processing ? 'REGISTRANDO...' : 'CREAR CUENTA' }}
              </Button>

              <p class="text-center text-xs text-muted-foreground">
                ¿Ya tienes cuenta?
                <Link href="/login" class="text-cyan hover:underline">Inicia sesión</Link>
              </p>
            </div>
          </form>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>