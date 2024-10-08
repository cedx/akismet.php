<?php declare(strict_types=1);
use akismet\{Author, Blog, Client, Comment};

// Submits spam to the Akismet service.
try {
	$blog = new Blog("https://www.yourblog.com");
	$client = new Client("123YourAPIKey", $blog);

	$comment = new Comment(
		content: "Spam!",
		author: new Author(
			ipAddress: "192.168.123.456",
			userAgent: "Spam Bot/6.6.6"
		)
	);

	$client->submitSpam($comment);
	print("The comment was successfully submitted as spam.");
}
catch (RuntimeException $e) {
	print "An error occurred: {$e->getMessage()}";
}
