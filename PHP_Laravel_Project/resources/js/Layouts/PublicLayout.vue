<script setup>
import { ref, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AnimatedBackground from '@/Components/AnimatedBackground.vue'
import gsap from 'gsap'

const page = usePage()
const navbarRef = ref(null)
const isScrolled = ref(false)

onMounted(() => {
  // Animación de entrada del navbar
  gsap.from(navbarRef.value, {
    y: -100,
    opacity: 0,
    duration: 0.8,
    ease: 'power3.out'
  })

  // Detectar scroll para efecto blur
  window.addEventListener('scroll', () => {
    isScrolled.value = window.scrollY > 20
  })
})
</script>

<template>
  <div class="relative min-h-screen font-sans text-slate-100">
    <!-- Fondo animado -->
    <AnimatedBackground />

    <!-- Contenido principal -->
    <div class="relative z-10">
      <!-- Navbar -->
      <header
        ref="navbarRef"
        class="sticky top-0 z-50 transition-all duration-300"
        :class="isScrolled ? 'backdrop-blur-xl bg-[#06080f]/80 border-b border-[rgba(0,220,255,0.12)]' : ''"
      >
        <div class="mx-auto max-w-7xl px-6 py-4 flex items-center justify-between">
          <!-- Logo -->
          <Link href="/" class="flex items-center gap-3 group">
            <div class="w-10 h-10 bg-[#0c1018] border border-[#00dcff] flex items-center justify-center transition-colors group-hover:bg-[#00dcff]/10">
              <svg class="w-5 h-5 text-[#00dcff]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
            </div>
            <span class="font-sans font-medium text-[#f0f4f8]">VideoConf<span class="text-[#00dcff]">.</span></span>
          </Link>

          <!-- Nav Links + CTA -->
          <nav class="flex items-center gap-8">
            <Link
              href="/"
              class="font-sans text-xs uppercase tracking-widest text-[#5a7080] hover:text-[#00dcff] transition-colors"
            >
              Salas
            </Link>
            <Link
              href="/historial"
              class="bg-[#c8ff00] text-[#06080f] font-sans font-medium px-5 py-2 hover:bg-[#d4ff33] transition-colors"
            >
              Historial
            </Link>
          </nav>
        </div>
      </header>

      <!-- Flash Messages -->
      <div v-if="page.props.flash?.success" class="mx-auto max-w-7xl px-6 pt-4">
        <div class="border border-[#00dcff] bg-[#00dcff]/10 px-4 py-3 font-sans text-sm text-[#00dcff]">
          {{ page.props.flash.success }}
        </div>
      </div>

      <!-- Main Content -->
      <main class="mx-auto max-w-7xl px-6 py-12">
        <slot />
      </main>

      <!-- Footer -->
      <footer class="border-t border-[rgba(0,220,255,0.08)] py-6 text-center">
        <p class="font-mono text-xs text-[#2a3a48]">
          © 2026 VideoConf — Powered by Quantum
        </p>
      </footer>
    </div>
  </div>
</template>
