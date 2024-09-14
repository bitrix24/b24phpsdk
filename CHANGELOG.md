# b24-php-sdk change log

<!--
## Unreleased
### Added
### Changed
### Deprecated
### Removed
### Fixed
### Security
-->

## 1.1.0 – 2024.09.15

### Added

- Added method `Bitrix24\SDK\Services\ServiceBuilderFactory::createServiceBuilderFromWebhook` for simple work with
  webhook, see [add super-simple kick-off guide](https://github.com/bitrix24/b24phpsdk/issues/17).
- Added method `Bitrix24\SDK\Services\ServiceBuilderFactory::createServiceBuilderFromPlacementRequest` for simple work
  with placement request, see [add super-simple kick-off guide](https://github.com/bitrix24/b24phpsdk/issues/17).
- Added helpers for build local application in namespace `Bitrix24\SDK\Application\Local`:
  - `Local\Entity\LocalAppAuth`: auth data for local application. Contains: `AuthToken`, domainUrl and applicationToken.
  - `Local\Infrastructure\Filesystem\AppAuthFileStorage`: class for store LocalAppAuth in file
  - `Local\Repository\LocalAppAuthRepositoryInterface`: interface for LocalAppAuthRepository.
- Added example `/examples/local-app-with-token-storage` for demonstrate all options for work with SDK. 
- Added `WrongClientException` for handle errors with wrong application client configuration. 
- Added `WrongConfigurationException` for handle errors with wrong application infrastructure configuration. 
- Added checks for empty string in args for constructor `Bitrix24\SDK\Core\Credentials\ApplicationProfile`
- Added checks for empty string in args for constructor `Bitrix24\SDK\Core\Credentials\ApplicationProfile`
- Added class `Bitrix24\SDK\Application\Requests\Events\ApplicationLifeCycleEventsFabric`
- Documentation: added section [Basic necessary knowledge](docs/EN/documentation.md) in SDK documentation

### Changed
- Changed order in expired_token case: 
  - old: get new auth token → repeat api-call → emit event `Bitrix24\SDK\Events\AuthTokenRenewedEvent` for store token
  - new: get new auth token → emit event `Bitrix24\SDK\Events\AuthTokenRenewedEvent` for store token → repeat api-call
- Changed dependencies for `Bitrix24\SDK\Core\ApiClient` - added class `Bitrix24\SDK\Core\ApiLevelErrorHandler`
- Changed scope for properties `Bitrix24\SDK\Core\Credentials\ApplicationProfile` - mark as public
- Changed scope for properties `Bitrix24\SDK\Core\Credentials\AuthToken` - mark as public
- Changed example for work with webhook in [README.md](README.md) file and directory `/examples/webhook/`
- Changed example for work with local application in [README.md](README.md) file and directory
  `/examples/local-application/`
- Changed bitrix24-php-sdk version in headers in class `Bitrix24\SDK\Core\ApiClient`,
  see [wrong API-client and sdk version in headers](https://github.com/bitrix24/b24phpsdk/issues/13).
- Changed scope for property `core` in `Bitrix24\SDK\Services\AbstractServiceBuilder` - for better DX,
  see [Make core public in service builder](https://github.com/bitrix24/b24phpsdk/issues/26).
- Changed method name `Bitrix24\SDK\Services\ServiceBuilderFactory::initFromRequest` to `Bitrix24\SDK\Services\ServiceBuilderFactory::init`
### Fixed

- Fixed variable names in `Bitrix24\SDK\Services\ServiceBuilderFactory::initFromRequest`,
  see [wrong variable name](https://github.com/bitrix24/b24phpsdk/issues/30).

<!--
### Deprecated

### Removed



### Security
-->

## 1.0

* Initial release