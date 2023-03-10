/** Performs the static analysis of source code. **/
function main() {
	Sys.command("lix run checkstyle --config etc/checkstyle.json --exitcode --source scripts");
	Sys.command("vendor/bin/phpstan", ["analyse", "--configuration=etc/phpstan.neon", "--memory-limit=256M"]);
}
