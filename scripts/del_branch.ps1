# Script de PowerShell para eliminar una rama (branch) de Git de forma local y remota
# Uso: .\delbranch.ps1 -BranchName "nombre-rama" [-Remote "origin"]

param(
    [Parameter(Mandatory=$true, HelpMessage="Nombre de la rama a eliminar")]
    [string]$BranchName,
    
    [Parameter(Mandatory=$false, HelpMessage="Nombre del repositorio remoto (por defecto: origin)")]
    [string]$Remote = "origin",
    
    [Parameter(Mandatory=$false, HelpMessage="Fuerza la eliminación sin preguntar confirmación")]
    [switch]$Force
)

# Colores para la salida
$colors = @{
    Success = 'Green'
    Error   = 'Red'
    Warning = 'Yellow'
    Info    = 'Cyan'
}

function Write-ColorOutput {
    param(
        [string]$Message,
        [string]$Color = 'White'
    )
    Write-Host $Message -ForegroundColor $Color
}

# Verificar que estamos en un repositorio Git
try {
    $gitStatus = git rev-parse --git-dir 2>$null
    if (-not $gitStatus) {
        throw "No se encontró un repositorio Git en el directorio actual"
    }
}
catch {
    Write-ColorOutput "Error: No estamos en un repositorio Git valido" $colors.Error
    exit 1
}

# Obtener la rama actual
$currentBranch = git rev-parse --abbrev-ref HEAD

# Validar que no estamos intentando eliminar la rama actual
if ($currentBranch -eq $BranchName) {
    Write-ColorOutput "Error: No puedes eliminar la rama actual ($BranchName)" $colors.Error
    Write-ColorOutput "   Cambia a otra rama primero con: git checkout [otra-rama]" $colors.Info
    exit 1
}

# Confirmar la acción si no se usa -Force
if (-not $Force) {
    Write-ColorOutput "`nAdvertencia: Vas a eliminar la rama '$BranchName'" $colors.Warning
    Write-ColorOutput "   - Local: Si" $colors.Info
    Write-ColorOutput "   - Remoto ($Remote): Si" $colors.Info
    $confirmation = Read-Host "`nDeseas continuar? (s/n)"
    
    if ($confirmation -ne 's' -and $confirmation -ne 'S' -and $confirmation -ne 'yes' -and $confirmation -ne 'Yes') {
        Write-ColorOutput "Operacion cancelada" $colors.Warning
        exit 0
    }
}

Write-ColorOutput "`nProcesando eliminacion de rama '$BranchName'..." $colors.Info

# Paso 1: Eliminar la rama de forma local
Write-ColorOutput "`nEliminando rama local..." $colors.Info
try {
    git branch -d $BranchName 2>$null
    if ($LASTEXITCODE -ne 0) {
        # Si falla con -d, intentar con -D (force)
        git branch -D $BranchName 2>$null
        if ($LASTEXITCODE -eq 0) {
            Write-ColorOutput "Rama local eliminada (con force)" $colors.Success
        }
        else {
            throw "No se pudo eliminar la rama local"
        }
    }
    else {
        Write-ColorOutput "Rama local eliminada" $colors.Success
    }
}
catch {
    Write-ColorOutput "Error al eliminar la rama local: $_" $colors.Error
    exit 1
}

# Paso 2: Eliminar la rama del repositorio remoto
Write-ColorOutput "`nEliminando rama remota ($Remote)..." $colors.Info
try {
    git push $Remote --delete $BranchName 2>$null
    if ($LASTEXITCODE -eq 0) {
        Write-ColorOutput "Rama remota eliminada de $Remote" $colors.Success
    }
    else {
        # Verificar si la rama existe en remoto
        $remoteBranches = git branch -r | Select-String "$Remote/$BranchName"
        if ($remoteBranches) {
            throw "Error al eliminar la rama remota"
        }
        else {
            Write-ColorOutput "La rama remota ya no existe en $Remote" $colors.Warning
        }
    }
}
catch {
    Write-ColorOutput "Error al eliminar la rama remota: $_" $colors.Error
    exit 1
}

# Resumen final
Write-ColorOutput "`n"
Write-ColorOutput "========================================" $colors.Success
Write-ColorOutput "Rama eliminada correctamente" $colors.Success
Write-ColorOutput "========================================" $colors.Success
Write-ColorOutput "`nResumen:" $colors.Info
Write-ColorOutput "   - Rama: $BranchName" $colors.Info
Write-ColorOutput "   - Local: Eliminada" $colors.Success
Write-ColorOutput "   - Remoto ($Remote): Eliminada" $colors.Success
Write-ColorOutput "`n"

exit 0