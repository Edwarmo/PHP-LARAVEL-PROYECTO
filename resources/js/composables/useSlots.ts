import { ref } from 'vue'
import type { Slot } from '@/types/domain'
import { route } from 'ziggy-js'

export function useSlots(spaceSlug: string) {
  const slots = ref<Slot[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)

  async function fetchSlots(date: string): Promise<void> {
    loading.value = true
    error.value = null

    try {
      const response = await fetch(route('api.slots', spaceSlug, false) + `?date=${date}`)
      if (!response.ok) throw new Error('Error al cargar los horarios')
      const data = await response.json()
      slots.value = data.slots ?? []
    } catch (e) {
      error.value = e instanceof Error ? e.message : 'Error desconocido'
      slots.value = []
    } finally {
      loading.value = false
    }
  }

  return { slots, loading, error, fetchSlots }
}
