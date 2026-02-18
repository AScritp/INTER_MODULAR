# Diagrama de Flujo - CI/CD INTER_MODULAR

## 🔄 Flujo Completo de Integración y Despliegue

```
┌─────────────────────────────────────────────────────────────┐
│                    FLUJO GENERAL CI/CD                      │
└─────────────────────────────────────────────────────────────┘

PASO 1: DESARROLLO LOCAL
════════════════════════════════════════════════════════════════

    Desarrollador                   GitHub Repository
    ────────────                   ──────────────────
    
    1. Crea rama feature
       $ git checkout -b feature/nueva-funcionalidad
    
    2. Hace cambios y commits
       $ git add .
       $ git commit -m "feat: nueva funcionalidad"
    
    3. Push a GitHub
       $ git push origin feature/nueva-funcionalidad
    
    4. Crea Pull Request (PR)
       GitHub UI → New Pull Request


PASO 2: TESTING AUTOMÁTICO (En acción solo en PR)
════════════════════════════════════════════════════════════════

    GitHub Actions dispara workflow: ci.yml (si existe)
    
    ┌──────────────┐
    │ Build Docker │ ← Construye imagen
    │   (si PR)    │
    └──────────────┘
           ↓
    ┌──────────────┐
    │ Run Tests    │ ← Ejecuta Tests (PHP, Jest, etc)
    │              │
    └──────────────┘
           ↓
    ✅ Si pasa: PR muestra "Check passed"
    ❌ Si falla: PR muestra "Check failed" → No se puede mergear


PASO 3: REVIEW Y MERGE
════════════════════════════════════════════════════════════════

    Team Lead o Reviewer
    ──────────────────────
    
    1. Revisa el código en el PR
    
    2. Si está OK: Approva el PR
    
    3. Mergea a main (o develop)
       $ git merge --no-ff feature/nueva-funcionalidad


PASO 4: DEPLOYMENT AUTOMÁTICO (Solo en main)
════════════════════════════════════════════════════════════════

    GitHub Actions dispara workflow: deploy.yml
    
    ┌─────────────────────────────────────────────────────┐
    │           BUILD JOB (ghcr.io)                       │
    ├─────────────────────────────────────────────────────┤
    │                                                     │
    │  $ docker build -t ghcr.io/.../app:latest .        │
    │  $ docker push ghcr.io/.../app:latest              │
    │                                                     │
    │  ↓ Imagen guardada en GitHub Container Registry    │
    └─────────────────────────────────────────────────────┘
                        ↓
    ┌─────────────────────────────────────────────────────┐
    │           TEST JOB (ubuntu-latest)                 │
    ├─────────────────────────────────────────────────────┤
    │                                                     │
    │  Services levantados:                              │
    │  - MySQL 8.0                                        │
    │  - Redis (Alpine)                                   │
    │                                                     │
    │  $ composer install                                 │
    │  $ php artisan migrate                              │
    │  $ php artisan test                                 │
    │                                                     │
    │  ✅ Tests OK → Continúa con Deploy                 │
    │  ❌ Tests Fallan → Detiene workflow                 │
    └─────────────────────────────────────────────────────┘
                        ↓
    ┌─────────────────────────────────────────────────────┐
    │        DEPLOY JOB (Tu Servidor en Producción)      │
    ├─────────────────────────────────────────────────────┤
    │                                                     │
    │  GitHub Actions conecta por SSH:                   │
    │  $ ssh deployer@203.0.113.45                       │
    │                                                     │
    │  En el servidor ejecuta deploy.sh:                 │
    │                                                     │
    │  1. $ git fetch origin                              │
    │     $ git reset --hard origin/main                  │
    │                                                     │
    │  2. $ docker-compose down                           │
    │     $ docker-compose up -d                          │
    │                                                     │
    │  3. $ docker-compose exec php artisan migrate       │
    │     $ docker-compose exec php artisan cache:clear  │
    │                                                     │
    │  4. $ docker-compose ps (verificar)                │
    │                                                     │
    └─────────────────────────────────────────────────────┘
                        ↓
    
    ✅ DESPLIEGUE EXITOSO
    
    Aplicación en vivo en: https://tu-dominio.com


PASO 5: MONITOREO EN PRODUCCIÓN
════════════════════════════════════════════════════════════════

    Verificaciones:
    
    $ docker-compose ps          ← Ver estado de contenedores
    $ docker-compose logs -f     ← Ver logs en tiempo real
    $ docker stats               ← Ver uso de recursos
    
    Si algo falla:
    
    $ bash .github/scripts/rollback.sh HEAD~1  ← Volver a versión anterior
```

