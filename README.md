# Sistema de Reservas de Salas de Videoconferencia

Sistema web moderno para gestión y reserva de salas de videoconferencia construido con Laravel 12, Vue 3, Inertia.js y PostgreSQL.

## 🚀 Características

### Área Pública
- **Catálogo de Salas**: Visualización de salas disponibles con filtros por tipo
- **Sistema de Reservas**: Selección de fecha/hora con disponibilidad en tiempo real
- **Historial de Reservas**: Consulta de reservas por email
- **Diseño Quantum Conference**: Sistema de diseño editorial con animaciones GSAP

### Área Administrativa
- **Dashboard**: Métricas de reservas con contadores animados
- **Gestión de Reservas**: Aprobar, rechazar o cancelar solicitudes
- **Calendario Semanal**: Vista de disponibilidad por sala
- **Gestión de Salas**: CRUD completo de espacios

## 🛠️ Stack Tecnológico

- **Backend**: Laravel 12 (PHP 8.3+)
- **Frontend**: Vue 3 + Inertia.js
- **Base de Datos**: PostgreSQL
- **Estilos**: Tailwind CSS 4
- **Animaciones**: GSAP
- **Build Tool**: Vite 8 con Rolldown

## 📋 Requisitos

- PHP 8.3 o superior
- Composer
- Node.js 18+ y npm
- PostgreSQL 14+

## 🔧 Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/Edwarmo/PHP-LARAVEL-PROYECTO.git
cd PHP-LARAVEL-PROYECTO/PHP_Laravel_Project
```

2. **Instalar dependencias PHP**
```bash
composer install
```

3. **Instalar dependencias JavaScript**
```bash
npm install
```

4. **Configurar variables de entorno**
```bash
cp .env.example .env
php artisan key:generate
```

Editar `.env` con tus credenciales de PostgreSQL:
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=videoconf_reservas
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
```

5. **Ejecutar migraciones y seeders**
```bash
php artisan migrate --seed
```

6. **Compilar assets**
```bash
npm run dev
```

7. **Iniciar servidor**
```bash
php artisan serve
```

Visita: `http://localhost:8000`

## 🎨 Sistema de Diseño "Quantum Conference"

### Paleta de Colores
- **Background Base**: `#06080f`
- **Background Card**: `#0c1018`
- **Cyan (Acento)**: `#00dcff`
- **Lime (Precio/Destacado)**: `#c8ff00`
- **Text Primary**: `#f0f4f8`
- **Text Muted**: `#5a7080`
- **Text Dim**: `#2a3a48`

### Tipografía
- **Display**: Cormorant Garamond (títulos)
- **UI**: DM Sans (interfaz)
- **Mono**: JetBrains Mono (código/datos técnicos)

### Principios de Diseño
- Formas angulares (border-radius: 0)
- Animaciones suaves con GSAP
- Grid editorial con gap-px
- Hover states con transiciones cyan

## 📁 Estructura del Proyecto

```
PHP_Laravel_Project/
├── app/
│   ├── Http/Controllers/
│   │   ├── Admin/           # Controladores administrativos
│   │   ├── SpaceController.php
│   │   └── ReservationController.php
│   ├── Models/
│   │   ├── Space.php
│   │   ├── Reservation.php
│   │   └── Availability.php
│   └── Events/
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── js/
│   │   ├── Components/      # Componentes Vue reutilizables
│   │   ├── Layouts/         # Layouts de aplicación
│   │   └── Pages/           # Vistas Inertia
│   │       ├── Spaces/
│   │       ├── Reservations/
│   │       └── Admin/
│   ├── css/
│   │   └── app.css          # Estilos globales + Tailwind
│   └── views/
│       └── app.blade.php    # Template principal
└── routes/
    ├── web.php              # Rutas públicas y admin
    └── api.php              # API para slots disponibles
```

## 🔐 Autenticación

El sistema incluye autenticación para el área administrativa:

**Usuario por defecto:**
- Email: `admin@videoconf.test`
- Password: `password`

## 🌐 Rutas Principales

### Públicas
- `/` - Listado de salas
- `/spaces/{slug}` - Detalle de sala con disponibilidad
- `/reservations/new` - Formulario de nueva reserva
- `/reservations/{slug}` - Estado de reserva
- `/historial` - Consultar reservas por email

### Administrativas (requiere auth)
- `/admin` - Dashboard
- `/admin/reservations` - Gestión de reservas
- `/admin/calendar` - Calendario semanal
- `/admin/spaces` - CRUD de salas

## 📊 Base de Datos

### Tablas Principales
- `spaces` - Salas de videoconferencia
- `reservations` - Reservas de usuarios
- `availabilities` - Horarios de disponibilidad por sala
- `users` - Usuarios administrativos

## 🎯 Estados de Reserva

- **pendiente**: Solicitud recibida, esperando aprobación
- **confirmada**: Reserva aprobada
- **rechazada**: Solicitud rechazada
- **cancelada**: Reserva cancelada
- **finalizada**: Reserva completada

## 🚀 Despliegue

### Producción
```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Variables de Entorno Importantes
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com
```

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo la licencia MIT.

## 👨‍💻 Autor

**Edwarmo**
- GitHub: [@Edwarmo](https://github.com/Edwarmo)

## 🙏 Agradecimientos

- Laravel Framework
- Vue.js & Inertia.js
- GSAP Animation Library
- Tailwind CSS
- PostgreSQL

---

⭐ Si este proyecto te fue útil, considera darle una estrella en GitHub!
