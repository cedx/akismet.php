# Akismet for PHP
Used by millions of websites, [Akismet](https://akismet.com) filters out hundreds of millions of spam comments from the Web every day.
Add Akismet to your [PHP](https://www.php.net) applications so you don't have to worry about spam again.

## Quick start

### Get an API key
The Akismet service requires an API key. If you are not already registered, [sign up for an Akismet account](https://akismet.com/developers).

### Get the library
Install the latest version of **Akismet for PHP** with [Composer](https://getcomposer.org) package manager:

```shell
composer require cedx/akismet
```

For detailed instructions, see the [installation guide](installation.md).

## Usage
There are three different types of calls to [Akismet](https://akismet.com):

1. [Key verification](usage/verify_key.md) will verify whether a valid API key is being used. This is especially useful if you will have multiple users with their own Akismet subscriptions using your application.
2. [Comment check](usage/check_comment.md) is used to ask Akismet whether a given post, comment, profile, etc. is spam.
3. [Submit spam](usage/submit_spam.md) and [submit ham](usage/submit_ham.md) are follow-ups to let Akismet know when it got something wrong (missed spam and false positives). These are very important, and you shouldn't develop using the Akismet API without a facility to include reporting missed spam and false positives.

Before integrating this library into your application, you should [test your API calls](testing.md) to ensure a proper usage.

## See also
- [API reference](api/)
- [Packagist package](https://packagist.org/packages/cedx/akismet)
