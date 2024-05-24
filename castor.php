<?php
use Castor\Attribute\{AsContext, AsTask};
use Castor\Context;
use function Castor\{exit_code, finder, fs, http_download, run, variable};

#[AsContext(default: true)]
function context(): Context {
	return new Context(["package" => json_decode(file_get_contents("composer.json"))]);
}

#[AsTask(description: "Builds the project")]
function build(): void {
	$pkg = variable("package");
	replaceInFile("src/Client.php", '/version = "\d+(\.\d+){2}"/', "version = \"$pkg->version\"");
}

#[AsTask(description: "Deletes all generated files")]
function clean(): void {
	fs()->remove(finder()->in("var"));
	fs()->remove("www");
}

#[AsTask(description: "Packages the application")]
function dist(): void {
	clean();
	build();
}

#[AsTask(description: "Builds the documentation")]
function doc(): void {
	foreach (["CHANGELOG.md", "LICENSE.md"] as $file) fs()->copy($file, "docs/".mb_strtolower($file));

	$pkg = variable("package");
	replaceInFile("etc/phpdoc.xml", '/version number="\d+(\.\d+){2}"/', "version number=\"$pkg->version\"");

	fs()->remove("docs/api");
	http_download("https://phpdoc.org/phpDocumentor.phar", "var/phpDocumentor.phar");
	run("php var/phpDocumentor.phar --config=etc/phpdoc.xml");
	fs()->copy("docs/favicon.ico", "docs/api/images/favicon.ico");
}

#[AsTask(description: "Performs the static analysis of source code")]
function lint(): int {
	return exit_code("php vendor/bin/phpstan analyse --configuration=etc/phpstan.php --memory-limit=256M --verbose");
}

#[AsTask(description: "Publishes the package")]
function publish(): void {
	$pkg = variable("package");
	foreach (["tag", "push origin"] as $action) run("git $action v$pkg->version");
}

#[AsTask(description: "Starts the development server")]
function serve(): void {
	doc();
	run("mkdocs serve --config-file=etc/mkdocs.yaml");
}

#[AsTask(description: "Runs the test suite")]
function test(): int {
	return exit_code("php vendor/bin/phpunit --configuration=etc/phpunit.xml");
}

/**
 * Replaces in the specified file the substring which the pattern matches with the given replacement.
 * @param string $file The path of the file to process.
 * @param string $pattern The pattern to search for.
 * @param string $replacement The string to replace.
 */
function replaceInFile(string $file, string $pattern, string $replacement): void {
	file_put_contents($file, preg_replace($pattern, $replacement, file_get_contents($file)));
}
