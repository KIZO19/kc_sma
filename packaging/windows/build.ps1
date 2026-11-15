$ErrorActionPreference = 'Stop'

$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$sourcePath = Join-Path $PSScriptRoot 'launcher.cs'
$outputDirectory = Join-Path $projectRoot 'dist'
$outputPath = Join-Path $outputDirectory 'KC-SMA-Lanceur.exe'

$frameworkDirectory = if ([Environment]::Is64BitOperatingSystem) {
    Join-Path $env:WINDIR 'Microsoft.NET\Framework64\v4.0.30319'
}
else {
    Join-Path $env:WINDIR 'Microsoft.NET\Framework\v4.0.30319'
}
$compiler = Join-Path $frameworkDirectory 'csc.exe'

if (-not (Test-Path -LiteralPath $compiler)) {
    throw ".NET Framework C# compiler is unavailable: $compiler"
}

New-Item -ItemType Directory -Path $outputDirectory -Force | Out-Null
& $compiler /nologo /target:winexe "/out:$outputPath" /reference:System.Windows.Forms.dll /reference:System.Drawing.dll $sourcePath
if ($LASTEXITCODE -ne 0) {
    throw "C# compilation failed with exit code $LASTEXITCODE."
}
if (-not (Test-Path -LiteralPath $outputPath)) {
    throw "The expected executable was not generated: $outputPath"
}

Write-Output "Launcher generated: $outputPath"
