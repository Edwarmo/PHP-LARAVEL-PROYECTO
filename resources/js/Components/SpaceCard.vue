<script setup>
import { Link } from '@inertiajs/vue3'
import { Card, CardContent, CardFooter } from '@/Components/ui'
import { Badge } from '@/Components/ui'

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
    class="block border border-cyan/20 p-6 transition-all duration-200 ease-out hover:-translate-y-1 hover:border-cyan/40"
  >
    <div class="flex items-start justify-between mb-4">
      <Badge variant="outline" class="border-cyan text-cyan">
        {{ typeLabels[space.type] || space.type }}
      </Badge>
      <span class="flex items-center gap-1 font-mono text-sm text-muted-foreground">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 3 0 01-4 0 2 3 0 014 0zM7 10a2 3 0 01-4 0 2 3 0 014 0z" />
        </svg>
        {{ space.capacity }}
      </span>
    </div>

    <h3 class="text-2xl font-light text-foreground mb-3">
      {{ space.name }}
    </h3>

    <p class="text-sm text-muted-foreground line-clamp-2 mb-6">
      {{ space.description }}
    </p>

    <div class="flex items-end justify-between border-t border-cyan/10 pt-4">
      <div>
        <p class="font-mono text-xs uppercase text-muted-foreground tracking-wider mb-1">Precio/hora</p>
        <p class="font-mono text-xl text-lime transition-colors">
          ${{ Number(space.price_per_hour).toLocaleString('es-CO') }}
        </p>
      </div>
      <span class="text-sm text-cyan hover:underline">
        Ver →
      </span>
    </div>
  </Link>
</template>