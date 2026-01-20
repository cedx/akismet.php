"Publishing the package..."
$version = Get-Content "composer.json" | ConvertFrom-Json | Select-Object -ExpandProperty version
git tag "v$version"
git push origin "v$version"
