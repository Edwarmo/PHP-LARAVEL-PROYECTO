const LOCALE = 'es-CO'

export function formatDate(iso: string, style: 'short' | 'long' = 'short'): string {
  const opts: Intl.DateTimeFormatOptions =
    style === 'long'
      ? { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
      : { day: 'numeric', month: 'numeric', year: 'numeric' }
  return new Date(iso).toLocaleDateString(LOCALE, opts)
}

export function formatTime(iso: string): string {
  return new Date(iso).toTimeString().slice(0, 5)
}

export function formatTimeRange(startIso: string, endIso: string): string {
  return `${formatTime(startIso)} – ${formatTime(endIso)}`
}

export function formatCurrency(value: string | number): string {
  return Number(value).toLocaleString(LOCALE)
}
