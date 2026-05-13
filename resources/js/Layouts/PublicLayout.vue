<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AnimatedBackground from '@/Components/AnimatedBackground.vue'
import { Skeleton } from '@/Components/ui'

const page = usePage()
const navbarRef = ref(null)
const isScrolled = ref(false)
const isMobileMenuOpen = ref(false)

let gsapInstance = null

onMounted(async () => {
  const { gsap } = await import('gsap')
  gsapInstance = gsap
  gsap.from(navbarRef.value, {
    y: -100,
    opacity: 0,
    duration: 0.8,
    ease: 'power3.out'
  })

  window.addEventListener('scroll', handleScroll)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

function handleScroll() {
  isScrolled.value = window.scrollY > 20
}
</script>

<template>
  <div class="relative min-h-screen font-sans text-slate-100">
    <AnimatedBackground />

    <div class="relative z-10">
      <header
        ref="navbarRef"
        class="sticky top-0 z-50 transition-all duration-300"
        :class="isScrolled ? 'backdrop-blur-2xl bg-background/30 border-b border-cyan/10 shadow-lg shadow-cyan/5' : ''"
      >
        <div class="mx-auto max-w-7xl px-6 py-4 flex items-center justify-between">
          <Link href="/" class="flex items-center gap-3 group">
            <div class="flex h-10 w-10 items-center justify-center border border-cyan transition-colors group-hover:bg-cyan/10">
              <svg class="h-5 w-5 text-cyan" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
              </svg>
            </div>
            <span class="font-medium text-foreground">VideoConf<span class="text-cyan">.</span></span>
          </Link>

          <button 
            @click="isMobileMenuOpen = !isMobileMenuOpen" 
            class="flex h-11 w-11 items-center justify-center text-cyan hover:bg-cyan/10 md:hidden"
            aria-label="Toggle Menu"
          >
            <svg v-if="!isMobileMenuOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <nav v-if="$page.props.auth?.user" class="hidden items-center gap-8 md:flex">
            <Link
              href="/"
              class="flex items-center font-mono text-xs font-light lowercase tracking-wide transition-colors"
              :class="$page.url === '/' ? 'text-foreground border-b border-foreground/40' : 'text-muted-foreground hover:text-foreground'"
            >
              salas
            </Link>
            <Link
              href="/historial"
              class="flex items-center font-mono text-xs font-light lowercase tracking-wide transition-colors"
              :class="$page.url === '/historial' ? 'text-foreground border-b border-foreground/40' : 'text-muted-foreground hover:text-foreground'"
            >
              historial
            </Link>

            <Link
              v-if="$page.props.auth.user?.email === 'admin@videoconfreservas.com'"
              href="/admin"
              class="font-mono text-xs lowercase text-lime hover:opacity-80"
            >
              admin
            </Link>

            <Link
              href="/logout"
              method="post"
              as="button"
              class="font-mono text-xs font-light lowercase tracking-wide text-muted-foreground hover:text-foreground"
            >
              salir ({{ $page.props.auth.user?.name?.split(' ')[0] }})
            </Link>
          </nav>
        </div>

        <div 
          v-if="isMobileMenuOpen && $page.props.auth?.user" 
          class="absolute top-full left-0 w-full bg-card border-b border-border shadow-xl md:hidden"
        >
          <nav class="flex flex-col px-6 py-4 space-y-2">
            <Link
              href="/"
              @click="isMobileMenuOpen = false"
              class="flex items-center font-mono text-sm font-light lowercase tracking-wide border-b border-border py-2"
              :class="$page.url === '/' ? 'text-foreground' : 'text-muted-foreground'"
            >
              salas
            </Link>
            <Link
              href="/historial"
              @click="isMobileMenuOpen = false"
              class="flex items-center font-mono text-sm font-light lowercase tracking-wide border-b border-border py-2"
              :class="$page.url === '/historial' ? 'text-foreground' : 'text-muted-foreground'"
            >
              historial
            </Link>
            <Link v-if="$page.props.auth.user?.email === 'admin@videoconfreservas.com'" href="/admin" @click="isMobileMenuOpen = false" class="font-mono text-sm lowercase text-lime py-2">admin</Link>
            <Link href="/logout" method="post" as="button" @click="isMobileMenuOpen = false" class="font-mono text-sm lowercase text-muted-foreground py-2 text-left">salir</Link>
          </nav>
        </div>
      </header>

      <div v-if="page.props.flash?.success" class="mx-auto max-w-7xl px-6 pt-4">
        <div class="border border-cyan bg-cyan/10 px-4 py-3 text-sm text-cyan animate-in fade-in slide-in-from-top-2 duration-300">
          {{ page.props.flash.success }}
        </div>
      </div>

      <main class="mx-auto max-w-7xl px-6 py-12">
        <slot />
      </main>

      <footer class="border-t border-border py-6 text-center">
        <p class="font-mono text-xs text-muted-foreground">
          &copy; 2026 VideoConf — Powered by Quantum
        </p>
      </footer>
    </div>
  </div>
</template>
