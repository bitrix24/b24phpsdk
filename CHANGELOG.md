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

## 1.1.0 – 2024.09.

### Added

- Added method `Bitrix24\SDK\Services\ServiceBuilderFactory::createServiceBuilderFromWebhook` for simple work with
  webhook, see [add super-simple kick-off guide](https://github.com/bitrix24/b24phpsdk/issues/17).
- Added method `Bitrix24\SDK\Services\ServiceBuilderFactory::createServiceBuilderFromPlacementRequest` for simple work
  with placement request, see [add super-simple kick-off guide](https://github.com/bitrix24/b24phpsdk/issues/17).

### Changed

- Changed scope for properties `Bitrix24\SDK\Core\Credentials\ApplicationProfile` - mark as public
- Changed example for work with webhook in [README.md](README.md) file and directory `/examples/webhook/`
- Changed example for work with local application in [README.md](README.md) file and directory
  `/examples/local-application/`

<!--
### Deprecated

### Removed

### Fixed

### Security
-->

## 1.0

* Initial release