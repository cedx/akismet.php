import haxe.Json;
import sys.io.File;

/** Publishes the package. **/
function main() {
	final version = Json.parse(File.getContent("composer.json")).version;
	for (action in ["tag", "push origin"]) Sys.command('git $action v$version');
}
