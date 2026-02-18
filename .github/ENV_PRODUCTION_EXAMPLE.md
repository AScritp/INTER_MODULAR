# Variables de Entorno para Producción

Copia este archivo a `.env` en el servidor y actualiza los valores según tu configuración.

```env
###################################
#  APLICACIÓN LARAVEL
###################################

APP_NAME="INTER_MODULAR"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

# Generar con: php artisan key:generate --show
# NUNCA dejes esto vacío en producción
APP_KEY=base64:YOUR_KEY_HERE

# Zona horaria de la aplicación
APP_TIMEZONE=America/Argentina/Buenos_Aires

# Locale
APP_LOCALE=es
APP_FALLBACK_LOCALE=es

###################################
#  BASE DE DATOS
###################################

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=notion_db
DB_USERNAME=notion_user
# CAMBIA ESTO - Usa una contraseña segura
DB_PASSWORD=ChangeMe123!@#

# Pool de conexiones
DB_POOL_SIZE=5
DB_POOL_QUEUE_SIZE=0

###################################
#  REDIS (CACHE Y QUEUE)
###################################

REDIS_HOST=redis
REDIS_PASSWORD=ChangeMe123!@#
REDIS_PORT=6379

# Driver de cache
CACHE_DRIVER=redis
CACHE_TTL=3600

# Queue driver
QUEUE_CONNECTION=redis
QUEUE_FAILED_TABLE=failed_jobs

# Session
SESSION_DRIVER=cookie
SESSION_LIFETIME=120
SESSION_DOMAIN=null

###################################
#  EMAIL Y NOTIFICACIONES
###################################

MAIL_MAILER=log
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@inter-modular.local
MAIL_FROM_NAME="${APP_NAME}"

###################################
#  AUTENTICACIÓN API
###################################

# Sanctum - Token authentication
SANCTUM_STATEFUL_DOMAINS=localhost:8080,127.0.0.1:8080,tu-dominio.com
SANCTUM_EXPIRATION=

###################################
#  LOGGING
###################################

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=single
LOG_LEVEL=notice
LOG_SLACK_WEBHOOK_URL=

###################################
#  FILEYSTEM (STORAGE)
###################################

FILESYSTEM_DISK=local
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

###################################
#  PAGINACIÓN
###################################

PAGINATION_VIEW=pagination::bootstrap-4
PAGINATION_PER_PAGE=15

###################################
#  OPCIONES ADICIONALES
###################################

# Broadcasting
BROADCAST_DRIVER=log
BROADCAST_CONNECTION=

# Procesos en segundo plano
HORIZON_PREFIX=horizon

# Permitir framing (si necesitas que se embeba en otros sitios)
ALLOW_FRAME_NESTING=false

###################################
#  SEGURIDAD
###################################

# CORS
CORS_ALLOWED_ORIGINS="https://tu-dominio.com"

# Trusted proxies (si está detrás de un load balancer)
TRUSTED_PROXIES=*
TRUSTED_HOSTS=*.tu-dominio.com

###################################
#  DEBUG APENAS EN DESARROLLO
###################################

# NUNCA activar en producción
DEBUGBAR_ENABLED=false

###################################
#  NOTAS IMPORTANTES
###################################

# 1. Cambiar todas las contraseñas (DB_PASSWORD, REDIS_PASSWORD)
# 2. Usar contraseñas de al menos 16 caracteres
# 3. APP_DEBUG SIEMPRE debe ser false en producción
# 4. APP_KEY debe estar configurado (usado para encriptación)
# 5. MAIL_FROM_ADDRESS debe ser un email válido
# 6. Configurar certificados SSL (HTTPS)
# 7. Hacer backup de este archivo en lugar seguro
# 8. No cometer este archivo al repositorio si tiene secretos
```

## 🔐 Generación de Valores Seguros

### 1. APP_KEY

```bash
cd /home/deployer/apps/inter-modular/src
php artisan key:generate --show
# Output: base64:xxx...
```

