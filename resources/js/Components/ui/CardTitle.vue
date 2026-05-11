<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String
})

const classes = computed(() => cn('font-heading text-sm font-medium', props.class))
</script>

<template>
  <div :class="classes">
    <slot />
  </div>
</template>