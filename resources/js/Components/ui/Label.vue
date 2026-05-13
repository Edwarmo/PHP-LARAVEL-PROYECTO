<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String,
  htmlFor: String
})

const classes = computed(() => cn(
  'text-xs font-medium leading-none text-foreground peer-disabled:cursor-not-allowed peer-disabled:opacity-50',
  props.class
))
</script>

<template>
  <label :class="classes" :for="htmlFor">
    <slot />
  </label>
</template>