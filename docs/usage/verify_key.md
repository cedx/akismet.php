# Key verification
Key verification authenticates your API key before calling the [comment check](usage/check_comment.md),
[submit spam](usage/submit_spam.md) or [submit ham](usage/submit_ham.md) methods.

```php
Client->verifyKey(): bool
```

This is the first call that you should make to Akismet and is especially useful
if you will have multiple users with their own Akismet subscriptions using your application.

See the [Akismet API documentation](https://akismet.com/developers/detailed-docs/key-verification) for more information.

## Parameters
None.

## Return value
A `bool` value indicating whether the client's API key is valid.

The method throws a `Psr\Http\Client\ClientExceptionInterface` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

It can also throw a custom error code and message (respectively provided by the `X-akismet-alert-code` and `X-akismet-alert-msg` headers).
See [Response Error Codes](https://akismet.com/developers/detailed-docs/errors) for more information.

## Example

```php
<?php
use akismet\{Blog, Client};
use Psr\Http\Client\ClientExceptionInterface;

try {
  $blog = new Blog("https://www.yourblog.com");
  $client = new Client("123YourAPIKey", $blog);

  $isValid = $client->verifyKey();
  print $isValid ? "The API key is valid." : "The API key is invalid.";
}
catch (ClientExceptionInterface $e) {
  print "An error occurred: {$e->getMessage()}";
}
```

See the [API reference](api/) for detailed information about the `Client` and `Blog` classes, and their properties and methods.
