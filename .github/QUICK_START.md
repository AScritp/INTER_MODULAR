# 🚀 Inicio Rápido - CI/CD en 10 Pasos

Esta es una guía rápida para poner en marcha CI/CD en menos de 30 minutos.

## 📋 Requisitos Previos

- [ ] Un repositorio en GitHub
- [ ] Un servidor con Docker instalado (o usar el script `server-setup.sh`)
- [ ] Acceso SSH al servidor
- [ ] GitHub configurado localmente

---

## ⚡ 10 Pasos Rápidos

### 1️⃣ Clonar/Inicializar repositorio

```bash
cd c:\Users\Miguel\Desktop\INTER_MODULAR

# Si ya existe un repo
git remote add origin https://github.com/tu-usuario/inter-modular.git

# Si no existe
git init
git add .
git commit -m "Initial commit: INTER_MODULAR project"
git branch -M main
git remote add origin https://github.com/tu-usuario/inter-modular.git

# Push
git push -u origin main
```

### 2️⃣ Generar claves SSH

En tu servidor:

```bash
ssh-keygen -t ed25519 -f ~/.ssh/github_deploy -N "" -C "github-actions"
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### 3️⃣ Copiar clave privada

En el servidor:

```bash
cat ~/.ssh/github_deploy
```

**Copia TODO el contenido** (incluyendo `-----BEGIN OPENSSH PRIVATE KEY-----` y `-----END OPENSSH PRIVATE KEY-----`)

### 4️⃣ Generar APP_KEY

En tu máquina local:

```bash
cd src
php artisan key:generate --show
```

**Copia el output** (ej: `base64:abc123...`)

### 5️⃣ Agregar secretos en GitHub

Ve a: **GitHub Repo → Settings → Secrets and variables → Actions → New repository secret**

Crea estos secretos (reemplaza con TUS valores):

| Nombre | Valor |
|--------|-------|
| `SSH_PRIVATE_KEY` | *Contenido de ~/.ssh/github_deploy* |
| `SSH_HOST` | `203.0.113.45` (IP de tu servidor) |
| `SSH_USER` | `deployer` o `ubuntu` |
| `SSH_PORT` | `22` |
| `DEPLOY_PATH` | `/home/deployer/apps/inter-modular` |
| `APP_KEY` | *Output de php artisan key:generate --show* |
| `DB_PASSWORD` | `MySuperSecurePass123!` (elige una segura) |
| `REDIS_PASSWORD` | `RedisSecurePass123!` |
| `APP_ENV` | `production` |
| `APP_DEBUG` | `false` |

### 6️⃣ Preparar el servidor

SSH al servidor:

```bash
ssh deployer@203.0.113.45

# Descargar y ejecutar script de instalación
curl https://raw.githubusercontent.com/tu-usuario/inter-modular/main/.github/scripts/server-setup.sh -o server-setup.sh
chmod +x server-setup.sh
sudo ./server-setup.sh
```

O descarga [estos archivos](https://github.com/tu-usuario/inter-modular) localmente y ejecuta:

```bash
sh .github/scripts/server-setup.sh
```

### 7️⃣ Crear workflow de GitHub Actions

El archivo `.github/workflows/deploy.yml` ya está creado. Si no, verifica que exista:

- ✅ `.github/workflows/deploy.yml` - Workflow principal
- ✅ `.github/GITHUB_SECRETS_SETUP.md` - Guía de secretos
- ✅ `.github/scripts/deploy.sh` - Script de despliegue manual

### 8️⃣ Hacer push para disparar workflow

```bash
# Crear un cambio pequeño
echo "# CI/CD Ready" >> README.md

# Commit y push
git add .
git commit -m "ci: Setup CI/CD pipeline"
git push origin main
```

Ve a **GitHub Repo → Actions** para ver el workflow ejecutándose.

### 9️⃣ Monitorear despliegue

En GitHub Actions:
- ✅ Haz clic en el workflow en ejecución
- ✅ Mira los pasos (Build → Test → Deploy)
- ✅ Si todo es verde, ¡el despliegue fue exitoso!

En el servidor:

```bash
ssh deployer@203.0.113.45
cd /home/deployer/apps/inter-modular

