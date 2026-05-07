bitrix24-php-sdk documentation
=============================================

## Basic necessary knowledge

Full list of buzzwords, patterns and dependencies used in SDK.

### Bitrix24

- [Bitrix24 REST API](https://apidocs.bitrix24.com/) and Marketing Applications

### PHP

- [namespaces](https://www.php.net/manual/en/language.namespaces.php): Organize code into logical groups
- [env variables](https://www.php.net/manual/en/reserved.variables.environment.php): Access environment-specific
  configuration
- [generators](https://www.php.net/manual/en/language.generators.php): Create iterators for large data sets efficiently
- [interfaces](https://www.php.net/manual/en/language.oop5.interfaces.php): Define contracts for classes
- [inheritance](https://www.php.net/manual/en/language.oop5.inheritance.php): Extend class functionality

### PHP standards for interoperability

- [PSR-3: Logger Interface](https://www.php-fig.org/psr/psr-3/): Common interface for logging libraries.
- [PSR-7: HTTP message interfaces](https://www.php-fig.org/psr/psr-7/): Common interfaces for representing HTTP messages
  as described in RFC 7230 and RFC 7231, and URIs for use with HTTP messages as described in RFC 3986.

### Design patterns

- [Fabric](https://refactoring.guru/design-patterns/factory-method): Create objects without specifying exact class
- [Observer](https://refactoring.guru/design-patterns/observer): Implement publish-subscribe architecture
- [Builder](https://refactoring.guru/design-patterns/builder): Design pattern that lets you construct complex objects
  step by step
- [Strategy](https://refactoring.guru/design-patterns/strategy): Behavioral design pattern that lets you define a family
  of algorithms, put each of them into a separate class, and make their objects interchangeable.

### Software architecture

- [The Twelve-Factor App](https://12factor.net/): The twelve-factor app is a methodology for building
  software-as-a-service apps.

### Infrastructure

- [composer](https://getcomposer.org/doc/): PHP dependency management tool
- [make](https://www.gnu.org/software/make/manual/make.html): Automate build processes
- [env-files](https://12factor.net/config): Store configuration in the environment
- [yaml](https://learnxinyminutes.com/docs/yaml/): YAML is a data serialisation language designed to be directly
  writable and readable by humans.
- [GitHub Actions](https://docs.github.com/en/actions): automate tasks throughout the software development lifecycle.
- [ngrok](https://ngrok.com/use-cases/developer-preview): Share your local app without deploying
- [PHP built-in web-server](https://www.php.net/manual/en/features.commandline.webserver.php): This web server is
  designed to aid application development. It may also be useful for testing purposes or for application demonstrations
  that are run in controlled environments.
- [cURL](https://curl.se/docs/tutorial.html): Command line tool and library for transferring data with URLs

### Code quality and refactoring

- [phpunit](https://phpunit.de/documentation.html): PHP testing framework
- [phpstan](https://phpstan.org/user-guide/getting-started): PHP static analysis tool
- [rector](https://getrector.org/documentation): PHP automated refactoring tool

### Additional PHP dependencies

- [monolog](https://github.com/Seldaek/monolog): Logging for PHP, supports severity levels
  from [rfc5424](https://datatracker.ietf.org/doc/html/rfc5424)
- [libphonenumber for PHP](https://github.com/giggsey/libphonenumber-for-php): Library for parsing, formatting, storing
  and validating international phone numbers. This library is based on
  Google's [libphonenumber](https://github.com/google/libphonenumber).
- [darsyn\IP](https://github.com/darsyn/ip): IP is an immutable value object for (both version 4 and 6) IP addresses.
- [carbon](https://github.com/briannesbitt/carbon): Simple PHP API extension for DateTime.
- [moneyphp](https://github.com/moneyphp/money): PHP implementation of the Money pattern, as described
  in [Patterns of Enterprise Application Architecture](https://martinfowler.com/books/eaa.html).
- [moneyphp-percentage](https://github.com/mesilov/moneyphp-percentage): Percentage (VAT) value object for
  moneyphp/money package.

### Symfony components

- [symfony/http-client](https://symfony.com/doc/current/http_client.html): low-level HTTP client with support for both
  PHP stream wrappers and cURL. It provides utilities to consume APIs and supports synchronous and asynchronous
  operations.
- [symfony/console](https://symfony.com/doc/current/components/console.html): eases the creation of beautiful and
  testable command line interfaces.
- [symfony/dotenv](https://github.com/symfony/dotenv): parses .env files to make environment variables stored in them
  accessible via `$_SERVER` or `$_ENV`.
- [symfony/filesystem](https://symfony.com/doc/current/components/filesystem.html): provides platform-independent
  utilities for filesystem operations and for file/directory paths manipulation.
- [symfony/mime](https://symfony.com/doc/current/components/mime.html): allows manipulating the MIME messages used to
  send emails and provides utilities related to MIME types.
- [symfony/finder](https://symfony.com/doc/current/components/finder.html): finds files and directories based on
  different criteria (name, file size, modification time, etc.) via an intuitive fluent interface.
- [symfony/http-client-contracts](https://github.com/symfony/http-client-contracts): a set of abstractions extracted out
  of the Symfony components.
- [symfony/http-foundation](https://symfony.com/doc/current/components/http_foundation.html): defines an object-oriented
  layer for the HTTP specification.
- [symfony/event-dispatcher](https://symfony.com/doc/current/components/event_dispatcher.html): provides tools that
  allow your application components to communicate with each other by dispatching events and listening to them.
- [symfony/uid](https://symfony.com/doc/current/components/uid.html): provides utilities to work with unique
  identifiers (UIDs) such as UUIDs and ULIDs.