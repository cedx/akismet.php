"Publishing the package..."
$version = (Get-Content "composer.json" | ConvertFrom-Json).version
git tag "v$version"
git push origin "v$version"
