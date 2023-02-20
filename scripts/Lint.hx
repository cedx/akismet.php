/** Performs the static analysis of source code. **/
function main() Sys.command("vendor/bin/phpstan", ["analyse",
	"--configuration=etc/phpstan.neon",
	"--memory-limit=256M"
]);
