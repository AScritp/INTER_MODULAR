# 📑 Índice Complete de CI/CD - INTER_MODULAR

Lee este archivo primero para entender la estructura de la guía CI/CD.

---

## 🎯 ¿Por dónde empezar?

Dependiendo de tu experiencia, elige:

### ⚡ Si tienes prisa (30 min)
Ir a: [QUICK_START.md](QUICK_START.md)
- Guía de 10 pasos
- Checklist rápido
- Comandos directos

### 📚 Si tienes tiempo (1-2 horas)
Ir a: [CI_CD_GUIDE.md](../CI_CD_GUIDE.md)
- Guía completa y detallada
- Explicaciones de cada paso
- Troubleshooting detallado

### 🔐 Si necesitas configurar Secretos
Ir a: [GITHUB_SECRETS_SETUP.md](GITHUB_SECRETS_SETUP.md)
- Cómo generar secretos
- Dónde agregarlos en GitHub
- Resolución de problemas de autenticación

### 🐳 Si necesitas optimizar Docker
Ir a: [DOCKER_PRODUCTION_CONFIG.md](DOCKER_PRODUCTION_CONFIG.md)
- Docker Compose para producción
- Configuración de Nginx segura
- Limits de recursos
- Backups y monitoreo

### 📝 Si necesitas configurar variables de entorno
Ir a: [ENV_PRODUCTION_EXAMPLE.md](ENV_PRODUCTION_EXAMPLE.md)
- Plantilla completa de `.env`
- Explicación de cada variable
- Seguridad y mejores prácticas

---

## 📂 Archivos de Configuración

```
.github/
├── workflows/
│   └── deploy.yml              ← Workflow principal de CI/CD
├── scripts/
│   ├── deploy.sh               ← Script de despliegue manual
│   ├── server-setup.sh         ← Preparación inicial del servidor
│   └── rollback.sh             ← Reversión a versión anterior
├── README.md                   ← ESTE ARCHIVO
├── QUICK_START.md              ← Guía de 10 pasos (recomendado empezar aquí)
├── CI_CD_GUIDE.md              ← Guía completa detallada
├── GITHUB_SECRETS_SETUP.md     ← Configuración de secretos
├── DOCKER_PRODUCTION_CONFIG.md ← Docker para producción
└── ENV_PRODUCTION_EXAMPLE.md   ← Variables de entorno
```

---

## 🚀 Flujo de Despliegue

```
Desarrollo Local
     ↓
git push a GitHub (rama main)
     ↓
GitHub Actions dispara workflow
     ↓
┌─────────────────────────────────┐
│ 1. BUILD JOB                    │
│ - Construye imagen Docker       │
│ - Push a GitHub Container Reg   │
└─────────────────────────────────┘
     ↓
┌─────────────────────────────────┐
│ 2. TEST JOB                     │
│ - Instala dependencias PHP      │
│ - Crea BD de testing            │
│ - Ejecuta tests                 │
└─────────────────────────────────┘
     ↓
┌─────────────────────────────────┐
│ 3. DEPLOY JOB (Solo en main)    │
│ - Conecta por SSH al servidor   │
│ - Actualiza código (git pull)   │
│ - Actualiza contenedores        │
│ - Ejecuta migraciones           │
│ - Limpia cache                  │
│ - Compila assets                │
└─────────────────────────────────┘
     ↓
Aplicación en Producción
```

---

## 📋 Checklist de Instalación

### Paso 1: Preparar Servidor
- [ ] Ejecuta: `sudo bash .github/scripts/server-setup.sh`
- [ ] Verifica Docker: `docker --version`
- [ ] Verifica Docker Compose: `docker-compose --version`

### Paso 2: Generar Secretos
- [ ] Genera claves SSH: `ssh-keygen -t ed25519 -f ~/.ssh/github_deploy -N ""`
- [ ] Genera APP_KEY: `php artisan key:generate --show`
- [ ] Genera contraseñas seguras: `openssl rand -base64 32`

### Paso 3: Configurar GitHub
- [ ] Crea repositorio en GitHub
- [ ] Agrega 10 secretos en Settings → Secrets
- [ ] Verifica que el workflow `.github/workflows/deploy.yml` existe

### Paso 4: Desplegar
- [ ] Crea rama `main` si no existe
- [ ] Haz `git push` a `main`
- [ ] Observa Actions para ver el despliegue
- [ ] Verifica en el servidor: `docker-compose ps`

---

## 🔧 Comandos Útiles

### En tu máquina local

```bash
# Verificar status de Git
git status

# Hacer push para disparar workflow
git add .
git commit -m "cambios"
git push origin main

# Ver logs del workflow (en GitHub Actions UI)
# O usar GitHub CLI:
gh run list
gh run view <run-id>
```

