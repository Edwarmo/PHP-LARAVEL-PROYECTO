<script setup>
import { ref, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AnimatedBackground from '@/Components/AnimatedBackground.vue'
import gsap from 'gsap'

const page = usePage()
const navbarRef = ref(null)
const isScrolled = ref(false)
const isMobileMenuOpen = ref(false)

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

          <!-- Mobile Menu Button -->
          <button 
            @click="isMobileMenuOpen = !isMobileMenuOpen" 
            class="md:hidden flex items-center justify-center w-11 h-11 text-[#00dcff] hover:bg-[#00dcff]/10 transition-colors"
            aria-label="Toggle Menu"
          >
            <svg v-if="!isMobileMenuOpen" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <!-- Desktop Nav Links -->
          <nav class="hidden md:flex items-center gap-8">
            <Link
              href="/"
              class="font-mono text-xs font-light lowercase tracking-wide transition-colors flex items-center h-11"
              :class="$page.url === '/' ? 'text-[#f0f4f8] border-b border-[rgba(240,244,248,0.4)]' : 'text-[#5a7080] hover:text-[#f0f4f8]'"
            >
              salas
            </Link>
            <Link
              href="/historial"
              class="font-mono text-xs font-light lowercase tracking-wide transition-colors flex items-center h-11"
              :class="$page.url === '/historial' ? 'text-[#f0f4f8] border-b border-[rgba(240,244,248,0.4)]' : 'text-[#5a7080] hover:text-[#f0f4f8]'"
            >
              historial
            </Link>

            <!-- Auth Links -->
            <template v-if="!$page.props.auth?.user">
              <Link
                href="/login"
                class="font-mono text-xs font-light lowercase tracking-wide transition-colors flex items-center h-11 text-[#5a7080] hover:text-[#f0f4f8]"
              >
                login
              </Link>
              <Link
                href="/register"
                class="font-mono text-xs font-light lowercase tracking-wide transition-colors flex items-center h-11 px-4 border border-[#00dcff] text-[#00dcff] hover:bg-[#00dcff]/10"
              >
                registro
              </Link>
            </template>
            <template v-else>
              <Link
                v-if="$page.props.auth.user.email === 'admin@videoconfreservas.com'"
                href="/admin"
                class="font-mono text-xs font-light lowercase tracking-wide transition-colors flex items-center h-11 text-[#c8ff00] hover:opacity-80"
              >
                admin
              </Link>
              <Link
                href="/logout"
                method="post"
                as="button"
                class="font-mono text-xs font-light lowercase tracking-wide transition-colors flex items-center h-11 text-[#5a7080] hover:text-[#f0f4f8]"
              >
                salir ({{ $page.props.auth.user.name.split(' ')[0] }})
              </Link>
            </template>
          </nav>
        </div>

        <!-- Mobile Dropdown Menu -->
        <div 
          v-show="isMobileMenuOpen" 
          class="md:hidden absolute top-full left-0 w-full bg-[#0c1018] border-b border-[rgba(0,220,255,0.12)] shadow-xl"
        >
          <nav class="flex flex-col px-6 py-4 space-y-2">
            <Link
              href="/"
              @click="isMobileMenuOpen = false"
              class="font-mono text-sm font-light lowercase tracking-wide transition-colors flex items-center h-12 w-full border-b border-[rgba(255,255,255,0.05)]"
              :class="$page.url === '/' ? 'text-[#f0f4f8]' : 'text-[#5a7080]'"
            >
              salas
            </Link>
            <Link
              href="/historial"
              @click="isMobileMenuOpen = false"
              class="font-mono text-sm font-light lowercase tracking-wide transition-colors flex items-center h-12 w-full border-b border-[rgba(255,255,255,0.05)]"
              :class="$page.url === '/historial' ? 'text-[#f0f4f8]' : 'text-[#5a7080]'"
            >
              historial
            </Link>
            
            <template v-if="!$page.props.auth?.user">
              <Link href="/login" @click="isMobileMenuOpen = false" class="font-mono text-sm lowercase text-[#5a7080] py-2">login</Link>
              <Link href="/register" @click="isMobileMenuOpen = false" class="font-mono text-sm lowercase text-[#00dcff] py-2">registro</Link>
            </template>
            <template v-else>
              <Link v-if="$page.props.auth.user.email === 'admin@videoconfreservas.com'" href="/admin" @click="isMobileMenuOpen = false" class="font-mono text-sm lowercase text-[#c8ff00] py-2">admin</Link>
              <Link href="/logout" method="post" as="button" @click="isMobileMenuOpen = false" class="font-mono text-sm lowercase text-[#5a7080] py-2 text-left">salir</Link>
            </template>
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
