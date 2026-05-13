export interface Space {
  id: number
  name: string
  slug: string
  type: string
  capacity: number
  description: string | null
  price_per_hour: string | number
  is_active: boolean
  availabilities?: Availability[]
  blocked_slots?: BlockedSlot[]
}

export interface Reservation {
  slug: string
  space_id: number
  user_name: string
  user_email: string
  start_time: string
  end_time: string
  status: ReservationStatus
  notes: string | null
  space?: Space
}

export type ReservationStatus =
  | 'pendiente'
  | 'confirmada'
  | 'rechazada'
  | 'cancelada'
  | 'finalizada'

export interface Availability {
  id: number
  space_id: number
  day_of_week: number
  start_time: string
  end_time: string
}

export interface BlockedSlot {
  id: number
  space_id: number
  start_time: string
  end_time: string
  reason: string | null
}

export interface Slot {
  label: string
  available: boolean
}

export interface AvailableDay {
  date: string
  day_name: string
  available_slots_count: number
}

export interface WeekDay {
  date: string
  label: string
}

export interface CalendarReservation {
  slug: string
  user_name: string
  space_name: string
  space_id: number
  start_time: string
  end_time: string
  status: ReservationStatus
  day_index: number
}

export interface DashboardMetrics {
  pendientes: number
  confirmadas: number
  hoy: number
  esta_semana: number
}
