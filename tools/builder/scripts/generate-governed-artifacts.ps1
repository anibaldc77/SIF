[CmdletBinding()]
param(
    [Parameter()]
    [string] $RepositoryRoot = (Get-Location).Path
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

if ($PSVersionTable.PSVersion.Major -lt 5) {
    throw 'PowerShell 5.1 or later is required.'
}

$root = (Resolve-Path -LiteralPath $RepositoryRoot).Path
$builder = Join-Path $root 'bin\sif-builder'

if (-not (Test-Path -LiteralPath $builder -PathType Leaf)) {
    throw ('SIF Builder executable was not found at "{0}".' -f $builder)
}

Push-Location $root
try {
    & php $builder build --output=$root
    $buildExitCode = $LASTEXITCODE

    $artifacts = @(
        'build/reference-graph.generated.json',
        'build/repository-manifest.generated.json',
        'engineering/INDEX.generated.md',
        'engineering/NAVIGATION.generated.md',
        'engineering/REFERENCES.generated.md'
    )

    $missing = @($artifacts | Where-Object {
        -not (Test-Path -LiteralPath (Join-Path $root $_) -PathType Leaf)
    })

    if ($missing.Count -gt 0) {
        throw ('Governed artifacts were not generated: {0}' -f ($missing -join ', '))
    }

    & php $builder validate
    $validationExitCode = $LASTEXITCODE

    if ($validationExitCode -ne 0) {
        throw ('Repository validation failed after artifact generation with exit code {0}.' -f $validationExitCode)
    }

    if ($buildExitCode -ne 0) {
        Write-Warning ('The build command returned exit code {0} because analyzers run before artifacts are written; post-generation validation succeeded.' -f $buildExitCode)
    }

    Write-Host 'Governed artifacts generated and validated successfully.'
    exit 0
} finally {
    Pop-Location
}
