# Installation

## Requirements
Before installing **Akismet for PHP**, you need to make sure you have [PHP](https://www.php.net)
and [Composer](https://getcomposer.org), the PHP package manager, up and running.

You can verify if you're already good to go with the following commands:

```shell
php --version
# PHP 8.0.2 (cli) (built: Feb  3 2021 18:36:37) ( NTS Visual C++ 2019 x64 )

composer --version
# Composer version 2.0.9 2021-01-27 16:09:27
```

?> If you plan to play with the package sources, you will also need the latest versions of [PowerShell](https://docs.microsoft.com/en-us/powershell).

## Installing with Composer package manager

### 1. Install it
From a command prompt, run:

```shell
composer require cedx/akismet
```

### 2. Import it
Now in your [PHP](https://www.php.net) code, you can use:

```php
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
