import haxe.Json;
import sys.FileSystem;
import sys.io.File;

/** Builds the documentation. **/
function main() {
	final version = Json.parse(File.getContent("composer.json")).version;
	Tools.replaceInFile("etc/phpdoc.xml", ~/version number="\d+(\.\d+){2}"/, 'version number="$version"');

	if (FileSystem.exists("docs")) Tools.removeDirectory("docs");
	Sys.command("phpdoc --config=etc/phpdoc.xml");
	File.copy("www/favicon.ico", "docs/images/favicon.ico");
}
