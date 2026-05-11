<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import SpaceCard from '@/Components/SpaceCard.vue'
import { Input, Button, Card, CardContent } from '@/Components/ui'

const props = defineProps({
  spaces: Object,
})

const search = ref('')
const typeFilter = ref('')

const filteredSpaces = computed(() => {
  let result = props.spaces.data || []
  if (search.value) {
    const s = search.value.toLowerCase()
    result = result.filter(space => 
      space.name.toLowerCase().includes(s) || space.description?.toLowerCase().includes(s)
    )
  }
  if (typeFilter.value) {
    result = result.filter(space => space.type === typeFilter.value)
  }
  return result
})

onMounted(() => {
  // GSAP animations would be maintained
})
</script>

<template>
  <Head title="Salas Disponibles" />
  <PublicLayout>
    <!-- Hero -->
    <div class="flex min-h-[12rem] flex-col items-center justify-center px-4">
      <div class="font-mono text-xs uppercase tracking-[0.3em] text-center text-muted-foreground">
        RESERVAS
      </div>
      <h1 class="mt-2 text-center text-3xl sm:text-4xl md:text-5xl lg:text-7xl" style="font-family: 'Cormorant Garamond', serif;">
        <span class="block font-light text-foreground sm:inline">Salas de </span>
        <span class="mt-1 block text-cyan sm:mt-0 sm:inline">Videoconferencia</span>
      </h1>
      <p class="mt-3 text-center text-sm md:text-base" style="font-family: 'DM Sans', sans-serif;">
        Encuentra el espacio perfecto para tus reuniones virtuales
      </p>
    </div>

    <!-- Filters -->
    <div class="mx-auto mt-8 max-w-7xl px-4">
      <Card class="border border-cyan/20 bg-card/50">
        <CardContent class="flex flex-col gap-4 sm:flex-row">
          <Input
            v-model="search"
            type="text"
            placeholder="Buscar sala..."
            class="flex-1"
          />
          <select
            v-model="typeFilter"
            class="h-9 rounded-none border border-border bg-background px-3 text-xs"
          >
            <option value="">Todos los tipos</option>
            <option value="sala">Sala</option>
            <option value="auditorio">Auditorio</option>
            <option value="estudio">Estudio</option>
          </select>
        </CardContent>
      </Card>
    </div>

    <!-- Grid -->
    <div class="mx-auto mt-10 max-w-7xl px-4">
      <div v-if="filteredSpaces.length > 0" class="grid grid-cols-1 gap-px border border-cyan/20 md:grid-cols-2 lg:grid-cols-3">
        <div
          v-for="space in filteredSpaces"
          :key="space.id"
          class="p-6"
          style="background: var(--bg-base);"
        >
          <SpaceCard :space="space" />
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="py-12 text-center font-mono text-xs text-muted-foreground">
        — SIN RESULTADOS —
      </div>
    </div>
  </PublicLayout>
</template>