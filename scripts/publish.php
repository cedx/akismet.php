<?php require __DIR__."/tools.php";

/**
 * Publishes the package.
 */
$version = json_decode((string) file_get_contents(__DIR__."/../composer.json"))->version;
foreach (["tag", "push origin"] as $action) shell_exec("git $action v$version");
