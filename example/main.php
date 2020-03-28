<?php declare(strict_types=1);

use Akismet\{Author, Blog, CheckResult, Client, ClientException, Comment, CommentType};
use Nyholm\Psr7\{Uri};

/** Queries the Akismet service. */
function main(): void {
  try {
    $blog = (new Blog(new Uri('https://www.yourblog.com')))->setCharset('UTF-8');
    $blog->getLanguages()->append('fr');
    $client = new Client('123YourAPIKey', $blog);

    // Key verification.
    $isValid = $client->verifyKey();
    echo $isValid ? 'The API key is valid' : 'The API key is invalid';

    // Comment check.
    $author = (new Author)
      ->setEmail('john.doe@domain.com')
      ->setName('John Doe')
      ->setRole('guest');

    $comment = (new Comment($author))
      ->setContent('A user comment')
      ->setDate(new DateTimeImmutable)
      ->setType(CommentType::contactForm);

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
