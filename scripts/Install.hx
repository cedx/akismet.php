import sys.FileSystem;

/** Installs the project dependencies. **/
function main() {
	Sys.command("lix download");
	Sys.command('composer ${FileSystem.exists("composer.lock") ? "install" : "update"}');
}
