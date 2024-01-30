<?php require __DIR__."/tools.php";

// Updates the version number in the sources.
$version = json_decode((string) file_get_contents(__DIR__."/../composer.json"))->version;
replaceInFile("src/Client.php", '/version = "\d+(\.\d+){2}"/', "version = \"$version\"");
