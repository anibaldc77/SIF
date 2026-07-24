[CmdletBinding()]
param(
    [Parameter()]
    [string] $RepositoryRoot = (Get-Location).Path,

    [Parameter()]
    [string] $CsvPath
)

Set-StrictMode -Version Latest
$ErrorActionPreference = 'Stop'

if ($PSVersionTable.PSVersion.Major -lt 5) {
    throw 'PowerShell 5.1 or later is required.'
}

Write-Verbose ('PowerShell {0}' -f $PSVersionTable.PSVersion)

$excludedSegments = @(
    '.git', '.idea', '.vscode', 'node_modules', 'vendor', 'build', 'dist',
    'coverage', '.cache', '.phpunit.cache', '.phpstan.cache', '.generated',
    'generated', 'tmp', 'temp'
)

$requiredFields = @(
    'id', 'title', 'status', 'version', 'category', 'authors', 'created',
    'updated', 'tags', 'depends_on', 'related_adrs'
)


function Get-CompatibleRelativePath {
    param(
        [Parameter(Mandatory)][string] $BasePath,
        [Parameter(Mandatory)][string] $TargetPath
    )

    $resolvedBase = (Resolve-Path -LiteralPath $BasePath).Path
    $resolvedTarget = (Resolve-Path -LiteralPath $TargetPath).Path
    $separator = [System.IO.Path]::DirectorySeparatorChar
    $baseWithSeparator = $resolvedBase.TrimEnd('\', '/') + $separator

    $baseUri = New-Object System.Uri($baseWithSeparator)
    $targetUri = New-Object System.Uri($resolvedTarget)
    $relativeUri = $baseUri.MakeRelativeUri($targetUri)

    return [System.Uri]::UnescapeDataString($relativeUri.ToString()).Replace('\', '/')
}

function Test-IsExcludedPath {
    param([Parameter(Mandatory)][string] $RelativePath)

    $segments = ($RelativePath -replace '\\', '/') -split '/'
    foreach ($segment in $segments) {
        if ($excludedSegments -contains $segment.ToLowerInvariant()) {
            return $true
        }
    }

    return $false
}

function Get-FrontMatterClassification {
    param([Parameter(Mandatory)][string] $Path)

    $lines = @(Get-Content -LiteralPath $Path -Encoding UTF8)
    if ($lines.Count -eq 0 -or $lines[0] -ne '---') {
        return [pscustomobject]@{ Classification = 'missing_front_matter'; MissingFields = $requiredFields -join ',' }
    }

    $closing = -1
    for ($i = 1; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -eq '---') {
            $closing = $i
            break
        }
    }

    if ($closing -lt 1) {
        return [pscustomobject]@{ Classification = 'invalid_front_matter'; MissingFields = '' }
    }

    $keys = @{}
    for ($i = 1; $i -lt $closing; $i++) {
        if ($lines[$i] -match '^([a-z][a-z0-9_]*)\s*:') {
            $keys[$Matches[1]] = $true
        }
    }

    $missing = @($requiredFields | Where-Object { -not $keys.ContainsKey($_) })
    if ($missing.Count -gt 0) {
        return [pscustomobject]@{ Classification = 'incomplete_front_matter'; MissingFields = $missing -join ',' }
    }

    return [pscustomobject]@{ Classification = 'compliant'; MissingFields = '' }
}

try {
    $root = (Resolve-Path -LiteralPath $RepositoryRoot).Path
    $files = Get-ChildItem -LiteralPath $root -Recurse -File -Filter '*.md' |
        ForEach-Object {
            $relative = Get-CompatibleRelativePath -BasePath $root -TargetPath $_.FullName
            if (-not (Test-IsExcludedPath -RelativePath $relative)) {
                [pscustomobject]@{ File = $_; RelativePath = $relative }
            }
        } |
        Sort-Object RelativePath

    $results = foreach ($entry in $files) {
        $classification = Get-FrontMatterClassification -Path $entry.File.FullName
        [pscustomobject]@{
            Path = $entry.RelativePath
            Classification = $classification.Classification
            MissingFields = $classification.MissingFields
        }
    }

    $results | Format-Table -AutoSize

    Write-Host ''
    Write-Host 'Summary'
    $results | Group-Object Classification | Sort-Object Name | ForEach-Object {
        Write-Host ('- {0}: {1}' -f $_.Name, $_.Count)
    }
    Write-Host ('- total: {0}' -f @($results).Count)

    if ($CsvPath) {
        $target = if ([System.IO.Path]::IsPathRooted($CsvPath)) {
            $CsvPath
        } else {
            Join-Path $root $CsvPath
        }

        $directory = Split-Path -Parent $target
        if ($directory -and -not (Test-Path -LiteralPath $directory)) {
            New-Item -ItemType Directory -Path $directory -Force | Out-Null
        }

        $results | Export-Csv -LiteralPath $target -NoTypeInformation -Encoding UTF8
        Write-Host ('CSV: {0}' -f $target)
    }

    exit 0
} catch {
    Write-Error $_
    exit 1
}
