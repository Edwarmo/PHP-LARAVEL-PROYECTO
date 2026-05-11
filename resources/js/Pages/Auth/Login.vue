<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/Components/ui'
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
      <Card class="border border-cyan/20 bg-card/80 backdrop-blur-sm">
        <CardHeader>
          <CardTitle class="text-2xl font-light">Acceso Usuarios</CardTitle>
          <CardDescription>BIENVENIDO — Ingresa tus credenciales</CardDescription>
        </CardHeader>
        
        <form @submit.prevent="submit">
          <CardContent class="space-y-4">
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
              <p v-if="form.errors.email" class="text-xs text-destructive">{{ form.errors.email }}</p>
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
              <p v-if="form.errors.password" class="text-xs text-destructive">{{ form.errors.password }}</p>
            </div>

            <div class="flex items-center space-x-2">
              <input
                type="checkbox"
                id="remember"
                v-model="form.remember"
                class="h-4 w-4 rounded-none border-border bg-background"
              />
              <Label for="remember" class="font-normal">Recordar sesión</Label>
            </div>
          </CardContent>

          <CardFooter class="flex flex-col space-y-4">
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
          </CardFooter>
        </form>
      </Card>
    </div>
  </PublicLayout>
</template>