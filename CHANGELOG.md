# Changelog
This file contains highlights of what changes on each version of the [Akismet for PHP](https://github.com/cedx/akismet.php) library.

## Version 4.0.0
- Breaking change: changed the `Blog::language` string property for the `languages` array property.
- Breaking change: renamed the `Client::test` property to `isTest` and the `Client::setTest` method to `setIstTest`.
- Replaced the [Codacy](https://www.codacy.com) code coverage service by the [Coveralls](https://coveralls.io) one.
- Removed the vendor suffix from the PHP version number in the `Client::userAgent` property.
- Removed the `dist` build task.
- Updated the package dependencies.

## Version 3.0.0
- Breaking change: removed the `toJSON()` methods.
- Added the `onRequest` and `onResponse` event streams to the `Client` class.
- Removed the `final` modifier from the `jsonSerialize()` methods.

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
