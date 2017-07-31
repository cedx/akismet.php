# Akismet for PHP
![Runtime](https://img.shields.io/badge/php-%3E%3D7.0-brightgreen.svg) ![Release](https://img.shields.io/packagist/v/cedx/akismet.svg) ![License](https://img.shields.io/packagist/l/cedx/akismet.svg) ![Downloads](https://img.shields.io/packagist/dt/cedx/akismet.svg) ![Coverage](https://coveralls.io/repos/github/cedx/akismet.php/badge.svg) ![Build](https://travis-ci.org/cedx/akismet.php.svg)

Prevent comment spam using [Akismet](https://akismet.com) service, in [PHP](https://secure.php.net).

## Features
- [Key verification](https://akismet.com/development/api/#verify-key): checks an Akismet API key and gets a value indicating whether it is valid.
- [Comment check](https://akismet.com/development/api/#comment-check): checks a comment and gets a value indicating whether it is spam.
- [Submit spam](https://akismet.com/development/api/#submit-spam): submits a comment that was not marked as spam but should have been.
- [Submit ham](https://akismet.com/development/api/#submit-ham): submits a comment that was incorrectly marked as spam but should not have been.

## Requirements
The latest [PHP](https://secure.php.net) and [Composer](https://getcomposer.org) versions.
If you plan to play with the sources, you will also need the latest [Phing](https://www.phing.info) version.

## Installing via [Composer](https://getcomposer.org)
From a command prompt, run:

```shell
$ composer require cedx/akismet
```

## Usage
This package has an API based on [Observables](http://reactivex.io/intro.html).

> When running the tests, the scheduler is automatically bootstrapped.
> When using [RxPHP](https://github.com/ReactiveX/RxPHP) within your own project, you'll need to set the default scheduler.

### Key verification

```php
use Akismet\{Client};

$client = new Client('YourAPIKey', 'http://your.blog.url');
$client->verifyKey()->subscribe(function($isValid) {
  echo $isValid ? 'Your API key is valid.' : 'Your API key is invalid.';
});
```

### Comment check

```php
use Akismet\{Author, Comment};

$comment = new Comment(
  new Author('127.0.0.1', 'Mozilla/5.0'),
  'A comment.'
);

$client->checkComment($comment)->subscribe(function($isSpam) {
  echo $isSpam ? 'The comment is marked as spam.' : 'The comment is marked as ham.';
});
```

### Submit spam/ham

```php
$client->submitSpam($comment)->subscribe(function() {
  echo 'Spam submitted.';
});

$client->submitHam($comment)->subscribe(function() {
  echo 'Ham submitted.';
});
```

## Events
The `Client` class triggers some events during its life cycle:

- `request` : emitted every time a request is made to the remote service.
- `response` : emitted every time a response is received from the remote service.

These events are exposed as [Observable](http://reactivex.io/intro.html), you can subscribe to them using the `on<EventName>` methods:

```php
use Psr\Http\Message\{RequestInterface, ResponseInterface};

$client->onRequest()->subscribe(function(RequestInterface $request) {
  echo 'Client request: ', $request->getUri();
});

$client->onResponse()->subscribe(function(ResponseInterface $response) {
  echo 'Server response: ', $response->getStatusCode();
});
```

## Unit tests
In order to run the tests, you must set the `AKISMET_API_KEY` environment variable to the value of your Akismet API key:

```shell
$ export AKISMET_API_KEY="<YourAPIKey>"
```

Then, you can run the `test` script from the command prompt:

```shell
$ composer test
```

## See also
- [API reference](https://cedx.github.io/akismet.php)
- [Code coverage](https://coveralls.io/github/cedx/akismet.php)
- [Continuous integration](https://travis-ci.org/cedx/akismet.php)

## License
[Akismet for PHP](https://github.com/cedx/akismet.php) is distributed under the Apache License, version 2.0.
