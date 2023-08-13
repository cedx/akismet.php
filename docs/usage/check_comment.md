# Comment check
This is the call you will make the most. It takes a number of arguments and characteristics about the submitted content
and then returns a thumbs up or thumbs down. **Performance can drop dramatically if you choose to exclude data points.**
The more data you send Akismet about each comment, the greater the accuracy. We recommend erring on the side of including too much data.

```php
Client->checkComment(Comment $comment): string
```

It is important to [test Akismet](testing.md) with a significant amount of real, live data in order to draw any conclusions on accuracy.
Akismet works by comparing content to genuine spam activity happening **right now** (and this is based on more than just the content itself),
so artificially generating spam comments is not a viable approach.

See the [Akismet API documentation](https://akismet.com/developers/comment-check) for more information.

## Parameters

### Comment **$comment**
The `Comment` providing the user's message to be checked.

## Return value
A `CheckResult` value indicating whether the given `Comment` is ham, spam or pervasive spam.

> A comment classified as pervasive spam can be safely discarded.

The method throws a `Psr\Http\Client\ClientExceptionInterface` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

It can also throws a custom error code and message (respectively provided by the `X-akismet-alert-code` and `X-akismet-alert-msg` headers).
See [Response Error Codes](https://akismet.com/developers/errors) for more information.

## Example
```php
use Akismet\{Author, Blog, CheckResult, Client, Comment, CommentType};
use Psr\Http\Client\ClientExceptionInterface;

try {
  $author = new Author(
    email: "john.doe@domain.com",
    ipAddress: "192.168.123.456",
    name: "John Doe",
    role: "guest",
    userAgent: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/102.0.0.0 Safari/537.36"
  );

  $comment = new Comment(
    author: $author,
    date: new DateTime,
    content: "A user comment.",
    referrer: "https://github.com/cedx/akismet.js",
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
```

See the [API reference](api/) for detailed information about the `Author` and `Comment` classes, and their properties.
