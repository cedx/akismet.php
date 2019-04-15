# Comment check
This is the call you will make the most. It takes a number of arguments and characteristics about the submitted content
and then returns a thumbs up or thumbs down. **Performance can drop dramatically if you choose to exclude data points.**
The more data you send Akismet about each comment, the greater the accuracy. We recommend erring on the side of including too much data.

```
Client->checkComment(Comment $comment): bool
```

!!! tip "Testing your data"
    It is important to test Akismet with a significant amount of real, live data in order to draw any conclusions on accuracy.
    Akismet works by comparing content to genuine spam activity happening right now (and this is based on more than just the content itself),
    so artificially generating spam comments is not a viable approach.

## Parameters

### Comment **$comment**
The `Comment` providing the user message to be checked.

## Return value
A `bool` value indicating whether the given `Comment` is spam.

The method throws a `ClientException` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

## Example

```php
<?php
use Akismet\{Author, Blog, Comment, CommentType};
use Akismet\Http\{Client, ClientException};
use GuzzleHttp\Psr7\{Uri};

function main(): void {
  try {
    $comment = new Comment(
      new Author('127.0.0.1', 'Mozilla/5.0'),
      'A user comment',
      CommentType::contactForm
    );

    $blog = new Blog(new Uri('https://www.yourblog.com'));
    $client = new Client('123YourAPIKey', $blog);
    $isSpam = $client->checkComment($comment);
    echo $isSpam ? 'The comment is spam' : 'The comment is ham';
  }

  catch (ClientException $e) {
    echo 'An error occurred: ', $e->getMessage();
  }
}
```
