Write-Host "Iniciando aplicacion con Docker..."
docker compose up --build -d
if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "Error al iniciar la aplicacion."
    Write-Host "Revisa que Docker Desktop este abierto."
    exit 1
}

Write-Host ""
Write-Host "App iniciada en: http://localhost:3000"
Write-Host ""
docker compose ps
Write-Host ""
Write-Host "Listo."
