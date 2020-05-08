#!/usr/bin/env pwsh
Set-StrictMode -Version Latest
Set-Location (Split-Path $PSScriptRoot)

$version = (Get-Content composer.json | ConvertFrom-Json).version
$lines = @(
  '<?php declare(strict_types=1);', '',
  '// The version number of the package.',
  "return `$packageVersion = '$version';"
)

$eol = $IsWindows ? "`r`n" : "`n"
Set-Content src/version.g.php ($lines -join $eol)
