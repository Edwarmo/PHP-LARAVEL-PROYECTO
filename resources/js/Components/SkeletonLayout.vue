<template>
  <div class="skeleton-page">
    <div class="skeleton-header">
      <div class="skeleton-line" style="width: 12rem; height: 20px; margin-bottom: 8px;" />
      <div class="skeleton-line" style="width: 6rem; height: 14px;" />
    </div>
    <div class="skeleton-grid">
      <div v-for="n in 4" :key="n" class="skeleton-card">
        <div class="skeleton-image" />
        <div class="skeleton-body">
          <div class="skeleton-line" style="width: 75%; height: 16px;" />
          <div class="skeleton-line" style="width: 50%; height: 12px; margin-top: 8px;" />
          <div class="skeleton-line" style="width: 100%; height: 12px; margin-top: 12px;" />
          <div class="skeleton-line" style="width: 33%; height: 12px; margin-top: 8px;" />
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.skeleton-page {
  padding: 16px;
  max-width: 640px;
  margin: 0 auto;
}
.skeleton-header { margin-bottom: 24px; }
.skeleton-grid {
  display: flex;
  flex-direction: column;
  gap: 16px;
}
.skeleton-card {
  background: #141414;
  border-radius: 10px;
  overflow: hidden;
}
.skeleton-image {
  aspect-ratio: 16 / 9;
  background: linear-gradient(90deg, #1a1a1a 25%, #242424 50%, #1a1a1a 75%);
  background-size: 200% 100%;
  animation: pulse 1.5s ease-in-out infinite;
}
.skeleton-body { padding: 16px; }
.skeleton-line {
  background: linear-gradient(90deg, #1a1a1a 25%, #242424 50%, #1a1a1a 75%);
  background-size: 200% 100%;
  border-radius: 4px;
  animation: pulse 1.5s ease-in-out infinite;
}
@keyframes pulse {
  0%, 100% { background-position: 200% 0; }
  50% { background-position: -200% 0; }
}
</style>
