using module ./Cmdlets.psm1

"Running the test suite..."
Invoke-PhpUnit "$PSScriptRoot/../etc/PHPUnit.xml"
