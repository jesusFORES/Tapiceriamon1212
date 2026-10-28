# Script para levantar servicios Docker y ejecutar el seeder
# Úsalo desde PowerShell en el proyecto: .\scripts\up.ps1

# Comprobar docker
if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Error "Docker no está instalado o no está en PATH. Instala Docker Desktop para Windows e inténtalo de nuevo."
    exit 1
}

# Ir al directorio raíz del proyecto (asumiendo que el script está en scripts/)
$scriptDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
Set-Location $scriptDir
Set-Location ..

Write-Host "Ruta del proyecto: $(Get-Location)"

Write-Host "Construyendo y levantando los servicios..."
docker compose up --build -d
if ($LASTEXITCODE -ne 0) {
    Write-Error "docker compose falló. Revisa los logs con: docker compose logs -f"
    exit 1
}

Write-Host "Esperando a que el servicio 'db' esté healthy..."
$cid = docker compose ps -q db
if (-not $cid) {
    Write-Warning "No se encontró el servicio 'db' (container id vacío). Verifica 'docker compose ps'."
}
else {
    $max = 60
    for ($i=0; $i -lt $max; $i++) {
        $status = docker inspect --format='{{.State.Health.Status}}' $cid 2>$null
        if ($status -eq 'healthy') {
            Write-Host "DB está healthy"
            break
        }
        Write-Host "Esperando DB... intentos: $($i+1)/$max"
        Start-Sleep -Seconds 2
    }
}

Write-Host "Ejecutando el seeder desde el contenedor web (si existe)..."
try {
    docker compose exec web php /var/www/html/sql/seeds.php
} catch {
    Write-Warning "No se pudo ejecutar el seeder con 'docker compose exec'. Intenta: docker compose logs -f o ejecuta el seeder manualmente."
}

Write-Host "Comprobando servicios activos..."
docker compose ps

Write-Host "Hecho. Abre http://localhost:8080 en tu navegador."
