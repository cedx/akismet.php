# Key verification
Key verification authenticates your key before calling the [comment check](features/comment_check.md),
[submit spam](features/submit_spam.md) or [submit ham](features/submit_ham.md) methods.

```php
Client->verifyKey(): bool
```

This is the first call that you should make to Akismet and is especially useful
if you will have multiple users with their own Akismet subscriptions using your application.

See the [Akismet API documentation](https://akismet.com/development/api/#verify-key) for more information.

## Parameters
None.

## Return value
A `bool` value indicating whether the client's API key is valid.

The method throws a `ClientException` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

## Example

```php
use Akismet\{Blog, Client, ClientException};
use Nyholm\Psr7\Uri;

function main(): void {
  try {
    $blog = new Blog(new Uri("https://www.yourblog.com"));
    $client = new Client("123YourAPIKey", $blog);

    $isValid = $client->verifyKey();
    print $isValid ? "The API key is valid." : "The API key is invalid.";
  }

  catch (ClientException $e) {
    print "An error occurred: {$e->getMessage()}";
  }
}
```
