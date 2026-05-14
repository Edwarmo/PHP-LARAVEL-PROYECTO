import { ref } from 'vue'
import { formatDate, formatTime } from '@/lib/formatters'
import { exportCsv } from '@/lib/csv'

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

  function downloadCsv(reservas, filename = 'videoconf_reservas.csv') {
    exportCsv(reservas,
      ['Sala', 'Usuario', 'Fecha', 'Inicio', 'Fin', 'Estado'],
      [
        r => r.space_name,
        r => r.user_name,
        r => formatDate(r.start_time),
        r => formatTime(r.start_time),
        r => r.end_time ? formatTime(r.end_time) : '—',
        r => r.status,
      ],
      filename
    )
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
    downloadCsv,
    metricLabels,
    metricColors,
    statusBadgeColors,
    formatDate,
    formatTime,
  }
}
