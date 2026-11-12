param(
    [Parameter(Mandatory = $true)]
    [string]$Destination
)

$ErrorActionPreference = 'Stop'
$source = Split-Path -Parent $PSScriptRoot
$destinationPath = $ExecutionContext.SessionState.Path.GetUnresolvedProviderPathFromPSPath($Destination)
if (Test-Path -LiteralPath $destinationPath) {
    throw "Destination already exists: $destinationPath"
}
if (-not (Test-Path -LiteralPath (Join-Path $source 'vendor/autoload.php'))) {
    throw 'Install production Composer dependencies before building the plugin.'
}

Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem
$parent = Split-Path -Parent $destinationPath
[System.IO.Directory]::CreateDirectory($parent) | Out-Null
$stream = [System.IO.File]::Open($destinationPath, [System.IO.FileMode]::CreateNew)
$archive = [System.IO.Compression.ZipArchive]::new($stream, [System.IO.Compression.ZipArchiveMode]::Create)
try {
    $files = @('simple-customizable-faq-accordion.php', 'index.php', 'readme.txt', 'README.md', 'CHANGELOG.md', 'LICENSE')
    foreach ($directory in @('admin', 'public', 'includes', 'vendor')) {
        $files += @(Get-ChildItem -LiteralPath (Join-Path $source $directory) -File -Recurse | ForEach-Object {
            $_.FullName.Substring($source.Length + 1)
        })
    }
    foreach ($relative in $files) {
        $entry = 'simple-customizable-faq-accordion/' + $relative.Replace('\', '/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, (Join-Path $source $relative), $entry, [System.IO.Compression.CompressionLevel]::Optimal) | Out-Null
    }
} finally {
    $archive.Dispose()
    $stream.Dispose()
}
Get-Item -LiteralPath $destinationPath | Select-Object FullName, Length
