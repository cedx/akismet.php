# Akismet for PHP
![Release](https://img.shields.io/packagist/v/cedx/akismet.svg) ![License](https://img.shields.io/packagist/l/cedx/akismet.svg) ![Downloads](https://img.shields.io/packagist/dt/cedx/akismet.svg) ![Code quality](https://img.shields.io/codacy/grade/34982a060f094758917dddaaf4b40364.svg) ![Build](https://img.shields.io/travis/cedx/akismet.php.svg)

Prevent comment spam using [Akismet](https://akismet.com) service, in [PHP](https://secure.php.net).

## Features
- [Key Verification](https://akismet.com/development/api/#verify-key): checks an Akismet API key and gets a value indicating whether it is valid.
- [Comment Check](https://akismet.com/development/api/#comment-check): checks a comment and gets a value indicating whether it is spam.
- [Submit Spam](https://akismet.com/development/api/#submit-spam): submits a comment that was not marked as spam but should have been.
- [Submit Ham](https://akismet.com/development/api/#submit-ham): submits a comment that was incorrectly marked as spam but should not have been.

## Requirements
The latest [PHP](https://secure.php.net) and [Composer](https://getcomposer.org) versions.
If you plan to play with the sources, you will also need the [Phing](https://www.phing.info) latest version.

## Installing via [Composer](https://getcomposer.org)
From a command prompt, run:

```shell
$ composer require cedx/akismet
```

## Usage
This package has an API based on [Observables](http://reactivex.io/intro.html).

### Data Classes
The `Author`, `Blog`, and `Comment` classes provide standard getters and setters to access their properties.

To ease the initialization of these classes, their constructor accepts an associative array of property values (`"property" => "value"`), and their setters have a fluent interface:

```php
// Using an associative array on instanciation.
$author = new Author([
  'ipAddress' => '127.0.0.1',
  'name' => 'Anonymous',
  'userAgent' => 'Mozilla/5.0'
]);

// Using the fluent interface of the setters.
$author = (new Author())
  ->setIPAddress('127.0.0.1')
  ->setName('Anonymous')
  ->setUserAgent('Mozilla/5.0');
```

### Key Verification

```php
use akismet\{Client};

$client = new Client('YourAPIKey', 'http://your.blog.url');
$client->verifyKey()->subscribeCallback(function($isValid) {
  echo $isValid ? 'Your API key is valid.' : 'Your API key is invalid.';
});
```

### Comment Check

```php
use akismet\{Author, Comment};

$comment = new Comment([
  'author' => new Author(['ipAddress' => '127.0.0.1', 'userAgent' => 'Mozilla/5.0']),
  'content' => 'A comment.'
]);

$client->checkComment($comment)->subscribeCallback(function($isSpam) {
  echo $isSpam ? 'The comment is marked as spam.' : 'The comment is marked as ham.';
});
```

### Submit Spam/Ham

```php
$client->submitSpam($comment)->subscribeCallback(function() {
  echo 'Spam submitted.';
});

$client->submitHam($comment)->subscribeCallback(function() {
  echo 'Ham submitted.';
});
```

## Unit Tests
In order to run the tests, you must set the `AKISMET_API_KEY` environment variable to the value of your Akismet API key:

```shell
$ export AKISMET_API_KEY=<YourApiKey>
```

Then, you can run the `test` script from the command prompt:

```shell
$ phing test
```

## See Also
- [Code Quality](https://www.codacy.com/app/cedx/akismet-php)
- [Continuous Integration](https://travis-ci.org/cedx/akismet.php)

## License
[Akismet for PHP](https://github.com/cedx/akismet.php) is distributed under the Apache License, version 2.0.
