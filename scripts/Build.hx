import haxe.Json;
import sys.io.File;

/** Updates the version number in the sources. **/
function main() {
	final version = Json.parse(File.getContent("composer.json")).version;
	Tools.replaceInFile("src/Client.php", ~/const version = "\d+(\.\d+){2}"/, 'const version = "$version"');
}
