[CmdletBinding()]
param(
    [Parameter(Mandatory = $false)]
    [string]$RepositoryRoot = 'D:\SIF',

    [Parameter(Mandatory = $false)]
    [string]$OutputDirectory = "$env:TEMP\SIF-WP-201-I1-Baseline"
)

$ErrorActionPreference = 'Stop'
$repository = (Resolve-Path -LiteralPath $RepositoryRoot).Path
$timestamp = (Get-Date).ToUniversalTime().ToString('yyyyMMddTHHmmssZ')
$staging = Join-Path $OutputDirectory "baseline-$timestamp"
$zipPath = Join-Path $OutputDirectory "SIF-WP-201-I1-Baseline-$timestamp.zip"

$paths = @(
    'composer.json',
    'composer.lock',
    'phpunit.xml',
    'phpstan.neon',
    'src/Foundation/Runtime.php',
    'src/Foundation/RuntimeState.php',
    'src/Foundation/Kernel.php',
    'src/Foundation/Application.php',
    'src/Foundation/Bootstrap.php',
    'src/Foundation/Lifecycle.php',
    'src/Foundation/BootStage.php',
    'src/Foundation/BootResult.php',
    'src/Foundation/Contracts/RuntimeInterface.php',
    'src/Foundation/Contracts/KernelInterface.php',
    'src/Foundation/Contracts/ApplicationInterface.php',
    'src/Foundation/Contracts/LifecycleInterface.php',
    'src/Foundation/Exceptions/FoundationException.php',
    'src/Foundation/Exceptions/RuntimeException.php',
    'src/Foundation/Exceptions/InvalidRuntimeTransitionException.php',
    'tests/Foundation/Unit/RuntimeTest.php',
    'tests/Foundation/Unit/KernelTest.php',
    'tests/Foundation/Integration/RuntimeLifecycleTest.php',
    'tests/Foundation/Integration/ServiceProviderLifecycleTest.php',
    'engineering/specifications/WP-003-Runtime-Foundation.md',
    'engineering/specifications/WP-201/EG-202-Runtime-Core-Model.md',
    'engineering/reviews/WP-201-Architecture-Review.md'
)

New-Item -ItemType Directory -Force -Path $staging | Out-Null
$manifest = [System.Collections.Generic.List[object]]::new()

foreach ($relative in $paths) {
    $source = Join-Path $repository ($relative -replace '/', [IO.Path]::DirectorySeparatorChar)
    if (-not (Test-Path -LiteralPath $source -PathType Leaf)) {
        $manifest.Add([pscustomobject]@{
            path = $relative
            status = 'missing'
            sha256 = $null
        })
        continue
    }

    $destination = Join-Path $staging ($relative -replace '/', [IO.Path]::DirectorySeparatorChar)
    $parent = Split-Path -Parent $destination
    New-Item -ItemType Directory -Force -Path $parent | Out-Null
    Copy-Item -LiteralPath $source -Destination $destination -Force

    $hash = (Get-FileHash -LiteralPath $source -Algorithm SHA256).Hash.ToLowerInvariant()
    $manifest.Add([pscustomobject]@{
        path = $relative
        status = 'captured'
        sha256 = $hash
    })
}

$runtimeFiles = Get-ChildItem -LiteralPath (Join-Path $repository 'src\Foundation') -File -Recurse -ErrorAction SilentlyContinue |
    Where-Object {
        $_.Name -match 'Runtime|Kernel|Lifecycle|Application|Bootstrap|State|Transition|Boot'
    }

foreach ($file in $runtimeFiles) {
    $relative = $file.FullName.Substring($repository.Length).TrimStart([IO.Path]::DirectorySeparatorChar, [IO.Path]::AltDirectorySeparatorChar) -replace [regex]::Escape([IO.Path]::DirectorySeparatorChar), '/'
    if ($manifest.path -contains $relative) { continue }

    $destination = Join-Path $staging ($relative -replace '/', [IO.Path]::DirectorySeparatorChar)
    $parent = Split-Path -Parent $destination
    New-Item -ItemType Directory -Force -Path $parent | Out-Null
    Copy-Item -LiteralPath $file.FullName -Destination $destination -Force
    $hash = (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash.ToLowerInvariant()
    $manifest.Add([pscustomobject]@{ path = $relative; status = 'captured-discovered'; sha256 = $hash })
}

$testFiles = Get-ChildItem -LiteralPath (Join-Path $repository 'tests\Foundation') -File -Recurse -ErrorAction SilentlyContinue |
    Where-Object {
        $_.Name -match 'Runtime|Kernel|Lifecycle|Application|Bootstrap|State|Transition|Boot'
    }

foreach ($file in $testFiles) {
    $relative = $file.FullName.Substring($repository.Length).TrimStart([IO.Path]::DirectorySeparatorChar, [IO.Path]::AltDirectorySeparatorChar) -replace [regex]::Escape([IO.Path]::DirectorySeparatorChar), '/'
    if ($manifest.path -contains $relative) { continue }

    $destination = Join-Path $staging ($relative -replace '/', [IO.Path]::DirectorySeparatorChar)
    $parent = Split-Path -Parent $destination
    New-Item -ItemType Directory -Force -Path $parent | Out-Null
    Copy-Item -LiteralPath $file.FullName -Destination $destination -Force
    $hash = (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash.ToLowerInvariant()
    $manifest.Add([pscustomobject]@{ path = $relative; status = 'captured-discovered'; sha256 = $hash })
}

$manifest | Sort-Object path | ConvertTo-Json -Depth 4 | Set-Content -LiteralPath (Join-Path $staging 'BASELINE-MANIFEST.json') -Encoding UTF8

$gitStatus = & git -C $repository status --short 2>&1
$gitStatus | Set-Content -LiteralPath (Join-Path $staging 'GIT-STATUS.txt') -Encoding UTF8

$gitHead = & git -C $repository rev-parse HEAD 2>&1
$gitHead | Set-Content -LiteralPath (Join-Path $staging 'GIT-HEAD.txt') -Encoding UTF8

if (Test-Path -LiteralPath $zipPath) { Remove-Item -LiteralPath $zipPath -Force }
Compress-Archive -Path (Join-Path $staging '*') -DestinationPath $zipPath -CompressionLevel Optimal
$zipHash = (Get-FileHash -LiteralPath $zipPath -Algorithm SHA256).Hash.ToLowerInvariant()

Write-Host ''
Write-Host 'WP-201-I1 source baseline captured.' -ForegroundColor Green
Write-Host "Archive: $zipPath"
Write-Host "SHA-256: $zipHash"
Write-Host "Captured: $(($manifest | Where-Object status -like 'captured*').Count)"
Write-Host "Missing: $(($manifest | Where-Object status -eq 'missing').Count)"