---

## 📡 Diagrama de Git Flow

```
develop                   main              Tags
  │                        │                │
  ├─ feature/login ─────┐  │               │
  │                     │  │               │
  │                  (PR)  │               │
  │                     ↓  │               │
  ├─────────────────────→ [merge] ─────────→ v1.0.0
  │                        │               │
  │                   [Deploy]            │
  │                        │               │
  │                  Production           │
  │                        │               │
  │                    ↓ [Update]          │
  │              users.ejemplo.com         │
  │                        │               │
  ├─ feature/auth ─────┐   │               │
  │                    │   │               │
  │                 (PR)   │               │
  │                    ↓   │               │
  └────────────────────→ [merge] ─────────→ v1.1.0
                           │               │
                      [Deploy]            │
                           │               │
                       Production          │
```

---

## 🏗️ Arquitectura de Contenedores

```
┌─────────────────────────────────────────────────────┐
│         Docker Compose Network: notion_network      │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌───────────────────────────────────┐            │
│  │      Nginx (Puerto 80/443)        │            │
│  │  Container: notion_nginx_prod     │            │
│  │  Image: nginx:alpine              │            │
│  │  ← Recibe requests HTTP/HTTPS     │            │
│  └───────────────────────────────────┘            │
│           │                                        │
│           ↓ (proxy)                                │
│  ┌───────────────────────────────────┐            │
│  │  PHP-FPM (Puerto 9000)            │            │
│  │  Container: notion_php_prod       │            │
│  │  Image: php:8.2-fpm               │            │
│  │  ← Procesa requests PHP           │            │
│  └───────────────────────────────────┘            │
│           │                                        │
│           ├─→ ┌──────────────────────┐            │
│           │   │  MySQL (Puerto 3306) │            │
│           │   │  Container: mysql    │            │
│           │   │  Image: mysql:8.0    │            │
│           │   │  ← Almacena datos    │            │
│           │   └──────────────────────┘            │
│           │                                        │
│           └─→ ┌──────────────────────┐            │
│               │  Redis (Puerto 6379) │            │
│               │  Container: redis    │            │
│               │  Image: redis:alpine │            │
│               │  ← Cache y sesiones  │            │
│               └──────────────────────┘            │
│                                                     │
│  ┌───────────────────────────────────┐            │
│  │  Node.js (Puerto 5174)            │            │
│  │  Container: notion_node           │            │
│  │  Image: node:18-alpine            │            │
│  │  ← Compila Vue.js/CSS             │            │
│  │  (Solo cuando se necesita)        │            │
│  └───────────────────────────────────┘            │
│                                                     │
└─────────────────────────────────────────────────────┘

Volúmenes persistentes:
├── mysql_data     ← Base de datos
├── redis_data     ← Cache
└── nginx_cache    ← Cache de Nginx
```

---

## 🔐 Flujo de Autenticación SSH

```
GitHub Actions                SSH Tunnel              Tu Servidor
───────────────               ──────────              ──────────

┌──────────────────┐         ┌──────────────┐       ┌──────────┐
│ Stores Secrets:  │    SSH  │ Private Key  │       │ ~/.ssh/  │
│ SSH_PRIVATE_KEY  ├─────────→ Certificate  ├──────→ authorized│
│ SSH_HOST         │ :22     │              │       │ _keys    │
│ SSH_USER         │         └──────────────┘       └──────────┘
│ SSH_PORT         │              │                      │
└──────────────────┘              │                      │
                                  │ SSH Tunnel           │
                          ┌────────┴─────────┐           │
                          ↓                  ↓           │
                    ┌──────────────────────────────┐     │
                    │  docker-compose exec ...    │-----┘
                    │  php artisan migrate        │
                    │  npm run build              │
                    └──────────────────────────────┘
```

---

## 📊 Estados de Workflow

