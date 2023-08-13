# Submit spam
This call is for submitting comments that weren't marked as spam but should have been.

```php
Client->submitHam(Comment $comment): void
```

It is very important that the values you submit with this call match those of your [comment check](usage/check_comment.md) calls as closely as possible.
In order to learn from its mistakes, Akismet needs to match your missed spam and false positive reports
to the original [comment check](usage/check_comment.md) API calls made when the content was first posted. While it is normal for less information
to be available for [submit spam](usage/submit_spam.md) and [submit ham](usage/submit_ham.md) calls (most comment systems and forums will not store all metadata),
you should ensure that the values that you do send match those of the original content.

See the [Akismet API documentation](https://akismet.com/developers/submit-spam-missed-spam) for more information.

## Parameters

### Comment **$comment**
The user's `Comment` to be submitted, incorrectly classified as ham.

> Ideally, it should be the same object as the one passed to the original [comment check](usage/check_comment.md) API call.

## Return value
None.

The method throws a `Psr\Http\Client\ClientExceptionInterface` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

It can also throws a custom error code and message (respectively provided by the `X-akismet-alert-code` and `X-akismet-alert-msg` headers).
See [Response Error Codes](https://akismet.com/developers/errors) for more information.

## Example

```php
use Akismet\{Author, Blog, Client, Comment};
use Psr\Http\Client\ClientExceptionInterface;

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

catch (ClientExceptionInterface $e) {
  print "An error occurred: {$e->getMessage()}";
}
```
