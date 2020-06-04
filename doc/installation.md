# Installation

## Requirements
Before installing **Akismet for PHP**, you need to make sure you have [PHP](https://www.php.net)
and [Composer](https://getcomposer.org), the PHP package manager, up and running.

You can verify if you're already good to go with the following commands:

``` shell
php --version
# PHP 7.4.6 (cli) (built: May 12 2020 11:38:52) ( NTS Visual C++ 2017 x64 )

composer --version
# Composer version 1.10.7 2020-06-03 10:03:56
```

!!! info
	If you plan to play with the package sources, you will also need the latest versions of
	[PowerShell](https://docs.microsoft.com/en-us/powershell) and [Material for MkDocs](https://squidfunk.github.io/mkdocs-material).

## Installing with Composer package manager

### 1. Install it
From a command prompt, run:

``` shell
composer require cedx/akismet
```

### 2. Import it
Now in your [PHP](https://www.php.net) code, you can use:

``` php
<?php
use Akismet\{
	Author,
	Blog,
	CheckResult,
	Client,
	ClientException,
	Comment,
	CommentType
};
```
