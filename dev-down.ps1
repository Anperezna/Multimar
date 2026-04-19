Write-Host "Deteniendo aplicacion..."
docker compose down
if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "No se pudo detener la aplicacion correctamente."
    exit 1
}

Write-Host ""
Write-Host "Aplicacion detenida."
