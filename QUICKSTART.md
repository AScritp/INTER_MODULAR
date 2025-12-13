# ⚡ Quick Start - Notion-Like Platform

## 🏃 Inicio en 5 Minutos

### Opción 1: Con Docker (Recomendado - MÁS FÁCIL)

```bash
# 1. En la carpeta INTER_MODULAR
cd INTER_MODULAR

# 2. Levantar contenedores
docker-compose up -d

# 3. Entrar al contenedor PHP
docker-compose exec app bash

# 4. Dentro del contenedor:
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
npm install
npm run build
exit

# 5. Acceder a http://localhost
```

---

### Opción 2: Sin Docker (Manual)

```bash
# 1. Ir al directorio
cd INTER_MODULAR/src

# 2. Instalar dependencias
composer install
npm install

# 3. Configurar
cp .env.example .env
php artisan key:generate

# 4. Base de datos (ejecutar en otra terminal)
mysql -u root -p
CREATE DATABASE notion_like CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
exit

# 5. Migraciones
php artisan migrate
php artisan db:seed

# 6. Build
npm run build

# 7. Servidor (terminal 1)
php artisan serve

# 8. Hot reload opcional (terminal 2)
npm run dev

# 9. Acceder a http://localhost:8000
```

---

## 🔐 Credenciales de Prueba

```
Email:    test@example.com
Password: password
```

---

## 📍 Qué Hacer Primero

### 1️⃣ Login
- Inicia sesión con las credenciales arriba
- Verás el Dashboard con "Mis Workspaces"

### 2️⃣ Crear Workspace
- Haz clic en "+ Nuevo Workspace"
- Completa:
  - Nombre: "Mi Primer Workspace"
  - Descripción: "Espacio para apuntes"
  - Compartible: ☑️ (opcional)
- Clic en "Crear Workspace"

### 3️⃣ Ver Workspace
- Haz clic en "Ver" en el workspace creado
- Verás 3 tabs: Documentos, Calendario, Compartir

### 4️⃣ Crear Documento
- En la pestaña "Documentos"
- Haz clic en "+ Nuevo Documento"
- Completa:
  - Título: "Mi Primer Documento"
  - Contenido: (opcional)
- Clic en "Crear Documento"
- Se abrirá el editor automáticamente

### 5️⃣ Editar Documento
- **Escribe contenido** en el editor
- Observa "Guardando..." → "Guardado" (autoguardado)
- El contenido se guarda automáticamente cada 2-3 segundos
- Usa la barra de herramientas para:
  - **Bold** / *Italic* / <u>Underline</u>
  - Listas ordenadas y desordenadas
  - Títulos (H1, H2, H3)
  - Citas (blockquote)
  - Enlaces e imágenes

### 6️⃣ Compartir Workspace
- Vuelve al workspace (← Volver)
- Pestaña "Compartir"
- Completa:
  - Email del usuario: (debe estar registrado)
  - Rol: "Editor" o "Lector"
- Haz clic en "Invitar"

### 7️⃣ Crear Evento
- En la pestaña "Calendario"
- Haz clic en "+ Nuevo Evento"
- Completa:
  - Título: "Mi Evento"
  - Descripción: (opcional)
  - Fecha/Hora inicio
  - Fecha/Hora fin
- Haz clic en "Crear Evento"

---

## 🎮 Ejemplo Completo de Uso

```
1. LOGIN (test@example.com / password)
   ↓
2. CREAR WORKSPACE "Proyecto Final"
   ↓
3. CREAR DOCUMENTO "Requisitos"
   ├─ Escribe: "- Requisito 1"
   ├─ Escribe: "- Requisito 2"
   └─ Autoguardado automático
   ↓
4. CREAR DOCUMENTO "Diseño"
   ├─ Escribe tu contenido
   └─ Autoguardado automático
   ↓
5. COMPARTIR WORKSPACE
   ├─ Email: collaborator@example.com
   └─ Rol: Editor
   ↓
6. CREAR EVENTO "Entrega Final"
   ├─ Fecha: 2025-12-20
   ├─ Hora: 18:00 - 20:00
   └─ Se ve en calendario
```

---

## 🔌 Rutas Importantes

| URL | Descripción |
|-----|-------------|
| `/login` | Iniciar sesión |
| `/register` | Registrar usuario |
| `/dashboard` | Dashboard principal |
| `/workspaces` | Listar workspaces |
| `/workspaces/create` | Crear workspace |
| `/workspaces/{id}` | Ver workspace |
| `/documents/{id}/edit` | Editar documento |
| `/workspaces/{id}/events/create` | Crear evento |

---

## 🐛 Si Algo No Funciona

### "Página en blanco"
```bash
php artisan cache:clear
php artisan view:clear
npm run build
```

### "Error de conexión a BD"
```bash
# Verificar MySQL está corriendo
mysql -u root -p -e "SELECT 1;"

# Verificar .env tiene credenciales correctas
cat .env | grep DB_
```

### "Assets no cargan"
```bash
npm install
npm run build
```

### "Clase no encontrada"
```bash
composer dump-autoload
```

---

## 🧪 Probar Más Funcionalidades

### Crear segundo usuario
```bash
# En Tinker
php artisan tinker
>>> \App\Models\User::factory()->create(['email' => 'user2@test.com'])
>>> exit
```

### Luego:
1. Login con user2@test.com
2. El user de prueba puede invitar a user2 a su workspace
3. user2 verá el workspace compartido en su lista

---

## 📚 Documentación Completa

Para más detalles, ver:
- `README.md` - Descripción general
- `SETUP_GUIDE.md` - Instalación detallada
- `IMPLEMENTATION_SUMMARY.md` - Qué se implementó
- `VERIFICATION_CHECKLIST.md` - Checklist de testing
- `DEVELOPER_NOTES.md` - Notas técnicas

---

## ✨ Características Ya Funcionando

✅ **Autenticación** - Login/Registro  
✅ **Workspaces** - Crear, editar, eliminar, compartir  
✅ **Documentos** - Editor con autoguardado cada 2-3 segundos  
✅ **Eventos** - Calendario con eventos  
✅ **Permisos** - Roles y autorización granular  
✅ **Drag & Drop** - Reordenar documentos  
✅ **Soft Delete** - Eliminar y restaurar documentos  
✅ **Responsive** - Mobile-friendly con Tailwind CSS  

---

## 🎯 Próximo Paso

Después de probar, puedes:

1. **Personalizar la marca** (logo, colores)
2. **Agregar más campos** a documentos
3. **Integrar búsqueda** full-text
4. **Agregar comentarios** en documentos
5. **Implementar WebSockets** para colaboración en tiempo real

---

**¡Listo! Ahora disfruta tu plataforma tipo Notion 🎉**

Para soporte: revisa `DEVELOPER_NOTES.md`
