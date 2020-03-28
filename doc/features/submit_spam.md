# Submit spam
This call is for submitting comments that weren't marked as spam but should have been.

```
Client->submitSpam(Comment $comment): void
```

It is very important that the values you submit with this call match those of your [comment check](comment_check.md) calls as closely as possible.
In order to learn from its mistakes, Akismet needs to match your missed spam and false positive reports
to the original [comment check](comment_check.md) API calls made when the content was first posted. While it is normal for less information
to be available for [submit spam](submit_spam.md) and [submit ham](submit_ham.md) calls (most comment systems and forums will not store all metadata),
you should ensure that the values that you do send match those of the original content.

See the [Akismet API documentation](https://akismet.com/development/api/#submit-spam) for more information.

## Parameters

### Comment **$comment**
The user `Comment` to be submitted, incorrectly classified as ham.

!!! tip
    Ideally, it should be the same object as the one passed to the original [comment check](comment_check.md) API call.

## Return value
None.

The method throws a `ClientException` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

## Example

```php
<?php
use Akismet\{Author, Blog, Client, ClientException, Comment};
use Nyholm\Psr7\{Uri};

function main(): void {
  try {
    $blog = new Blog(new Uri('https://www.yourblog.com'));
    $client = new Client('123YourAPIKey', $blog);

    $comment = (new Comment(new Author))->setContent('An invalid user comment (spam)');
    $result = $client->checkComment($comment);
    // Got `CheckResult::isHam`, but `CheckResult::isSpam` expected.

    echo 'The comment was incorrectly classified as ham.';
    $client->submitSpam($comment);
  }

  catch (ClientException $e) {
    echo 'An error occurred: ', $e->getMessage();
  }
}
```
