<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  space: {
    type: Object,
    required: true
  }
})

const typeLabels = {
  conferencia: 'Conferencia',
  reunión: 'Reunión',
  webinar: 'Webinar'
}
</script>

<template>
  <Link
    :href="`/spaces/${space.slug}`"
    class="card section-card block p-6 transition-all duration-200 ease-out hover:-translate-y-1 hover:shadow-lg"
  >
    <div class="flex items-start justify-between mb-4">
      <span class="badge-custom" style="background:#00C2CB22;border-color:#00C2CB44;color:#00C2CB;font-size:.72rem">
        {{ typeLabels[space.type] || space.type }}
      </span>
      <span class="flex items-center gap-1 font-mono text-sm" style="color:#9FB2D1">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 3 0 01-4 0 2 3 0 014 0zM7 10a2 3 0 01-4 0 2 3 0 014 0z" />
        </svg>
        {{ space.capacity }}
      </span>
    </div>

    <h3 class="text-2xl font-light mb-3" style="color:#EAF2FF">
      {{ space.name }}
    </h3>

    <p class="text-sm line-clamp-2 mb-6" style="color:#9FB2D1">
      {{ space.description }}
    </p>

    <div class="flex items-end justify-between pt-4" style="border-top:1px solid rgba(255,255,255,0.06)">
      <div>
        <p class="font-mono text-xs uppercase tracking-wider mb-1" style="color:#9FB2D1">Precio/hora</p>
        <p class="font-mono text-xl" style="color:#c8ff00">
          ${{ Number(space.price_per_hour).toLocaleString('es-CO') }}
        </p>
      </div>
      <span class="text-sm" style="color:#00C2CB;font-weight:700">
        Ver →
      </span>
    </div>
  </Link>
</template>