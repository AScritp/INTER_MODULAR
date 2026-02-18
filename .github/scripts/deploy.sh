#!/bin/bash

# Script de despliegue manual para INTER_MODULAR
# Uso: ./deploy.sh o ssh usuario@servidor 'bash -s' < deploy.sh

set -e

# Colores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Variables de configuración
APP_PATH="${APP_PATH:=/home/Administrador/apps/inter-modular}"
REPO_URL="${REPO_URL:=https://github.com/AScritp/INTER_MODULAR.git}"
BRANCH="${BRANCH:=main}"
DB_PASSWORD="${DB_PASSWORD:=notion_pass}"
REDIS_PASSWORD="${REDIS_PASSWORD:=redis_pass}"

# Funciones
log_info() {
    echo -e "${BLUE}ℹ️  $1${NC}"
}

log_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

log_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

log_error() {
    echo -e "${RED}❌ $1${NC}"
}

# Header
echo -e "${BLUE}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║     INTER_MODULAR - Despliegue Automático con Docker       ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"

if [ ! -x "$(command -v docker)" ]; then
    log_error "Docker no está instalado"
    exit 1
fi

if [ ! -x "$(command -v git)" ]; then
    log_error "Git no está instalado"
    exit 1
fi

# 1. Preparar directorio
log_info "Preparando directorio de aplicación..."
mkdir -p "$APP_PATH"
cd "$APP_PATH"
log_success "Directorio listo: $APP_PATH"

# 2. Clonar o actualizar repositorio
if [ -d ".git" ]; then
    log_info "Actualizando repositorio desde $BRANCH..."
    git fetch origin
    git reset --hard origin/$BRANCH
    log_success "Repositorio actualizado"
else
    log_info "Clonando repositorio desde $REPO_URL..."
    git clone -b $BRANCH $REPO_URL .
    log_success "Repositorio clonado"
fi

# 3. Crear archivo .env si no existe
if [ ! -f ".env" ]; then
    log_info "Creando archivo .env..."
    cp src/.env.example .env
    
    # Reemplazar valores sensibles
    sed -i "s/APP_ENV=local/APP_ENV=production/" .env
    sed -i "s/APP_DEBUG=true/APP_DEBUG=false/" .env
    sed -i "s/DB_PASSWORD=notion_pass/DB_PASSWORD=$DB_PASSWORD/" .env
    
    log_warning "⚠️  Revisa el archivo .env y actualiza valores sensibles como:"
    log_warning "   - APP_KEY (ejecuta: php artisan key:generate --show)"
    log_warning "   - APP_URL"
    log_warning "   - Credenciales de email/SMTP"
    log_warning ""
    log_warning "Presiona Enter para continuar (o Ctrl+C para cancelar)..."
    read
else
    log_success ".env encontrado, usando configuración existente"
fi

# 4. Detener contenedores anteriores
log_info "Deteniendo contenedores anteriores..."
docker-compose -f docker-compose.yml down || true
log_success "Contenedores detenidos"

# 5. Actualizar imagen do Docker
log_info "Descargando imágenes Docker más recientes..."
docker-compose -f docker-compose.yml pull || true
log_success "Imágenes descargadas"

# 6. Iniciar contenedores
log_info "Iniciando contenedores con Docker Compose..."
docker-compose -f docker-compose.yml up -d
log_success "Contenedores iniciados"

# 7. Esperar a que los servicios estén listos
log_info "Esperando a que los servicios estén disponibles..."
sleep 15

# 8. Instalar dependencias de Composer
log_info "Instalando dependencias de Composer..."
docker-compose -f docker-compose.yml exec -T php composer install --no-dev --optimize-autoloader --no-interaction
log_success "Dependencias de Composer instaladas"

# 9. Generar APP_KEY si no existe
log_info "Verificando APP_KEY..."
docker-compose -f docker-compose.yml exec -T php php artisan key:generate --force --no-interaction || true
log_success "APP_KEY verificada"

# 10. Ejecutar migraciones
log_info "Ejecutando migraciones de base de datos..."
docker-compose -f docker-compose.yml exec -T php php artisan migrate --force
log_success "Migraciones ejecutadas"

# 11. Ejecutar seeders (opcional)
log_warning ""
log_warning "¿Deseas ejecutar los seeders? (s/n)"
read -n 1 run_seeders
if [ "$run_seeders" = "s" ] || [ "$run_seeders" = "S" ]; then
    log_info "Ejecutando seeders..."
    docker-compose -f docker-compose.yml exec -T php php artisan db:seed
    log_success "Seeders ejecutados"
fi
echo ""

# 12. Limpiar cache
log_info "Limpiando y regenerando cache..."
docker-compose -f docker-compose.yml exec -T php php artisan cache:clear
docker-compose -f docker-compose.yml exec -T php php artisan config:cache
docker-compose -f docker-compose.yml exec -T php php artisan route:cache
log_success "Cache regenerado"

# 13. Establecer permisos correctos
log_info "Estableciendo permisos de archivos..."
docker-compose -f docker-compose.yml exec -T php chown -R www-data:www-data /var/www/html
docker-compose -f docker-compose.yml exec -T php chmod -R 755 /var/www/html/storage
docker-compose -f docker-compose.yml exec -T php chmod -R 755 /var/www/html/bootstrap/cache
log_success "Permisos configurados"

# 14. Compilar assets (si Node está disponible)
if docker-compose -f docker-compose.yml exec -T node npm --version &>/dev/null; then
    log_info "Compilando assets con Node..."
    docker-compose -f docker-compose.yml exec -T node npm install
    docker-compose -f docker-compose.yml exec -T node npm run build
    log_success "Assets compilados"
fi

# 15. Mostrar estado
log_info "Estado de contenedores:"
docker-compose -f docker-compose.yml ps

# Footer
echo ""
echo -e "${GREEN}"
echo "╔════════════════════════════════════════════════════════════╗"
echo "║            ✅ ¡DESPLIEGUE COMPLETADO!                     ║"
echo "╠════════════════════════════════════════════════════════════╣"
echo "║  Próximos pasos:                                           ║"
echo "║  1. Verifica los logs: docker-compose logs -f              ║"
echo "║  2. Accede a: http://localhost:8080                        ║"
echo "║  3. Configura tu dominio en .env (APP_URL)                ║"
echo "║  4. Configura certificado SSL (Let's Encrypt)             ║"
echo "║  5. Configura tu proxy/firewall si es necesario           ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo -e "${NC}"
