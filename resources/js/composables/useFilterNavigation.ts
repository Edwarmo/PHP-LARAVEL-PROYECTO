import { router } from '@inertiajs/vue3'

interface FilterParams {
  [key: string]: string | number | boolean | undefined
}

export function useFilterNavigation(baseUrl: string) {
  function navigate(params: FilterParams): void {
    router.get(baseUrl, params, {
      preserveState: true,
      preserveScroll: true,
    })
  }

  return { navigate }
}
