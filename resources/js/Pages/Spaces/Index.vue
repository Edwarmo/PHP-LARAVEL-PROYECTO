<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import SpaceCard from '@/Components/SpaceCard.vue'
import { gsap } from 'gsap'

const props = defineProps({
  spaces: Object,
})

const search = ref('')
const typeFilter = ref('')

const mobileSearchActive = ref(false)
const mobileTypeActive = ref(false)
const searchInputRef = ref(null)

const activateMobileSearch = async () => {
  mobileSearchActive.value = true
  await nextTick()
  if (searchInputRef.value) searchInputRef.value.focus()
}

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
    <div class="min-h-[12rem] flex flex-col items-center justify-center hero-title px-4">
      <div class="font-mono text-xs uppercase tracking-[0.3em] text-center" style="color: var(--text-dim);">
        RESERVAS
      </div>
      <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-7xl mt-2 text-center break-words max-w-full leading-tight" style="font-family: 'Cormorant Garamond', serif;">
        <span class="font-light block sm:inline" style="color: var(--text-primary);">Salas de </span>
        <span class="block sm:inline mt-1 sm:mt-0" style="color: var(--cyan);">Videoconferencia</span>
      </h1>
      <p class="text-sm md:text-base mt-3 text-center" style="font-family: 'DM Sans', sans-serif; color: var(--text-muted);">
        Encuentra el espacio perfecto para tus reuniones virtuales
      </p>
    </div>

    <!-- Filters -->
    <div class="max-w-7xl mx-auto px-4 mt-8">
      
      <!-- Mobile Filters -->
      <div class="sm:hidden flex flex-col gap-2">
        <!-- Search Toggle -->
        <div v-if="!mobileSearchActive && !search" @click="activateMobileSearch" class="w-full px-4 py-3 border text-center text-xs uppercase tracking-wider cursor-pointer transition-colors" style="border-color: var(--border); color: var(--text-primary); border-radius: 0;">
          Buscar Sala
        </div>
        <input
          v-else
          ref="searchInputRef"
          v-model="search"
          type="text"
          placeholder="Buscar sala..."
          class="w-full px-4 py-3 bg-transparent border focus:outline-none transition-colors"
          style="border-color: var(--cyan); color: var(--text-primary); border-radius: 0;"
          @blur="if(!search) mobileSearchActive = false"
        />

        <!-- Type Toggle -->
        <div v-if="!mobileTypeActive && !typeFilter" @click="mobileTypeActive = true" class="w-full px-4 py-3 border text-center text-xs uppercase tracking-wider cursor-pointer transition-colors mt-2" style="border-color: var(--border); color: var(--text-primary); border-radius: 0;">
          Tipos de Sala
        </div>
        <select
          v-else
          v-model="typeFilter"
          class="w-full px-4 py-3 border focus:outline-none mt-2"
          style="background: var(--bg-card); border-color: var(--cyan); color: var(--text-primary); border-radius: 0;"
          @blur="if(!typeFilter) mobileTypeActive = false"
        >
          <option value="">Todos los tipos</option>
          <option value="sala">Sala</option>
          <option value="auditorio">Auditorio</option>
          <option value="estudio">Estudio</option>
        </select>
      </div>

      <!-- Desktop Filters -->
      <div class="hidden sm:flex flex-row gap-4">
        <input
          v-model="search"
          type="text"
          placeholder="Buscar sala..."
          class="flex-1 px-4 py-3 bg-transparent border-b focus:outline-none transition-colors"
          style="border-color: var(--border); color: var(--text-primary); border-radius: 0;"
          @focus="$event.target.style.borderColor = 'var(--cyan)'"
          @blur="$event.target.style.borderColor = 'var(--border)'"
        />
        <select
          v-model="typeFilter"
          class="w-auto px-4 py-3 border-b focus:outline-none"
          style="background: var(--bg-card); border-color: var(--border); color: var(--text-primary); border-radius: 0;"
        >
          <option value="">Todos los tipos</option>
          <option value="sala">Sala</option>
          <option value="auditorio">Auditorio</option>
          <option value="estudio">Estudio</option>
        </select>
      </div>
      <div class="hidden sm:block h-px mt-4" style="background: var(--border);"></div>
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
