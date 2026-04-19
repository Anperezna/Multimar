# Guia rapida de uso de Multimar

Este documento explica solo el uso practico del proyecto: como arrancar, parar, desplegar y comprobar que todo funciona.

## 1. Arranque local

### Windows (PowerShell)

Ejecuta:

```powershell
.\dev-up.ps1
```

Abre en navegador:

```text
http://localhost:3000
```

Para parar:

```powershell
.\dev-down.ps1
```

### Linux/Mac (shell)

Ejecuta:

```bash
sh dev-up.sh
```

Para parar:

```bash
sh dev-down.sh
```

## 2. Despliegue automatico y publicacion Docker

El flujo principal esta en:

`/.github/workflows/build-docker.yml`

Que hace:

- Al haber cambios en `master`, construye imagen Docker.
- Publica la imagen en Docker Hub como:
    - `anperezna/multimar-app:latest`
    - `anperezna/multimar-app:<sha>`

## 3. Comprobar que la imagen esta publicada

Ejecuta:

```bash
docker pull anperezna/multimar-app:latest
```

Si descarga sin error, la imagen esta correctamente en Docker Hub.

## 4. Flujo personalizado (correo)

El flujo esta en:

`/.github/workflows/custom-flow.yml`

Que hace:

- Envia correo al equipo cuando hay cambios en `master` (push) o cuando se fusiona un PR a `master`.

## 5. Secrets necesarios en GitHub

### Para Docker Hub

- `DOCKERHUB_USERNAME`
- `DOCKERHUB_TOKEN`

### Para correo SMTP

- `SMTP_SERVER`
- `SMTP_PORT`
- `SMTP_USERNAME`
- `SMTP_PASSWORD`

### Opcionales para correo

- `TEAM_EMAILS`
- `MAIL_FROM`

## 6. Prueba completa recomendada

1. Haz un cambio pequeño y subelo a `master`.
2. Revisa `Actions` en GitHub:
    - workflow Docker en verde.
    - workflow de correo en verde.
3. Comprueba Docker Hub con `docker pull`.
4. Comprueba llegada de correo en bandeja/spam.

## 7. Referencia de archivos clave

- `Dockerfile`
- `docker-compose.yml`
- `docker/nginx/monolith.conf`
- `docker/supervisor/supervisord.conf`
- `.github/workflows/build-docker.yml`
- `.github/workflows/custom-flow.yml`
- `dev-up.ps1`
- `dev-down.ps1`
- `dev-up.sh`
- `dev-down.sh`
