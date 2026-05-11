<script setup>
import { Badge } from '@/Components/ui'

const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: (value) => ['pendiente', 'confirmada', 'rechazada', 'cancelada', 'finalizada'].includes(value)
  }
})

const statusConfig = {
  pendiente: { variant: 'secondary', label: 'Pendiente' },
  confirmada: { variant: 'default', label: 'Confirmada' },
  rechazada: { variant: 'destructive', label: 'Rechazada' },
  cancelada: { variant: 'secondary', label: 'Cancelada' },
  finalizada: { variant: 'secondary', label: 'Finalizada' }
}

const config = statusConfig[props.status]
</script>

<template>
  <Badge :variant="config.variant">
    {{ config.label }}
  </Badge>
</template>