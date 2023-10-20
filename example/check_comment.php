<?php
use akismet\{Author, Blog, CheckResult, Client, Comment, CommentType};
use Psr\Http\Client\ClientExceptionInterface;

/**
 * Checks a comment against the Akismet service.
 */
try {
	$author = new Author(
		email: "john.doe@domain.com",
		ipAddress: "192.168.123.456",
		name: "John Doe",
		role: "guest",
		userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36"
	);

	$comment = new Comment(
		author: $author,
		date: new DateTime,
		content: "A user comment.",
		referrer: "https://github.com/cedx/akismet.php",
		type: CommentType::contactForm->value
	);

	$blog = new Blog(
		charset: "UTF-8",
		languages: ["fr"],
		url: "https://www.yourblog.com"
	);

	$result = (new Client("123YourAPIKey", $blog))->checkComment($comment);
	print $result == CheckResult::ham ? "The comment is ham." : "The comment is spam.";
}
catch (ClientExceptionInterface $e) {
	print "An error occurred: {$e->getMessage()}";
}
