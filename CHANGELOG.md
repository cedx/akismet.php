# Changelog
This file contains highlights of what changes on each version of the [Akismet for PHP](https://github.com/cedx/akismet.php) library.

## Version 2.0.1
- Fixed a missing `implements \JsonSerializable` statement.

## Version 2.0.0
- Breaking change: modified the return type of the `jsonSerialize()` and `toJSON()` methods.
- Breaking change: modified the signature of the constructor of the `Client` class.
- Added the `Client::DEBUG_HEADER` constant.
- Added the `jsonSerialize()` and `toJSON()` methods to the `Client` class.
- Added the missing `User-Agent` HTTP header to the outgoing `Client` requests.
- Added property setters to the `Client` class.

## Version 1.1.0
- Added return type declarations on the fluent setters.

## Version 1.0.0
- Initial release.
