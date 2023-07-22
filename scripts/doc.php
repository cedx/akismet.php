<?php require __DIR__."/tools.php";

/**
 * Builds the documentation.
 */
$version = json_decode((string) file_get_contents(__DIR__."/../composer.json"))->version;
foreach (["CHANGELOG.md", "LICENSE.md"] as $file) copy($file, "docs/".mb_strtolower($file));
replaceInFile("etc/phpdoc.xml", '/version number="\d+(\.\d+){2}"/', "version number=\"$version\"");

if (is_dir("docs/api")) removeDirectory("docs/api");
shell_exec("phpdoc --config=etc/phpdoc.xml");
mkdir("docs/api/images", recursive: true);
copy("docs/favicon.ico", "docs/api/images/favicon.ico");
