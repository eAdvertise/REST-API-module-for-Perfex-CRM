param(
    [switch]$CustomOnly,
    [switch]$Crm
)

$ErrorActionPreference = "Stop"
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $Root

python -m venv .venv
& .\.venv\Scripts\python.exe -m pip install --upgrade pip
& .\.venv\Scripts\python.exe -m pip install -r requirements.txt

if ($Crm) {
    if (Test-Path .env) {
        Get-Content .env | ForEach-Object {
            if ($_ -match '^\s*([^#][^=]*)=(.*)$') {
                [Environment]::SetEnvironmentVariable($matches[1].Trim(), $matches[2].Trim(), 'Process')
            }
        }
    }
    if (-not $env:PERFEX_BASE_URL -or -not $env:PERFEX_API_TOKEN) {
        throw "Set PERFEX_BASE_URL and PERFEX_API_TOKEN in the environment or .env"
    }
    & .\.venv\Scripts\python.exe scripts\bootstrap.py --source crm --crm-url $env:PERFEX_BASE_URL --force
} elseif (-not $CustomOnly) {
    & .\.venv\Scripts\python.exe scripts\bootstrap.py --source github
}

& .\.venv\Scripts\python.exe scripts\merge_openapi.py
& .\.venv\Scripts\python.exe scripts\validate_openapi.py openapi\combined.openapi.json
& .\.venv\Scripts\python.exe scripts\build_site.py
Write-Host "Build complete: $Root\site"
Write-Host "Preview: .\.venv\Scripts\python.exe -m http.server 8080 --directory site"
