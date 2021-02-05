# Comment check
This is the call you will make the most. It takes a number of arguments and characteristics about the submitted content
and then returns a thumbs up or thumbs down. **Performance can drop dramatically if you choose to exclude data points.**
The more data you send Akismet about each comment, the greater the accuracy. We recommend erring on the side of including too much data.

```php
Client->checkComment(Comment $comment): string
```

It is important to [test Akismet](advanced/testing.md) with a significant amount of real, live data in order to draw any conclusions on accuracy.
Akismet works by comparing content to genuine spam activity happening **right now** (and this is based on more than just the content itself),
so artificially generating spam comments is not a viable approach.

See the [Akismet API documentation](https://akismet.com/development/api/#comment-check) for more information.

## Parameters

### Comment **$comment**
The `Comment` providing the user message to be checked.

## Return value
A `CheckResult` value indicating whether the given `Comment` is ham, spam or pervasive spam.

?> A comment classified as pervasive spam can be safely discarded.

The method throws a `ClientException` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

## Example

```php
use Akismet\{Author, Blog, CheckResult, Client, ClientException, Comment, CommentType};
use Nyholm\Psr7\Uri;

function main(): void {
	try {
		$comment = (new Comment(new Author))
			->setContent("A user comment")
			->setType(CommentType::contactForm);

		$blog = new Blog(new Uri("https://www.yourblog.com"));
		$client = new Client("123YourAPIKey", $blog);

		$result = $client->checkComment($comment);
		print $result == CheckResult::isHam ? "The comment is ham." : "The comment is spam.";
	}

	catch (ClientException $e) {
		print "An error occurred: {$e->getMessage()}";
	}
}
```

See the [API reference](https://cedx.github.io/akismet.php/api) of this library for detailed information about the `Author` and `Comment` classes, and their properties.
