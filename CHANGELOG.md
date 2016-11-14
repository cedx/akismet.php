# Changelog
This file contains highlights of what changes on each version of the [Akismet for PHP](https://github.com/cedx/akismet.php) library.

## Version 2.0.0
- Breaking change: changed the return type of the `jsonSerialize()` and `toJSON()` methods.
- Added the `Client::DEBUG_HEADER` constant.
- Added the `jsonSerialize()` and `toJSON()` methods to the `Client` class.
- Added the missing `User-Agent` HTTP header to the outgoing `Client` requests.

## Version 1.1.0
- Added return type declarations on the fluent setters.

## Version 1.0.0
- Initial release.
