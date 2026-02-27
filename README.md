Bitrix24 REST API PHP SDK
================
[![Total Downloads](https://img.shields.io/packagist/dt/bitrix24/b24phpsdk.svg)](https://packagist.org/packages/bitrix24/b24phpsdk)
[![Latest Stable Version](https://img.shields.io/packagist/v/bitrix24/b24phpsdk.svg)](https://packagist.org/packages/bitrix24/b24phpsdk)

An official PHP library for the Bitrix24 REST API

## SDK Versions

This library ships two major versions that coexist on separate branches:

| | v1 (`main` branch) | v3 (`v3` branch) |
|---|---|---|
| **PHP** | 8.2, 8.3, 8.4 | 8.4, 8.5 |
| **API endpoints** | `{portal}/rest/{user_id}/{token}/{method}` | `{portal}/rest/api/{user_id}/{token}/{method}` |
| **New REST methods** | — | ✅ |
| **Breaking changes** | No | ✅ |
| **Semver** | `1.*` | `3.*` |
| **Status** | Stable / production-ready | Active development |

**Which version should I use?**

- **v1** — choose this for PHP 8.2–8.4 projects, production deployments, or when you don't need the newest Bitrix24 API methods.
- **v3** — choose this for PHP 8.4+ projects that need access to new REST API methods and are comfortable adopting breaking changes.

## Build status

| CI\CD check | [`main`](https://github.com/bitrix24/b24phpsdk/actions?query=branch%3Amain) | [`v3`](https://github.com/bitrix24/b24phpsdk/actions?query=branch%3Av3) |
|---|---|---|
| allowed licenses | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/license-check.yml/badge.svg?branch=main)](https://github.com/bitrix24/b24phpsdk/actions/workflows/license-check.yml) | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/license-check.yml/badge.svg?branch=v3)](https://github.com/bitrix24/b24phpsdk/actions/workflows/license-check.yml) |
| php-cs-fixer | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/php-cs-fixer.yml/badge.svg?branch=main)](https://github.com/bitrix24/b24phpsdk/actions/workflows/php-cs-fixer.yml) | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/php-cs-fixer.yml/badge.svg?branch=v3)](https://github.com/bitrix24/b24phpsdk/actions/workflows/php-cs-fixer.yml) |
| phpstan | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/phpstan.yml/badge.svg?branch=main)](https://github.com/bitrix24/b24phpsdk/actions/workflows/phpstan.yml) | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/phpstan.yml/badge.svg?branch=v3)](https://github.com/bitrix24/b24phpsdk/actions/workflows/phpstan.yml) |
| rector | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/rector.yml/badge.svg?branch=main)](https://github.com/bitrix24/b24phpsdk/actions/workflows/rector.yml) | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/rector.yml/badge.svg?branch=v3)](https://github.com/bitrix24/b24phpsdk/actions/workflows/rector.yml) |
| deptrac | — | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/deptrac.yml/badge.svg?branch=v3)](https://github.com/bitrix24/b24phpsdk/actions/workflows/deptrac.yml) |
| unit tests | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/phpunit.yml/badge.svg?branch=main)](https://github.com/bitrix24/b24phpsdk/actions/workflows/phpunit.yml) | [![](https://github.com/bitrix24/b24phpsdk/actions/workflows/phpunit.yml/badge.svg?branch=v3)](https://github.com/bitrix24/b24phpsdk/actions/workflows/phpunit.yml) |

Integration tests run in GitHub actions with real Bitrix24 portal

## Installation

Install the stable v1 version (PHP 8.2+):

```bash
composer require bitrix24/b24phpsdk:"^1.0"
```

Install the new v3 version (PHP 8.4+, breaking changes):

```bash
composer require bitrix24/b24phpsdk:"^3.0"
```

If you work on Windows:
- please use [WSL - Windows Subsystem for Linux](https://learn.microsoft.com/en-us/windows/wsl/)
- if your filesystem is NTFS, you can disable the flag `git config --global core.protectNTFS false` for checkout folders starting with a dot.

## Branch status

| Branch | Purpose |
|---|---|
| `main` | Stable v1.x production releases |
| `dev` | v1.x integration and pre-release testing |
| `v3` | Stable v3.x production releases |
| `v3-dev` | Active v3 development with breaking changes |

Each major version has its own `dev` branch. Cross-version changes are applied via cherry-pick — branches are never merged across major versions.

## B24PhpSdk ✨FEATURES✨

Support both auth modes:

- [x] work with auth tokens for mass-market Bitrix24 applications
- [x] work with incoming webhooks for simple integration projects for a single Bitrix24 account

Domain core events:

- [x] Access Token expired
- [x] Url of a Bitrix24 account domain changed

API - level features

- [x] Auto renew access tokens
- [x] List queries with «start=-1» support
- [ ] offline queues

Performance improvements 🚀

- [x] Batch queries implemented with [PHP Generators](https://www.php.net/manual/en/language.generators.overview.php) –
  constant low memory and low CPI usage:
- [x] batch read data from bitrix24
- [x] batch write data to bitrix24
- [x] read without count flag

## Development principles

- Good developer experience
    - auto-completion of methods at the IDE
    - typed method call signatures
    - typed results of method calls
    - helpers for typical operations
- Good documentation
    - documentation on the operation of a specific method containing a link to the official documentation
    - documentation for working with the SDK
- Performance first:
    - minimal impact on client code
    - ability to work with large amounts of data with constant memory consumption
    - efficient operation of the API using batch requests
- Modern technology stack
    - based on [Symfony HttpClient](https://symfony.com/doc/current/http_client.html)
    - actual PHP versions language features
- Reliable:
    - test coverage: unit, integration, contract
    - typical examples typical for different modes of operation and they are optimized for memory \ performance

## Architecture

### Abstraction layers

```
- http2 protocol via json data structures
- symfony http client
- \Bitrix24\SDK\Core\ApiClient - work with b24 rest-api endpoints
    input: arrays \ strings
    output: Symfony\Contracts\HttpClient\ResponseInterface, operate with strings
    process: network operations 
- \Bitrix24\SDK\Services\* - work with b24 rest-api entities
    input: arrays \ strings
    output: b24 response dto
    process: b24 entities, operate with immutable objects  
```

## Documentation

- [Bitrix24 API documentation - English](https://apidocs.bitrix24.com/)

## Requirements

- php: >=8.2
- ext-json: *
- ext-curl: *

## Examples

### Work with webhook

1. Go to `/examples/webhook` folder
2. Open console and install dependencies

```shell
composer install
```

3. Open Bitrix24 account: Developer resources → Other → Inbound webhook
4. Open example file and insert webhook url into `$webhookUrl`

```php
declare(strict_types=1);

use Bitrix24\SDK\Services\ServiceBuilderFactory;

require_once 'vendor/autoload.php';

// init bitrix24-php-sdk service from webhook
$b24Service = ServiceBuilderFactory::createServiceBuilderFromWebhook('INSERT_HERE_YOUR_WEBHOOK_URL');

// call some method
var_dump($b24Service->getMainScope()->main()->getApplicationInfo()->applicationInfo());
// call core for method in not implemented service
var_dump($b24Service->core->call('user.current'));

```

5. Call php file in shell

```shell
php -f example.php
```

### Work with local application

1. Go to `/examples/local-app` folder
2. Open console and install dependencies

```shell
composer install
```

3. Start local development server

```shell
sudo php -S 127.0.0.1:80
```

4. Expose local server to public via [ngrok](https://ngrok.com/) and remember temporally public url –
   `https://****.ngrok-free.app`

```shell
ngrok http 127.0.0.1
```

5. Check public url from ngrok and see `x-powered-by` header with **200** status-code.

```shell
curl https://****.ngrok-free.app -I
HTTP/2 200 
content-type: text/html; charset=UTF-8
date: Mon, 26 Aug 2024 19:09:24 GMT
host: ****.ngrok-free.app
x-powered-by: PHP/8.3.8
```

6. Open Bitrix24 account: Developer resources → Other → Local application and create new local application:
    - `type`: server
    - `handler path`: `https://****.ngrok-free.app/index.php`
    - `Initial installation path`: `https://****.ngrok-free.app/install.php`
    - `Menu item text`: `Test local app`
    - `scope`: `crm`
7. Save application parameters in `index.php` file:
    - `Application ID (client_id)` — `BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID`
    - `Application key (client_secret)` — `BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET`
    - `Assing permitions (scope)` — `BITRIX24_PHP_SDK_APPLICATION_SCOPE`

<details>
  <summary>see index.php file</summary>

```php
declare(strict_types=1);

use Bitrix24\SDK\Core\Credentials\ApplicationProfile;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Symfony\Component\HttpFoundation\Request;

require_once 'vendor/autoload.php';
?>
    <pre>
    Application is worked, auth tokens from bitrix24:
    <?= print_r($_REQUEST, true) ?>
</pre>
<?php

$appProfile = ApplicationProfile::initFromArray([
    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_ID' => 'INSERT_HERE_YOUR_DATA',
    'BITRIX24_PHP_SDK_APPLICATION_CLIENT_SECRET' => 'INSERT_HERE_YOUR_DATA',
    'BITRIX24_PHP_SDK_APPLICATION_SCOPE' => 'INSERT_HERE_YOUR_DATA'
]);

$b24Service = ServiceBuilderFactory::createServiceBuilderFromPlacementRequest(Request::createFromGlobals(), $appProfile);

var_dump($b24Service->getMainScope()->main()->getCurrentUserProfile()->getUserProfile());
```

</details>
8. Save local application in Bitrix24 tab and press «OPEN APPLICATION» button.    

### Create application for Bitrix24 marketplace

if you want to create application you can use production-ready contracts in namespace
`Bitrix24\SDK\Application\Contracts`:

- `Bitrix24Accounts` — Store auth tokens and
  provides [methods](src/Application/Contracts/Bitrix24Accounts/Docs/Bitrix24Accounts.md) for work with Bitrix24
  account.
- `ApplicationInstallations` — Store information
  about [application installation](src/Application/Contracts/ApplicationInstallations/Docs/ApplicationInstallations.md),
  linked with Bitrix24 Account with auth
  tokens. Optional can store links to:
    - Client contact person: client person who responsible for application usage
    - Bitrix24 Partner contact person: partner contact person who supports client and configure application
    - Bitrix24 Partner: partner who supports client portal
- `ContactPersons` – Store information [about person](src/Application/Contracts/ContactPersons/Docs/ContactPersons.md)
  who installed application.
- `Bitrix24Partners` – Store information
  about [Bitrix24 Partner](src/Application/Contracts/Bitrix24Partners/Docs/Bitrix24Partners.md) who supports client
  portal and install or configure application.

Steps:

1. Create own entity of this bounded contexts.
2. Implement all methods in contract interfaces.
3. Test own implementation behavior with contract-tests `tests/Unit/Application/Contracts/*` – examples.

## Tests

Tests locate in folder `tests` and we have two test types.
In folder tests create file `.env.local` and fill environment variables from `.env`.

### PHP Static Analysis Tool – phpstan

Call in command line

```shell
make lint-phpstan
```

### PHP Static Analysis Tool – rector

Call in command line for validate

```shell
make lint-rector
```

Call in command line for fix codebase

```shell
make lint-rector-fix
```

### Unit tests

**Fast**, in-memory tests without a network I\O For run unit tests you must call in command line

```shell
make test-unit
```

### Integration tests

**Slow** tests with full lifecycle with your **test** Bitrix24 account via webhook.

❗️Do not run integration tests with production Bitrix24 accounts

For running integration test you must:

1. Create new Bitrix24 account for development tests.
2. Go to left menu, click «Sitemap».
3. Find menu item «Developer resources».
4. Click on menu item «Other».
5. Click on menu item «Inbound webhook».
6. Assign all permisions with webhook and click «save» button.
7. Create file `/tests/.env.local` with same settings, see comments in `/tests/.env` file.

```dotenv
BITRIX24_WEBHOOK=https://your-portal.bitrix24.com/rest/1/your-webhook-token/
INTEGRATION_TEST_LOG_LEVEL=100
```

8. call in command line

```shell
make test-integration-core
make test-integration-scope-telephony
make test-integration-scope-workflows
make test-integration-scope-user
```

## Submitting bugs and feature requests

Bugs and feature request are tracked on [GitHub](https://github.com/bitrix24/b24phpsdk/issues)

## Thanks to all the people who contributed so far!

<a href="https://github.com/bitrix24/b24phpsdk/graphs/contributors">
  <img src="https://contributors-img.web.app/image?repo=bitrix24/b24phpsdk" />
</a>

## License

B24PhpSdk is licensed under the MIT License - see the `MIT-LICENSE.txt` file for details
