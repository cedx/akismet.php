/** Runs the test suite. **/
function main() {
	final xdebug = Tools.captureCommand("php", ["-r", "print extension_loaded('xdebug') ? 'true' : 'false';"]);
	Sys.putEnv("XDEBUG_MODE", "coverage");
	Sys.command("vendor/bin/phpunit", ["--configuration=etc/phpunit.xml"].concat(xdebug == "true" ? [] : ["--no-coverage"]));
}
