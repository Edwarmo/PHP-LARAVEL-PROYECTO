<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String,
  size: { type: String, default: 'default' }
})

const classes = computed(() => cn(
  'flex flex-col gap-4 rounded-none bg-card py-4 text-xs text-card-foreground ring-1 ring-foreground/10',
  props.class
))
</script>

<template>
  <div :class="classes">
    <slot />
  </div>
</template>