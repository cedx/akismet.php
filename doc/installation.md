# Installation

## Requirements
Before installing **Akismet for PHP**, you need to make sure you have [PHP](https://secure.php.net)
and [Composer](https://getcomposer.org), the PHP package manager, up and running.

!!! warning
    Akismet for PHP requires PHP >= **7.1.0**.

!!! info
    If you plan to play with the library sources, you will also need
    [Phing](https://www.phing.info) and [Material for MkDocs](https://squidfunk.github.io/mkdocs-material).
    
You can verify if you're already good to go with the following commands:

```shell
php --version
# PHP 7.1.11-0ubuntu0.17.10.1 (cli) (built: Nov  1 2017 16:30:52) ( NTS )

composer --version
# Composer version 1.6.2 2018-01-05 15:28:41
```

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
