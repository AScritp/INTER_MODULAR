# Configuración de Docker para Producción

Este archivo contiene configuraciones adicionales y mejoras para ejecutar INTER_MODULAR en producción.

## 🐳 Docker Compose Recomendada para Producción

Si deseas optimizar tu `docker-compose.yml` para producción, crea una variante:

**Archivo: `docker-compose.prod.yml`**

```yaml
version: '3.8'

services:
  # Nginx - Servidor Web
  nginx:
    image: nginx:alpine
    container_name: notion_nginx_prod
    restart: always
    ports:
      - "80:80"
      - "443:443"  # Agregado para HTTPS
    volumes:
      - ./src:/var/www/html:ro  # readonly en producción
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      - ./certs:/etc/nginx/certs:ro  # Certificados SSL
      - nginx_cache:/var/cache/nginx  # Cache de Nginx
    depends_on:
      - php
    networks:
      - notion_network
    healthcheck:
      test: ["CMD", "wget", "--quiet", "--tries=1", "--spider", "http://localhost/health"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"

  # PHP-FPM
  php:
    build:
      context: ./docker/php
      dockerfile: Dockerfile
    container_name: notion_php_prod
    restart: always
    volumes:
      - ./src:/var/www/html:rw
      - ./docker/php/www.conf:/usr/local/etc/php-fpm.d/www.conf:ro
      - ./docker/php/php.ini:/usr/local/etc/php/conf.d/php.ini:ro
    environment:
      DB_CONNECTION: mysql
      DB_HOST: mysql
      DB_PORT: 3306
      DB_DATABASE: ${DB_DATABASE:-notion_db}
      DB_USERNAME: ${DB_USERNAME:-notion_user}
      DB_PASSWORD: ${DB_PASSWORD:-notion_pass}
      APP_ENV: ${APP_ENV:-production}
      APP_DEBUG: ${APP_DEBUG:-false}
      REDIS_HOST: redis
      REDIS_PORT: 6379
    depends_on:
      - mysql
      - redis
    networks:
      - notion_network
    deploy:
      resources:
        limits:
          cpus: '1'
          memory: 1G
        reservations:
          cpus: '0.5'
          memory: 512M
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"

  # MySQL Database
  mysql:
    image: mysql:8.0
    container_name: notion_mysql_prod
    restart: always
    ports:
      - "127.0.0.1:3306:3306"  # Solo localhost
    environment:
      MYSQL_DATABASE: ${DB_DATABASE:-notion_db}
      MYSQL_USER: ${DB_USERNAME:-notion_user}
      MYSQL_PASSWORD: ${DB_PASSWORD:-notion_pass}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD:-root_pass}
      MYSQL_INITDB_SKIP_TZINFO: "yes"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql
      - ./docker/mysql/my.cnf:/etc/mysql/conf.d/my.cnf:ro
    networks:
      - notion_network
    deploy:
      resources:
        limits:
          cpus: '1.5'
          memory: 2G
        reservations:
          cpus: '1'
          memory: 1G
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost"]
      interval: 30s
      timeout: 10s
      retries: 3
      start_period: 40s
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"

  # Redis Cache
  redis:
    image: redis:7-alpine
    container_name: notion_redis_prod
    restart: always
    command: redis-server --requirepass ${REDIS_PASSWORD:-redis_pass} --appendonly yes
    ports:
      - "127.0.0.1:6379:6379"  # Solo localhost
    volumes:
      - redis_data:/data
    networks:
      - notion_network
    deploy:
      resources:
        limits:
          cpus: '0.5'
          memory: 512M
        reservations:
          cpus: '0.25'
          memory: 256M
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 30s
      timeout: 10s
      retries: 3
    logging:
      driver: "json-file"
      options:
        max-size: "10m"
        max-file: "3"

  # Node (solo si necesitas compilar assets)
  node:
    image: node:18-alpine
    container_name: notion_node_prod
    working_dir: /var/www/html
    volumes:
      - ./src:/var/www/html
    command: sh -c "npm install && npm run build"
    profiles:  # Este servicio se levanta solo cuando lo necesites
      - build

volumes:
  mysql_data:
    driver: local
  redis_data:
    driver: local
  nginx_cache:
    driver: local

networks:
  notion_network:
    driver: bridge
```