### 2. Contraseñas Seguras

```bash
# Generar contraseña aleatoria
openssl rand -base64 32

# O usando python
python3 -c "import secrets; print(secrets.token_urlsafe(32))"

# O en Linux
head -c 32 /dev/urandom | base64
```

**Ejemplo de contraseña segura:**
- `P@ssw0rd123!@#$%^&*()`
- `6K9x@L#pQ2m$W8nV3vB!`

### 3. Sanctum Tokens (opcional)

```bash
# Si usas Sanctum para APIs
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

---

## 📋 Checklist de Seguridad

Antes de desplegar a producción, verifica:

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] `APP_KEY` configurado y con valor válido
- [ ] `DB_PASSWORD` cambiada a valor seguro
- [ ] `REDIS_PASSWORD` cambiada a valor seguro
- [ ] Certificado SSL/TLS configurado
- [ ] Base de datos con constraseña fuerte
- [ ] Redis con contraseña configurada
- [ ] Logs correctamente rotados
- [ ] Backups automáticos configurados

---

## 🚀 Carga en Producción

### Método 1: Variables locales

```bash
# En el servidor, crea/edita el archivo .env
nano /home/deployer/apps/inter-modular/.env

# Copia los valores de arriba y actualiza con tus datos
```

### Método 2: Heredar de GitHub Actions

El workflow `.github/workflows/deploy.yml` puede generar el `.env` automáticamente usando secrets:

En el workflow, se genera así:
```yaml
cat > .env.production << EOF
APP_KEY=${{ secrets.APP_KEY }}
DB_PASSWORD=${{ secrets.DB_PASSWORD }}
# ... etc
EOF
```

---

## 🔄 Actualizar Variables en Vivo

Si necesitas cambiar variables sin desplegar:

```bash
# 1. SSH al servidor
ssh deployer@tu-servidor

# 2. Edita el archivo .env
nano /home/deployer/apps/inter-modular/.env

# 3. Recarga la configuración
docker-compose exec php php artisan config:cache

# 4. Reinicia PHP si es necesario
docker-compose restart php
```

---

## ⚠️ Variables Sensibles NO deben estar en Git

Nunca commitees:
- `.env` (archivo completo)
- Contraseñas de base de datos
- Claves de API
- Claves privadas SSH
- Tokens de acceso

En cambio, usa [GitHub Secrets](CI_CD_GUIDE.md#configuración-en-github).

---

## 📊 Monitoreo de Variables

Verifica valores actuales:

```bash
# Ver todas las variables
docker-compose exec php php artisan tinker
>>> config('app.env')  // Ver APP_ENV
>>> config('database.connections.mysql.host')  // Ver DB_HOST
>>> config('cache.stores.redis')  // Ver configuración de Redis
>>> exit()

# O desde la línea de comandos
docker-compose exec php php artisan config:show
```

---

## 🆘 Problemas Comunes

### "Unable to connect to Redis"
```bash
# Verifica las variables
# - REDIS_HOST debe ser 'redis'
# - REDIS_PASSWORD debe coincidir con el docker-compose.yml
docker-compose logs redis
```

### "SQLSTATE[HY000]: General error"
```bash
# Probablemente configuración de MySQL incorrecta
# Verifica:
# - DB_HOST = mysql (nombre del servicio)
# - DB_PASSWORD coincida con MYSQL_PASSWORD
docker-compose logs mysql
```

### "Cache not working"
```bash
# CACHE_DRIVER debe ser 'redis'
# Verifica que REDIS_HOST y REDIS_PASSWORD sean correctas
docker-compose exec php php artisan cache:clear
```

---

## 🎯 Siguiente Paso: Despliegue

Cuando tengas el `.env` configurado:

```bash
# Desde la raíz del repositorio
docker-compose up -d

# Ejecutar migraciones
docker-compose exec php php artisan migrate --force

# Hacer un test
curl http://localhost:8080/health
```

Para desplegar desde GitHub Actions, basta hacer un `git push` a `main`.

