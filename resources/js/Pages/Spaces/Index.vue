<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import SpaceCard from '@/Components/SpaceCard.vue'

const props = defineProps({
  spaces: Object,
})

const search = ref('')
const typeFilter = ref('')

const heroTitle = ref(null)
const heroSubtitle = ref(null)
const heroCta = ref(null)
const gridContainer = ref(null)

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

onMounted(async () => {
  const { gsap } = await import('gsap')

  const tl = gsap.timeline({ defaults: { duration: 0.7, ease: 'power3.out' } })
  tl.from(heroTitle.value, { opacity: 0, y: 24 })
    .from(heroSubtitle.value, { opacity: 0, y: 24 }, '+=0.3')
    .from(heroCta.value, { opacity: 0, y: 24 }, '+=0.3')

  if (gridContainer.value) {
    gsap.from(gridContainer.value.children, {
      opacity: 0,
      y: 16,
      duration: 0.5,
      stagger: 0.08,
      ease: 'power2.out',
    })
  }
})
</script>

<style scoped>
.minimal-input, .minimal-select {
  background: color-mix(in oklab, var(--card) 40%, transparent);
  border: 1px solid var(--border);
  color: var(--foreground);
  border-radius: 999px;
  min-height: 44px;
  padding: 0 18px;
  outline: none;
  font-size: .88rem;
  backdrop-filter: blur(8px);
  transition: border-color .2s, box-shadow .2s;
}
.minimal-input:focus, .minimal-select:focus {
  border-color: #00C2CB;
  box-shadow: 0 0 0 1px #00C2CB44;
}
.minimal-input { flex: 1 1 260px; }
.minimal-select { min-width: 140px; cursor: pointer; }
.minimal-select option { background: var(--background); color: var(--foreground); }

.transparent-card :deep(.card) {
  background: color-mix(in oklab, var(--card) 45%, transparent) !important;
  backdrop-filter: blur(16px) !important;
}
</style>

<template>
  <Head title="Salas Disponibles" />
  <PublicLayout>
    <!-- Hero -->
    <div ref="heroTitle" class="flex flex-col items-center justify-center py-8">
      <div class="eyebrow">Reservas</div>
      <h1 class="mt-4 text-center text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-light" style="font-family: 'Cormorant Garamond', serif; color:#EAF2FF">
        Salas de <span class="text-cyan">Videoconferencia</span>
      </h1>
      <p ref="heroSubtitle" class="mt-4 text-center text-muted-foreground/80 text-base max-w-lg">
        Encuentra el espacio perfecto para tus reuniones virtuales
      </p>
    </div>

    <!-- Filters -->
    <div ref="heroCta" class="mb-10 flex items-center justify-center gap-3">
      <input v-model="search" type="text" placeholder="Buscar sala..." class="minimal-input" />
      <select v-model="typeFilter" class="minimal-select">
        <option value="">Todas</option>
        <option v-for="type in ['Sala Privada', 'Sala Pública']" :key="type" :value="type">{{ type }}</option>
      </select>
    </div>

    <!-- Grid -->
    <div v-if="filteredSpaces.length > 0" ref="gridContainer" class="grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-3">
      <div v-for="space in filteredSpaces" :key="space.id" class="transparent-card">
        <SpaceCard :space="space" />
      </div>
    </div>

    <!-- Empty -->
    <div v-else class="empty-state">— SIN RESULTADOS —</div>
  </PublicLayout>
</template>