import { ref } from 'vue'
import { formatDate, formatTime } from '@/lib/formatters'

export function useAdminDashboard() {
  const searchQuery = ref('')
  const statusFilter = ref('todas')

  function filteredReservations(reservas) {
    const q = searchQuery.value.toLowerCase().trim()
    const s = statusFilter.value
    return reservas.filter(r => {
      const haystack = `${r.space_name} ${r.user_name} ${r.start_time} ${r.status}`.toLowerCase()
      return (!q || haystack.includes(q)) && (s === 'todas' || r.status === s)
    })
  }

  function exportCsv(reservas, filename = 'videoconf_reservas.csv') {
    const headers = ['sala', 'usuario', 'fecha', 'inicio', 'fin', 'estado']
    const rows = reservas.map(r => [
      r.space_name, r.user_name,
      formatDate(r.start_time), formatTime(r.start_time),
      r.end_time ? formatTime(r.end_time) : '',
      r.status
    ].map(v => `"${String(v).replaceAll('"', '""')}"`).join(','))
    const csv = [headers.join(','), ...rows].join('\n')
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    link.href = URL.createObjectURL(blob)
    link.download = filename
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }

  const metricLabels = {
    pendientes: 'Pendientes',
    confirmadas: 'Confirmadas',
    hoy: 'Reservas hoy',
    esta_semana: 'Esta semana',
    total_spaces: 'Salas activas',
  }

  const metricColors = {
    pendientes: '#F59E0B',
    confirmadas: '#00C2CB',
    hoy: '#22C55E',
    esta_semana: '#8B5CF6',
    total_spaces: '#F472B6',
  }

  const statusBadgeColors = {
    pendiente: '#F59E0B',
    confirmada: '#00C2CB',
    rechazada: '#EF4444',
    cancelada: '#8B5CF6',
    finalizada: '#22C55E',
  }

  return {
    searchQuery,
    statusFilter,
    filteredReservations,
    exportCsv,
    metricLabels,
    metricColors,
    statusBadgeColors,
    formatDate,
    formatTime,
  }
}
