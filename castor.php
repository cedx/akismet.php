<?php declare(strict_types=1);

// TODO !!!
#[AsTask(description: "Builds the project")]
function build(): void {
	$file = "src/Client.php";
	$pkg = variable("package");
	file_put_contents($file, preg_replace('/version = "\d+(\.\d+){2}"/', "version = \"$pkg->version\"", file_get_contents($file)));
}
