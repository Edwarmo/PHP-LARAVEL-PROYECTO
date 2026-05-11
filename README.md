# Sistema de Reservas de Salas de Videoconferencia

Sistema web profesional para la gestion y reserva de espacios de videoconferencia. Permite a los usuarios consultar disponibilidad en tiempo real, solicitar reservas y realizar un seguimiento de sus solicitudes por correo electronico. El area administrativa permite gestionar el calendario semanal, aprobar solicitudes y administrar el catalogo de salas.

## Requisitos del sistema

- PHP 8.3 o superior
- Composer 2.x
- Node.js 18 o superior
- npm 9 o superior
- PostgreSQL 14 o superior (o cuenta en Supabase)
- Docker Desktop (para pruebas locales en contenedor)
- Git

## Instalacion local (sin Docker)

1. Clonar el repositorio:
```bash
git clone https://github.com/Edwarmo/PHP-LARAVEL-PROYECTO.git
cd PHP-LARAVEL-PROYECTO
```

2. Crear archivo de entorno:
```bash
cp .env.example .env
```

3. Instalar dependencias de PHP:
```bash
composer install
```

4. Generar clave de aplicacion:
```bash
php artisan key:generate
```

5. Configurar la base de datos en el archivo .env:
```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-us-east-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=tu_usuario
DB_PASSWORD=tu_password
DB_SSLMODE=require
```

6. Ejecutar migraciones:
```bash
php artisan migrate
```

7. Poblar la base de datos:
```bash
php artisan db:seed
```

8. Instalar dependencias de Node:
```bash
npm install
```

9. Compilar assets para produccion:
```bash
npm run build
```

10. Iniciar el servidor local:
```bash
php artisan serve
```
11. Usuario administrador:
```bash
Email: admin@videoconfreservas.com
Password: password
```
Acceder a: http://localhost:8000

## Instalacion con Docker

1. Construir la imagen:
```bash
docker build -t videoconf .
```

2. Ejecutar el contenedor con las variables necesarias:
```bash
docker run -p 8080:8080 \
  -e APP_KEY=base64:tu_key_generada \
  -e DB_CONNECTION=pgsql \
  -e DB_HOST=aws-0-us-east-1.pooler.supabase.com \
  -e DB_PORT=6543 \
  -e DB_DATABASE=postgres \
  -e DB_USERNAME=tu_usuario \
  -e DB_PASSWORD=tu_password \
  -e DB_SSLMODE=require \
  -e SESSION_DRIVER=cookie \
  -e CACHE_DRIVER=array \
  --name videoconf-app videoconf
```

3. Verificar en: http://localhost:8080

## Configuracion de Supabase

Para una conexion exitosa con Supabase, siga estas reglas obligatorias:

- Utilice el host del Connection Pooler (Transaction Mode).
- El puerto correcto es 6543 (no el puerto estandar 5432).
- El formato del nombre de usuario debe ser postgres.ID_DE_PROYECTO.
- La variable DB_SSLMODE=require es obligatoria para conexiones externas.

Ejemplo de configuracion:
```env
DB_CONNECTION=pgsql
DB_HOST=aws-0-us-east-1.pooler.supabase.com
DB_PORT=6543
DB_DATABASE=postgres
DB_USERNAME=postgres.qsbeluygtxexjcrxwbme
DB_PASSWORD=tu_password_segura
DB_SSLMODE=require
```

## Despliegue en Render.com

1. Subir el codigo a un repositorio en GitHub.
2. Crear un nuevo Web Service en Render.
3. Conectar el repositorio de GitHub.
4. Seleccionar el entorno Docker.
5. Configurar las siguientes variables de entorno:

| Variable | Valor sugerido |
| :--- | :--- |
| APP_NAME | Videoconferencia |
| APP_ENV | production |
| APP_KEY | base64:... (generado con php artisan key:generate) |
| APP_DEBUG | false |
| APP_URL | https://tu-app.onrender.com |
| DB_CONNECTION | pgsql |
| DB_HOST | aws-0-us-east-1.pooler.supabase.com |
| DB_PORT | 6543 |
| DB_DATABASE | postgres |
| DB_USERNAME | postgres.ID_DE_PROYECTO |
| DB_PASSWORD | tu_password_supabase |
| DB_SSLMODE | require |
| SESSION_DRIVER | cookie |
| CACHE_DRIVER | array |
| QUEUE_CONNECTION | sync |
| LOG_CHANNEL | errorlog |
| PORT | 8080 |

6. Desplegar y verificar en el endpoint: https://tu-app.onrender.com/health

## Variables de entorno

