# b24-php-sdk change log

## 1.2.0 – 2024.12.7

### Added

- Added service `CRM\Company\Service` with support methods,
  see [add crm.company.* methods](https://github.com/bitrix24/b24phpsdk/issues/85):
    - `get` get company by id
    - `add` add new company with batch support
    - `delete` delete company by id with batch support
    - `list`  get list of companies with batch support
    - `update`  update companies with batch support
    - `countByFilter` count companies count with filter
- Added service `CRM\Company\Service\CompanyUserfield` with support methods,
  see [add crm.company.* methods](https://github.com/bitrix24/b24phpsdk/issues/85):
    - `add` add userfield to company
    - `get` get userfield to company
    - `list` list userfields
    - `delete` delete userfield
    - `update` update userfield
- Added service `CRM\Company\Service\CompanyCompanyContact` with support methods,
  see [add crm.company.* methods](https://github.com/bitrix24/b24phpsdk/issues/85):
    - `fields` get fiels for company contact connection
    - `setItems` set contacts related with company
    - `get` get contacts related to company
    - `deleteItems` delete all relations for company
    - `add` add contact relation with company
    - `delete` delete contact relation with company
- Added service `CRM\Company\Service\CompanyDetailsConfiguration` with support methods,
  see [add crm.company.* methods](https://github.com/bitrix24/b24phpsdk/issues/85):
    - `getPersonal` method retrieves the settings of company cards for personal user
    - `getGeneral` method retrieves the settings of company cards for all users
    - `resetPersonal` method reset for item user settings
    - `resetGeneral` method reset all card settings for all users
    - `setPersonal` method set card configuration
    - `setGeneral` method set card configuration for all company
    - `setForceCommonConfigForAll` method set common detail form for All Users
- Added support for events:
    - `OnCrmCompanyAdd`
    - `OnCrmCompanyDelete`
    - `OnCrmCompanyUpdate`
    - `OnCrmCompanyUserFieldAdd`
    - `OnCrmCompanyUserFieldDelete`
    - `OnCrmCompanyUserFieldSetEnumValues`
    - `OnCrmCompanyUserFieldUpdate`
- Added service `CRM\Enum\Service\Enum` with support methods:
    - `activityStatus`
    - `activityNotifyType`
    - `activityPriority`
    - `activityDirection`
    - `activityType`
    - `addressType`
    - `contentType`
    - `orderOwnerTypes`
    - `settingsMode`
    - `fields`
    - `ownerType`
- Added enums:
    - `Bitrix24\SDK\Services\CRM\Enum\AddressType`
    - `Bitrix24\SDK\Services\CRM\Enum\ContentType`
    - `Bitrix24\SDK\Services\CRM\Enum\CrmSettingsMode`
- Added methods for filtration entity fields in `Bitrix24\SDK\Core\Fields\FieldsFilter`:
    - `Bitrix24\SDK\Core\Fields\FieldsFilter::filterUserFields`
    - `Bitrix24\SDK\Core\Fields\FieldsFilter::filterSmartProcessFields`
- Added method `Bitrix24AccountRepositoryInterface::findByApplicationToken` in contracts for
  support «[Delete Application](https://github.com/bitrix24/b24phpsdk/issues/62)» use case
- Added `Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Exceptions\MultipleBitrix24AccountsFoundException`
- Added nullable comments in events `Bitrix24AccountBlockedEvent` and `Bitrix24AccountUnblockedEvent`,
  see [add comment to events](https://github.com/bitrix24/b24phpsdk/issues/79).
- Add result type `Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\File`
- Add exception `Bitrix24\SDK\Core\Exceptions\ItemNotFoundException`
- In `ApiLevelErrorHandler` added processing API response `error_not_found` error code.
- Added fields for `Bitrix24\SDK\Services\CRM\Deal\Result\DealItemResult`:
    - `int|null $ASSIGNED_BY_ID`
    - `array|null $CONTACT_IDS`
    - `int|null $CREATED_BY_ID`
    - `CarbonImmutable $DATE_CREATE`
    - `CarbonImmutable $DATE_MODIFY`
    - `int|null $LAST_ACTIVITY_BY`
    - `CarbonImmutable $LAST_ACTIVITY_TIME`
    - `int|null $MODIFY_BY_ID`
    - `int|null $MOVED_BY_ID`
    - `CarbonImmutable $MOVED_TIME`
- Added service `Bitrix24\SDK\Services\CRM\Userfield\Service\UserfieldConstraints` for check userfield naming rules.
- Developer experience: added example `/examples/local-app-workflows` for demonstrate work
  with [workflows](https://apidocs.bitrix24.com/api-reference/bizproc/index.html).
- Developer experience: added cli make command `make dev-show-fields-description` for show typehints for methods
  arguments from bitrix24 types from *.fields method
- Developer experience: added in CI pipeline check for allowed licenses in composer package dependencies: **only** MIT,
  BSD-3-Clause, Apache.
- Developer experience: added in CI pipeline unit-tests on Windows Server 2022 in GitHub actions and updated
  [installation instructions](https://github.com/bitrix24/b24phpsdk/issues/52) for Windows-based systems.
- Developer experience: start move make commands to docker
- Developer experience: added attribute `Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata` for document service
  builders per scope
- Developer experience: added trait with asserts `Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions` for
  additional checks in php-unit with methods:
    - `assertBitrix24AllResultItemFieldsAnnotated` - for check phpdoc annotations and result of `*.fields` command
    - `assertBitrix24AllResultItemFieldsHasValidTypeAnnotation` - for check phpdoc annotations and bitrix24 custom types
      mapping
- Developer experience: added file `.gitattributes` with config to export data when you use composer flags
  `--prefer-source` and `--prefer-dist`
- Developer experience: start use [PhpCsFixer](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer) in some project folders.

### Changed

- Added nullable argument `bitrix24UserId` in method `Bitrix24AccountRepositoryInterface::findByMemberId` in contracts
  for support use case «[RenewAuthToken](https://github.com/bitrix24/b24phpsdk/issues/63)»
- Developer experience: moved CLI-command `GenerateCoverageDocumentationCommand` to namespace
  `Bitrix24\SDK\Infrastructure\Console\Commands\Documentation`

### Fixed

- Fixed errors in `Bitrix24\SDK\Services\Workflows\Common\WorkflowDocumentId`,
  see [parsing errors](https://github.com/bitrix24/b24phpsdk/issues/54).
- Fixed the problem with mismatch Deals fields in API and SDK, see
  [Increasing code coverage with annotations](https://github.com/bitrix24/b24phpsdk/issues/60).
- Fixed error in `Bitrix24\SDK\Core\Fields\FieldsFilter::filterSystemFields`,
  see [filtration errors](https://github.com/bitrix24/b24phpsdk/issues/65).
- Fixed error in contract tests design,
  see [bitrix24AccountRepositoryInterface has problem with contract tests design - can't add flusher](https://github.com/bitrix24/b24phpsdk/issues/74).
- Fixed error in bitrix24 account contract test data provider,
  see [incorrect data in data provider](https://github.com/bitrix24/b24phpsdk/issues/77).
- ❗️**BC** Fixed typehints and return types in `ActivityItemResult`, `ContactItemResult`,
  see [wrong type hints in ActivityItemResult](https://github.com/bitrix24/b24phpsdk/issues/81)
- Fixed error in method `Bitrix24\SDK\Core\Fields\FieldsFilter::filterSystemFields` for product user fields case.
- ❗️**BC** Fixed typehints and return types in `Bitrix24\SDK\Services\CRM\Lead\Result\LeadItemResult`
  see [wrong typehints in LeadItemResult](https://github.com/bitrix24/b24phpsdk/issues/82):
    - `CURRENCY_ID` `string` → `Currency|null`
    - `OPPORTUNITY` `string` → `Money|null`
    - `IS_MANUAL_OPPORTUNITY` `string` → `bool|null`
    - `OPENED` `string` → `bool|null`
    - `HAS_PHONE` `string` → `bool|null`
    - `HAS_EMAIL` `string` → `bool|null`
    - `HAS_IMOL` `string` → `bool|null`
    - `ASSIGNED_BY_ID` `string` → `int|null`
    - `CREATED_BY_ID` `string` → `int|null`
    - `MODIFY_BY_ID` `string` → `int|null`
    - `MOVED_BY_ID` `string` → `int|null`
    - `DATE_CREATE` `string` → `CarbonImmutable|null`
    - `DATE_MODIFY` `string` → `CarbonImmutable|null`
    - `MOVED_TIME` `string` → `CarbonImmutable|null`
    - `COMPANY_ID` `string` → `int|null`
    - `CONTACT_ID` `string` → `int|null`
    - `CONTACT_IDS` `string` → `array|null`
    - `IS_RETURN_CUSTOMER` `string` → `bool|null`
    - `DATE_CLOSED` `string` → `CarbonImmutable|null`
    - `LAST_ACTIVITY_BY` `string` → `int|null`
    - `LAST_ACTIVITY_TIME` `string` → `CarbonImmutable|null`
- ❗️**BC** Fixed typehints and return types in `Bitrix24\SDK\Services\CRM\Product\Result\ProductItemResult`:
    - `PRICE` `string` → `Money`
    - `CURRENCY_ID` `string` → `Currency`
    - `ACTIVE` `string` → `bool`
    - `VAT_INCLUDED` `string` → `bool`
    - `DATE_CREATE` `string` → `CarbonImmutable`
    - `TIMESTAMP_X` `string` → `CarbonImmutable`
- ❗️**BC** Fixed typehints and return types in `Bitrix24\SDK\Services\CRM\Userfield\Result\AbstractUserfieldItemResult`:
    - `ID` `string` → `int`
    - `SORT` `string` → `int`
    - `MULTIPLE` `string` → `bool`
    - `MANDATORY` `string` → `bool`
    - `SHOW_FILTER` `string` → `bool`
    - `SHOW_IN_LIST` `string` → `bool`
    - `EDIT_IN_LIST` `string` → `bool`
    - `IS_SEARCHABLE` `string` → `bool`

### Deprecated

- Deprecated class `RemoteEventsFabric` use `RemoteEventsFactory`
- Deprecated class `ApplicationLifeCycleEventsFabric` use `ApplicationLifeCycleEventsFactory`
- Deprecated class `TelephonyEventsFabric` use `TelephonyEventsFactory`

### Statistics

```
Bitrix24 API-methods count: 1135
Supported in bitrix24-php-sdk methods count: 191
Coverage percentage: 16.83% 🚀
Supported in bitrix24-php-sdk methods with batch wrapper count: 22
```

<!--
## Unreleased
### Added
### Changed
### Removed
### Fixed
### Security
-->

## 1.1.0 – 2024.09.25

### Added

- Added class `Bitrix24\SDK\Services\RemoteEventsFabric` for simple work with builtin Bitrix24 events. You can create
  Bitrix24 events from `Symfony\Component\HttpFoundation\Request` object. If event is not supported in SDK, fabric will
  create `Bitrix24\SDK\Core\Requests\Events\UnsupportedRemoteEvent` with generic interface
  `Bitrix24\SDK\Core\Contracts\Events\EventInterface` without typehints. Every event checked with valid
  `application_token` signature.
- Added method `Bitrix24\SDK\Services\ServiceBuilderFactory::createServiceBuilderFromWebhook` for simple work with
  webhook, see [add super-simple kick-off guide](https://github.com/bitrix24/b24phpsdk/issues/17).
- Added method `Bitrix24\SDK\Services\ServiceBuilderFactory::createServiceBuilderFromPlacementRequest` for simple work
  with placement request, see [add super-simple kick-off guide](https://github.com/bitrix24/b24phpsdk/issues/17).
- Added `Bitrix24\SDK\Core\Contracts\Events\EventsFabricInterface` for scope-based event fabrics.
- Added `Bitrix24\SDK\Core\Requests\Events\UnsupportedEvent` as a default event container object for unsupported in SDK
  Bitrix24 events.
- Added helpers for build local application in namespace `Bitrix24\SDK\Application\Local`:
    - `Local\Entity\LocalAppAuth`: auth data for local application. Contains: `AuthToken`, `domainUrl` and
      `applicationToken`.
    - `Local\Infrastructure\Filesystem\AppAuthFileStorage`: class for store LocalAppAuth in file
    - `Local\Repository\LocalAppAuthRepositoryInterface`: interface for LocalAppAuthRepository.
- Developer experience: added example `/examples/local-app-with-token-storage` for demonstrate all options for work with
  SDK and created local
  application skeleton.
- Developer experience: added example `/examples/webhook-error-handling` for demonstrate exceptions handling.
- Developer experience: added example `/examples/local-app-placement` for demonstrate work with placements.
- Added `WrongClientException` for handle errors with wrong application client configuration.
- Added `PaymentRequiredException` for handle errors with expired subscription.
- Added `WrongConfigurationException` for handle errors with wrong application infrastructure configuration.
- Added `WrongSecuritySignatureException` for handle errors
  with [wrong signature events](https://apidocs.bitrix24.com/api-reference/events/safe-event-handlers.html) with
  `application_token`.
- Added checks for empty string in args for constructor `Bitrix24\SDK\Core\Credentials\ApplicationProfile`
- Added class `Bitrix24\SDK\Application\Requests\Events\ApplicationLifeCycleEventsFabric`
- Documentation: added section [Basic necessary knowledge](docs/EN/README.md)
  in [SDK documentation](https://github.com/bitrix24/b24phpsdk/issues/35)
- Documentation: added section [Call unsupported methods](docs/EN/README.md)
  in [SDK documentation](https://github.com/bitrix24/b24phpsdk/issues/15)
- Developer experience: add issue template [Ship new SDK release](https://github.com/bitrix24/b24phpsdk/issues/42)

### Changed

- ❗️moved interface `EventInterface` from `Bitrix24\SDK\Application\Requests\Events` to
  `Bitrix24\SDK\Core\Contracts\Events`
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
- Changed method name `Bitrix24\SDK\Services\ServiceBuilderFactory::initFromRequest` to
  `Bitrix24\SDK\Services\ServiceBuilderFactory::init`

### Fixed

- Fixed variable names in `Bitrix24\SDK\Services\ServiceBuilderFactory::initFromRequest`,
  see [wrong variable name](https://github.com/bitrix24/b24phpsdk/issues/30).
- Fixed some corner cases in `Bitrix24\SDK\Core\ApiLevelErrorHandler`

<!--
### Deprecated

### Removed



### Security
-->

## 1.0

* Initial release