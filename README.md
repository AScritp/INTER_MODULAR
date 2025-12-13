# Notion-Like Platform - Herramienta de Gestión de Documentos

Un proyecto completo de plataforma tipo Notion/Obsidian para gestión de apuntes, documentación, workspaces compartibles, y calendario integrado.

## 📁 Estructura del Proyecto

```
INTER_MODULAR/
├── src/                          # Código principal de la aplicación Laravel
│   ├── app/
│   │   ├── Http/Controllers/     # Controladores (Workspace, Document, Event)
│   │   ├── Http/Middleware/      # Middleware personalizado
│   │   ├── Http/Requests/        # Form Requests para validación
│   │   ├── Models/               # Modelos Eloquent
│   │   ├── Policies/             # Políticas de autorización
│   │   ├── Services/             # Servicios de lógica reutilizable
│   │   └── Providers/            # Service Providers
│   ├── database/
│   │   ├── migrations/           # Migraciones de base de datos
│   │   ├── factories/            # Factories para testing
│   │   └── seeders/              # Seeders con datos iniciales
│   ├── resources/
│   │   ├── js/Pages/             # Componentes Vue.js con Inertia
│   │   ├── css/                  # Estilos Tailwind
│   │   └── views/                # Vistas Blade
│   ├── routes/                   # Definición de rutas web y API
│   ├── tests/                    # Tests unitarios y funcionales
│   ├── public/                   # Archivos públicos
│   ├── storage/                  # Almacenamiento de datos
│   ├── config/                   # Archivos de configuración
│   ├── docker/                   # Configuración Docker
│   ├── .env.example              # Variables de entorno de ejemplo
│   ├── composer.json             # Dependencias PHP
│   ├── package.json              # Dependencias Node.js
│   └── vite.config.js            # Configuración Vite
├── docker-compose.yml            # Orquestación de contenedores
├── SETUP_GUIDE.md                # Guía completa de instalación
├── IMPLEMENTATION_SUMMARY.md     # Resumen de lo implementado
├── VERIFICATION_CHECKLIST.md     # Checklist de verificación
└── README.md                     # Este archivo

```

## 🚀 Inicio Rápido

### Con Docker (Recomendado)
```bash
cd INTER_MODULAR

# Subir contenedores
docker-compose up -d

# Instalar dependencias PHP
docker-compose exec app composer install

# Copiar .env
docker-compose exec app cp .env.example .env

# Generar key
docker-compose exec app php artisan key:generate

# Ejecutar migraciones
docker-compose exec app php artisan migrate

# Ejecutar seeders
docker-compose exec app php artisan db:seed

# Instalar dependencias Node
docker-compose exec app npm install

# Compilar assets
docker-compose exec app npm run build
```

Accede a `http://localhost`

### Sin Docker (Manual)
```bash
cd INTER_MODULAR/src

# Instalar dependencias
composer install
npm install

# Configurar
cp .env.example .env
php artisan key:generate

# Base de datos
mysql -u root -p -e "CREATE DATABASE notion_like CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
php artisan db:seed

# Compilar y servir
npm run build
php artisan serve
npm run dev  # En otra terminal
```

Accede a `http://localhost:8000`

### Credenciales de Prueba
- **Email**: test@example.com
- **Password**: password

## ✨ Características Principales

### 🏢 Gestión de Workspaces
- Crear múltiples espacios de trabajo
- Compartir con otros usuarios
- Asignar roles (Propietario, Editor, Lector)
- Editar propiedades del workspace

### 📝 Editor de Documentos
- Editor WYSIWYG completo con Quill.js
- **Autoguardado automático** cada 2-3 segundos
- Soporte para formato texto, listas, títulos, etc.
- Drag & drop para reordenar documentos
- Soft delete con opción de restaurar

### 📅 Calendario de Eventos
- Crear eventos con fecha y hora
- Vista de calendario integrada
- Gestionar eventos en cada workspace
- Asociar eventos a documentos

### 👥 Compartición Colaborativa
- Invitar usuarios por email
- Asignar roles con permisos específicos
- Gestionar acceso en tiempo real
- Remover usuarios cuando sea necesario

### 🔐 Seguridad
- Autenticación segura con Laravel Sanctum
- Autorización basada en Policies
- Validación en servidor y cliente
- Soft deletes para protección de datos

## 🛠️ Tecnología Stack

### Backend
- **Laravel 12** - Framework PHP moderno
- **Laravel Sanctum** - Autenticación API
- **Inertia.js** - Bridge Laravel ↔ Vue.js
- **MySQL** - Base de datos relacional

