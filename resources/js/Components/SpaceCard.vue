<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import gsap from 'gsap'

const props = defineProps({
  space: {
    type: Object,
    required: true
  }
})

const cardRef = ref(null)
const priceRef = ref(null)

const typeLabels = {
  conferencia: 'Conferencia',
  reunión: 'Reunión',
  webinar: 'Webinar'
}

const handleMouseEnter = () => {
  if (cardRef.value) {
    const el = cardRef.value.$el || cardRef.value
    gsap.to(el, {
      y: -4,
      scale: 1.01,
      duration: 0.3,
      ease: 'power2.out'
    })
    el.style.borderColor = 'rgba(0, 220, 255, 0.35)'
  }
  if (priceRef.value) {
    gsap.to(priceRef.value, {
      color: '#00dcff',
      duration: 0.3
    })
  }
}

const handleMouseLeave = () => {
  if (cardRef.value) {
    const el = cardRef.value.$el || cardRef.value
    gsap.to(el, {
      y: 0,
      scale: 1,
      duration: 0.3,
      ease: 'power2.out'
    })
    el.style.borderColor = 'rgba(0, 220, 255, 0.12)'
  }
  if (priceRef.value) {
    gsap.to(priceRef.value, {
      color: '#c8ff00',
      duration: 0.3
    })
  }
}
</script>

<template>
  <Link
    :href="`/spaces/${space.slug}`"
    ref="cardRef"
    class="group block bg-[#0c1018] border border-[rgba(0,220,255,0.12)] p-6 transition-colors"
    @mouseenter="handleMouseEnter"
    @mouseleave="handleMouseLeave"
  >
    <!-- Header: Badge + Capacidad -->
    <div class="flex items-start justify-between mb-4">
      <span class="border border-[#00dcff] text-[#00dcff] font-mono text-xs uppercase px-2 py-1 tracking-wider">
        {{ typeLabels[space.type] || space.type }}
      </span>
      <span class="flex items-center gap-1 font-mono text-[#5a7080] text-sm">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
        </svg>
        {{ space.capacity }}
      </span>
    </div>

    <!-- Nombre -->
    <h3 class="font-display text-[28px] font-light text-[#f0f4f8] leading-tight mb-3">
      {{ space.name }}
    </h3>

    <!-- Descripción -->
    <p class="font-sans text-sm text-[#5a7080] line-clamp-2 mb-6">
      {{ space.description }}
    </p>

    <!-- Footer: Precio + Link -->
    <div class="flex items-end justify-between border-t border-[rgba(0,220,255,0.08)] pt-4">
      <div>
        <p class="font-mono text-xs uppercase text-[#2a3a48] tracking-wider mb-1">Precio/hora</p>
        <p ref="priceRef" class="font-mono text-xl text-[#c8ff00] transition-colors">
          ${{ Number(space.price_per_hour).toLocaleString('es-CO') }}
        </p>
      </div>
      <span class="font-sans text-sm text-[#00dcff] group-hover:underline">
        Ver →
      </span>
    </div>
  </Link>
</template>
