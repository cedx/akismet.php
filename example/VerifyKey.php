<?php declare(strict_types=1);
use Belin\Akismet\{Blog, Client};

// Verifies an Akismet API key.
try {
	$blog = new Blog("https://www.yourblog.com");
	$client = new Client("123YourAPIKey", $blog);

	$isValid = $client->verifyKey();
	print $isValid ? "The API key is valid." : "The API key is invalid.";
}
catch (RuntimeException $e) {
	print "An error occurred: {$e->getMessage()}";
}
