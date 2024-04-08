# Submit ham
This call is intended for the submission of false positives - items that were incorrectly classified as spam by Akismet.
It takes identical arguments as [comment check](check_comment.md) and [submit spam](submit_spam.md).

```php
<?php Client->submitHam(Comment $comment): void
```

Remember that, as explained in the [submit spam](submit_spam.md) documentation, you should ensure
that any values you're passing here match up with the original and corresponding [comment check](check_comment.md) call.

See the [Akismet API documentation](https://akismet.com/developers/detailed-docs/submit-ham-false-positives) for more information.

## Parameters

### Comment **$comment**
The user's `Comment` to be submitted, incorrectly classified as spam.

!!! note
    Ideally, it should be the same object as the one passed to the original [comment check](check_comment.md) API call.

## Return value
None.

The method throws a `Psr\Http\Client\ClientExceptionInterface` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

It can also throw a custom error code and message (respectively provided by the `X-akismet-alert-code` and `X-akismet-alert-msg` headers).
See [Response Error Codes](https://akismet.com/developers/detailed-docs/errors) for more information.

## Example

```php
<?php
use akismet\{Author, Blog, Client, Comment};
use Psr\Http\Client\ClientExceptionInterface;

try {
  $blog = new Blog("https://www.yourblog.com");
  $client = new Client("123YourAPIKey", $blog);

  $comment = new Comment(
    content: "I'm testing out the Service API.",
    author: new Author(
      ipAddress: "192.168.123.456",
      userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
    )
  );

  $client->submitHam($comment);
  print("The comment was successfully submitted as ham.");
}
catch (ClientExceptionInterface $e) {
  print "An error occurred: {$e->getMessage()}";
}
```
