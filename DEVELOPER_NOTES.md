# 📝 Notas Importantes para Desarrolladores

## 🎯 Lo que se ha implementado

### ✅ Completamente Funcional
1. **Autenticación y Autorización**
   - Login/Registro con Laravel Breeze/Sanctum
   - Políticas (Policies) para autorización granular
   - Middleware personalizado para verificación de permisos

2. **Gestión de Workspaces**
   - CRUD completo (Crear, Leer, Actualizar, Eliminar)
   - Compartir con otros usuarios
   - Asignación de roles (Editor, Lector)
   - Vista maestro-detalle

3. **Editor de Documentos**
   - Editor WYSIWYG con Quill.js
   - Autoguardado cada 2-3 segundos via API
   - Soft deletes (Eliminación segura)
   - Reordenamiento por drag & drop
   - Validación en servidor y cliente

4. **Calendario de Eventos**
   - CRUD de eventos
   - Vista de calendario
   - Asociación a workspaces

5. **Compartición Colaborativa**
   - Tabla pivote workspace_user
   - Sistema de roles flexible
   - Validaciones de acceso

---

## ⚠️ Puntos Críticos a Revisar

### 1. Axios en Editor.vue
El componente DocumentEditor.vue usa `axios` directamente. Asegúrate de que está importado en `resources/js/app.js`:

```javascript
// resources/js/app.js
import axios from 'axios'
window.axios = axios
```

### 2. Router en Delete/Destroy
Algunos componentes usan `router.delete()`. Si da error, reemplazar con:

```javascript
// En lugar de router.delete():
window.location.href = `/ruta/a/eliminar/${id}`

// O usar fetch API:
fetch(`/ruta/${id}`, { method: 'DELETE' })
```

### 3. Validación de Permisos
Las Policies están registradas en `AppServiceProvider.php`. Verificar que se están usando correctamente:

```php
$this->authorize('update', $workspace);  // Usa WorkspacePolicy
$this->authorize('delete', $document);   // Usa DocumentPolicy
```

### 4. Relaciones M:N (workspace_user)
La tabla pivote requiere permiso en el controlador:

```php
// Para agregar usuario
$workspace->users()->syncWithoutDetaching([
    $user->id => ['role' => 'editor'],
]);
```

---

## 🔧 Configuraciones Necesarias

### 1. Registrar Policies en AppServiceProvider
✅ **YA ESTÁ HECHO** - Ver `app/Providers/AppServiceProvider.php`

### 2. Crear base de datos
```bash
mysql -u root -p
CREATE DATABASE notion_like CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit;
```

### 3. Ejecutar migraciones en orden
```bash
php artisan migrate
# Orden importante:
# 1. users (Laravel)
# 2. workspaces
# 3. documents
# 4. events
# 5. workspace_user (pivote)
```

### 4. Compilar assets
```bash
npm install
npm run build
# o con hot reload:
npm run dev
```

---

## 🚨 Errores Comunes y Soluciones

### Error 1: "Target class does not exist"
**Causa**: Controlador no existe o ruta mal configurada
**Solución**:
```bash
# Regenerar autoload
composer dump-autoload

# Verificar que controlador existe
ls app/Http/Controllers/WorkspaceController.php
```

### Error 2: "SQLSTATE[HY000]"
**Causa**: Base de datos no existe o no conecta
**Solución**:
```bash
# Verificar MySQL
mysql -u root -p -e "SELECT 1;"

# Crear DB si no existe
mysql -u root -p
CREATE DATABASE notion_like CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Ejecutar migraciones
php artisan migrate
```

### Error 3: "Call to undefined method"
**Causa**: Falta importar Model o relación mal definida
**Solución**:
```php
// Siempre importar en controllers
use App\Models\Workspace;
use App\Models\Document;
use App\Models\Event;

// Verificar que relaciones existen en modelos
public function documents() { return $this->hasMany(Document::class); }
```

### Error 4: "CSRF token mismatch"
**Causa**: Token CSRF no se envía en formularios
**Solución**:
```blade
<form method="POST">
    @csrf  <!-- Requerido en Blade -->
    ...
</form>
```

---

## 📋 Checklist para Después de Git Clone

- [ ] `composer install`
- [ ] `npm install`
- [ ] `cp .env.example .env`
- [ ] `php artisan key:generate`
- [ ] Crear base de datos MySQL
- [ ] `php artisan migrate`
- [ ] `php artisan db:seed`
- [ ] `npm run build`
- [ ] `php artisan serve`
- [ ] Verificar http://localhost:8000

---

## 🔐 Seguridad - Verificaciones

### Validaciones Implementadas
- ✅ Form Requests para validar input
- ✅ Policies para autorización
- ✅ Middleware para permisos
- ✅ CSRF protection en formularios
- ✅ Soft deletes para datos seguros

