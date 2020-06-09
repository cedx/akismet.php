<?php declare(strict_types=1);

use Akismet\{Author, Blog, CheckResult, Client, ClientException, Comment, CommentType};
use Nyholm\Psr7\{Uri};

/** Queries the Akismet service. */
function main(): void {
	try {
		$client = new Client("123YourAPIKey", (new Blog(new Uri("https://www.yourblog.com")))
			->setCharset("UTF-8")
			->setLanguages(["fr"]));

		// Key verification.
		$isValid = $client->verifyKey();
		print $isValid ? "The API key is valid" : "The API key is invalid";

		// Comment check.
		$author = (new Author)
			->setEmail("john.doe@domain.com")
			->setName("John Doe")
			->setRole("guest");

		$comment = (new Comment($author))
			->setContent("A user comment")
			->setDate(new DateTimeImmutable)
			->setType(CommentType::contactForm);

		$result = $client->checkComment($comment);
		print $result == CheckResult::isHam ? "The comment is ham" : "The comment is spam";

		// Submit spam / ham.
		$client->submitSpam($comment);
		print "Spam submitted";

		$client->submitHam($comment);
		print "Ham submitted";
	}

	catch (Throwable $e) {
		print "An error occurred: {$e->getMessage()}" . PHP_EOL;
		if ($e instanceof ClientException) print "From: {$e->getUri()}" . PHP_EOL;
	}
}
