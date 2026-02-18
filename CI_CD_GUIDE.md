# Guía Completa de CI/CD - INTER_MODULAR

## 📋 Índice
1. [Requisitos Previos](#requisitos-previos)
2. [Configuración SSH](#configuración-ssh)
3. [Configuración en GitHub](#configuración-en-github)
4. [GitHub Actions Workflow](#github-actions-workflow)
5. [Configuración del Servidor](#configuración-del-servidor)
6. [Despliegue Manual](#despliegue-manual)
7. [Monitoreo y Troubleshooting](#monitoreo-y-troubleshooting)

---

## Requisitos Previos

### En tu máquina local:
- Git instalado
- GitHub CLI (opcional pero recomendado)
- Docker instalado (para testing local)

### En tu servidor de producción:
- Acceso SSH configurado
- Docker y Docker Compose instalados
- Ubuntu 20.04+ (o similar)
- Mínimo 2GB RAM, 2 vCPU
- Puerto 80 y 443 disponibles

---

## Configuración SSH

### Paso 1: Generar claves SSH en tu servidor

Conéctate a tu servidor y ejecuta:

```bash
# Crear directorio .ssh si no existe
mkdir -p ~/.ssh
chmod 700 ~/.ssh

# Generar nueva clave SSH (sin passphrase para GitHub Actions)
ssh-keygen -t ed25519 -f ~/.ssh/github_deploy -N "" -C "github-actions-deploy"

# Ver la clave pública (la copiarás más adelante)
cat ~/.ssh/github_deploy.pub
```

### Paso 2: Configurar autorización SSH

```bash
# Agregar la clave pública al archivo authorized_keys
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Verificar permisos (opcional pero importante)
ls -la ~/.ssh/
# Debe mostrar:
# -rw------- authorized_keys
# -rw------- github_deploy
# -rw-r--r-- github_deploy.pub
```

### Paso 3: Obtener la clave privada

```bash
# Mostrar el contenido (lo usarás en GitHub Secrets)
cat ~/.ssh/github_deploy
```

**⚠️ IMPORTANTE:** Copia todo el contenido incluyendo las líneas:
- `-----BEGIN OPENSSH PRIVATE KEY-----`
- `-----END OPENSSH PRIVATE KEY-----`

---

## Configuración en GitHub

### Paso 1: Crear el Repositorio

```bash
cd c:\Users\Miguel\Desktop\INTER_MODULAR
git init
git add .
git commit -m "Initial commit: INTER_MODULAR project"
git branch -M main
git remote add origin https://github.com/tu-usuario/inter-modular.git
git push -u origin main
```

### Paso 2: Agregar Secretos en GitHub

1. Ve a **Settings** → **Secrets and variables** → **Actions**
2. Crea los siguientes secretos:

| Nombre | Descripción | Valor |
|--------|-------------|-------|
| `SSH_PRIVATE_KEY` | Clave privada SSH | Contenido completo de `~/.ssh/github_deploy` |
| `SSH_HOST` | IP o dominio del servidor | `203.0.113.45` o `deploy.ejemplo.com` |
| `SSH_USER` | Usuario SSH | `ubuntu` o `root` |
| `SSH_PORT` | Puerto SSH (default: 22) | `22` |
| `DEPLOY_PATH` | Ruta donde desplegar | `/home/ubuntu/apps/inter-modular` |
| `APP_ENV` | Ambiente | `production` |
| `APP_DEBUG` | Debug mode | `false` |
| `APP_KEY` | Clave de encriptación Laravel | Generada: `base64:...` |
| `DB_PASSWORD` | Contraseña MySQL | Contraseña segura |
| `REDIS_PASSWORD` | Contraseña Redis (opcional) | Contraseña segura |

#### Generar APP_KEY:

```bash
# En local
cd src
php artisan key:generate --show
# Output: base64:xxx...
```

### Paso 3: Crear Variables de Entorno

Crea también variables (no secretas) en **Settings** → **Variables**:

| Nombre | Valor |
|--------|-------|
| `DOCKER_REGISTRY` | `ghcr.io` (GitHub Container Registry) |
| `IMAGE_NAME` | `tu-usuario/inter-modular` |

---

## GitHub Actions Workflow

### Paso 1: Crear la estructura de directorios

```bash
mkdir -p .github/workflows
mkdir -p .github/scripts
```

### Paso 2: Crear el archivo principal de workflow

**Archivo: `.github/workflows/deploy.yml`**

```yaml
name: Build, Test and Deploy

on:
  push:
    branches:
      - main
      - develop
  pull_request:
    branches:
      - main
      - develop

env:
  REGISTRY: ghcr.io
  IMAGE_NAME: ${{ github.repository }}

jobs:
  # Job 1: Build Docker Image
  build:
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Set up Docker Buildx
        uses: docker/setup-buildx-action@v3

      - name: Log in to Container Registry
        if: github.event_name != 'pull_request'
        uses: docker/login-action@v3
        with:
          registry: ${{ env.REGISTRY }}
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}

      - name: Build and push Docker image
        uses: docker/build-push-action@v5
        with:
          context: ./docker/php
          push: ${{ github.event_name != 'pull_request' }}
          tags: |
            ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}/app:latest
            ${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}/app:${{ github.sha }}
          cache-from: type=registry,ref=${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}/app:buildcache
          cache-to: type=registry,ref=${{ env.REGISTRY }}/${{ env.IMAGE_NAME }}/app:buildcache,mode=max

  # Job 2: Run Tests
  test:
    runs-on: ubuntu-latest
    needs: build
    
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: notion_test
          MYSQL_USER: notion_user
          MYSQL_PASSWORD: notion_pass
          MYSQL_ROOT_PASSWORD: root_pass
        options: >-
          --health-cmd="mysqladmin ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
        ports:
          - 3306:3306

      redis:
        image: redis:alpine
        options: >-
          --health-cmd="redis-cli ping"
          --health-interval=10s
          --health-timeout=5s
          --health-retries=3
        ports:
          - 6379:6379

    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Set up PHP
        uses: shivammathur/setup-php-action@v2
        with:
          php-version: '8.2'
          extensions: pdo_mysql, redis, gd, zip
          tools: composer

      - name: Get Composer cache directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer dependencies
        uses: actions/cache@v3
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install PHP dependencies
        working-directory: ./src
        run: composer install --prefer-dist --no-progress

      - name: Copy environment file
        working-directory: ./src
        run: cp .env.example .env.testing

      - name: Generate application key
        working-directory: ./src
        run: php artisan key:generate --env=testing

      - name: Run migrations
        working-directory: ./src
        env:
          DB_CONNECTION: mysql
          DB_DATABASE: notion_test
          DB_USERNAME: notion_user
          DB_PASSWORD: notion_pass
          DB_HOST: 127.0.0.1
        run: php artisan migrate --env=testing

      - name: Run tests
        working-directory: ./src
        env:
          DB_CONNECTION: mysql
          DB_DATABASE: notion_test
          DB_USERNAME: notion_user
          DB_PASSWORD: notion_pass
          DB_HOST: 127.0.0.1
        run: php artisan test

  # Job 3: Deploy to Production
  deploy:
    runs-on: ubuntu-latest
    needs: [build, test]
    if: github.ref == 'refs/heads/main' && github.event_name == 'push'
    
    steps:
      - name: Checkout code
        uses: actions/checkout@v4

      - name: Deploy via SSH
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SSH_HOST }}
          username: ${{ secrets.SSH_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          port: ${{ secrets.SSH_PORT }}
          script_stop_on_error: true
          script: |
            set -e
            
            # Variables
            APP_PATH="${{ secrets.DEPLOY_PATH }}"
            IMAGE_REGISTRY="${{ env.REGISTRY }}"
            IMAGE_NAME="${{ env.IMAGE_NAME }}"
            IMAGE_TAG="${{ github.sha }}"
            
            echo "🚀 Iniciando despliegue de INTER_MODULAR..."
            echo "📍 Ruta: $APP_PATH"
            
            # 1. Crear directorio si no existe
            if [ ! -d "$APP_PATH" ]; then
              echo "📁 Creando directorio de aplicación..."
              mkdir -p "$APP_PATH"
            fi
            
            cd "$APP_PATH"
            
            # 2. Clonar o actualizar repositorio
            if [ -d ".git" ]; then
              echo "🔄 Actualizando repositorio..."
              git fetch origin
              git reset --hard origin/main
            else
              echo "📥 Clonando repositorio..."
              git clone https://github.com/${{ github.repository }}.git .
            fi
            
            # 3. Login al Docker Registry
            echo "🔐 Autenticándose en Docker Registry..."
            echo "${{ secrets.GITHUB_TOKEN }}" | docker login $IMAGE_REGISTRY -u ${{ github.actor }} --password-stdin
            
            # 4. Actualizar variables de entorno
            echo "⚙️  Configurando variables de entorno..."
            cat > .env.production << EOF
            APP_NAME="INTER_MODULAR"
            APP_ENV=${{ secrets.APP_ENV }}
            APP_DEBUG=${{ secrets.APP_DEBUG }}
            APP_KEY=${{ secrets.APP_KEY }}
            APP_URL=https://${{ secrets.SSH_HOST }}
            
            DB_CONNECTION=mysql
            DB_HOST=mysql
            DB_PORT=3306
            DB_DATABASE=notion_db
            DB_USERNAME=notion_user
            DB_PASSWORD=${{ secrets.DB_PASSWORD }}
            
            CACHE_DRIVER=redis
            QUEUE_CONNECTION=redis
            SESSION_DRIVER=cookie
            
            REDIS_HOST=redis
            REDIS_PASSWORD=${{ secrets.REDIS_PASSWORD }}
            REDIS_PORT=6379
            
            MAIL_MAILER=log
            MAIL_HOST=smtp.mailtrap.io
            MAIL_PORT=2525
            MAIL_USERNAME=
            MAIL_PASSWORD=
            
            SANCTUM_STATEFUL_DOMAINS=localhost:8080,127.0.0.1:8080
            EOF
            
            # 5. Detener contenedores viejos
            echo "🛑 Deteniendo contenedores anteriores..."
            docker-compose -f docker-compose.yml down || true
            
            # 6. Actualizar imagen Docker
            echo "📦 Descargando nueva imagen Docker..."
            docker pull $IMAGE_REGISTRY/$IMAGE_NAME/app:$IMAGE_TAG || docker pull $IMAGE_REGISTRY/$IMAGE_NAME/app:latest
            
            # 7. Inicia contenedores
            echo "🐳 Iniciando contenedores con Docker Compose..."
            docker-compose -f docker-compose.yml up -d
            
            # 8. Esperar a que PHP esté listo
            echo "⏳ Esperando a que PHP esté disponible..."
            sleep 10
            
            # 9. Ejecutar migraciones
            echo "🗄️  Ejecutando migraciones de base de datos..."
            docker-compose -f docker-compose.yml exec -T php php artisan migrate --force
            
            # 10. Limpiar cache
            echo "🧹 Limpiando cache de aplicación..."
            docker-compose -f docker-compose.yml exec -T php php artisan cache:clear
            docker-compose -f docker-compose.yml exec -T php php artisan config:cache
            docker-compose -f docker-compose.yml exec -T php php artisan route:cache
            
            # 11. Compilar assets
            echo "🎨 Compilando assets..."
            docker-compose -f docker-compose.yml exec -T node npm install
            docker-compose -f docker-compose.yml exec -T node npm run build
            
            # 12. Verificar estado
            echo "✅ Verificando estado de contenedores..."
            docker-compose -f docker-compose.yml ps
            
            echo "🎉 ¡Despliegue completado exitosamente!"
            echo "🌐 Aplicación disponible en: https://${{ secrets.SSH_HOST }}"

      - name: Notify Deployment Success
        if: success()
        uses: actions/github-script@v7
        with:
          script: |
            github.rest.issues.createComment({
              issue_number: context.issue.number,
              owner: context.repo.owner,
              repo: context.repo.repo,
              body: '✅ Despliegue a producción completado exitosamente!\n\n🌐 URL: https://${{ secrets.SSH_HOST }}'
            })

      - name: Notify Deployment Failure
        if: failure()
        uses: actions/github-script@v7
        with:
          script: |
            github.rest.issues.createComment({
              issue_number: context.issue.number,
              owner: context.repo.owner,
              repo: context.repo.repo,
              body: '❌ El despliegue falló. Revisa los logs en Actions.'
            })
```

---

## Configuración del Servidor

### Paso 1: Preparar el servidor

```bash
# Actualizar sistema
sudo apt-get update && sudo apt-get upgrade -y

# Instalar Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Instalar Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Agregar usuario actual al grupo docker
sudo usermod -aG docker $USER
newgrp docker

# Instalar Git
sudo apt-get install -y git

# Crear directorio de aplicación
mkdir -p ~/apps
```

### Paso 2: Crear usuario SSH (opcional pero recomendado)

```bash
# Crear usuario específico para despliegue
sudo useradd -m -s /bin/bash deployer

# Agregar a grupo docker
sudo usermod -aG docker deployer

# Crear directorio .ssh
sudo mkdir -p /home/deployer/.ssh
sudo chmod 700 /home/deployer/.ssh

# Agregar clave pública
echo "tu_clave_publica_aqui" | sudo tee /home/deployer/.ssh/authorized_keys
sudo chmod 600 /home/deployer/.ssh/authorized_keys
sudo chown -R deployer:deployer /home/deployer/.ssh

# Crear directorio de aplicación
sudo mkdir -p /home/deployer/apps/inter-modular
sudo chown -R deployer:deployer /home/deployer/apps
```

### Paso 3: Configurar Firewall (si usa UFW)

```bash
sudo ufw allow 22/tcp      # SSH
sudo ufw allow 80/tcp      # HTTP
sudo ufw allow 443/tcp     # HTTPS
sudo ufw enable
```

---

## Despliegue Manual

Si necesitas desplegar sin esperar a GitHub Actions:

```bash
# En tu máquina local
ssh -p 22 deployer@203.0.113.45 'bash -s' < deploy.sh
```

**Archivo: `deploy.sh`**

```bash
#!/bin/bash

set -e

APP_PATH="/home/deployer/apps/inter-modular"
REPO_URL="https://github.com/tu-usuario/inter-modular.git"
BRANCH="main"

echo "🚀 Iniciando despliegue manual..."

# Crear directorio
mkdir -p "$APP_PATH"
cd "$APP_PATH"

# Clonar o actualizar
if [ -d ".git" ]; then
  echo "🔄 Actualizando repositorio..."
  git fetch origin
  git reset --hard origin/$BRANCH
else
  echo "📥 Clonando repositorio..."
  git clone -b $BRANCH $REPO_URL .
fi

# Actualizar variables de entorno
if [ ! -f ".env" ]; then
  cp src/.env.example src/.env
  # EDITAR MANUALMENTE .env si es necesario
fi

# Levantar servicios
echo "🐳 Iniciando Docker Compose..."
docker-compose -f docker-compose.yml down || true
docker-compose -f docker-compose.yml up -d

# Esperar a PHP
sleep 10

# Ejecutar comandos
echo "🗄️  Ejecutando migraciones..."
docker-compose -f docker-compose.yml exec -T php php artisan migrate --force

echo "🧹 Limpiando cache..."
docker-compose -f docker-compose.yml exec -T php php artisan cache:clear
docker-compose -f docker-compose.yml exec -T php php artisan config:cache

echo "🎉 ¡Despliegue completado!"
docker-compose -f docker-compose.yml ps
```

---

## Monitoreo y Troubleshooting

### Logs de Docker

```bash
# Ver logs de todos los servicios
docker-compose logs -f

# Ver logs de un servicio específico
docker-compose logs -f php
docker-compose logs -f nginx
docker-compose logs -f mysql

# Ver logs recientes (últimas 100 líneas)
docker-compose logs --tail=100
```

### Verificar estado de los contenedores

```bash
# Ver estado activo
docker-compose ps

# Ver detalles de un contenedor
docker inspect notion_php
docker stats
```

### Problemas comunes

#### 1. "Connection refused" a la base de datos
```bash
# Verificar que MySQL está corriendo
docker-compose logs mysql

# Reiniciar MySQL
docker-compose restart mysql
```

#### 2. "Permission denied" en archivos
```bash
# Fijar permisos en el servidor
docker-compose exec php chown -R www-data:www-data /var/www/html
docker-compose exec php chmod -R 755 /var/www/html/storage
docker-compose exec php chmod -R 755 /var/www/html/bootstrap/cache
```

#### 3. "Port already in use"
```bash
# Cambiar puertos en docker-compose.yml
# Por ejemplo: "9000:9000" → "9001:9000"
docker-compose down
docker-compose up -d
```

#### 4. Rollback a versión anterior
```bash
# Detener servicios
docker-compose down

# Cambiar a commit anterior
git reset --hard <commit_hash>

# Reiniciar
docker-compose up -d
docker-compose exec -T php php artisan migrate --force
```

---

## Flujo de trabajo completo

### Para desarrolladores:

1. **Crear rama feature**
   ```bash
   git checkout -b feature/nueva-funcionalidad
   ```

2. **Hacer cambios y commit**
   ```bash
   git add .
   git commit -m "feat: descripción del cambio"
   ```

3. **Crear Pull Request en GitHub**
   - GitHub Actions ejecutará automáticamente tests
   - El PR mostrará ✅ si todo pasa

4. **Merge a main**
   - Una vez aprobado, merge en main
   - GitHub Actions ejecutará el workflow completo
   - **Si todo pasa**, despliegue automático a producción

### Monitorear despliegues:

- Ve a **Actions** en GitHub
- Haz clic en el workflow ejecutándose
- Ve los logs en tiempo real

---

## Cheat Sheet de Comandos

```bash
# GitHub Actions
git push origin main                        # Disparar workflow
git reset --hard <commit>                   # Force push (cuidado)

# SSH
ssh -i ~/.ssh/github_deploy deployer@server  # Conectar al servidor
scp -r -i ~/.ssh/github_deploy ./src/* deployer@server:/path

# Docker
docker-compose ps                           # Ver estado
docker-compose logs -f                      # Ver logs
docker-compose exec php bash                # Acceder a contenedor
docker-compose restart                      # Reiniciar servicios
docker-compose down && docker-compose up -d # Reinicio completo

# Laravel (dentro del contenedor)
php artisan migrate                         # Ejecutar migraciones
php artisan cache:clear                     # Limpiar cache
php artisan db:seed                         # Ejecutar seeders
php artisan tinker                          # CLI interactivo
```

---

## Resumen de Seguridad

✅ **Cosas que debes hacer:**
- Usar claves SSH sin passphrase SOLO para CI/CD
- Guardar secretos en GitHub Secrets (nunca en código)
- Mantener .env en servidor, no en repositorio
- Hacer backup regular de la base de datos
- Actualizar dependencias regularmente

❌ **Nunca hagas:**
- Commitear `.env` o claves privadas
- Usar credenciales débiles
- Desactivar SSH key validation
- Commitar `vendor/` o `node_modules/`

---

## Próximos pasos

1. ✅ Configurar SSH en el servidor
2. ✅ Agregar secretos en GitHub
3. ✅ Crear el workflow `.github/workflows/deploy.yml`
4. ✅ Hacer push a repositorio
5. ✅ Verificar que el workflow se ejecute
6. ✅ Monitorear el despliegue

**¡Listo! Tu CI/CD está configurado.** 🚀

