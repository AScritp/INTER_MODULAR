#!/bin/bash

# Script para hacer rollback a una versión anterior
# Uso: ./rollback.sh <commit_hash> o ./rollback.sh HEAD~1

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

APP_PATH="${APP_PATH:=/home/deployer/apps/inter-modular}"
TARGET_COMMIT="${1:-HEAD~1}"

echo -e "${BLUE}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║     INTER_MODULAR - Script de Rollback                  ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════╝${NC}"

if [ ! -d "$APP_PATH/.git" ]; then
    echo -e "${RED}❌ No es un repositorio Git válido: $APP_PATH${NC}"
    exit 1
fi

cd "$APP_PATH"

# Mostrar información actual
echo -e "${YELLOW}Información actual:${NC}"
echo -e "   Rama: $(git rev-parse --abbrev-ref HEAD)"
echo -e "   Commit actual: $(git rev-parse --short HEAD)"
echo ""

# Confirmar acción
echo -e "${YELLOW}⚠️  ¿Deseas hacer rollback a: $TARGET_COMMIT? (s/n)${NC}"
read -n 1 confirm
if [ "$confirm" != "s" ] && [ "$confirm" != "S" ]; then
    echo -e "\n${RED}Operación cancelada${NC}"
    exit 0
fi
echo ""

# Hacer backup de .env
if [ -f ".env" ]; then
    echo -e "${BLUE}Haciendo backup de .env...${NC}"
    cp .env .env.backup.$(date +%s)
    echo -e "${GREEN}✓ Backup creado${NC}"
fi

# Detener contenedores
echo -e "${BLUE}Deteniendo contenedores...${NC}"
docker-compose -f docker-compose.yml down || true
echo -e "${GREEN}✓ Contenedores detenidos${NC}"

# Hacer backup de base de datos (opcional)
echo -e "${YELLOW}¿Deseas hacer backup de la base de datos antes? (s/n)${NC}"
read -n 1 backup_db
echo ""
if [ "$backup_db" = "s" ] || [ "$backup_db" = "S" ]; then
    BACKUP_FILE="mysql_backup_$(date +%Y%m%d_%H%M%S).sql"
    echo -e "${BLUE}Haciendo backup de base de datos a $BACKUP_FILE...${NC}"
    docker-compose -f docker-compose.yml exec -T mysql mysqldump -uroot -proot_pass notion_db > "$BACKUP_FILE"
    echo -e "${GREEN}✓ Backup de DB creado${NC}"
fi

# Hacer rollback del código
echo -e "${BLUE}Haciendo rollback del código a $TARGET_COMMIT...${NC}"
git fetch origin
git reset --hard "$TARGET_COMMIT"
echo -e "${GREEN}✓ Código actualizado a $(git rev-parse --short HEAD)${NC}"

# Reiniciar contenedores
echo -e "${BLUE}Reiniciando contenedores...${NC}"
docker-compose -f docker-compose.yml up -d
sleep 10
echo -e "${GREEN}✓ Contenedores iniciados${NC}"

# Ejecutar migraciones (si fueron revertidas)
echo -e "${BLUE}Verificando integridad de base de datos...${NC}"
docker-compose -f docker-compose.yml exec -T php php artisan migrate --force
echo -e "${GREEN}✓ Migraciones ejecutadas${NC}"

# Limpiar cache
echo -e "${BLUE}Limpiando cache...${NC}"
docker-compose -f docker-compose.yml exec -T php php artisan cache:clear
docker-compose -f docker-compose.yml exec -T php php artisan config:cache
docker-compose -f docker-compose.yml exec -T php php artisan route:cache
echo -e "${GREEN}✓ Cache limpiado${NC}"

# Mostrar estado
echo ""
echo -e "${BLUE}Estado de contenedores:${NC}"
docker-compose -f docker-compose.yml ps

echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ Rollback completado exitosamente!                   ║${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}║  Commit actual: $(git rev-parse --short HEAD)                    ║${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}║  Para ver logs: docker-compose logs -f                   ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
