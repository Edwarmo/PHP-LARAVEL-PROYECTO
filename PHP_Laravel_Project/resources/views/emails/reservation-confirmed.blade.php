<x-mail::message>
# ¡Tu reserva ha sido confirmada! 🎉

Hola, **{{ $reservation->user_name }}**,

¡Excelentes noticias! Tu reserva en **{{ $space->name }}** ha sido **confirmada**.
Te esperamos en la fecha y hora acordadas. ¡Prepárate para una gran experiencia!

---

<x-mail::panel>
**📅 Recordatorio de tu reserva**

| Campo | Información |
|---|---|
| 🏢 **Sala** | {{ $space->name }} |
| 🏷️ **Tipo** | {{ ucfirst($space->type) }} |
| 👥 **Capacidad** | {{ $space->capacity }} personas |
| 📅 **Fecha** | {{ $start->translatedFormat('l, d \d\e F \d\e Y') }} |
| 🕐 **Hora de inicio** | {{ $start->format('H:i') }} hrs |
| 🕓 **Hora de fin** | {{ $end->format('H:i') }} hrs |
| ⏱️ **Duración** | {{ $start->diffInMinutes($end) }} minutos |
| ✅ **Estado** | **Confirmada** |
</x-mail::panel>

### 📌 Recuerda antes de llegar:

- Llega **5 minutos antes** del inicio de tu sesión.
- Si necesitas cancelar, hazlo con al menos **2 horas de anticipación**.
- Asegúrate de contar con tu **enlace de videoconferencia** listo.

<x-mail::button :url="$trackingUrl" color="success">
📋 Ver detalles de mi reserva
</x-mail::button>

Nos vemos pronto,
**Equipo Reservas VideoConf**

<x-mail::subcopy>
Referencia: `{{ $reservation->slug }}` — Contacto: noreply@videoconfreservas.com
</x-mail::subcopy>
</x-mail::message>