| Variable | Descripcion | Valor ejemplo |
| :--- | :--- | :--- |
| APP_NAME | Nombre de la aplicacion | Laravel |
| APP_ENV | Entorno de ejecucion | local / production |
| APP_KEY | Clave de encriptacion | base64:xxx |
| APP_DEBUG | Modo de depuracion | true / false |
| APP_URL | URL base de la aplicacion | http://localhost:8000 |
| DB_CONNECTION | Driver de base de datos | pgsql |
| DB_HOST | Host de la base de datos | 127.0.0.1 / pooler.supabase.com |
| DB_PORT | Puerto de conexion | 6543 |
| DB_DATABASE | Nombre de la base de datos | videoconf |
| DB_USERNAME | Usuario de la base de datos | postgres.xxx |
| DB_PASSWORD | Contrasena del usuario | xxxxxxx |
| DB_SSLMODE | Modo SSL para Postgres | require |
| SESSION_DRIVER | Driver de sesiones | cookie / file / database |
| CACHE_DRIVER | Driver de cache | array / file |
| PORT | Puerto para el servidor Apache | 8080 |

## Estructura del proyecto

- app/Http/Controllers/: Controladores de la logica de negocio.
- app/Models/: Modelos Eloquent (Space, Reservation, User).
- config/: Archivos de configuracion del framework.
- database/migrations/: Definicion de la estructura de tablas.
- database/seeders/: Datos iniciales y de prueba.
- docker/: Configuraciones para el contenedor (Apache y script de inicio).
- resources/js/Pages/: Vistas principales construidas con Vue 3.
- resources/js/Components/: Componentes de UI reutilizables.
- resources/js/Layouts/: Plantillas de estructura (Public y Admin).
- routes/: Definicion de rutas web y API.
- public/: Punto de entrada y assets compilados.

## Rutas disponibles

### Rutas Publicas
| Metodo | URL | Descripcion |
| :--- | :--- | :--- |
| GET | / | Listado de salas disponibles |
| GET | /spaces/{slug} | Detalle de sala y slots de tiempo |
| GET | /reservations/new | Formulario de creacion de reserva |
| POST | /reservations | Enviar solicitud de reserva |
| GET | /reservations/{slug} | Ver estado de una reserva especifica |
| GET | /historial | Consultar historial por email |
| GET | /health | Verificacion de estado del servicio |

### Rutas Administrativas (requieren autenticacion)
| Metodo | URL | Descripcion |
| :--- | :--- | :--- |
| GET | /admin | Dashboard con metricas generales |
| GET | /admin/reservations | Listado gestionable de reservas |
| GET | /admin/calendar | Vista de calendario de ocupacion |
| GET | /admin/spaces | Gestion del catalogo de salas |
| POST | /admin/reservations/{slug}/accept | Aprobar una reserva |
| POST | /admin/reservations/{slug}/reject | Rechazar una reserva |
| POST | /admin/reservations/{slug}/cancel | Cancelar una reserva confirmada |

### Rutas API
| Metodo | URL | Descripcion |
| :--- | :--- | :--- |
| GET | /api/spaces/{slug}/slots | Obtener slots libres para una fecha |

## Credenciales por defecto

El seeder crea un administrador inicial:
- Email: admin@videoconf.test
- Password: password

Importante: cambie estas credenciales inmediatamente en entornos de produccion.

## Comandos utiles

| Comando | Descripcion |
| :--- | :--- |
| php artisan migrate | Ejecuta las migraciones pendientes |
| php artisan migrate:fresh --seed | Limpia la base de datos y carga datos iniciales |
| php artisan db:seed | Ejecuta los seeders de la base de datos |
| php artisan cache:clear | Limpia el cache de la aplicacion |
| php artisan config:clear | Limpia el cache de configuracion |
| php artisan test | Ejecuta las pruebas unitarias y funcionales |
| npm run dev | Inicia el servidor de desarrollo de Vite |
| npm run build | Compila los assets para produccion |
| docker build -t videoconf . | Construye la imagen Docker local |

## Solucion de problemas comunes

| Error | Causa | Solucion |
| :--- | :--- | :--- |
| SQLSTATE[42883] boolean = integer | Incompatibilidad de tipos en Postgres | Asegurese de que los campos is_active se comparen con 'true' o 'false' como strings o booleanos reales, no con 0 o 1. |
| SQLSTATE[08006] Network unreachable | Bloqueo de puerto 5432 o falta de IPv6 | Cambie el puerto a 6543 en la configuracion de Supabase. |
| FATAL: Tenant or user not found | Nombre de usuario incorrecto | El usuario de Supabase debe incluir el prefijo postgres. seguido del ID de referencia del proyecto. |
| No open ports detected en Render | Proceso Apache mal configurado | Verifique que start.sh termine con exec apache2-foreground para que Docker detecte el proceso correctamente. |
| Address already in use | Conflicto de puertos en Apache | No ejecute comandos de inicio de Apache manuales antes del comando final en start.sh. |
| Assets no cargan (404) | Falta compilacion de produccion | Ejecute npm run build antes de desplegar o construir la imagen Docker. |

## Licencia y autor

Licencia MIT
Autor: Edwarmo - https://github.com/Edwarmo




