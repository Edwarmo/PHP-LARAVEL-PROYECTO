<x-mail::message>
# Confirmación de cancelación de reserva

Hola, **{{ $reservation->user_name }}**,

Te confirmamos que tu reserva en **{{ $space->name }}** ha sido **cancelada**.
Si tienes alguna consulta sobre este proceso, no dudes en contactarnos.

---

<x-mail::panel>
**Detalles de la reserva cancelada**

| Campo | Información |
|---|---|
| 🏢 **Sala** | {{ $space->name }} |
| 📅 **Fecha** | {{ $reservation->start_time->translatedFormat('l, d \d\e F \d\e Y') }} |
| 🕐 **Horario** | {{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }} hrs |
| 🚫 **Estado** | **Cancelada** |
</x-mail::panel>

@if($reason)
### 📝 Motivo de la cancelación

> {{ $reason }}
@endif

---

### ¿Necesitas un nuevo espacio?

Si deseas hacer una nueva reserva, nuestros espacios siguen disponibles para ti.

<x-mail::button :url="$browseUrl" color="primary">
🔎 Explorar espacios disponibles
</x-mail::button>

Esperamos poder atenderte nuevamente en el futuro.

Saludos,
**Equipo Reservas VideoConf**

<x-mail::subcopy>
Referencia de tu reserva cancelada: `{{ $reservation->slug }}`
Si crees que esto fue un error, responde este correo y te ayudaremos a solucionarlo.
</x-mail::subcopy>
</x-mail::message>
