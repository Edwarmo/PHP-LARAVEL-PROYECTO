<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  variant: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'outline', 'secondary', 'ghost', 'destructive', 'link'].includes(v)
  },
  size: {
    type: String,
    default: 'default',
    validator: (v) => ['default', 'xs', 'sm', 'lg', 'icon', 'icon-xs', 'icon-sm', 'icon-lg'].includes(v)
  },
  asChild: { type: Boolean, default: false },
  class: String,
  disabled: Boolean
})

const emit = defineEmits(['click'])

const variantClasses = {
  default: 'bg-primary text-primary-foreground hover:bg-primary/90',
  outline: 'border border-border bg-background hover:bg-muted hover:text-foreground',
  secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
  ghost: 'hover:bg-muted hover:text-foreground',
  destructive: 'bg-destructive/10 text-destructive hover:bg-destructive/20',
  link: 'text-primary underline-offset-4 hover:underline'
}

const sizeClasses = {
  default: 'h-8 px-3 text-xs',
  xs: 'h-6 px-2 text-xs',
  sm: 'h-7 px-2.5 text-xs',
  lg: 'h-9 px-4 text-sm',
  icon: 'h-8 w-8',
  'icon-xs': 'h-6 w-6 text-xs',
  'icon-sm': 'h-7 w-7',
  'icon-lg': 'h-9 w-9'
}

const classes = computed(() => cn(
  'inline-flex items-center justify-center rounded-none font-medium transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50',
  variantClasses[props.variant],
  sizeClasses[props.size],
  props.class
))
</script>

<template>
  <button
    :class="classes"
    :disabled="disabled"
    @click="emit('click', $event)"
  >
    <slot />
  </button>
</template>