### En el servidor

```bash
# Ver estado de contenedores
docker-compose ps

# Ver logs en tiempo real
docker-compose logs -f

# Acceder a PHP
docker-compose exec php bash

# Ejecutar comando Laravel
docker-compose exec php php artisan tinker

# Hacer backup de BD
docker-compose exec mysql mysqldump -uroot -proot_pass notion_db > backup.sql

# Hacer rollback
bash .github/scripts/rollback.sh HEAD~1
```

---

## 🐛 Troubleshooting Rápido

| Problema | Causa | Solución |
|----------|-------|----------|
| "Permission denied (publickey)" | Clave SSH no configurada | Ver [GITHUB_SECRETS_SETUP.md](GITHUB_SECRETS_SETUP.md#troubleshooting) |
| "Connection refused" en MySQL | MySQL no iniciado | `docker-compose logs mysql` |
| "Port already in use" | Puerto en uso | Cambiar puerto en docker-compose.yml |
| Workflow no dispara | Rama no es `main` | Hacer push a `main` |
| Deploy falla en migraciones | BD no lista | Aumentar `sleep` en deploy.yml |
| App muestra error 500 | Permisos de archivos | Ejecutar `docker-compose exec php chown -R www-data:www-data /var/www/html` |

---

## 📚 Recursos Adicionales

### Documentación Oficial
- [GitHub Actions Docs](https://docs.github.com/actions)
- [Docker Compose Reference](https://docs.docker.com/compose/)
- [Laravel Deployment](https://laravel.com/docs/deployment)
- [Nginx Configuration](https://nginx.org/en/docs/)

### Guías Relacionadas
- [CI_CD_GUIDE.md](../CI_CD_GUIDE.md) - Guía completa detallada
- [README.md](../) - Información del proyecto
- [SETUP_GUIDE.md](../SETUP_GUIDE.md) - Setup general del proyecto

---

## 🎯 Metas Alcanzables

### Corto Plazo (Hoy)
- [ ] Repositorio en GitHub creado
- [ ] Servidor preparado con Docker
- [ ] Secrets configurados en GitHub
- [ ] Primer despliegue automático funcionando

### Mediano Plazo (Esta semana)
- [ ] HTTPS/SSL configurado
- [ ] Backups automáticos configurados
- [ ] Monitoreo de logs activado
- [ ] Rollback automático en caso de fallo

### Largo Plazo (Este mes)
- [ ] CD/CD fully automated
- [ ] Testing coverage >80%
- [ ] Alertas en Slack/Discord
- [ ] Zero-downtime deployments

---

## 💡 Mejores Prácticas

✅ **Debes hacer:**
- Usar `main` para producción, `develop` para staging
- Hacer pull requests antes de mergear a `main`
- Requerir que pasen tests antes de mergear
- Hacer backups regularmente
- Monitorear logs en producción
- Actualizar dependencias semanalmente

❌ **Nunca hagas:**
- Hacer push directo a `main`
- Cometer secretos o contraseñas
- Desactivar tests para deployments
- Ignorar errores en los logs
- Olvidar hacer backups
- Cambiar credenciales sin actualizar secrets

---

## 📞 Soporte y Ayuda

### Si algo no funciona:

1. **Lee la guía relevante** - Comienza aquí
2. **Revisa logs** - Mirar logs siempre revela el problema
3. **Busca en Troubleshooting** - Muchos problemas ya están documentados
4. **Pregunta a GitHub Copilot** - "¿Por qué falla mi deploy?"

### Logs más útiles

```bash
# GitHub Actions - Ver directamente en GitHub UI

# Servidor - ver logs de servicios
docker-compose logs -f php
docker-compose logs -f mysql
docker-compose logs -f nginx

# Aplicación Laravel
docker-compose exec php tail -f storage/logs/laravel.log
```

---

## 🎓 Aprender Más

Después de que todo funcione, aprende:
- Cómo escribir GitHub Actions workflows mejores
- Docker security best practices
- nginx configuration tuning
- Laravel optimization
- Kubernetes (escalar beyond Docker Compose)

---

## 📝 Notas Finales

Este setup es **production-ready** con:
- ✅ Builds automáticos de Docker
- ✅ CI/CD con testing
- ✅ Despliegue automático por SSH
- ✅ Soporte para rollback
- ✅ Documentación completa
- ✅ Mejores prácticas de seguridad

**Tiempo estimado de setup:** 30-60 minutos
**Mantenimiento:** 5-10 min/week

---

## 🏁 Próximo Paso

👉 Comienza por [QUICK_START.md](QUICK_START.md) o [CI_CD_GUIDE.md](../CI_CD_GUIDE.md)

¡Buena suerte! 🚀

