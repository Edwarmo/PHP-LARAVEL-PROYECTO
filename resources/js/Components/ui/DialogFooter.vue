<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String
})

const classes = computed(() => cn('flex flex-col-reverse sm:flex-row sm:justify-end gap-2', props.class))
</script>

<template>
  <div :class="classes">
    <slot />
  </div>
</template>
