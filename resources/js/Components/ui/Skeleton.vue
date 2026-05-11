<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String,
  variant: {
    type: String,
    default: 'text',
    validator: (v) => ['text', 'card', 'avatar', 'button'].includes(v)
  }
})

const base = 'animate-pulse bg-muted rounded-none'

const variants = {
  text: 'h-3 w-full',
  card: 'h-48 w-full',
  avatar: 'h-10 w-10 rounded-full',
  button: 'h-8 w-24',
}

const classes = computed(() => cn(
  base,
  variants[props.variant],
  props.class
))
</script>

<template>
  <div :class="classes" />
</template>
