# Testing
When you will integrate this library with your own application, you will of course need to test it. Often we see developers get ahead of themselves, making a few trivial API calls with minimal values and drawing the wrong conclusions about Akismet's accuracy.

## Simulate a positive (spam) result
Make a [comment check](../features/comment_check.md) API call with the `Author->getName()` set to `"viagra-test-123"` or `Author->getEmail()` set to `"akismet-guaranteed-spam@example.com"`. Populate all other required fields with typical values.

The Akismet API will always return a `CheckResult::isSpam` response to a valid request with one of those values. If you receive anything else, something is wrong in your client, data, or communications.

```php
<?php
use Akismet\{Author, Blog, Client, Comment};
use Nyholm\Psr7\{Uri};

function main(): void {
  $author = new Author('127.0.0.1', 'Mozilla/5.0', 'viagra-test-123');
  $comment = (new Comment($author))->setContent('A user comment');
    
  $blog = new Blog(new Uri('https://www.yourblog.com'));
  $client = new Client('123YourAPIKey', $blog);
    
  $result = $client->checkComment($comment);
  echo 'It should be "CheckResult.isSpam": ', $result;
}
```

## Simulate a negative (not spam) result
Make a [comment check](../features/comment_check.md) API call with the `Author->getRole()` set to `"administrator"` and all other required fields populated with typical values.

The Akismet API will always return a `CheckResult::isHam` response. Any other response indicates a data or communication problem.

```php
<?php
use Akismet\{Author, Blog, Client, Comment};
use Nyholm\Psr7\{Uri};

function main(): void {
  $author = (new Author('127.0.0.1', 'Mozilla/5.0'))->setRole('administrator');
  $comment = (new Comment($author))->setContent('A user comment');
    
  $blog = new Blog(new Uri('https://www.yourblog.com'));
  $client = new Client('123YourAPIKey', $blog);
    
  $result = $client->checkComment($comment);
  echo 'It should be "CheckResult.isHam": ', $result;
}
```

## Automated testing
Enable the `Client->isTest()` option in your tests.

That will tell Akismet not to change its behaviour based on those API calls: they will have no training effect. That means your tests will be somewhat repeatable, in the sense that one test won't influence subsequent calls.

```php
<?php
use Akismet\{Author, Blog, Client, Comment};
use Nyholm\Psr7\{Uri};

function main(): void {
  $author = new Author('127.0.0.1', 'Mozilla/5.0');
  $comment = (new Comment($author))->setContent('A user comment');
    
  $blog = new Blog(new Uri('https://www.yourblog.com'));
  $client = (new Client('123YourAPIKey', $blog))->setTest(true);
    
  echo 'It should not influence subsequent calls.';
  $client->checkComment($comment);
}
```
