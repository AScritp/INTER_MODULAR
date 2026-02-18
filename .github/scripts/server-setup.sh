#!/bin/bash

# Script para preparar el servidor Ubuntu para ejecutar INTER_MODULAR
# Este script debe ejecutarse una sola vez en el servidor

set -e

# Colores
BLUE='\033[0;34m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║  Preparación Inicial del Servidor - INTER_MODULAR        ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════╝${NC}"

# Verificar que se ejecuta como root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Este script debe ejecutarse como root (usa: sudo ./server-setup.sh)${NC}"
    exit 1
fi

# 1. Actualizar sistema
echo -e "${YELLOW}[1/8]${NC} Actualizando repositorios del sistema..."
apt-get update -qq
apt-get upgrade -y -qq
echo -e "${GREEN}✓ Sistema actualizado${NC}"

# 2. Instalar dependencias base
echo -e "${YELLOW}[2/8]${NC} Instalando dependencias base..."
apt-get install -y -qq \
    curl \
    wget \
    git \
    build-essential \
    libssl-dev \
    openssl \
    software-properties-common \
    apt-transport-https \
    ca-certificates \
    gnupg \
    lsb-release
echo -e "${GREEN}✓ Dependencias instaladas${NC}"

# 3. Instalar Docker
echo -e "${YELLOW}[3/8]${NC} Instalando Docker..."
if command -v docker &> /dev/null; then
    echo -e "${YELLOW}   Docker ya está instalado: $(docker --version)${NC}"
else
    curl -fsSL https://get.docker.com -o get-docker.sh
    sh get-docker.sh
    rm get-docker.sh
    systemctl start docker
    systemctl enable docker
    echo -e "${GREEN}✓ Docker instalado${NC}"
fi

# 4. Instalar Docker Compose
echo -e "${YELLOW}[4/8]${NC} Instalando Docker Compose..."
if command -v docker-compose &> /dev/null; then
    echo -e "${YELLOW}   Docker Compose ya está instalado: $(docker-compose --version)${NC}"
else
    LATEST_VERSION=$(curl -s https://api.github.com/repos/docker/compose/releases/latest | grep 'tag_name' | cut -d'"' -f4)
    curl -L "https://github.com/docker/compose/releases/download/${LATEST_VERSION}/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    chmod +x /usr/local/bin/docker-compose
    echo -e "${GREEN}✓ Docker Compose instalado${NC}"
fi

# 5. Crear usuario deployer
echo -e "${YELLOW}[5/8]${NC} Creando usuario 'deployer' para despliegues..."
if id "deployer" &>/dev/null; then
    echo -e "${YELLOW}   Usuario 'deployer' ya existe${NC}"
else
    useradd -m -s /bin/bash deployer
    usermod -aG docker deployer
    usermod -aG sudo deployer
    echo -e "${GREEN}✓ Usuario 'deployer' creado${NC}"
fi

# 6. Preparar directorios
echo -e "${YELLOW}[6/8]${NC} Preparando directorios..."
mkdir -p /home/deployer/apps
mkdir -p /home/deployer/.ssh
chmod 700 /home/deployer/.ssh
chown -R deployer:deployer /home/deployer/apps
chown -R deployer:deployer /home/deployer/.ssh
echo -e "${GREEN}✓ Directorios preparados${NC}"

# 7. Configurar Firewall (UFW)
echo -e "${YELLOW}[7/8]${NC} Configurando firewall (UFW)..."
if ! command -v ufw &> /dev/null; then
    apt-get install -y -qq ufw
fi

ufw --force enable &>/dev/null || true
ufw allow 22/tcp &>/dev/null || true
ufw allow 80/tcp &>/dev/null || true
ufw allow 443/tcp &>/dev/null || true

echo -e "${GREEN}✓ Firewall configurado${NC}"
echo -e "${YELLOW}   Puertos permitidos: 22 (SSH), 80 (HTTP), 443 (HTTPS)${NC}"

# 8. Mostrar información del servidor
echo -e "${YELLOW}[8/8]${NC} Información del servidor...\n${NC}"
echo -e "${BLUE}Sistema:${NC}"
uname -a
echo -e "\n${BLUE}Docker:${NC}"
docker --version
docker-compose --version

# Instrucciones finales
echo ""
echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  ✅ Servidor preparado exitosamente!                    ║${NC}"
echo -e "${GREEN}╠═══════════════════════════════════════════════════════════╣${NC}"
echo -e "${GREEN}║  Próximos pasos:                                         ║${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}║  1. Agregar clave SSH de github actions:                ║${NC}"
echo -e "${GREEN}║     ssh deployer@servidor                               ║${NC}"
echo -e "${GREEN}║     mkdir -p ~/.ssh                                      ║${NC}"
echo -e "${GREEN}║     echo 'tu_clave_publica' >> ~/.ssh/authorized_keys    ║${NC}"
echo -e "${GREEN}║     chmod 600 ~/.ssh/authorized_keys                     ║${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}║  2. Clona el repositorio:                               ║${NC}"
echo -e "${GREEN}║     su - deployer                                        ║${NC}"
echo -e "${GREEN}║     cd ~/apps                                            ║${NC}"
echo -e "${GREEN}║     git clone <repo-url> inter-modular                  ║${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}║  3. Ejecuta el script de despliegue:                    ║${NC}"
echo -e "${GREEN}║     cd inter-modular                                     ║${NC}"
echo -e "${GREEN}║     bash .github/scripts/deploy.sh                       ║${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}║  4. Configura HTTPS (opcional pero recomendado):        ║${NC}"
echo -e "${GREEN}║     sudo apt install certbot python3-certbot-nginx       ║${NC}"
echo -e "${GREEN}║     sudo certbot certonly --standalone -d tu_dominio    ║${NC}"
echo -e "${GREEN}║                                                           ║${NC}"
echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
