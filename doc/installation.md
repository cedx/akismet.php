# Installation

## Requirements
Before installing **Akismet for PHP**, you need to make sure you have [PHP](https://secure.php.net)
and [Composer](https://getcomposer.org), the PHP package manager, up and running.

!!! warning
    Akismet for PHP requires PHP >= **7.1.0**.
    
You can verify if you're already good to go with the following commands:

```shell
php --version
# PHP 7.2.3-1ubuntu1 (cli) (built: Mar 14 2018 22:03:58) ( NTS )

composer --version
# Composer version 1.6.4 2018-04-13 12:04:24
```

!!! info
    If you plan to play with the package sources, you will also need
    [Phing](https://www.phing.info) and [Material for MkDocs](https://squidfunk.github.io/mkdocs-material).

## Installing with Composer package manager

### 1. Install it
From a command prompt, run:

```shell
composer require cedx/akismet
```

### 2. Import it
Now in your [PHP](https://secure.php.net) code, you can use:

```php
<?php
use Akismet\{
  Author, Comment, CommentType,
  Blog, Client, ClientException
};
```
