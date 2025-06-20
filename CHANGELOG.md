# b24-php-sdk change log

## ⏳ UPCOMING 1.4.0 – 2025.07.01

### Added

- Added service `CRM\Item\Service\ItemDetailsConfiguration` with support methods,
  see [add crm.item.details.* methods](https://github.com/bitrix24/b24phpsdk/issues/168):
    - `getPersonal` method retrieves the settings of item cards for personal user
    - `getGeneral` method retrieves the settings of item cards for all users
    - `resetPersonal` method reset for item user settings
    - `resetGeneral` method reset all card settings for all users
    - `setPersonal` method set card configuration
    - `setGeneral` method set card configuration for all users
    - `setForceCommonConfigForAll` method set common detail form for All Users
- Added service `Services\CRM\Deal\Service\DealRecurring` with support methods,
  see [crm.deal.recurring.* methods](https://github.com/bitrix24/b24phpsdk/issues/160):
    - `fields` returns a list of fields for the recurring deal template
    - `get` returns the settings of the recurring deal template by Id
    - `list` returns a list of recurring deal templates
    - `add` creates a new recurring deal template
    - `delete` deletes a recurring deal template
    - `update` modifies the settings of the recurring deal template
    - `expose` creates a new deal based on the template
- Added service `Services\CRM\Automation\Service\Trigger` with support methods,
  see [add crm.automation.trigger* methods](https://github.com/bitrix24/b24phpsdk/issues/148):
    - `add` add new trigger, with batch calls support
    - `delete` delete trigger, with batch calls support
    - `list`  get list of triggers, with batch calls support
    - `execute` execute trigger, with batch calls support
- Added service `Services\CRM\Currency` with support methods,
  see [Add crm.currency.* methods](https://github.com/bitrix24/b24phpsdk/issues/155):
    - `get` get currency
    - `fields` get currency fields
    - `list` get currency list
    - `add` add new currency, with batch calls support
    - `delete` delete currency, with batch calls support
    - `update`  update currency, with batch calls support
- Added service `Services\CRM\Currency\Localizations` with support methods,
  see [Add crm.currency.* methods](https://github.com/bitrix24/b24phpsdk/issues/155):
    - `set` set localizations, with batch calls support
    - `get` get localizations
    - `fields` get localization fields
    - `delete` delete currency, with batch calls support

### Fixed

- Fixed error in arguments in service for method `placement.bind`, [see details](https://github.com/bitrix24/b24phpsdk/issues/151)
- Fixed errors in `task.elapseditem.*` call in ApiClient [see details](https://github.com/bitrix24/b24phpsdk/issues/180) 
### Changed

- ❗**️️BC** Changed contract `Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountInterface`, this change needs to process corner cases
  when [installed application with UI or without UI](https://github.com/bitrix24/b24phpsdk/issues/150):
    - changed method `public function applicationInstalled(?string $applicationToken): void` application token now is nullable
    - added method `public function setApplicationToken(string $applicationToken): void;`
- ❗**️️BC** Changed contract `Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity\Bitrix24AccountInterface`, this change needs to process corner cases
  when we need to store [multiple accounts from one Bitrix24 portal](https://github.com/bitrix24/b24phpsdk/issues/161).
    - added method `isMasterAccount`
- ❗**️️BC** Changed contract `Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationInterface`, this change needs to process
  corner cases when [installed application with UI or without UI](https://github.com/bitrix24/b24phpsdk/issues/137).
    - added method `setApplicationToken`
    - added method `isApplicationTokenValid`
    - changed method `public function applicationInstalled(?string $applicationToken): void` application token now is nullable
    - changed method `public function applicationUninstalled(?string $applicationToken): void` application token now is nullable

### Statistics

work in progress

## 1.3.0 – 2025.04.23

### Added

- Added **PHP 8.4** [support](https://github.com/bitrix24/b24phpsdk/issues/120) 🚀
- Added method `Bitrix24\SDK\Services\Main\Service::guardValidateCurrentAuthToken` for validate current auth token with
  api-call `app.info` on vendor OAUTH server.
- Added support new scope `entity`
- Added service `Services\Entity\Service\Item` with support methods,
  see [fix entity.item.* methods](https://github.com/bitrix24/b24phpsdk/issues/53):
    - `get` get item, with batch calls support
    - `add` add new item, with batch calls support
    - `delete` delete item, with batch calls support
    - `update`  update item, with batch calls support
- Added service `Services\Entity\Service\Entity` with support methods,
  see [fix entity.* methods](https://github.com/bitrix24/b24phpsdk/issues/53):
    - `get` get entity
    - `add` add new entity
    - `delete` delete entity
    - `update` update entity
    - `rights` get or change access permissions
- Added new application scope nodes `humanresources.hcmlink` and `sign.b2e`
- Added method `Bitrix24\SDK\Core\Credentials\Scope::contains` for check is current scope code contains in scope, for
  task «[split cli commands](https://github.com/bitrix24/b24phpsdk/issues/92)»
- Added method `Bitrix24\SDK\Core\Credentials\Scope::getAvailableScopeCodes` returned all available scope codes, for
  task «[split cli commands](https://github.com/bitrix24/b24phpsdk/issues/92)»
- Added service `Services\CRM\VatRates\Service\Vat` with support methods,
  see [add crm.vat.* methods](https://github.com/bitrix24/b24phpsdk/issues/20):
    - `get` get vat rate by id
    - `add` add new vat rate
    - `delete` delete vat rate
    - `list`  get list of vat rates
    - `update`  update vat rate
- Added service `Services\CRM\Contact\Service\ContactCompany` with support methods,
  see [crm.contact.company.* methods](https://github.com/bitrix24/b24phpsdk/issues/20):
    - `fields` get fields for contact with company connection
    - `setItems` set companies related with contact
    - `get` get companies related with contact
    - `deleteItems` delete all relations for contact
    - `add` add company relation with contact
    - `delete` delete company relation with contact
- Added service `Services\CRM\Requisites\Service\Requisite` with support methods,
  see [crm.requisite.* methods](https://github.com/bitrix24/b24phpsdk/issues/20):
    - `fields` get fields for requisite item
    - `list` get requisites list
    - `get` returns a requisite by the requisite id
    - `add` add requisite
    - `delete` delete requisite and related objects
    - `update` delete requisite
- Added service `Services\CRM\Requisites\Service\RequisitePreset` with support methods,
  see [crm.requisite.preset.* methods](https://github.com/bitrix24/b24phpsdk/issues/20):
    - `fields` get fields for requisite item
    - `list` get requisites list
    - `get` returns a requisite by the requisite id
    - `add` add requisite
    - `countries` get countries list
    - `delete` delete requisite and related objects
    - `update` delete requisite
- Added batch service `Bitrix24\SDK\Services\User\Service\Batch`
  with [support methods](https://github.com/bitrix24/b24phpsdk/issues/103):
    - `add` add (invite) users
    - `get` get users list
      Added service `Services\AI\Engine\Service\Engine` with support methods:
    - `ai.engine.register` - method registers an engine and updates it upon subsequent calls
    - `ai.engine.list` - get the list of ai services
    - `ai.engine.unregister` - Delete registered ai service
      Added class `Bitrix24\SDK\Core\Exceptions\LogicException` for logic exceptions
- Developer experience: added cli command `b24-dev:show-sdk-coverage-statistics` for show actual SDK coverage for
  REST-API, see task «[split cli commands](https://github.com/bitrix24/b24phpsdk/issues/92)»
- Developer experience: added class `Bitrix24\SDK\Deprecations\DeprecatedMethods` with list of
  all [deprecated methods](https://github.com/bitrix24/b24phpsdk/issues/97)
- Developer experience: commands from makefile now run inside docker container `php-cli`
- Developer experience: added cache folder in phpstan config
- Developer experience: added article «[How to Contribute to Bitrix24 PHP SDK](docs/EN/Development/how-to-contribute.md)»

### Changed

- Added nullable argument `$scope` in method `Bitrix24\SDK\Attributes\Services::getSupportedInSdkApiMethods`,
  for task «[split cli commands](https://github.com/bitrix24/b24phpsdk/issues/92)»
- Added class `Bitrix24\SDK\Core\Exceptions\LogicException` for logic exceptions,
  for task «[fix contract tests](https://github.com/bitrix24/b24phpsdk/issues/129)»
- Changed method signature `Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Entity::updateApplicationVersion`, for
  task «[add bitrixUserId and AuthToken](https://github.com/bitrix24/b24phpsdk/issues/115)»
- Developer experience: webhook example moved to
  repository [bitrix24/b24sdk-examples](https://github.com/bitrix24/b24sdk-examples/tree/main/php/quick-start/simple/02-work-with-webhook)

### Fixed

- Fixed errors in `Bitrix24\SDK\Core\Batch` for method
  `user.get`, [see details](https://github.com/bitrix24/b24phpsdk/issues/103)
- Fixed errors in `Bitrix24\SDK\Core\Batch` for methods `entity.item.get` and
  `entity.item.update`, [see details](https://github.com/bitrix24/b24phpsdk/issues/53)
- Fixed errors in `Bitrix24\SDK\Core\ApiClient` for methods with strict arguments
  order, [see details](https://github.com/bitrix24/b24phpsdk/issues/101)
- Fixed errors in `ApplicationInstallationRepositoryInterfaceTest` for work with storage [see details](https://github.com/bitrix24/b24phpsdk/issues/123)
- Fixed errors in `Bitrix24AccountInterfaceTest`, remove some [arguments in constructor](https://github.com/bitrix24/b24phpsdk/issues/141)

### Security

- Added method `Bitrix24\SDK\Services\Main\Service::guardValidateCurrentAuthToken` for validate current auth token with
  api-call `app.info` on vendor OAUTH server. You can validate incoming tokens from placements and events

### Removed

- Developer experience: removed example webhook-error-handling, see
  example [02-work-with-webhook](https://github.com/bitrix24/b24sdk-examples/tree/main/php/quick-start/simple/02-work-with-webhook)

### Statistics

```
Bitrix24 API-methods count: 1146
Supported in bitrix24-php-sdk methods count: 227
Coverage percentage: 19.81% 🚀
Supported in bitrix24-php-sdk methods with batch wrapper count: 29
```

<!--
## Unreleased
### Added
### Changed
### Removed
### Fixed
### Security
-->

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