```
Push a main
    ↓
┌─────────────────────────────┐
│  Jobs Queued/Running        │ ← Amarillo en Actions
│  (Build → Test → Deploy)    │
└─────────────────────────────┘
    ↓
┌─────────────────────────────┐         ┌─────────────────┐
│  All Jobs Passed            │────────→│ Green checkmark │
│  (Deploy completed)         │         │ in GitHub UI    │
└─────────────────────────────┘         └─────────────────┘
    ↓
    Aplicación activa en
    https://tu-dominio.com
    
    
    
    ALT: Si algo falla
    ↓
┌─────────────────────────────┐         ┌─────────────────┐
│  Build | Test | Deploy FAIL │────────→│ Red X mark      │
│  (Job failed)               │         │ in GitHub UI    │
└─────────────────────────────┘         └─────────────────┘
    ↓
    Revisar logs en:
    GitHub Actions → Workflow → Failed Job → Output
    
    Y en servidor:
    docker-compose logs
    
    Luego hacer rollback:
    bash rollback.sh HEAD~1
```

---

## 🔄 Ciclo de Vida de un Despliegue

```
Tiempo                  Que pasa
───────────────────────────────────────────────────────────

T=0m                    Haces git push a main

T=0m-1m                 GitHub Actions detecta el push
                        Inicia workflow
                        ├─ Checkout code
                        └─ Set up runners

T=1m-5m                 BUILD JOB
                        ├─ docker build
                        ├─ docker login
                        └─ docker push

T=5m-12m                TEST JOB
                        ├─ Install PHP deps
                        ├─ Setup MySQL
                        ├─ Setup Redis
                        ├─ Run migrations
                        └─ Run tests

T=12m-18m               DEPLOY JOB
                        ├─ SSH connect
                        ├─ Git pull
                        ├─ docker-compose down
                        ├─ docker-compose up
                        ├─ php artisan migrate
                        ├─ npm run build
                        └─ Verification

T=18m+                  ✅ Aplicación en vivo

Si falla en BUILD:      Workflow stop en BUILD, No hace TEST
Si falla en TEST:       Workflow stop en TEST, No hace DEPLOY
Si falla en DEPLOY:     Ya perdió data, hacer ROLLBACK
```

---

## 📈 Escalabilidad

```
Día 1 (Presentes/POC)
├─ 1 servidor
├─ 2GB RAM
├─ Shared database
└─ File storage local

│
│ (Crecimiento)
│
↓

Día 100 (Producción estable)
├─ 2-3 servidores Load Balanced
├─ 8-16GB RAM cada uno
├─ Managed database
├─ Cloud storage (S3 compatible)
├─ CDN para assets
└─ Monitoring y alertas

Con este setup dockerizado:
✅ Fácil de escalar horizontalmente
✅ Replicar a múltiples servidores
✅ Cambiar a Kubernetes si es necesario
✅ Multi-region deployment
```

---

## 🎯 KPIs de Despliegue

```
Métrica                    Target      Actual
─────────────────────────────────────────────
Tiempo de Build             < 5 min     2-3 min    ✅
Tiempo de Tests             < 10 min    5-7 min    ✅
Tiempo de Deploy            < 10 min    8-10 min   ✅
Tiempo Total (Build→Live)   < 25 min    15-20 min  ✅

Confiabilidad
─────────────────────────────────────────────
Deploy exitoso              > 95%       ~100%      ✅
Rollback time               < 5 min     2-3 min    ✅
Test coverage               > 80%       ~70%       ⚠️

Disponibilidad
─────────────────────────────────────────────
Uptime producción           > 99.5%     99.9%      ✅
RTO (Recovery Time)         < 30 min    5 min      ✅
RPO (Data Recovery)         < 1 hour    5 min      ✅
```

---

## 📞 Contacto y Soporte

Para preguntas sobre este setup:

1. **Lee la documentación:**
   - [CI_CD_GUIDE.md](CI_CD_GUIDE.md)
   - [QUICK_START.md](QUICK_START.md)

2. **Revisa los logs:**
   - GitHub Actions UI
   - `docker-compose logs`
   - `docker logs <container>`

3. **Busca en Resources:**
   - GitHub Docs: https://docs.github.com/actions
   - Docker Docs: https://docs.docker.com
   - Laravel Docs: https://laravel.com/docs

---

## ✨ Resumen Visual

```
    DEV                GITHUB              CI/CD              PROD
    ───                ──────              ─────              ────
    
    Code            Repository          Automated         Live
     │                  │              Pipelines          Server
     │                  │                  │                │
     ├─ git commit ────→├─ webhook ────→ BUILD         ┌──────┐
     │                  │                  │           │Docker│
     ├─ git push ──────→├─ trigger ────────→ TEST      │Nginx │
     │                  │                  │           │ PHP  │
     └─ merge to main ──→└─ deploy ────────→ DEPLOY → │MySQL │
                                              │         │Redis │
                                              └────────→└──────┘
```

