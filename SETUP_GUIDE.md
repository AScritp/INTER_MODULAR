# Notion-Like Documentation Platform

Una plataforma de gestión de documentos y notas similar a Notion u Obsidian, con soporte para compartir workspaces, edición colaborativa, calendario integrado y autoguardado.

## Características

✅ **Gestión de Usuarios**: Registro, login y autenticación segura con Laravel Sanctum
✅ **Workspaces Personales**: Crea múltiples espacios de trabajo independientes
✅ **Documentos Editables**: Editor WYSIWYG con Quill.js para crear y editar contenido
✅ **Compartir Recursos**: Invita usuarios a tus workspaces con roles (Editor, Lector, Propietario)
✅ **Autoguardado**: Los cambios se guardan automáticamente cada 2-3 segundos
✅ **Calendario**: Gestiona eventos importantes en cada workspace
✅ **Drag & Drop**: Arrastra documentos para reorganizarlos
✅ **Soft Delete**: Elimina documentos de forma segura con opción de restaurar
✅ **Control de Permisos**: Roles basados en acceso (editor, viewer)

## Requisitos

- PHP 8.2+
- MySQL 5.7+
- Node.js 18+
- Composer
- Docker (opcional, recomendado)

## Instalación

### 1. Clonar o descargar el proyecto
```bash
cd INTER_MODULAR/src
```

### 2. Instalar dependencias PHP
```bash
composer install
```

### 3. Instalar dependencias Node.js
```bash
npm install
```

### 4. Configurar variables de entorno
```bash
cp .env.example .env
php artisan key:generate
```

Edita `.env` y configura:
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=notion_like
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Crear base de datos
```bash
mysql -u root -p
CREATE DATABASE notion_like CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 6. Ejecutar migraciones
```bash
php artisan migrate
```

### 7. Ejecutar seeders (datos de prueba)
```bash
php artisan db:seed
```

### 8. Compilar assets del frontend
```bash
npm run build
# o para desarrollo con hot reload:
npm run dev
```

### 9. Iniciar el servidor
```bash
php artisan serve
```

Accede a `http://localhost:8000` en tu navegador.

## Credenciales de Prueba

Después de ejecutar `db:seed`, puedes usar:
- **Email**: test@example.com
- **Password**: password

## Estructura del Proyecto

```
src/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # WorkspaceController, DocumentController, EventController
│   │   ├── Middleware/      # CheckWorkspacePermission
│   │   ├── Requests/        # Form Requests para validación
│   │   └── Policies/        # Policies para autorización
│   ├── Models/              # User, Workspace, Document, Event
│   ├── Services/            # WorkspaceService (lógica reutilizable)
│   └── Providers/           # AppServiceProvider
├── database/
│   ├── migrations/          # Esquema de base de datos
│   └── seeders/             # Datos de prueba
├── resources/
│   ├── css/                 # Estilos Tailwind
│   └── js/Pages/            # Componentes Vue (Inertia)
├── routes/
│   ├── web.php              # Rutas web
│   └── api.php              # Rutas API
└── docker-compose.yml       # Configuración Docker
```

## Rutas Principales

### Autenticación
- `GET /login` - Formulario de login
- `POST /login` - Procesar login
- `GET /register` - Formulario de registro
- `POST /register` - Procesar registro
- `POST /logout` - Cerrar sesión

### Workspaces
- `GET /workspaces` - Listar tus workspaces
- `GET /workspaces/create` - Crear workspace
- `POST /workspaces` - Guardar workspace
- `GET /workspaces/{id}` - Ver workspace
- `GET /workspaces/{id}/edit` - Editar workspace
- `PATCH /workspaces/{id}` - Actualizar workspace
- `DELETE /workspaces/{id}` - Eliminar workspace

### Documentos
- `GET /workspaces/{id}/documents` - Listar documentos
- `GET /workspaces/{id}/documents/create` - Crear documento
- `POST /workspaces/{id}/documents` - Guardar documento
- `GET /documents/{id}/edit` - Editar documento
- `PATCH /documents/{id}` - Actualizar documento
- `DELETE /documents/{id}` - Eliminar documento
- `PATCH /documents/{id}/auto-save` - Autoguardado (API)

### Eventos
- `GET /workspaces/{id}/calendar` - Ver calendario
- `GET /workspaces/{id}/events/create` - Crear evento
- `POST /workspaces/{id}/events` - Guardar evento
- `PATCH /events/{id}` - Actualizar evento
- `DELETE /events/{id}` - Eliminar evento

