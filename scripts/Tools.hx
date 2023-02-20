import sys.FileSystem;
import sys.io.File;
import sys.io.Process;
using haxe.io.Path;
using StringTools;

/** Captures the output of the specified `command`. **/
function captureCommand(command: String, ?arguments: Array<String>) {
	final process = new Process(command, arguments);
	final stdout = process.stdout.readAll().toString();
	process.close();
	return stdout.rtrim();
}

/** Recursively deletes all files in the specified `directory`. **/
function cleanDirectory(directory: String) for (entry in FileSystem.readDirectory(directory).filter(entry -> entry != ".gitkeep")) {
	final path = Path.join([directory, entry]);
	FileSystem.isDirectory(path) ? removeDirectory(path) : FileSystem.deleteFile(path);
}

/** Recursively deletes the specified `directory`. **/
function removeDirectory(directory: String) {
	cleanDirectory(directory);
	FileSystem.deleteDirectory(directory);
}

/** Replaces in the specified `file` the substring which the `pattern` matches with the given `replacement`. **/
function replaceInFile(file: String, pattern: EReg, replacement: String)
	File.saveContent(file, pattern.replace(File.getContent(file), replacement));
