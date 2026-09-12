<#
.SYNOPSIS
    Build a WordPress-ready zip of the custom-woocommerce-tweaks plugin.

.DESCRIPTION
    Stages the plugin source into a clean temporary folder (excluding dev
    tooling, AI agent scratch files, and VCS metadata), then writes a zip
    whose top-level entry is <PluginSlug>/. Output paths inside the zip
    are always relative to the plugin folder so WordPress can unpack it
    directly into wp-content/plugins/.

.PARAMETER PluginSlug
    Folder name used as the top-level entry inside the zip. Must match the
    main plugin file basename. Defaults to 'custom-woocommerce-tweaks'.

.PARAMETER OutputDir
    Directory to write the zip into. Defaults to the script's own directory.

.EXAMPLE
    .\build-zip.ps1
    # Builds custom-woocommerce-tweaks.zip next to this script.
#>

[CmdletBinding()]
param(
    [string]$PluginSlug = 'custom-woocommerce-tweaks',
    [string]$OutputDir
)

# Resolve script directory reliably from $PSCommandPath (always set when
# invoked via -File). Fall back to $PSScriptRoot, then to the current dir
# if both are empty (e.g. when copy-pasted into an interactive session).
if (-not $OutputDir) {
    if ($PSCommandPath) {
        $OutputDir = Split-Path -Parent $PSCommandPath
    } elseif ($PSScriptRoot) {
        $OutputDir = $PSScriptRoot
    } else {
        $OutputDir = (Get-Location).Path
    }
}

$ErrorActionPreference = 'Stop'

# Capture this script's filename so we never zip ourselves up.
$ScriptName = if ($PSCommandPath) { Split-Path -Leaf $PSCommandPath } else { 'build-zip.ps1' }

# Anything in here is treated as dev/agent scratch and must NOT ship.
$ExcludeDirs = @(
    '.git', '.vscode', '.cursor', '.brainsync',
    '.agent', '.agent-mem', '.agents', '.claude', 'working'
)
$ExcludeFiles = @(
    'CLAUDE.md', 'AGENT.md', 'AGENTS.md',
    '.gitignore', '.gitattributes', '.windsurfrules',
    '.mcp.json', '.mcp.json.bak',
    'MEMORY.md', 'GEMINI.md',
    "$PluginSlug.zip",
    $ScriptName
)

$OutputZip = Join-Path $OutputDir "$PluginSlug.zip"
$TempRoot  = Join-Path ([System.IO.Path]::GetTempPath()) "cwt-build-$([guid]::NewGuid().ToString('N'))"
$Staging   = Join-Path $TempRoot $PluginSlug

try {
    # 1. Stage a clean copy of the plugin source.
    #    All paths copied into $Staging are RELATIVE -- no absolute Windows
    #    paths leak into the resulting zip.
    [void](New-Item -ItemType Directory -Path $Staging -Force)

    Get-ChildItem -LiteralPath $OutputDir -Force |
        Where-Object {
            if ($_.PSIsContainer) { return $ExcludeDirs -notcontains $_.Name }
            return $ExcludeFiles -notcontains $_.Name
        } |
        ForEach-Object {
            Copy-Item -LiteralPath $_.FullName -Destination $Staging -Recurse -Force
        }

    # 2. Sanity check -- refuse to ship a zip without the plugin header.
    $MainFile = Join-Path $Staging "$PluginSlug.php"
    if (-not (Test-Path -LiteralPath $MainFile)) {
        throw "Staged plugin folder is missing $PluginSlug.php -- refusing to zip."
    }

    # 3. Build the zip. includeBaseDirectory:$true wraps the staged contents
    #    in a top-level folder named after the staging directory's last
    #    component -- which is $PluginSlug. This is the layout WordPress
    #    expects when uploading a plugin zip.
    if (Test-Path -LiteralPath $OutputZip) {
        Remove-Item -LiteralPath $OutputZip -Force
    }
    Add-Type -AssemblyName System.IO.Compression.FileSystem
    [System.IO.Compression.ZipFile]::CreateFromDirectory(
        $Staging,
        $OutputZip,
        [System.IO.Compression.CompressionLevel]::Optimal,
        $true
    )

    $sizeKb = [math]::Round((Get-Item -LiteralPath $OutputZip).Length / 1KB, 1)
    Write-Host "Built: $OutputZip ($sizeKb KB)" -ForegroundColor Green
}
finally {
    if (Test-Path -LiteralPath $TempRoot) {
        Remove-Item -LiteralPath $TempRoot -Recurse -Force -ErrorAction SilentlyContinue
    }
}