<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String
})

const classes = computed(() => cn('flex items-center rounded-none border-t p-4', props.class))
</script>

<template>
  <div :class="classes">
    <slot />
  </div>
</template>