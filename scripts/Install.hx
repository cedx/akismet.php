import sys.FileSystem;

/** Installs the project dependencies. **/
function main()
	Sys.command('composer ${FileSystem.exists("composer.lock") ? "install" : "update"}');
