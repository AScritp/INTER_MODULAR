#!/bin/bash

# Script de verificación de configuración de CI/CD
# Ejecuta este script para verificar que todo está configurado correctamente

set -e

# Colores
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Verificación de Configuración de CI/CD                  ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""

passed=0
failed=0

# Función para verificar
check() {
    local test_name=$1
    local test_command=$2
    
    if eval "$test_command" &> /dev/null; then
        echo -e "${GREEN}✅${NC} $test_name"
        ((passed++))
    else
        echo -e "${RED}❌${NC} $test_name"
        ((failed++))
    fi
}

# ==================== VERIFICACIONES LOCALES ====================
echo -e "${YELLOW}[LOCAL] Verificaciones en tu máquina:${NC}"
echo ""

# Git
check "Git instalado" "command -v git"
check "En un repositorio Git" "[ -d .git ]"
check "Rama main existe" "git branch | grep -q 'main'"

# Docker (si está instalado localmente)
if command -v docker &> /dev/null; then
    check "Docker instalado" "docker --version"
    check "Docker Compose instalado" "docker-compose --version"
fi

# PHP (si está instalado)
if command -v php &> /dev/null; then
    check "PHP 8.2+" "php -v | grep -q '8\.[2-9]'"
    check "Composer instalado" "command -v composer"
fi

echo ""

# ==================== VERIFICACIONES DE CONFIGURACIÓN ====================
echo -e "${YELLOW}[CONFIG] Archivos de configuración:${NC}"
echo ""

check ".github/workflows/deploy.yml existe" "[ -f .github/workflows/deploy.yml ]"
check ".github/scripts/deploy.sh existe" "[ -f .github/scripts/deploy.sh ]"
check ".github/scripts/server-setup.sh existe" "[ -f .github/scripts/server-setup.sh ]"
check ".github/scripts/rollback.sh existe" "[ -f .github/scripts/rollback.sh ]"
check ".github/GITHUB_SECRETS_SETUP.md existe" "[ -f .github/GITHUB_SECRETS_SETUP.md ]"
check ".github/CI_CD_GUIDE.md existe" "[ -f ../CI_CD_GUIDE.md ]"

echo ""

# ==================== VERIFICACIONES DE CÓDIGO ====================
echo -e "${YELLOW}[CODE] Verificaciones del código fuente:${NC}"
echo ""

# Comentarios en los archivos de código
if [ -f "src/.env.example" ]; then
    check ".env.example existe" "[ -f src/.env.example ]"
fi

check "docker-compose.yml existe" "[ -f docker-compose.yml ]"
check "docker/php/Dockerfile existe" "[ -f docker/php/Dockerfile ]"
check "docker/nginx/default.conf existe" "[ -f docker/nginx/default.conf ]"

echo ""

# ==================== VERIFICACIONES DE SEGURIDAD ====================
echo -e "${YELLOW}[SECURITY] Verificaciones de seguridad:${NC}"
echo ""

check ".env NO commiteado" "! grep -r 'DB_PASSWORD=' .github/"
check ".env.local NO commiteado" "! find . -name '.env.local' -type f"
check "Claves privadas NO commiteadas" "! find . -name '*private*' -type f -not -path './.git/*'"

# Buscar secretos en el código
if command -v grep &> /dev/null; then
    check "No hay contraseñas en el código" "! grep -r 'password.*=' src/ --include='*.php' --include='*.js' | grep -v -E '(PASSWORD|password|PASS|pass|pwd).*=.*[\$]' || true"
fi

echo ""

# ==================== VERIFICACIONES DE REPOSITORIO ====================
echo -e "${YELLOW}[REPO] Verificaciones del repositorio:${NC}"
echo ""

# Git remoto
if git remote -v | grep -q "origin"; then
    echo -e "${GREEN}✅${NC} Remoto origin configurado"
    ((passed++))
else
    echo -e "${RED}❌${NC} Remoto origin NO configurado"
    ((failed++))
fi

# Verificar que no hay cambios sin commitar en archivos sensibles
if [ ! -f ".env" ]; then
    echo -e "${GREEN}✅${NC} .env no existe (bueno)"
    ((passed++))
else
    echo -e "${YELLOW}⚠️ ${NC} .env existe"
fi

echo ""

# ==================== VERIFICACIONES DEL SERVIDOR ====================
# Si estamos en un servidor
if command -v docker &> /dev/null && docker ps &> /dev/null; then
    echo -e "${YELLOW}[SERVER] Verificaciones del servidor:${NC}"
    echo ""
    
    check "Docker running" "docker ps > /dev/null"
    check "Docker Compose accessible" "docker-compose --version"
    
    # Si existe docker-compose.yml
    if [ -f "docker-compose.yml" ]; then
        check "Docker Compose válido" "docker-compose config > /dev/null"
    fi
    
    echo ""
fi

# ==================== RESUMEN FINAL ====================
echo -e "${BLUE}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  RESULTADO                                                ║${NC}"
echo -e "${BLUE}╠═══════════════════════════════════════════════════════════╣${NC}"

total=$((passed + failed))
echo ""
echo "   Pasadas:  ${GREEN}$passed${NC}"
echo "   Fallidas: ${RED}$failed${NC}"
echo "   Total:    $total"
echo ""

if [ $failed -eq 0 ]; then
    echo -e "${GREEN}✅ ¡Todo está correctamente configurado!${NC}"
    echo ""
    echo -e "${BLUE}Próximos pasos:${NC}"
    echo "1. Configura secretos en GitHub (Settings → Secrets)"
    echo "2. Haz push a la rama main: git push origin main"
    echo "3. Observa el workflow en GitHub Actions"
    echo "4. Verificar que el deploy fue exitoso"
    exit 0
else
    echo -e "${RED}⚠️  Hay $([ $failed -eq 1 ] && echo 'una' || echo 'algunas') verificación(es) que falló(aron)${NC}"
    echo ""
    echo -e "${YELLOW}Verifica:${NC}"
    echo "- .github/workflows/deploy.yml existe y es válido"
    echo "- Scripts ejecutables (.github/scripts/*.sh)"
    echo "- Documentación completa (.github/*.md)"
    echo "- No hay secretos en el código fuente"
    echo ""
    exit 1
fi
