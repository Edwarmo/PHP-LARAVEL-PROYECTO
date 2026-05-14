<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import PublicLayout from '@/Layouts/PublicLayout.vue'
import { Badge } from '@/Components/ui'
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui'
import { Button } from '@/Components/ui'
import { useSlots } from '@/composables/useSlots'
import { formatCurrency } from '@/lib/formatters'

const props = defineProps({
  space: Object,
  nextAvailableDays: Array,
  slotMinutes: Number,
})

const selectedDate = ref(props.nextAvailableDays[0]?.date ?? null)

const { slots: slotsForDate, loading: loadingSlots, error: slotsError, fetchSlots } = useSlots(props.space.slug)

function selectDay(date) {
  selectedDate.value = date
  fetchSlots(date)
}

function bookSlot(slot) {
  if (!slot.available || !selectedDate.value) return
  const startTime = `${selectedDate.value}T${slot.label.split(' ')[0]}:00`
  router.visit(`/reservations/new?space=${props.space.slug}&start=${startTime}&duration=60`)
}

onMounted(() => {
  if (selectedDate.value) selectDay(selectedDate.value)
})
</script>

<template>
  <Head :title="space.name" />
  <PublicLayout>
    <div class="mx-auto max-w-7xl px-4 py-8">
      <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
        <!-- Left Column -->
        <div>
          <div class="text-muted-foreground">
            <Link href="/" class="hover:opacity-70">Salas</Link> / {{ space.name }}
          </div>

          <div class="mt-4 flex items-center gap-3">
            <Badge variant="outline" class="border-border text-muted-foreground">
              {{ space.type }}
            </Badge>
            <span class="font-mono text-xs text-muted-foreground">{{ space.capacity }} personas</span>
          </div>

          <h1 class="mt-4 break-words text-3xl font-light md:text-5xl lg:text-7xl text-foreground">
            {{ space.name }}
          </h1>

          <div class="mb-6 mt-2 h-px w-10 bg-cyan"></div>

          <p class="text-muted-foreground">
            {{ space.description }}
          </p>

          <div v-if="space.rules" class="mt-8">
            <h2 class="mb-3 font-mono text-xs uppercase tracking-wide text-muted-foreground">Reglas de uso</h2>
            <ul class="space-y-2">
              <li v-for="(rule, i) in space.rules.split('\n')" :key="i" class="text-sm text-muted-foreground">
                <span class="text-muted-foreground">—</span> {{ rule }}
              </li>
            </ul>
          </div>

          <div class="border-t border-border pt-6 mt-8">
            <div class="font-mono text-xs uppercase tracking-wide mb-2 text-muted-foreground">Precio por hora</div>
            <div class="font-mono text-3xl text-lime">
              ${{ formatCurrency(space.price_per_hour) }}
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="sticky top-24 h-fit">
          <div class="mb-4 font-mono text-xs uppercase tracking-wide text-muted-foreground">Disponibilidad</div>

          <!-- Day Tabs -->
          <div class="mb-4 flex gap-px overflow-x-auto pb-2">
            <Button
              v-for="day in nextAvailableDays"
              :key="day.date"
              :variant="selectedDate === day.date ? 'default' : 'ghost'"
              size="sm"
              @click="selectDay(day.date)"
            >
              <div class="flex flex-col">
                <span class="text-[10px] font-light">{{ day.day_name.slice(0, 3) }}</span>
                <span>{{ day.date.split('-')[2] }}</span>
              </div>
            </Button>
          </div>

          <!-- Slots -->
          <div class="grid grid-cols-2 gap-px border border-cyan/20 lg:grid-cols-1">
            <div v-if="slotsError" class="col-span-full py-8 text-center font-mono text-xs text-red-400">{{ slotsError }}</div>
            <div v-else-if="loadingSlots" class="col-span-full py-8 text-center font-mono text-xs text-muted-foreground">cargando...</div>
            <div v-else-if="!slotsForDate.length" class="col-span-full py-8 text-center font-mono text-xs text-muted-foreground">sin horarios disponibles</div>
            <Button
              v-else
              v-for="slot in slotsForDate"
              :key="slot.label"
              :variant="slot.available ? 'ghost' : 'secondary'"
              :disabled="!slot.available"
              class="w-full justify-start"
              @click="bookSlot(slot)"
            >
              <span class="font-mono text-xs" :class="{ 'text-muted-foreground': !slot.available }">
                {{ slot.available ? slot.label : slot.label + ' — ocupado' }}
              </span>
            </Button>
          </div>
        </div>
      </div>
    </div>
  </PublicLayout>
</template>