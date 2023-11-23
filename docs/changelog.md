# Changelog

## Version [15.1.0](https://github.com/cedx/akismet.php/compare/v15.0.1...15.1.0)
- Updated the package dependencies.

## Version [15.0.1](https://github.com/cedx/akismet.php/compare/v15.0.0...15.0.1)
- Fixed a regression in the `Client->verifyKey()` method that called a wrong endpoint.

## Version [15.0.0](https://github.com/cedx/akismet.php/compare/v14.1.0...15.0.0)
- Breaking change: lowercased the `Akismet` namespace.
- Updated the package dependencies.

## Version [14.1.0](https://github.com/cedx/akismet.php/compare/v14.0.0...14.1.0)
- Dropped the dependency on [Symfony HTTP Client](https://symfony.com/doc/current/http_client.html).

## Version [14.0.0](https://github.com/cedx/akismet.php/compare/v13.0.0...14.0.0)
- Breaking change: changed the signature of the class constructors.
- Breaking change: raised the required [PHP](https://www.php.net) version.
- Breaking change: removed the `RequestEvent` and `ResponseEvent` classes.
- Breaking change: replaced the getter/setter methods by properties.
- Breaking change: the `Client` class is no longer an event dispatcher. 
- Breaking change: using PHP 8.1 features, like enumerations and readonly properties.
- Added support for the [Akismet response error codes](https://akismet.com/developers/errors).
- Updated the package dependencies.

## Version [13.0.0](https://github.com/cedx/akismet.php/compare/v12.1.0...v13.0.0)
- Breaking change: raised the required [PHP](https://www.php.net) version.
- Breaking change: using PHP 8.0 features, like constructor property promotion, static return type and throw expressions.
- Ported the documentation to [docsify](https://docsify.js.org).
- Replaced the build system based on [Robo](https://robo.li) by [PowerShell](https://docs.microsoft.com/en-us/powershell) scripts.
- Updated the package dependencies.

## Version [12.1.0](https://github.com/cedx/akismet.php/compare/v12.0.0...v12.1.0)
- Added the `Blog->setLanguages()` method.
- Updated the documentation.
- Updated the package dependencies.

## Version [12.0.0](https://github.com/cedx/akismet.php/compare/v11.1.0...v12.0.0)
- Breaking change: changed the signature of the class constructors.
- Breaking change: changed the signature of the `fromJson()` methods.
- Breaking change: replaced [Événement](https://github.com/igorw/evenement) library by [Symfony EventDispatcher](https://symfony.com/doc/current/components/event_dispatcher.html) for event handling.
- Breaking change: raised the required [PHP](https://www.php.net) version.
- Breaking change: removed the `Client::VERSION` constant.
- Breaking change: removed the `__toString()` methods.
- Breaking change: using camelcase instead of all caps for constants.
- Breaking change: using PHP 7.4 features, like arrow functions and typed properties.
- Added support for the `X-akismet-pro-tip` HTTP header.
- Added the `CheckResult` enumeration.
- Added the `ClientException` exception class.
- Added the `Comment->recheckReason` property.
- Added the `RequestEvent` and `ResponseEvent` event classes.
- Added a user guide based on [MkDocs](http://www.mkdocs.org).
- Added an example code.
- Replaced the [Guzzle HTTP client](http://docs.guzzlephp.org) by the [Symfony one](https://symfony.com/doc/current/components/http_client.html).
- Updated the package dependencies.

## Version [11.1.0](https://github.com/cedx/akismet.php/compare/v11.0.0...v11.1.0)
- Added the `EVENT_REQUEST` and `EVENT_RESPONSE` constants to the `Client` class.

## Version [11.0.0](https://github.com/cedx/akismet.php/compare/v10.0.0...v11.0.0)
- Breaking change: changed the signature of the `Client` events.
- Breaking change: raised the required [PHP](https://www.php.net) version.
- Breaking change: using PHP 7.1 features, like nullable types and void functions.

## Version [10.0.0](https://github.com/cedx/akismet.php/compare/v9.0.0...v10.0.0)
- Breaking change: changed the signature of most class constructors.
- Breaking change: most class properties are now read-only.
- Breaking change: removed the `jsonSerialize()` and `__toString()` methods from the `Client` class.
- Added new values to the `CommentType` enumeration.
- Updated the package dependencies.

## Version [9.0.0](https://github.com/cedx/akismet.php/compare/v8.0.0...v9.0.0)
- Breaking change: moved the `Observable` API to a synchronous one.
- Breaking change: moved the `Subject` event API to the `EventEmitter` one.
- Changed the licensing for the [MIT License](https://opensource.org/licenses/MIT).
- Restored the [Guzzle](http://docs.guzzlephp.org) HTTP client.

## Version [8.0.0](https://github.com/cedx/akismet.php/compare/v7.0.0...v8.0.0)
- Breaking change: properties representing URLs as strings now use instances of the [`Psr\Http\Message\UriInterface`](http://www.php-fig.org/psr/psr-7/#35-psrhttpmessageuriinterface) interface.
- Added new unit tests.
- Replaced the [Guzzle](http://docs.guzzlephp.org) HTTP client by an `Observable`-based one.

## Version [7.0.0](https://github.com/cedx/akismet.php/compare/v6.0.0...v7.0.0)
- Breaking change: renamed the `akismet` namespace to `Akismet`.
- Breaking change: reverted the API of the `Client` class to an [Observable](http://reactivex.io/intro.html)-based one.
- Enabled the strict typing.
- Replaced [phpDocumentor](https://www.phpdoc.org) documentation generator by [ApiGen](https://github.com/ApiGen/ApiGen).
- Updated the package dependencies.

## Version [6.0.0](https://github.com/cedx/akismet.php/compare/v5.1.0...v6.0.0)
- Breaking change: dropped the dependency on [Observables](http://reactivex.io/intro.html).
- Breaking change: the `Client` class is now an `EventEmitter`.
- Ported the unit test assertions from [TDD](https://en.wikipedia.org/wiki/Test-driven_development) to [BDD](https://en.wikipedia.org/wiki/Behavior-driven_development).
- Removed the dependency on the `cedx/enum` module.
- Updated the package dependencies.

## Version [5.1.0](https://github.com/cedx/akismet.php/compare/v5.0.0...v5.1.0)
- Restored the `jsonSerialize()` and `__toString()` methods of the `Client` class.

## Version [5.0.0](https://github.com/cedx/akismet.php/compare/v4.0.0...v5.0.0)
- Breaking change: changed the signature of all constructors.
- Breaking change: changed the return type of several `Client` methods.
- Breaking change: renamed the `Client::SERVICE_URL` constant to `DEFAULT_ENDPOINT`.
- Breaking change: removed the `Client->jsonSerialize()` method.
- Added the `Client->endPoint` property.
- Updated the package dependencies.

## Version [4.0.0](https://github.com/cedx/akismet.php/compare/v3.0.0...v4.0.0)
- Breaking change: changed the `Blog->language` string property for the `languages` array property.
- Breaking change: renamed the `Client->test` property to `isTest` and the `Client->setTest()` method to `setIsTest`.
- Replaced the [Codacy](https://www.codacy.com) code coverage service by the [Coveralls](https://coveralls.io) one.
- Removed the vendor suffix from the PHP version number in the `Client->userAgent` property.
- Removed the `dist` build task.
- Updated the package dependencies.

## Version [3.0.0](https://github.com/cedx/akismet.php/compare/v2.0.1...v3.0.0)
- Breaking change: removed the `toJSON()` methods.
- Added the `onRequest` and `onResponse` event streams to the `Client` class.
- Removed the `final` modifier from the `jsonSerialize()` methods.

## Version [2.0.1](https://github.com/cedx/akismet.php/compare/v2.0.0...v2.0.1)
- Fixed a missing `implements \JsonSerializable` statement.

## Version [2.0.0](https://github.com/cedx/akismet.php/compare/v1.1.0...v2.0.0)
- Breaking change: modified the return type of the `jsonSerialize()` and `toJSON()` methods.
- Breaking change: modified the signature of the constructor of the `Client` class.
- Added the `Client::DEBUG_HEADER` constant.
- Added the `jsonSerialize()` and `toJSON()` methods to the `Client` class.
- Added the missing `User-Agent` HTTP header to the outgoing `Client` requests.
- Added property setters to the `Client` class.

## Version [1.1.0](https://github.com/cedx/akismet.php/compare/v1.0.0...v1.1.0)
- Added return type declarations on the fluent setters.

## Version 1.0.0
- Initial release.
