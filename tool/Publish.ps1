using module ./Cmdlets.psm1

"Publishing the package..."
$version = (Get-Content composer.json | ConvertFrom-Json).version
New-GitTag "v$version"
