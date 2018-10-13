<?php
declare(strict_types=1);

use Akismet\{Author, Blog, Client, ClientException, Comment, CommentType};
use GuzzleHttp\Psr7\{Uri};

/**
 * Queries the Akismet service.
 */
function main(): void {
  try {
    // Key verification.
    $client = new Client('123YourAPIKey', new Blog(new Uri('https://www.yourblog.com')));
    $isValid = $client->verifyKey();
    echo $isValid ? 'The API key is valid' : 'The API key is invalid';

    // Comment check.
    $comment = new Comment(
      new Author('127.0.0.1', 'Mozilla/5.0'),
      'A user comment',
      CommentType::CONTACT_FORM
    );

    $isSpam = $client->checkComment($comment);
    echo $isSpam ? 'The comment is spam' : 'The comment is ham';

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
