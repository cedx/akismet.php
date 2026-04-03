# Installation

## Requirements
Before installing **Akismet for PHP**, you need to make sure you have [PHP](https://www.php.net)
and [Composer](https://getcomposer.org), the PHP package manager, up and running.
	
You can verify if you're already good to go with the following commands:

```shell
php --version
# PHP 8.5.4 (cli) (built: Mar 10 2026 23:29:54) (NTS Visual C++ 2022 x64)

composer --version
# Composer version 2.9.5 2026-01-29 11:40:53
```

## Installing with Composer package manager

### 1. Install it
From a command prompt, run:

```shell
composer require cedx/akismet
```

### 2. Import it
Now in your [PHP](https://www.php.net) code, you can use:

```php
use Belin\Akismet\{
  Author,
  Blog,
  CheckResult,
  Client,
  Comment
};
```
