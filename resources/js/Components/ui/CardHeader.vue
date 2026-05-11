<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String
})

const classes = computed(() => cn('grid auto-rows-min items-start gap-1.5 px-4', props.class))
</script>

<template>
  <div :class="classes">
    <slot />
  </div>
</template>