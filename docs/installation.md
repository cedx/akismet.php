# Installation

## Requirements
Before installing **Akismet for PHP**, you need to make sure you have [PHP](https://www.php.net)
and [Composer](https://getcomposer.org), the PHP package manager, up and running.
	
You can verify if you're already good to go with the following commands:

```shell
php --version
# PHP 8.3.2 (cli) (built: Jan 16 2024 20:48:07) (NTS Visual C++ 2019 x64)

composer --version
# Composer version 2.6.6 2023-12-08 18:32:26
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
use akismet\{
  Author,
  Blog,
  CheckResult,
  Client,
  Comment
};
```
