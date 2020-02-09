# Akismet for PHP
![Runtime](https://img.shields.io/packagist/php-v/cedx/akismet.svg) ![Release](https://img.shields.io/packagist/v/cedx/akismet.svg) ![License](https://img.shields.io/packagist/l/cedx/akismet.svg) ![Downloads](https://img.shields.io/packagist/dt/cedx/akismet.svg) ![Coverage](https://coveralls.io/repos/github/cedx/akismet.php/badge.svg) ![Build](https://github.com/cedx/akismet.php/workflows/build/badge.svg)

Prevent comment spam using [Akismet](https://akismet.com) service, in [PHP](https://www.php.net).

## Documentation
- [User guide](https://dev.belin.io/akismet.php)
- [API reference](https://dev.belin.io/akismet.php/api)

## Development
- [Git repository](https://github.com/cedx/akismet.php)
- [Packagist package](https://packagist.org/packages/cedx/akismet)
- [Submit an issue](https://github.com/cedx/akismet.php/issues)

## Features
- [Key verification](https://akismet.com/development/api/#verify-key): checks an Akismet API key and gets a value indicating whether it is valid.
- [Comment check](https://akismet.com/development/api/#comment-check): checks a comment and gets a value indicating whether it is spam.
- [Submit spam](https://akismet.com/development/api/#submit-spam): submits a comment that was not marked as spam but should have been.
- [Submit ham](https://akismet.com/development/api/#submit-ham): submits a comment that was incorrectly marked as spam but should not have been.

## Requirements
You need the latest [PHP](https://www.php.net) and [Composer](https://getcomposer.org) versions to use the Akismet library.

If you plan to play with the sources, you will also need the latest [Robo](https://robo.li) and [Material for MkDocs](https://squidfunk.github.io/mkdocs-material) versions.

## Installing with Composer package manager
From a command prompt, run:

```shell
composer require cedx/akismet
```

## Usage

### Key verification

```php
<?php
use Akismet\{Blog, Client, ClientException};
use GuzzleHttp\Psr7\{Uri};

try {
  $client = new Client('123YourAPIKey', new Blog(new Uri('https://www.yourblog.com')));
  $isValid = $client->verifyKey();
  echo $isValid ? 'The API key is valid' : 'The API key is invalid';
}

catch (ClientException $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```

### Comment check

```php
<?php
use Akismet\{Author, Comment, CommentType};

try {
  $comment = new Comment(
    new Author('127.0.0.1', 'Mozilla/5.0'),
    'A user comment',
    CommentType::contactForm
  );

  $isSpam = $client->checkComment($comment);
  echo $isSpam ? 'The comment is spam' : 'The comment is ham';
}

catch (ClientException $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```

### Submit spam / ham

```php
<?php
try {
  $client->submitSpam($comment);
  echo 'Spam submitted';

  $client->submitHam($comment);
  echo 'Ham submitted';
}

catch (ClientException $e) {
  echo 'An error occurred: ', $e->getMessage();
}
```

## License
[Akismet for PHP](https://dev.belin.io/akismet.php) is distributed under the MIT License.