### Frontend
- **Vue.js 3** - Framework JavaScript reactivo
- **Tailwind CSS** - Utilidades CSS moderno
- **Quill.js** - Editor WYSIWYG
- **FullCalendar** - Componente calendario
- **SortableJS** - Drag & drop

### DevOps
- **Docker** - Containerización
- **Docker Compose** - Orquestación
- **Vite** - Build tool ultrarrápido
- **MySQL** - Base de datos

## 📚 Documentación

- **[SETUP_GUIDE.md](SETUP_GUIDE.md)** - Guía detallada de instalación y uso
- **[IMPLEMENTATION_SUMMARY.md](IMPLEMENTATION_SUMMARY.md)** - Resumen técnico de implementación
- **[VERIFICATION_CHECKLIST.md](VERIFICATION_CHECKLIST.md)** - Checklist de verificación y testing

## 🔗 Rutas Principales

```
GET  /                           Página de inicio
GET  /login                      Formulario de login
POST /login                      Procesar login
GET  /register                   Formulario de registro
POST /register                   Procesar registro
POST /logout                     Cerrar sesión

GET  /dashboard                  Dashboard del usuario
GET  /workspaces                 Listar workspaces
POST /workspaces                 Crear workspace
GET  /workspaces/{id}            Ver workspace
PATCH/workspaces/{id}            Editar workspace
DEL  /workspaces/{id}            Eliminar workspace

GET  /workspaces/{id}/documents  Listar documentos
POST /workspaces/{id}/documents  Crear documento
GET  /documents/{id}/edit        Editor de documento
PATCH/documents/{id}             Actualizar documento
DEL  /documents/{id}             Eliminar documento

GET  /workspaces/{id}/calendar   Ver calendario
POST /workspaces/{id}/events     Crear evento
PATCH/events/{id}                Editar evento
DEL  /events/{id}                Eliminar evento

POST /workspaces/{id}/users                    Agregar usuario
DEL  /workspaces/{id}/users/{userId}          Remover usuario
PATCH/workspaces/{id}/users/{userId}          Cambiar rol
```

## 📊 Estructura de Base de Datos

### Tablas
- `users` - Usuarios del sistema
- `workspaces` - Espacios de trabajo
- `documents` - Documentos en workspaces
- `events` - Eventos del calendario
- `workspace_user` - Relación N:M para compartición

### Relaciones
```
User
  ├── hasMany(Workspace)
  ├── belongsToMany(Workspace, 'workspace_user')
  ├── hasMany(Document)
  └── hasMany(Event)

Workspace
  ├── belongsTo(User)
  ├── hasMany(Document)
  ├── hasMany(Event)
  └── belongsToMany(User, 'workspace_user')

Document
  ├── belongsTo(Workspace)
  └── belongsTo(User)

Event
  ├── belongsTo(Workspace)
  └── belongsTo(User)
```

## 🧪 Testing

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test tests/Feature/WorkspaceTest.php
php artisan test tests/Feature/DocumentTest.php

# Con coverage
php artisan test --coverage
```

## 🔧 Configuración Importante

### Variables de Entorno (.env)
```env
APP_NAME="Notion-Like Platform"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=notion_like
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

## 📦 Instalación de Dependencias

```bash
# PHP/Composer
composer install

# JavaScript/Node
npm install

# Frontend build
npm run build  # Producción
npm run dev    # Desarrollo con hot reload
```

## 🐛 Troubleshooting

**Error: "SQLSTATE[HY000]: General error"**
- Verificar que MySQL está corriendo
- Confirmar credenciales en .env
- Ejecutar `php artisan migrate`

**Error: "npm: command not found"**
- Instalar Node.js desde https://nodejs.org

**Assets no se cargan**
```bash
npm install
npm run build
php artisan cache:clear
php artisan view:clear
```

**Base de datos no migra**
```bash
php artisan migrate:rollback
php artisan migrate
php artisan db:seed
```

## 📈 Próximas Mejoras

- [ ] Colaboración en tiempo real con WebSockets
- [ ] Búsqueda full-text avanzada
- [ ] Historial de versiones de documentos
- [ ] Plantillas de documentos reutilizables
- [ ] Tags y categorías
- [ ] Exportar a PDF/Word/Markdown
- [ ] Autenticación OAuth (Google, GitHub)
- [ ] Dark mode
- [ ] Notificaciones por email
- [ ] API pública para integraciones

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:
1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver archivo `LICENSE` para más detalles.

## 👨‍💻 Autor

Desarrollado como una plataforma moderna de gestión de documentos usando las mejores prácticas de Laravel y Vue.js.

---

## 📞 Soporte

Para reportar bugs, sugerir mejoras o hacer preguntas, por favor abre un issue en el repositorio.

**¡Disfruta organizando tu trabajo con esta herramienta! 🎉**
