<script setup>
import Skeleton from '@/Components/ui/Skeleton.vue'
</script>

<template>
  <div class="mx-auto max-w-7xl px-6 py-12 space-y-6">
    <Skeleton variant="text" class="w-48" />
    <Skeleton variant="card" />
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <Skeleton variant="card" v-for="i in 3" :key="i" />
    </div>
  </div>
</template>