### Compartir
- `POST /workspaces/{id}/users` - Agregar usuario
- `DELETE /workspaces/{id}/users/{userId}` - Remover usuario
- `PATCH /workspaces/{id}/users/{userId}` - Actualizar rol

## Uso

### Crear un Workspace
1. Inicia sesión en la plataforma
2. Ve a "Mis Workspaces"
3. Haz clic en "+ Nuevo Workspace"
4. Completa el nombre y descripción
5. Haz clic en "Crear Workspace"

### Crear un Documento
1. Abre un workspace
2. En la pestaña "Documentos", haz clic en "+ Nuevo Documento"
3. Ingresa un título
4. Se abrirá el editor donde puedes escribir tu contenido
5. Los cambios se guardan automáticamente

### Compartir Workspace
1. Abre el workspace
2. Ve a la pestaña "Compartir"
3. Ingresa el email del usuario que deseas invitar
4. Selecciona el rol (Editor o Lector)
5. Haz clic en "Invitar"

### Crear Evento
1. Ve a la pestaña "Calendario"
2. Haz clic en "+ Nuevo Evento"
3. Completa los detalles del evento
4. Haz clic en "Crear Evento"

## Roles y Permisos

### Propietario (Owner)
- Crear, editar y eliminar documentos
- Crear, editar y eliminar eventos
- Compartir workspace con otros usuarios
- Eliminar workspace
- Cambiar rol de usuarios

### Editor
- Crear, editar y eliminar sus propios documentos
- Crear, editar y eliminar sus propios eventos
- Ver documentos de otros usuarios

### Lector (Viewer)
- Ver documentos
- Ver eventos
- No puede crear, editar ni eliminar

## API Endpoints

### Autoguardado de Documento
```bash
PATCH /api/documents/{id}/auto-save
Content-Type: application/json

{
  "title": "Nuevo título",
  "content": "<p>Contenido actualizado</p>"
}
```

### Obtener Eventos del Calendario
```bash
GET /api/workspaces/{id}/calendar/events
```

### Actualizar Orden de Documentos
```bash
PATCH /api/workspaces/{id}/documents/order
Content-Type: application/json

{
  "documents": [3, 1, 2]
}
```

## Tecnologías Utilizadas

### Backend
- **Laravel 12**: Framework web PHP
- **Laravel Sanctum**: Autenticación API
- **Inertia.js**: Puente entre Laravel y Vue.js
- **MySQL**: Base de datos relacional

### Frontend
- **Vue.js 3**: Framework JavaScript reactivo
- **Tailwind CSS**: Utilidades CSS
- **Quill.js**: Editor WYSIWYG
- **FullCalendar**: Calendario interactivo
- **SortableJS**: Drag & drop

### DevOps
- **Docker**: Containerización
- **Docker Compose**: Orquestación de contenedores
- **Vite**: Build tool moderno

## Desarrollo

### Modo desarrollo con hot reload
```bash
npm run dev
```

### Build para producción
```bash
npm run build
```

### Ejecutar tests
```bash
php artisan test
```

### Generar factoryies
```bash
php artisan make:factory DocumentFactory --model=Document
```

## Configuración de Docker

Para usar Docker (recomendado):

```bash
docker-compose up -d
```

Esto levantará:
- **PHP/Laravel** en `http://localhost`
- **MySQL** en `localhost:3306`
- **Nginx** como servidor web

Luego:
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app npm install
docker-compose exec app npm run build
```

## Troubleshooting

### Error: "SQLSTATE[HY000]: General error"
Asegúrate de que MySQL está corriendo y la base de datos existe.

### Error: "npm: command not found"
Instala Node.js desde [nodejs.org](https://nodejs.org)

### Error: "Class not found"
Ejecuta:
```bash
composer dump-autoload
```

### Assets no se cargan
Ejecuta:
```bash
npm install
npm run build
php artisan storage:link
```

## Próximas Características

- [ ] Colaboración en tiempo real con WebSockets
- [ ] Búsqueda full-text avanzada
- [ ] Historial de versiones de documentos
- [ ] Plantillas de documentos
- [ ] Tags y categorías
- [ ] Exportar documentos a PDF
- [ ] Autenticación con OAuth (Google, GitHub)

## Licencia

MIT License - Ver LICENSE file

## Soporte

Para reportar bugs o sugerir mejoras, abre un issue en el repositorio.

---

**Desarrollado con ❤️ usando Laravel y Vue.js**
