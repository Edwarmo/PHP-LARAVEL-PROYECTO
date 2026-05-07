<script setup>
import { ref, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { gsap } from 'gsap'
import PublicLayout from '@/Layouts/PublicLayout.vue'

const props = defineProps({
  space: Object,
  nextAvailableDays: Array,
  slotMinutes: Number,
})

const selectedDate = ref(props.nextAvailableDays[0]?.date ?? null)
const loadingSlots = ref(false)
const slotsForDate = ref([])

async function selectDay(date) {
  selectedDate.value = date
  loadingSlots.value = true
  try {
    const res = await fetch(`/api/spaces/${props.space.slug}/slots?date=${date}`, { headers: { Accept: 'application/json' } })
    const data = await res.json()
    slotsForDate.value = data.slots ?? []
  } catch {
    slotsForDate.value = []
  } finally {
    loadingSlots.value = false
  }
}

function bookSlot(slot) {
  if (!slot.available || !selectedDate.value) return
  const startTime = `${selectedDate.value}T${slot.label.split(' ')[0]}:00`
  router.visit(`/reservations/new?space=${props.space.slug}&start=${startTime}&duration=60`)
}

watch(slotsForDate, () => {
  setTimeout(() => {
    const items = document.querySelectorAll('.slot-item')
    if (items.length > 0) {
      gsap.from(items, { y: 20, opacity: 0, stagger: 0.05, duration: 0.5, ease: 'power3.out' })
    }
  }, 50)
})

onMounted(() => {
  gsap.from('.reveal-left', { y: 30, opacity: 0, stagger: 0.1, duration: 0.8, ease: 'power3.out' })
  if (selectedDate.value) selectDay(selectedDate.value)
})
</script>

<template>
  <Head :title="space.name" />
  <PublicLayout>
    <div class="max-w-7xl mx-auto px-4 py-8">
      <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
        <!-- Left Column -->
        <div>
          <div class="reveal-left font-mono text-xs uppercase tracking-wide" style="color: var(--text-dim);">
            <Link href="/" class="hover:opacity-70">Salas</Link> / {{ space.name }}
          </div>

          <div class="reveal-left flex items-center gap-3 mt-4">
            <span class="px-3 py-1 border text-xs uppercase" style="border-color: var(--border); color: var(--text-muted); border-radius: 0;">
              {{ space.type }}
            </span>
            <span class="font-mono text-xs" style="color: var(--text-dim);">{{ space.capacity }} personas</span>
          </div>

          <h1 class="reveal-left text-3xl md:text-5xl lg:text-7xl font-light mt-4 break-words" style="font-family: 'Cormorant Garamond', serif; color: var(--text-primary);">
            {{ space.name }}
          </h1>

          <div class="reveal-left w-10 h-px mt-2 mb-6" style="background: var(--cyan);"></div>

          <p class="reveal-left leading-relaxed" style="font-family: 'DM Sans', sans-serif; color: var(--text-muted);">
            {{ space.description }}
          </p>

          <div v-if="space.rules" class="reveal-left mt-8">
            <h2 class="font-mono text-xs uppercase tracking-wide mb-3" style="color: var(--text-dim);">Reglas de uso</h2>
            <ul class="space-y-2">
              <li v-for="(rule, i) in space.rules.split('\n')" :key="i" class="text-sm" style="font-family: 'DM Sans', sans-serif; color: var(--text-muted);">
                <span style="color: var(--text-dim);">—</span> {{ rule }}
              </li>
            </ul>
          </div>

          <div class="reveal-left mt-8 pt-6" style="border-top: 1px solid var(--border);">
            <div class="font-mono text-xs uppercase tracking-wide mb-2" style="color: var(--text-dim);">Precio por hora</div>
            <div class="text-3xl" style="font-family: 'JetBrains Mono', monospace; color: var(--lime);">
              ${{ Number(space.price_per_hour).toLocaleString('es-CO') }}
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="sticky top-24 h-fit mt-8 lg:mt-0">
          <div class="font-mono text-xs uppercase tracking-wide mb-4" style="color: var(--text-dim);">Disponibilidad</div>

          <!-- Day Tabs -->
          <div class="flex gap-px mb-4 overflow-x-auto pb-2" style="scrollbar-width: none; -ms-overflow-style: none;">
            <button
              v-for="day in nextAvailableDays"
              :key="day.date"
              @click="selectDay(day.date)"
              class="flex-1 min-w-[52px] py-3 px-2 flex flex-col items-center gap-1 transition-colors"
              :style="{
                background: 'transparent',
                borderBottom: selectedDate === day.date ? '1px solid rgba(240,244,248,0.5)' : '1px solid var(--border)',
                color: selectedDate === day.date ? 'var(--text-primary)' : 'var(--text-muted)',
              }"
            >
              <span class="font-mono text-[10px] font-light lowercase">{{ day.day_name.slice(0,3) }}</span>
              <span class="font-mono text-base font-light">{{ day.date.split('-')[2] }}</span>
              <span
                class="w-1 h-1 rounded-full transition-opacity"
                :style="{ background: 'var(--text-primary)', opacity: selectedDate === day.date ? 1 : 0 }"
              />
            </button>
          </div>

          <!-- Slots -->
          <div class="grid grid-cols-2 lg:grid-cols-1 gap-px">
            <div v-if="loadingSlots" class="col-span-full py-8 text-center font-mono text-xs" style="color: var(--text-dim);">cargando...</div>
            <button
              v-else
              v-for="slot in slotsForDate"
              :key="slot.label"
              @click="bookSlot(slot)"
              :disabled="!slot.available"
              class="slot-item w-full py-3 px-4 text-center lg:text-left transition-colors"
              :style="{
                background: 'transparent',
                borderBottom: '0.5px solid var(--border)',
                opacity: slot.available ? 1 : 0.3,
                cursor: slot.available ? 'pointer' : 'not-allowed',
              }"
              @mouseenter="e => { if (slot.available) e.currentTarget.style.background = 'rgba(240,244,248,0.03)' }"
              @mouseleave="e => e.currentTarget.style.background = 'transparent'"
            >
              <span class="font-mono text-xs font-light" :style="{ color: slot.available ? 'var(--text-primary)' : 'var(--text-dim)' }">
                {{ slot.available ? slot.label : slot.label + ' — ocupado' }}
              </span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>