# Ver logs
docker-compose logs -f

# Ver estado
docker-compose ps
```

### 🔟 Acceder a la aplicación

```bash
# Localmente (si expones los puertos)
http://tu-dominio:8080

# O configura DNS
# Agrega un registro A apuntando a tu servidor
# A  @  203.0.113.45
```

---

## ✅ Verificar que todo funciona

```bash
# En el servidor
ssh deployer@203.0.113.45

# Ver contenedores
docker-compose ps

# Ver logs
docker-compose logs -n 50 php

# Probar base de datos
docker-compose exec mysql mysql -uroot -proot_pass -e "SHOW DATABASES;"

# Probar Redis
docker-compose exec redis redis-cli ping

# Acceder al contenedor PHP
docker-compose exec php php artisan tinker
```

---

## 🎯 Próximas acciones

### Más seguridad (Recomendado):
```bash
# En el servidor
# 1. Configurar HTTPS con Let's Encrypt
sudo apt install certbot
sudo certbot certonly --standalone -d tu-dominio.com

# 2. Agregar certificados a Nginx en docker/nginx/default.conf
# 3. Actualizar docker-compose.yml con rutas a certificados
```

### Monitoreo (Opcional):
```bash
# Ver estadísticas de contenedores
docker stats

# Configurar alertas en GitHub
# Agregar Discord/Slack webhooks en tu workflow
```

### Mantenimiento:
```bash
# Actualizar dependencias
docker-compose pull
docker-compose up -d

# Backup de base de datos
docker-compose exec mysql mysqldump -uroot -proot_pass notion_db > backup.sql
```

---

## 🔧 Troubleshooting Rápido

### Workflow falla sin conectar
**Problema:** `Permission denied (publickey)`
```bash
# En el servidor, verifica que la clave sea accesible
ssh -i ~/.ssh/github_deploy localhost
```

### Contenedores no inician
**Problema:** Puerto en uso
```bash
# Mira qué está usando el puerto
sudo lsof -i :8080
# O cambia los puertos en docker-compose.yml
```

### Base de datos no migra
**Problema:** Timeout esperando MySQL
```bash
# Aumenta wait time en deploy.yml
# Mira logs
docker-compose logs mysql
```

### App muestra error 500
**Problema:** Permisos de archivos
```bash
# Fijar permisos
docker-compose exec php chown -R www-data:www-data /var/www/html
docker-compose exec php chmod -R 755 /var/www/html/storage
```

---

## 📚 Entender el flujo

```
1. Haces push a GitHub
         ↓
2. GitHub Actions dispara el workflow (.github/workflows/deploy.yml)
         ↓
3. Construye imagen Docker (Build job)
         ↓
4. Ejecuta tests (Test job)
         ↓
5. Si todo pasa, deploy automático (Deploy job)
         ↓
6. Se conecta por SSH al servidor
         ↓
7. Actualiza código, contenedores, migraciones
         ↓
8. ✅ Aplicación actualizada en producción
```

---

## 🎓 Recursos Adicionales

- [CI_CD_GUIDE.md](CI_CD_GUIDE.md) - Guía completa detallada
- [GITHUB_SECRETS_SETUP.md](GITHUB_SECRETS_SETUP.md) - Configuración de secretos
- [DOCKER_PRODUCTION_CONFIG.md](DOCKER_PRODUCTION_CONFIG.md) - Docker para producción
- [GitHub Actions Docs](https://docs.github.com/actions)
- [Docker Compose Reference](https://docs.docker.com/compose/compose-file/)

---

## 💡 Tips Finales

- **Configura notificaciones:** Agrega webhooks en el workflow para recibir alertas
- **Usa ramas:** `develop` para testing, `main` para producción
- **Haz rollback fácil:** Usa el script `rollback.sh`
- **Monitores logs:** Ejecuta `docker-compose logs -f` regularmente
- **Backups automáticos:** Agrega un cron job en el servidor

---

**¡Listo! Tu CI/CD está configurado.** 🎉

Si algo no funciona, revisa:
1. Los logs en GitHub Actions
2. Los logs en el servidor con `docker-compose logs`
3. La guía completa en `CI_CD_GUIDE.md`
