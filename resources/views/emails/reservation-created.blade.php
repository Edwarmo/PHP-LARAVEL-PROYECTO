<x-mail::message>
# ¡Tu solicitud de reserva ha sido recibida! 📋

Hola, **{{ $reservation->user_name }}**,

Hemos recibido correctamente tu solicitud para reservar un espacio en nuestra plataforma.
Nuestro equipo la revisará y te notificaremos pronto con la confirmación.

---

<x-mail::panel>
**Detalles de tu reserva**

| Campo | Información |
|---|---|
| 🏢 **Sala** | {{ $space->name }} |
| 📅 **Fecha** | {{ $start->translatedFormat('l, d \d\e F \d\e Y') }} |
| 🕐 **Inicio** | {{ $start->format('H:i') }} hrs |
| 🕓 **Fin** | {{ $end->format('H:i') }} hrs |
| 👤 **Nombre** | {{ $reservation->user_name }} |
| 📧 **Correo** | {{ $reservation->user_email }} |
| 🔖 **Estado** | Pendiente de confirmación |
</x-mail::panel>

@if($reservation->notes)
**Notas adicionales:** {{ $reservation->notes }}
@endif

<x-mail::button :url="$trackingUrl" color="primary">
🔍 Ver el estado de mi reserva
</x-mail::button>

> ⏳ El tiempo promedio de respuesta es de **2 a 4 horas hábiles**.
> Si tienes alguna pregunta, responde este correo directamente.

Gracias por elegirnos,
**Equipo Reservas VideoConf**

<x-mail::subcopy>
Si no reconoces esta solicitud, puedes ignorar este correo de forma segura.
Tu referencia de reserva es: `{{ $reservation->slug }}`
</x-mail::subcopy>
</x-mail::message>
