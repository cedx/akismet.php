# Installation

## Requirements
Before installing **Akismet for PHP**, you need to make sure you have [PHP](https://secure.php.net)
and [Composer](https://getcomposer.org), the PHP package manager, up and running.

!!! warning
    Akismet for PHP requires PHP >= **7.2.0**.
    
You can verify if you're already good to go with the following commands:

```shell
php --version
# PHP 7.2.7-0ubuntu0.18.04.2 (cli) (built: Jul  4 2018 16:55:24) ( NTS )

composer --version
# Composer version 1.7.1 2018-08-07 09:39:23
```

!!! info
    If you plan to play with the package sources, you will also need
    [Robo](https://robo.li) and [Material for MkDocs](https://squidfunk.github.io/mkdocs-material).

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