### Recomendaciones Adicionales
- [ ] Implementar rate limiting
- [ ] Agregar logs de auditoría
- [ ] Validar tamaño de archivos (si se agregan uploads)
- [ ] Sanitizar contenido HTML en editor
- [ ] Implementar 2FA (autenticación de dos factores)

---

## 🧪 Testing

### Ejecutar Tests
```bash
php artisan test

# Test específico
php artisan test tests/Feature/WorkspaceTest.php

# Con output detallado
php artisan test --verbose

# Con coverage
php artisan test --coverage
```

### Tests Existentes
- ✅ `WorkspaceTest.php` - CRUD de workspaces, compartición
- ✅ `DocumentTest.php` - CRUD de documentos, autoguardado

### Agregar Más Tests
```bash
php artisan make:test NuevoTest --unit
php artisan make:test NuevoFeatureTest --feature
```

---

## 📱 Componentes Vue Importantes

### Editor.vue (Autoguardado)
```javascript
const autoSave = () => {
  clearTimeout(saveTimeout);
  saveStatus.value = "saving";
  
  saveTimeout = setTimeout(() => {
    axios.patch(`/api/documents/${props.document.id}/auto-save`, {
      title: form.title,
      content: form.content,
    })
    .then(() => {
      saveStatus.value = "saved";
    })
    .catch(error => {
      console.error("Error:", error);
    });
  }, 2000); // 2 segundos de debounce
};
```

**Nota**: El debounce previene que se guarde en cada keystroke.

### Show.vue (Compartición)
La sección de "Compartir" solo aparece si el usuario es propietario:
```javascript
const isOwner = ref(false);
// Agregar verificación en mounted
```

---

## 🎨 Estilos Tailwind

- ✅ Tailwind CSS 3.2.1 configurado
- ✅ Custom colors disponibles
- ✅ Dark mode listo para implementar

### Agregar dark mode:
```vue
<!-- En componentes -->
<div class="bg-white dark:bg-gray-900">
  <!-- Contenido -->
</div>
```

---

## 🌐 API Endpoints Importantes

### Autoguardado
```
PATCH /api/documents/{id}/auto-save
Body: { title, content }
Response: { success, message, document }
```

### Reordenar Documentos
```
PATCH /api/workspaces/{id}/documents/order
Body: { documents: [id1, id2, id3] }
Response: { success }
```

### Obtener Eventos Calendario
```
GET /api/workspaces/{id}/calendar/events
Response: [{ id, title, start, end, extendedProps }]
```

---

## 📦 Dependencias NPM Clave

```json
{
  "quill": "^2.0.0",           // Editor WYSIWYG
  "sortablejs": "^1.15.0",     // Drag & drop
  "@fullcalendar/vue": "^6.1.10", // Calendario
  "@inertiajs/vue3": "^2.0.0", // Puente Laravel-Vue
  "axios": "^1.7.4"            // HTTP client
}
```

---

## 🚀 Próximas Tareas (Si es Necesario)

### Phase 2 - Colaboración
- [ ] WebSockets para edición en tiempo real
- [ ] Cursores de usuarios activos
- [ ] Notificaciones en tiempo real

### Phase 3 - Características Avanzadas
- [ ] Scout + Meilisearch para búsqueda
- [ ] Historial de versiones
- [ ] Exportar documentos (PDF, Word)
- [ ] Comentarios en documentos

### Phase 4 - Integración
- [ ] OAuth (Google, GitHub)
- [ ] API pública
- [ ] Webhooks para eventos

---

## 💡 Tips Útiles

### Para Debug
```php
// En controladores
dd($variable);  // Debugar y detener
dump($variable); // Debugar sin detener

// En JavaScript console
console.log(), console.error(), console.warn()
```

### Para Logs
```php
// En Laravel
Log::info('Mensaje', ['context' => $data]);
Log::error('Error', ['exception' => $exception]);
```

### Tinker (REPL)
```bash
php artisan tinker
>>> User::count()
>>> Workspace::all()
>>> Document::where('id', 1)->first()
```

---

## 📞 Soporte

Si encuentras problemas:
1. Revisar los LOGS (`storage/logs/`)
2. Ejecutar `php artisan cache:clear`
3. Ejecutar `php artisan migrate:refresh --seed`
4. Verificar permisos de archivos
5. Comprobar variables de entorno en `.env`

---

## ✨ Felicitaciones

¡Has implementado una plataforma completa tipo Notion! 🎉

Con esta base, puedes:
- ✅ Crear múltiples workspaces
- ✅ Editar documentos en tiempo real con autoguardado
- ✅ Compartir espacios de trabajo con otros
- ✅ Gestionar calendario de eventos
- ✅ Controlar permisos granulares

**¡A disfrutar desarrollando! 💻**
