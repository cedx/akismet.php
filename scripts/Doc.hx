import haxe.Json;
import sys.FileSystem;
import sys.io.File;
using Lambda;

/** Builds the documentation. **/
function main() {
	["CHANGELOG.md", "LICENSE.md"].iter(file -> File.copy(file, 'docs/${file.toLowerCase()}'));
	if (FileSystem.exists("docs/api")) Tools.removeDirectory("docs/api");

	final version = Json.parse(File.getContent("composer.json")).version;
	Tools.replaceInFile("etc/phpdoc.xml", ~/version number="\d+(\.\d+){2}"/, 'version number="$version"');
	Sys.command("phpdoc --config=etc/phpdoc.xml");
	File.copy("docs/favicon.ico", "docs/api/images/favicon.ico");
}
