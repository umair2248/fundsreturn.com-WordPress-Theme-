$ErrorActionPreference = 'Stop'
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$themePath = Join-Path $PSScriptRoot 'confidential-fund-recovery'
$zipPath = Join-Path $PSScriptRoot 'confidential-fund-recovery-theme.zip'
$requiredFiles = @('style.css', 'index.php', 'functions.php', 'front-page.php', 'inc/starter-content.json')
foreach ($relativePath in $requiredFiles) {
    if (-not (Test-Path -LiteralPath (Join-Path $themePath $relativePath) -PathType Leaf)) { throw "Required theme file missing: $relativePath" }
}
$stylesheet = Get-Content -LiteralPath (Join-Path $themePath 'style.css') -Raw -Encoding UTF8
if ($stylesheet -notmatch 'Theme Name:\s*Confidential Fund Recovery') { throw 'Theme Name header missing from style.css.' }

# Package only the theme, with a single root and portable forward-slash entry paths.
# Create mode deliberately refuses to overwrite an existing archive.
$zip = [System.IO.Compression.ZipFile]::Open($zipPath, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    $themeFiles = @(Get-ChildItem -LiteralPath $themePath -Recurse -File)
    foreach ($themeFile in $themeFiles) {
        $relativePath = $themeFile.FullName.Substring($themePath.Length + 1).Replace('\', '/')
        $null = [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $themeFile.FullName, ('confidential-fund-recovery/' + $relativePath), [System.IO.Compression.CompressionLevel]::Optimal)
    }
} finally { $zip.Dispose() }

# Verify the final ZIP structure and every file's SHA256 against the source.
$zip = [System.IO.Compression.ZipFile]::OpenRead($zipPath)
try {
    if ($zip.Entries.Count -ne $themeFiles.Count) { throw 'ZIP entry count mismatch.' }
    foreach ($relativePath in $requiredFiles) {
        if (-not $zip.GetEntry('confidential-fund-recovery/' + $relativePath)) { throw "Incorrect ZIP layout: $relativePath" }
    }
    foreach ($entry in $zip.Entries) {
        if (-not $entry.FullName.StartsWith('confidential-fund-recovery/') -or $entry.FullName.Contains('\') -or $entry.FullName.Contains('..')) { throw "Invalid ZIP entry: $($entry.FullName)" }
        $localPath = Join-Path $PSScriptRoot $entry.FullName.Replace('/', [System.IO.Path]::DirectorySeparatorChar)
        $stream = $entry.Open()
        $sha = [System.Security.Cryptography.SHA256]::Create()
        try { $archiveHash = [BitConverter]::ToString($sha.ComputeHash($stream)).Replace('-', '') }
        finally { $stream.Dispose(); $sha.Dispose() }
        if ($archiveHash -ne (Get-FileHash -LiteralPath $localPath -Algorithm SHA256).Hash) { throw "ZIP content mismatch: $($entry.FullName)" }
    }
    Write-Output "Verified $($zip.Entries.Count) theme files, correct WordPress folder structure and matching SHA256 hashes."
} finally { $zip.Dispose() }
Write-Output "Upload this file: $zipPath"
