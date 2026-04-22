<#
.SYNOPSIS
	Installs the specified Composer package, if any. Otherwise, installs all packages.
#>
function Install-ComposerPackage {
	param (
		# The package to install.
		[Parameter(Position = 0)]
		[string] $Package
	)

	$argumentList = , ($Package ? "require" : "install")
	if ($Package) { $argumentList += $Package }
	composer @argumentList
}

<#
.SYNOPSIS
	Invokes the PHPStan static analyzer.
#>
function Invoke-PhpStan {
	param (
		# The path to the configuration file.
		[Parameter(Position = 0)]
		[ValidateScript({ Test-Path $_ -PathType Leaf }, ErrorMessage = "The specified configuration file does not exist.")]
		[string] $Configuration,

		# The maximum memory allocated to the analysis.
		[ValidateRange("Positive")]
		[int] $MemoryLimit
	)

	$argumentList = "phpstan", "analyse", "--verbose"
	if ($Configuration) { $argumentList += "--configuration=$Configuration" }
	if ($MemoryLimit) { $argumentList += "--memory-limit=$MemoryLimit" }
	composer exec "--" @argumentList
}

<#
.SYNOPSIS
	Invokes the PHPUnit test runner.
#>
function Invoke-PhpUnit {
	param (
		# The path to the configuration file.
		[Parameter(Position = 0)]
		[ValidateScript({ Test-Path $_ -PathType Leaf }, ErrorMessage = "The specified configuration file does not exist.")]
		[string] $Configuration
	)

	$argumentList = , "phpunit"
	if ($Configuration) { $argumentList += "--configuration=$Configuration" }
	composer exec "--" @argumentList
}

<#
.SYNOPSIS
	Creates a new Git tag.
#>
function New-GitTag {
	param (
		# The tag name.
		[Parameter(Mandatory, Position = 0)]
		[string] $Name
	)

	git tag $Name
	git push origin $Name
}

<#
.SYNOPSIS
	Checks whether an update is available for the NuGet packages.
#>
function Test-ComposerPackageUpdate {
	composer outdated
}

<#
.SYNOPSIS
	Checks whether an update is available for the specified PowerShell module.
.INPUTS
	The PowerShell module to be checked.
.OUTPUTS
	An object providing the current and the latest version of the specified module if an update is available, otherwise none.
#>
function Test-PSResourceUpdate {
	[CmdletBinding()]
	[OutputType([psobject])]
	param (
		# The PowerShell module to be checked.
		[Parameter(Mandatory, Position = 0, ValueFromPipeline)]
		[Microsoft.PowerShell.PSResourceGet.UtilClasses.PSResourceInfo] $InputObject
	)

	process {
		if ($InputObject.Repository -ne "PSGallery") { return }

		$url = "https://www.powershellgallery.com/packages/$($InputObject.Name)"
		$response = Invoke-WebRequest $url -Method Head -MaximumRedirection 0 -SkipHttpErrorCheck -ErrorAction Ignore
		$latestVersion = [version] (Split-Path $response.Headers.Location -Leaf)

		$module = [pscustomobject]@{ ModuleName = $InputObject.Name; CurrentVersion = $InputObject.Version; LatestVersion = $latestVersion }
		if ($module.LatestVersion -gt $module.CurrentVersion) { $module }
	}
}

<#
.SYNOPSIS
	Updates the specified Composer package, if any. Otherwise, updates all packages.
#>
function Update-ComposerPackage {
	param (
		# The package to update.
		[Parameter(Position = 0)]
		[string] $Package
	)

	$argumentList = , "update"
	if ($Package) { $argumentList += $Package }
	composer @argumentList
}
