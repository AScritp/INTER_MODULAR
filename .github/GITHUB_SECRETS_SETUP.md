# Configuración de Secretos y Variables en GitHub

## 📋 Tabla de Secretos Requeridos

Los secretos deben configurarse en **Settings → Secrets and variables → Actions**

### Secretos (Valores sensibles - NUNCA committer al repositorio)

| Secreto | Descripción | Ejemplo | Cómo obtenerlo |
|---------|-------------|---------|-----------------|
| `SSH_PRIVATE_KEY` | Clave privada SSH para acceder al servidor | `-----BEGIN OPENSSH PRIVATE KEY-----...` | `cat ~/.ssh/github_deploy` en el servidor |
| `SSH_HOST` | IP o dominio del servidor | `203.0.113.45` o `deploy.ejemplo.com` | Tu proveedor de hosting |
| `SSH_USER` | Usuario SSH en el servidor | `deployer` o `ubuntu` | Configura en el servidor |
| `SSH_PORT` | Puerto SSH (default: 22) | `22` | Verifica con tu proveedor |
| `DEPLOY_PATH` | Ruta donde desplegar la aplicación | `/home/deployer/apps/inter-modular` | Ruta en el servidor |
| `APP_KEY` | Clave de encriptación de Laravel | `base64:abc123...` | Ver sección "Generar APP_KEY" |
| `DB_PASSWORD` | Contraseña MySQL | `MySecurePassword123!` | Contraseña segura (mín. 12 caracteres) |
| `REDIS_PASSWORD` | Contraseña Redis (opcional) | `RedisPass123!` | Contraseña segura |
| `APP_ENV` | Ambiente de ejecución | `production` | production, staging, etc. |
| `APP_DEBUG` | Modo debug (SIEMPRE false en prod) | `false` | false para producción |

---

## 🔑 Pasos para Configurar Secretos

### 1. Generar APP_KEY

En tu máquina local:

```bash
cd src
php artisan key:generate --show
```

Output: `base64:abc123def456...`

Copia este valor completo en el secreto `APP_KEY`.

### 2. Generar claves SSH

**En el servidor:**

```bash
# Generar clave sin contraseña
ssh-keygen -t ed25519 -f ~/.ssh/github_deploy -N "" -C "github-actions"

# Ver clave privada (para el secreto SSH_PRIVATE_KEY)
cat ~/.ssh/github_deploy

# Ver clave pública (para authorized_keys)
cat ~/.ssh/github_deploy.pub
```

**Autorizar la clave:**

```bash
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### 3. Agregar secretos en GitHub

1. Abre tu repositorio en GitHub
2. Ve a **Settings**
3. Selecciona **Secrets and variables** → **Actions**
4. Haz clic en **New repository secret**
5. Para cada secreto:
   - **Name:** Nombre exacto de la tabla de arriba
   - **Value:** El valor correspondiente
6. Haz clic en **Add secret**

### 4. Variables (valores no sensibles)

En **Settings → Secrets and variables → Variables**:

| Variable | Valor | Descripción |
|----------|-------|-------------|
| `DOCKER_REGISTRY` | `ghcr.io` | GitHub Container Registry |
| `IMAGE_NAME` | `tu-usuario/inter-modular` | Nombre de la imagen Docker |

---

## 🔐 Seguridad

### ✅ Buenas prácticas:

- **Usa contraseñas fuertes:**
  ```bash
  openssl rand -base64 32  # Genera contraseña segura
  ```

- **Usa claves ED25519** (más seguras que RSA)
  ```bash
  ssh-keygen -t ed25519 -f ~/.ssh/github_deploy -N ""
  ```

- **Nunca compartas secretos** en:
  - Mensajes de chat
  - Email
  - Archivos públicos
  - Código fuente

- **Rota secretos regularmente**
  - Genera nuevas claves SSH cada 6-12 meses
  - Actualiza contraseñas cada 90 días

### ❌ Nunca hagas:

- Pushear `.env` al repositorio
- Usar contraseñas débiles
- Reutilizar contraseñas entre servicios
- Commitear claves privadas
- Dejar secretos en logs o outputs

---

## 🛠️ Troubleshooting

### "Permission denied (publickey)"

**Causa:** La clave pública no está configurada correctamente

**Solución:**
```bash
# En el servidor
ssh-copy-id -i ~/.ssh/github_deploy.pub deployer@localhost

# O manual:
cat ~/.ssh/github_deploy.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### "Unable to authenticate with private key"

**Causa:** Clave privada mal copiada o con saltos de línea extra

**Solución:**
```bash
# Ver la clave (debe empezar y terminar con delimitadores)
cat ~/.ssh/github_deploy

# Copiar exactamente el CONTENIDO COMPLETO
# Incluyendo: -----BEGIN OPENSSH PRIVATE KEY-----
#             [contenido]
#             -----END OPENSSH PRIVATE KEY-----
```

### "Secret SSH_PRIVATE_KEY is missing or empty"

**Solución:** Verifica que:
1. El secreto está creado en GitHub
2. El nombre es exacto: `SSH_PRIVATE_KEY` (mayúsculas)
3. El contenido no está vacío

---

## 🔄 Actualizar un Secreto

1. Ve a **Settings → Secrets**
2. Busca el secreto a actualizar
3. Haz clic en el ícono de editar (lápiz)
4. Reemplaza el valor
5. Haz clic en **Update secret**

---

## 📝 Checklist antes de desplegar

- [ ] APP_KEY configurado (obtén con `php artisan key:generate --show`)
- [ ] SSH_PRIVATE_KEY agregado (contenido completo con delimitadores)
- [ ] SSH_HOST, SSH_USER, SSH_PORT configurados
- [ ] DEPLOY_PATH existe y es accesible por el usuario SSH
- [ ] DB_PASSWORD y REDIS_PASSWORD son contraseñas fuertes
- [ ] APP_DEBUG = false en producción
- [ ] APP_ENV = production
- [ ] Clave pública SSH agregada en `~/.ssh/authorized_keys` del servidor

---

## 🚀 Próximos pasos

1. ✅ Configura todos los secretos en GitHub
2. ✅ Prepara el servidor con `server-setup.sh`
3. ✅ Haz push a `main` para disparar el workflow
4. ✅ Monitorea en **Actions** en GitHub
5. ✅ Verifica que los contenedores estén corriendo en el servidor

