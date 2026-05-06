<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import SpaceCard from '@/Components/SpaceCard.vue'
import { gsap } from 'gsap'

const props = defineProps({
  spaces: Object,
})

const search = ref('')
const typeFilter = ref('')

const filteredSpaces = computed(() => {
  let result = props.spaces.data || []
  if (search.value) {
    const s = search.value.toLowerCase()
    result = result.filter(space => space.name.toLowerCase().includes(s) || space.description?.toLowerCase().includes(s))
  }
  if (typeFilter.value) result = result.filter(space => space.type === typeFilter.value)
  return result
})

onMounted(() => {
  gsap.from('.hero-title', { y: 20, opacity: 0, duration: 1, ease: 'power3.out' })
  gsap.from('.space-card', { y: 40, opacity: 0, stagger: 0.12, duration: 0.9, ease: 'power3.out' })
})
</script>

<template>
  <Head title="Salas Disponibles" />
  <PublicLayout>
    <!-- Hero -->
    <div class="h-48 flex flex-col items-center justify-center hero-title">
      <div class="font-mono text-xs uppercase tracking-[0.3em]" style="color: var(--text-dim);">
        RESERVAS
      </div>
      <h1 class="text-5xl mt-2" style="font-family: 'Cormorant Garamond', serif;">
        <span class="font-light" style="color: var(--text-primary);">Salas de</span>
        <span style="color: var(--cyan);">Videoconferencia</span>
      </h1>
      <p class="text-sm mt-3" style="font-family: 'DM Sans', sans-serif; color: var(--text-muted);">
        Encuentra el espacio perfecto para tus reuniones virtuales
      </p>
    </div>

    <!-- Filters -->
    <div class="max-w-7xl mx-auto px-4 mt-8">
      <div class="flex gap-4">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar sala..."
          class="flex-1 px-4 py-2 bg-transparent border-b focus:outline-none transition-colors"
          style="border-color: var(--border); color: var(--text-primary); border-radius: 0;"
          @focus="$event.target.style.borderColor = 'var(--cyan)'"
          @blur="$event.target.style.borderColor = 'var(--border)'"
        />
        <select
          v-model="typeFilter"
          class="px-4 py-2 border-b focus:outline-none"
          style="background: var(--bg-card); border-color: var(--border); color: var(--text-primary); border-radius: 0;"
        >
          <option value="">Todos los tipos</option>
          <option value="sala">Sala</option>
          <option value="auditorio">Auditorio</option>
          <option value="estudio">Estudio</option>
        </select>
      </div>
      <div class="h-px mt-4" style="background: var(--border);"></div>
    </div>

    <!-- Grid -->
    <div class="max-w-7xl mx-auto px-4 mt-10">
      <div v-if="filteredSpaces.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-px" style="background: var(--border);">
        <div
          v-for="space in filteredSpaces"
          :key="space.id"
          class="p-6 space-card"
          style="background: var(--bg-base);"
        >
          <SpaceCard :space="space" />
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="text-center py-12 font-mono text-xs" style="color: var(--text-dim);">
        — SIN RESULTADOS —
      </div>
    </div>
  </PublicLayout>
</template>
