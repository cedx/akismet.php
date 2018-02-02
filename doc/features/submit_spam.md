# Submit spam
This call is for submitting comments that weren't marked as spam but should have been.

It is very important that the values you submit with this call match those of your [comment check](comment_check.md) calls as closely as possible. In order to learn from its mistakes, Akismet needs to match your missed spam and false positive reports to the original [comment check](comment_check.md) API calls made when the content was first posted. While it is normal for less information to be available for [submit spam](submit_spam.md) and [submit ham](submit_ham.md) calls (most comment systems and forums will not store all metadata), you should ensure that the values that you do send match those of the original content.

```
Client->submitSpam(Comment $comment): void
```

## Parameters

### $comment
The user `Comment` to be submitted, incorrectly classified as ham.

!!! tip
    It should be the same object instance as the one passed to the original [comment check](comment_check.md) API call.

## Return value
None.

The method throws a `ClientException` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

## Example

```php
<?php
use Akismet\{Author, Client, ClientException, Comment};

try {
  $comment = new Comment(
    new Author('127.0.0.1', 'Mozilla/5.0'),
    'An invalid user comment (spam)'
  );

  $client = new Client('123YourAPIKey', 'http://www.yourblog.com');
  $isSpam = $client->checkComment($comment); // `false`, but `true` expected.

  echo 'The comment was incorrectly classified as ham';
  $client->submitSpam($comment);
}

catch (ClientException $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```
