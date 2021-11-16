#!/usr/bin/env pwsh
Set-StrictMode -Version Latest
Set-Location (Split-Path $PSScriptRoot)

if (Test-Path docs) { Remove-Item docs -Force -Recurse }
phpdoc --config=etc/phpdoc.xml

if (-not (Test-Path docs/images)) { New-Item docs/images -ItemType Directory | Out-Null }
Copy-Item www/favicon.ico docs/images
