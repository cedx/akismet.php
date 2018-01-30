path: blob/master
source: lib/Client.php

# Submit ham
This call is intended for the submission of false positives - items that were incorrectly classified as spam by Akismet. It takes identical arguments as [comment check](comment-check.md) and [submit spam](submit-spam.md).

Remember that, as explained in the [submit spam](submit-spam.md) documentation, you should ensure that any values you're passing here match up with the original and corresponding [comment check](comment-check.md) call.

```php
Client::submitHam(Comment $comment): void
```

## Example

```php
<?php
use Akismet\{Author, Client, Comment, CommentType};

try {
  $comment = new Comment(
    new Author('127.0.0.1', 'Mozilla/5.0'),
    'A user comment',
    CommentType::CONTACT_FORM
  );

  $client = new Client('123YourAPIKey', 'http://www.yourblog.com');
  $client->submitHam($comment);
  echo 'Ham submitted';
}

catch (\RuntimeException $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```
