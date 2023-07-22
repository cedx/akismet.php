<?php
/**
 * Recursively deletes all files in the specified directory.
 * @param string $directory The directory to clean.
 */
function cleanDirectory(string $directory): void {
	static $excludes = [".", "..", ".gitkeep"];
	foreach (array_filter(scandir($directory) ?: [], fn($item) => !in_array($item, $excludes)) as $entry)
		is_dir($path = "$directory/$entry") ? removeDirectory($path) : unlink($path);
}

/**
 * Recursively deletes the specified directory.
 * @param string $directory The directory to delete.
 */
function removeDirectory(string $directory): void {
	cleanDirectory($directory);
	rmdir($directory);
}

/**
 * Replaces in the specified file the substring which the pattern matches with the given replacement.
 * @param string $file The path of the file to process.
 * @param string $pattern The pattern to search for.
 * @param string $replacement The string to replace.
 */
function replaceInFile(string $file, string $pattern, string $replacement): void {
	file_put_contents($file, preg_replace($pattern, $replacement, (string) file_get_contents($file)));
}
