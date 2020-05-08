#!/usr/bin/env pwsh
Set-StrictMode -Version Latest
Set-Location (Split-Path $PSScriptRoot)

tool/build.ps1
tool/version.ps1

$version = (Get-Content composer.json | ConvertFrom-Json).version
git tag "v$version"
git push origin "v$version"
