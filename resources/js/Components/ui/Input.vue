<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  type: { type: String, default: 'text' },
  placeholder: String,
  modelValue: [String, Number],
  class: String,
  disabled: Boolean,
  required: Boolean
})

const emit = defineEmits(['update:modelValue'])

const classes = computed(() => cn(
  'flex w-full rounded-none border border-border bg-background px-3 py-2 text-xs text-foreground transition-colors file:border-0 file:bg-transparent file:text-xs file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50',
  props.class
))
</script>

<template>
  <input
    :type="type"
    :class="classes"
    :placeholder="placeholder"
    :value="modelValue"
    :disabled="disabled"
    :required="required"
    @input="emit('update:modelValue', $event.target.value)"
  />
</template>