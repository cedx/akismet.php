import haxe.Json;
import sys.FileSystem;
import sys.io.File;
using Lambda;

/** Builds the documentation. **/
function main() {
	final version = Json.parse(File.getContent("composer.json")).version;
	["CHANGELOG.md", "LICENSE.md"].iter(file -> File.copy(file, 'docs/${file.toLowerCase()}'));
	Tools.replaceInFile("etc/phpdoc.xml", ~/version number="\d+(\.\d+){2}"/, 'version number="$version"');

	if (FileSystem.exists("docs/api")) Tools.removeDirectory("docs/api");
	Sys.command("phpdoc --config=etc/phpdoc.xml");
	FileSystem.createDirectory("docs/api/images");
	File.copy("docs/favicon.ico", "docs/api/images/favicon.ico");
}