## 🌐 Configuración de Nginx para Producción

**Archivo: `docker/nginx/default.conf` (mejorado)**

```nginx
upstream php {
    server php:9000;
}

# Redirigir HTTP a HTTPS
server {
    listen 80;
    server_name _;
    return 301 https://$host$request_uri;
}

# HTTPS Principal
server {
    listen 443 ssl http2;
    server_name _;
    
    # Certificados SSL
    ssl_certificate /etc/nginx/certs/cert.pem;
    ssl_certificate_key /etc/nginx/certs/key.pem;
    
    # Configuración SSL segura
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    root /var/www/html/public;
    index index.php;
    
    # Logs
    access_log /var/log/nginx/access.log;
    error_log /var/log/nginx/error.log;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml application/json application/javascript application/xml+rss application/x-font-ttf font/opentype image/svg+xml;
    
    # Health check endpoint
    location /health {
        access_log off;
        return 200 "healthy\n";
        add_header Content-Type text/plain;
    }
    
    # Frontend SPA routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP
    location ~ \.php$ {
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_pass php;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param PATH_TRANSLATED $document_root$fastcgi_path_info;
        
        # Timeouts
        fastcgi_connect_timeout 60s;
        fastcgi_send_timeout 60s;
        fastcgi_read_timeout 60s;
        
        # Buffers
        fastcgi_buffering on;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
    }
    
    # Estáticos con cache
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
    
    location ~* ^/storage/ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|webp)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
}
```

## 🔄 Comandos de Producción

### Iniciar solo servicios de producción

```bash
# Usar docker-compose.prod.yml
docker-compose -f docker-compose.prod.yml up -d

# O levantar Node solo cuando necesites compilar
docker-compose -f docker-compose.prod.yml --profile build up node
```

### Monitoreo

```bash
# Ver recursos usados
docker stats

# Ver logs en tiempo real
docker-compose logs -f php

# Verificar contenedores
docker-compose ps
```

### Backups

```bash
# Backup de base de datos
docker-compose exec mysql mysqldump -uroot -p$DB_ROOT_PASSWORD --all-databases > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup de volúmenes
docker run --rm -v notion_mysql_data:/data -v $(pwd):/backup alpine tar czf /backup/mysql_backup_$(date +%Y%m%d).tar.gz -C / data

# Restore
docker run --rm -v notion_mysql_data:/data -v $(pwd):/backup alpine tar xzf /backup/mysql_backup_YYYYMMDD.tar.gz -C /
```

## 📊 Configuración de Resource Limits

Los límites de CPU y memoria se configuran en `deploy.resources`:

```yaml
deploy:
  resources:
    limits:      # Máximo que puede usar
      cpus: '1'
      memory: 1G
    reservations: # Mínimo reservado
      cpus: '0.5'
      memory: 512M
```

Ajusta según tu servidor:
- **Servidor pequeño (1GB RAM):** Reduce límites a 0.5 CPU, 512MB
- **Servidor mediano (2GB RAM):** 1 CPU, 1G (como está)
- **Servidor grande (4GB+ RAM):** 2 CPUs, 2G

## 🔒 Seguridad en Producción

Checklist de seguridad:

- [ ] Cambiar contraseñas de MySQL y Redis
- [ ] Usar certificados SSL/TLS válidos
- [ ] Configurar firewall (solo puertos 22, 80, 443)
- [ ] Habilitar backups automáticos
- [ ] Monitorear logs y uso de recursos
- [ ] Mantener actualizadas imágenes Docker
- [ ] Usar variables de entorno para secretos
- [ ] Deshabilitar acceso directo a MySQL/Redis desde el exterior
- [ ] Implementar rate limiting en Nginx
- [ ] Usar secrets de GitHub para credenciales

