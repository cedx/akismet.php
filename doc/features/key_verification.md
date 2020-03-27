# Key verification
Key verification authenticates your key before calling the [comment check](comment_check.md), [submit spam](submit_spam.md), 
or [submit ham](submit_ham.md) methods. This is the first call that you should make to Akismet and is especially useful
if you will have multiple users with their own Akismet subscriptions using your application.

```
Client->verifyKey(): bool
```

## Parameters
None.

## Return value
A `bool` value indicating whether the client's API key is valid.

The method throws a `ClientException` when an error occurs.
The exception `getMessage()` usually includes some debug information, provided by the `X-akismet-debug-help` HTTP header, about what exactly was invalid about the call.

## Example

```php
<?php
use Akismet\{Blog, Client, ClientException};
use Nyholm\Psr7\{Uri};

function main(): void {
  try {
    $blog = new Blog(new Uri('https://www.yourblog.com'));
    $client = new Client('123YourAPIKey', $blog);
    $isValid = $client->verifyKey();
    echo $isValid ? 'The API key is valid' : 'The API key is invalid';
  }

  catch (ClientException $e) {
    echo 'An error occurred: ', $e->getMessage();
  }
}
```
