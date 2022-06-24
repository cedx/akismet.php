<?php declare(strict_types=1);

use Akismet\{Author, Blog, Client, Comment};
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Submits ham to the Akismet service.
 */
try {
	$blog = new Blog("https://www.yourblog.com");
	$client = new Client("123YourAPIKey", $blog);

	$comment = new Comment(
		content: "I'm testing out the Service API.",
		author: new Author(
			ipAddress: "192.168.123.456",
			userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36"
		)
	);

	$client->submitHam($comment);
	print("The comment was successfully submitted as ham.");
}

catch (ClientExceptionInterface $e) {
	print "An error occurred: {$e->getMessage()}";
}
