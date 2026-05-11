<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String
})

const classes = computed(() => cn('text-xs text-muted-foreground', props.class))
</script>

<template>
  <div :class="classes">
    <slot />
  </div>
</template>
