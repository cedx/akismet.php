# Testing
When you will integrate this library with your own application, you will of course need to test it.
Often we see developers get ahead of themselves, making a few trivial API calls with minimal values
and drawing the wrong conclusions about Akismet's accuracy.

## Simulate a positive result (spam)
Make a [comment check](usage/check_comment.md) API call with the `Author->name` set to `"viagra-test-123"`
or `Author->email` set to `"akismet-guaranteed-spam@example.com"`. Populate all other required fields with typical values.

The Akismet API will always return a `CheckResult::spam` response to a valid request with one of those values.
If you receive anything else, something is wrong in your client, data, or communications.

```php
<?php use akismet\{Author, Blog, Client, Comment};

$comment = new Comment(
  content: "A user comment.",
  author: new Author(
    ipAddress: "127.0.0.1",
    name: "viagra-test-123",
    userAgent: "Mozilla/5.0"
  )
);

$blog = new Blog("https://www.yourblog.com");
$result = (new Client("123YourAPIKey", $blog))->checkComment($comment);
print "It should be 'CheckResult::spam': {$result->name}";
```

## Simulate a negative result (ham)
Make a [comment check](usage/check_comment.md) API call with the `Author->role` set to `"administrator"`
and all other required fields populated with typical values.

The Akismet API will always return a `CheckResult::ham` response. Any other response indicates a data or communication problem.

```php
<?php use akismet\{Author, AuthorRole, Blog, Client, Comment};

$comment = new Comment(
  content: "A user comment.",
  author: new Author(
    ipAddress: "127.0.0.1",
    role: AuthorRole::administrator->value,
    userAgent: "Mozilla/5.0"
  )
);

$blog = new Blog("https://www.yourblog.com");
$result = (new Client("123YourAPIKey", $blog))->checkComment($comment);
print "It should be 'CheckResult::ham': {$result->name}";
```

## Automated testing
Enable the `Client->isTest` option in your tests.

That will tell Akismet not to change its behaviour based on those API calls: they will have no training effect.
That means your tests will be somewhat repeatable, in the sense that one test won't influence subsequent calls.

```php
<?php use akismet\{Author, Blog, Client, Comment};

$blog = new Blog("https://www.yourblog.com");
$client = new Client("123YourAPIKey", $blog, isTest: true);

$comment = new Comment(
  content: "A user comment.",
  author: new Author(ipAddress: "127.0.0.1", userAgent: "Mozilla/5.0")
);

// It should not influence subsequent calls.
$client->checkComment($comment);
```
