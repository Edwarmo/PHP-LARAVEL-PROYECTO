<script setup>
const props = defineProps({
  status: {
    type: String,
    required: true,
    validator: (value) => ['pendiente', 'confirmada', 'rechazada', 'cancelada', 'finalizada'].includes(value)
  }
})

const statusConfig = {
  pendiente: {
    color: '#f0c040',
    label: 'Pendiente'
  },
  confirmada: {
    color: '#00dcff',
    label: 'Confirmada'
  },
  rechazada: {
    color: '#ff4060',
    label: 'Rechazada'
  },
  cancelada: {
    color: '#5a7080',
    label: 'Cancelada'
  },
  finalizada: {
    color: '#c8ff00',
    label: 'Finalizada'
  }
}

const config = statusConfig[props.status]
</script>

<template>
  <span
    class="inline-block border bg-transparent font-mono text-xs uppercase px-2 py-0.5 tracking-wider"
    :style="{
      borderColor: config.color,
      color: config.color
    }"
  >
    {{ config.label }}
  </span>
</template>
