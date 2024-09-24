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

## Authorisation

- use [incoming webhooks](Core/Auth/auth.md).
- use OAuth2.0 for applications.

## List of all supported methods

[All methods list](Services/bitrix24-php-sdk-methods.md), this list build automatically.

## Call unsupported methods in SDK

In SDK [all methods](https://apidocs.bitrix24.com/api-reference/index.html#bitrix24-tool) grouped by scope. If service
still not implemented in SDK, You can directly call from SDK core:

```php
declare(strict_types=1);

use Bitrix24\SDK\Services\ServiceBuilderFactory;

require_once 'vendor/autoload.php';

// init bitrix24-php-sdk service from webhook
$b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook('INSERT_HERE_YOUR_WEBHOOK_URL');

// call core if method not implemented in services
var_dump($b24Service->core->call('user.current')->getResponseData()->getResult());
```
After that You can create new issue on GitHub – 🚀 [SDK Feature Request](https://github.com/bitrix24/b24phpsdk/issues/new?assignees=&labels=enhancement+in+SDK&projects=&template=2_feature_request_sdk.yaml), and we add new method support in services.

## Application development

If you build application based on bitrix24-php-sdk You can use some domain contracts for interoperability.
They store in folder `src/Application/Contracts`.

Available contracts

- [Bitrix24Accounts](/src/Application/Contracts/Bitrix24Accounts/Docs/Bitrix24Accounts.md) – store auth tokens and
  provides methods for work with Bitrix24 account.
- [ApplicationInstallations](/src/Application/Contracts/ApplicationInstallations/Docs/ApplicationInstallations.md) –
  Store information about application installation, linked with Bitrix24 Account with auth tokens.
- [ContactPersons](/src/Application/Contracts/ContactPersons/Docs/ContactPersons.md) – Store information about person
  who installed application.
- [Bitrix24Partners](/src/Application/Contracts/Bitrix24Partners/Docs/Bitrix24Partners.md) – Store information about
  Bitrix24 Partner who supports client portal and install or configure application.
## Errors handling
In SDK implemented exceptions hierarchy, they stored in `Bitrix24\SDK\Core\Exceptions` folder.
```php
declare(strict_types=1);

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\ServiceBuilderFactory;

require_once 'vendor/autoload.php';
try {
    // init bitrix24-php-sdk service from webhook
    $b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook('INSERT_HERE_YOUR_WEBHOOK_URL');

    // call unknown method and throw  exception
    $b24Service->core->call('Unknown method');
} catch (InvalidArgumentException $exception) {
    print(sprintf('ERROR IN CONFIGURATION OR CALL ARGS: %s', $exception->getMessage()) . PHP_EOL);
    print($exception::class.PHP_EOL);
    print($exception->getTraceAsString());
} catch (Throwable $throwable) {
    print(sprintf('FATAL ERROR: %s', $throwable->getMessage()) . PHP_EOL);
    print($throwable::class.PHP_EOL);
    print($throwable->getTraceAsString());
}
```

## Development

### Update internal documentation: regenerate methods list

1. Create file `/tests/.env.local`
    - set env variable `BITRIX24_WEBHOOK`
    - set env variable `DOCUMENTATION_DEFAULT_TARGET_BRANCH`, default value `blob/master`
2. Call make file

```shell
make build-documentation
```

3. Commit updated file `/docs/EN/Services/bitrix24-php-sdk-methods.md`