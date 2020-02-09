<?php declare(strict_types=1);

use Akismet\{Author, Blog, CheckResult, Comment, CommentType};
use Akismet\Http\{Client, ClientException};
use GuzzleHttp\Psr7\{Uri};

/** Queries the Akismet service. */
function main(): void {
  try {
    $blog = new Blog(new Uri('https://www.yourblog.com'), 'UTF-8', ['fr']);
    $client = new Client('123YourAPIKey', $blog);

    // Key verification.
    $isValid = $client->verifyKey();
    echo $isValid ? 'The API key is valid' : 'The API key is invalid';

    // Comment check.
    $author = (new Author(
      '192.168.123.456',
      'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:72.0) Gecko/20100101 Firefox/72.0',
      'John Doe'
    ))->setEmail('john.doe@domain.com')->setRole('guest');

    $comment = (new Comment($author, 'A user comment', CommentType::contactForm))
      ->setDate(new \DateTimeImmutable);

    $result = $client->checkComment($comment);
    echo $result == CheckResult::isHam ? 'The comment is ham' : 'The comment is spam';

    // Submit spam / ham.
    $client->submitSpam($comment);
    echo 'Spam submitted';

    $client->submitHam($comment);
    echo 'Ham submitted';
  }

  catch (Throwable $e) {
    echo 'An error occurred: ', $e->getMessage(), PHP_EOL;
    if ($e instanceof ClientException) echo 'From: ', $e->getUri(), PHP_EOL;
  }
}
