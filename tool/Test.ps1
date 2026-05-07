using module ./Cmdlets.psm1

"Running the test suite..."
Invoke-PhpUnit etc/PHPUnit.xml
