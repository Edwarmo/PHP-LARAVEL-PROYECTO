<x-mail::message>
# Sobre tu solicitud de reserva en {{ $space->name }}

Hola, **{{ $reservation->user_name }}**,

Lamentamos informarte que tu solicitud de reserva en **{{ $space->name }}**
no ha podido ser procesada en esta ocasión.

---

<x-mail::panel>
**Detalles de la solicitud rechazada**

| Campo | Información |
|---|---|
| 🏢 **Sala** | {{ $space->name }} |
| 📅 **Fecha solicitada** | {{ $reservation->start_time->translatedFormat('l, d \d\e F \d\e Y') }} |
| 🕐 **Horario** | {{ $reservation->start_time->format('H:i') }} – {{ $reservation->end_time->format('H:i') }} hrs |
| ❌ **Estado** | **No procesada** |
</x-mail::panel>

@if($reason)
### 📝 Motivo

> {{ $reason }}
@else
### 📝 Motivo

Lamentablemente, el horario solicitado no estuvo disponible o no cumplió
con los requisitos necesarios para la confirmación en ese momento.
@endif

---

### ¿Qué puedes hacer ahora?

**¡No te desanimes!** Tenemos otros horarios disponibles para ti:

- Consulta nuestra disponibilidad actualizada en tiempo real.
- Elige un horario alternativo que se adapte mejor a tu agenda.
- Si tienes dudas, responde este correo y te ayudamos a encontrar la mejor opción.

<x-mail::button :url="$browseUrl" color="primary">
🔎 Ver otros horarios disponibles en {{ $space->name }}
</x-mail::button>

Lamentamos los inconvenientes y esperamos poder atenderte muy pronto.

Atentamente,
**Equipo Reservas VideoConf**

<x-mail::subcopy>
Referencia de tu solicitud: `{{ $reservation->slug }}`
</x-mail::subcopy>
</x-mail::message>
