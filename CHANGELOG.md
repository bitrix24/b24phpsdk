# b24-php-sdk change log
## 3.4.0 – UNRELEASED

### Added

### Changed

### Fixed

## 3.3.0

### Added

- Added offline-events `auth_connector` support: `Core\Contracts\CoreInterface::setAuthConnector()`
  (and `Core\CoreBuilder::withAuthConnector()`) auto-injects the parameter into every request and
  every batch sub-command to avoid offline-event cycles; new `Services\Main\Service\OfflineEvent`
  wraps `event.offline.get`, `event.offline.list`, `event.offline.clear`, `event.offline.error`;
  `Services\Main\Service\Event::bind()` and `unbind()` gained `event_type` (online|offline) and
  `auth_connector` support ([#386](https://github.com/bitrix24/b24phpsdk/issues/386))
- Added `Services\Booking\BookingServiceBuilder` with Booking scope wrappers and integration coverage for `booking.v1.clienttype.*`, `booking.v1.resourceType.*`, `booking.v1.resource.*`, `booking.v1.resource.slots.*`, `booking.v1.waitlist.*`, `booking.v1.waitlist.client.*`, `booking.v1.waitlist.externalData.*`, `booking.v1.booking.*`, `booking.v1.booking.client.*`, and `booking.v1.booking.externalData.*` methods ([#473](https://github.com/bitrix24/b24phpsdk/issues/473))
- Added `Services\Timeman` service with support for workday tracking methods,
  see [timeman.* methods](https://apidocs.bitrix24.com/api-reference/timeman/index.html):
    - `open` — starts a new workday or continues after pause/close
    - `pause` — pauses the current workday
    - `close` — closes the current workday
    - `status` — gets current workday status
    - `settings` — gets user's work time settings
      ([#484](https://github.com/bitrix24/b24phpsdk/issues/484))
- Added service `Services\Biconnector\Dataset` with support methods,
  see [biconnector.dataset.* methods](https://apidocs.bitrix24.com/api-reference/biconnector/dataset/index.html) ([#469](https://github.com/bitrix24/b24phpsdk/issues/469)):
    - `add` adds a new dataset, with batch calls support
    - `update` updates an existing dataset description, with batch calls support
    - `get` gets information about the dataset by its identifier
    - `list` gets the list of datasets, with batch calls support
    - `delete` deletes a dataset, with batch calls support
    - `fields` returns the fields description
    - `updateFields` adds, updates visibility of, or deletes individual dataset columns (`biconnector.dataset.fields.update`)
    - `count` counts datasets
- Added `dataset()` accessor to `BiconnectorServiceBuilder` ([#469](https://github.com/bitrix24/b24phpsdk/issues/469))
- Added service `Services\Biconnector\Source` with support methods,
  see [biconnector.source.* methods](https://apidocs.bitrix24.com/api-reference/biconnector/source/index.html) ([#469](https://github.com/bitrix24/b24phpsdk/issues/469)):
    - `add` adds a new data source, with batch calls support
    - `update` updates an existing data source, with batch calls support
    - `get` gets information about the data source by its identifier
    - `list` gets the list of data sources, with batch calls support
    - `delete` deletes a data source, with batch calls support
    - `fields` returns the fields description
    - `count` counts data sources
- Added `source()` accessor to `BiconnectorServiceBuilder` ([#469](https://github.com/bitrix24/b24phpsdk/issues/469))
- Added service `Services\Biconnector\Connector` with support methods,
  see [biconnector.connector.* methods](https://github.com/bitrix24/b24phpsdk/issues/469):
    - `add` adds a new connector, with batch calls support
    - `update` updates an existing connector, with batch calls support
    - `get` gets information about the connector by its identifier
    - `list` gets the list of connectors, with batch calls support
    - `delete` deletes a connector, with batch calls support
    - `fields` returns the fields description
    - `count` counts connectors
- Added `Bitrix24\SDK\Services\Biconnector\BiconnectorServiceBuilder` with `connector()` accessor ([#469](https://github.com/bitrix24/b24phpsdk/issues/469))
- Added `Bitrix24\SDK\Services\ServiceBuilder::getBiconnectorScope()` method ([#469](https://github.com/bitrix24/b24phpsdk/issues/469))

### Changed

- Committed `composer.lock` and pinned `symfony/*` dev/CI dependencies to the 8.0.x line so the
  CI PHPStan (1.x) job is no longer broken by Symfony 8.1 PHP 8.4 property hooks; library runtime
  version constraints in `composer.json` are unchanged ([#386](https://github.com/bitrix24/b24phpsdk/issues/386))

### Fixed

- Fixed README "Examples" section that pointed to non-existent `/examples/webhook` and
  `/examples/local-app` folders; instructions are now self-contained and reference
  `tests/ApplicationBridge` for the local application ([#474](https://github.com/bitrix24/b24phpsdk/issues/474))
- Fixed malformed `event_type` request parameter key (it contained a tab character, so the value
  never reached the API) in `Services\Main\Service\Event::bind()` and `unbind()` ([#386](https://github.com/bitrix24/b24phpsdk/issues/386))

### Statistics

```
Bitrix24 API-methods count: 1171
Supported in bitrix24-php-sdk methods count: 978
Coverage percentage: 83.52% 🚀
Supported in bitrix24-php-sdk methods with batch wrapper count: 124
```

## 3.2.0

### Added

- Added `Bitrix24\SDK\Services\IM\Department\Service\Department` service wrapping `im.department.get`, `im.department.colleagues.list`, `im.department.employees.get`, and `im.department.managers.get`, with typed department/user result wrappers and `IMServiceBuilder::department()` accessor ([#432](https://github.com/bitrix24/b24phpsdk/issues/432))
- Added `Bitrix24\SDK\Services\IM\Disk\Service\Disk` with `getFolderId(?int $chatId = null, ?string $dialogId = null)` for `im.disk.folder.get`, plus dedicated `FolderIdResult`, IM builder registration, and focused unit/integration coverage ([#435](https://github.com/bitrix24/b24phpsdk/issues/435))
- Added IM Disk file operations to `Bitrix24\SDK\Services\IM\Disk\Service\Disk`: `commitFile`, `deleteFile`, `saveFile`, and `shareRecord`, with dedicated result wrappers and live IM Disk integration coverage ([#482](https://github.com/bitrix24/b24phpsdk/issues/482))
- Added `Bitrix24\SDK\Services\IM\Chat\Service\ChatUser` service wrapping `im.chat.user.add`, `im.chat.user.delete`, `im.chat.user.list` for chat participant management, with `ChatUserListResult` and `IMServiceBuilder::chatUser()` accessor ([#424](https://github.com/bitrix24/b24phpsdk/issues/424))
- Added `Bitrix24\SDK\Services\IM\Search\Service\Search` service wrapping `im.search.chat.list`, `im.search.user.list`, `im.search.department.list`, and deprecated legacy `im.search.last.*` methods, with typed search result wrappers and `IMServiceBuilder::search()` accessor ([#431](https://github.com/bitrix24/b24phpsdk/issues/431))
- Added `Bitrix24\SDK\Services\IM\Recent\Service\Recent` service wrapping `im.recent.get`, `im.recent.list`, `im.recent.pin`, `im.recent.unread`, and `im.recent.hide`, with `RecentItemResult`/`RecentsResult` and `IMServiceBuilder::recent()` accessor ([#427](https://github.com/bitrix24/b24phpsdk/issues/427))
- Added `Bitrix24\SDK\Services\IM\Revision\Service\Revision` service wrapping `im.revision.get` for IM module API revision/compatibility checks, with `RevisionItemResult` (`rest`, `web`, `mobile`, `desktop`, `im_revision_mobile` fields) and `IMServiceBuilder::revision()` accessor ([#434](https://github.com/bitrix24/b24phpsdk/issues/434))
- Added `IM\Counters` service with `im.counters.get` support for retrieving unread message and notification counters ([#433](https://github.com/bitrix24/b24phpsdk/issues/433))
- Added `getLogoUrl()` and `changeLogoUrl()` methods to `Bitrix24PartnerInterface` and reference implementation with `Bitrix24PartnerLogoUrlChangedEvent` ([#452](https://github.com/bitrix24/b24phpsdk/issues/452))
- Added typed fluent `Services\IM\Message\Attach` payload builders for `ATTACH` blocks in `im.message.add` and `im.message.update`, plus `RawAttach::fromArray()` as an object-based escape hatch for unsupported or vendor-extended payload shapes ([#426](https://github.com/bitrix24/b24phpsdk/issues/426))
- Added `Bitrix24\SDK\Services\IM\Placements\PlacementLocationCodes` with constants `IM_TEXTAREA`, `IM_SIDEBAR`, `IM_CONTEXT_MENU`, `IM_NAVIGATION`, and `IM_SMILES_SELECTOR` (deprecated since `im 25.1600.0`) for IM widget placement codes ([#437](https://github.com/bitrix24/b24phpsdk/issues/437))
- Added `PlacementOptionsInterface` and fluent option builders `TextareaPlacementOptions`, `SidebarPlacementOptions`, `ContextMenuPlacementOptions` under `Bitrix24\SDK\Services\IM\Placements` namespace, backed by `ChatContext`, `PlacementColor` (IM-specific) and shared `Role`, `ExtranetAvailability` string-backed enums under `Bitrix24\SDK\Services\Placement` ([#437](https://github.com/bitrix24/b24phpsdk/issues/437))
- Added integration test `PlacementLocationCodesTest` that asserts (via reflection) every `IM_`-prefixed code returned by `placement.list` is declared as a constant in `PlacementLocationCodes` ([#437](https://github.com/bitrix24/b24phpsdk/issues/437))
- Added `Bitrix24\SDK\Services\IM\Chat\Service\Chat` service wrapping `im.chat.add`, `im.chat.get`, `im.chat.leave`, `im.chat.mute`, `im.chat.setOwner`, `im.chat.updateAvatar`, `im.chat.updateColor`, `im.chat.updateTitle`, with enums `ChatType`, `ChatColor`, `ChatEntityType`, result types `ChatItemResult`/`ChatResult`, and `IMServiceBuilder::chat()` accessor ([#423](https://github.com/bitrix24/b24phpsdk/issues/423))
- Added `Bitrix24\SDK\Services\IM\Dialog\Service\Dialog` service for `im.dialog.*` support, with typed result wrappers, `IMServiceBuilder::dialog()`, and dedicated unit/integration/annotation coverage ([#425](https://github.com/bitrix24/b24phpsdk/issues/425))
- Added `Bitrix24\SDK\Services\IM\User\Service\User` service for `im.user.get` and `im.user.list.get` support, with typed result wrappers `UserResult`/`UsersResult`/`UserItemResult`, `IMServiceBuilder::user()` accessor, and unit/integration/annotation test coverage ([#429](https://github.com/bitrix24/b24phpsdk/issues/429))
- Added `b24-dev:result-item-generator` developer command for staged ResultItem payload build, verification, apply, and generation workflows backed by OpenAPI, REST documentation, and live API metadata ([#425](https://github.com/bitrix24/b24phpsdk/issues/425))
- Added `Services\IM\Message\Service\Message` service for `im.message.*` support ([#426](https://github.com/bitrix24/b24phpsdk/issues/426)):
    - `add` — send a message (`im.message.add`)
    - `update` — edit text and parameters (`im.message.update`)
    - `delete` — delete a message (`im.message.delete`)
    - `like` — toggle the Like mark (`im.message.like`), with typed `LikeAction` enum (`auto`/`plus`/`minus`)
    - `share` — create an object from a message (`im.message.share`), with typed `ShareType` enum (`CHAT`/`TASK`/`POST`/`CALEND`)
    - `command` — invoke a chat-bot command (`im.message.command`)
- Added `IMServiceBuilder::message()` accessor and cached service instance ([#426](https://github.com/bitrix24/b24phpsdk/issues/426))
- Added `Bitrix24\SDK\Services\IM\User\Service\UserStatus` service wrapping `im.user.status.get`, `im.user.status.set`, `im.user.status.idle.start`, and `im.user.status.idle.end`, with `UserStatusType` enum and `UserStatusResult`; exposed via `IMServiceBuilder::userStatus()` ([#430](https://github.com/bitrix24/b24phpsdk/issues/430))
- Extended `IM\Notify` service with `send` (`im.notify`), `getList` (`im.notify.get`), `historySearch` (`im.notify.history.search`), `markAllAsRead` (`im.notify.read.all`), `getSchema` (`im.notify.schema.get`) methods; refactored `markMessagesAsRead`/`markMessagesAsUnread` to call `im.notify.read.list` instead of `im.notify.read`; added `NotifyItemResult`, `NotifiesResult`, `NotifyHistorySearchResult`, `NotifyReadAllResult`, `NotifySchemaItemResult`, `NotifySchemaResult` result types ([#428](https://github.com/bitrix24/b24phpsdk/issues/428))



### Changed

- Removed the duplicate `bitrix24PartnerNumber` uniqueness expectation from the `Bitrix24PartnerRepositoryInterface` contract so `save()` remains a persistence operation; uniqueness validation belongs in the use-case layer ([#468](https://github.com/bitrix24/b24phpsdk/issues/468))
- Removed dead `delete(Uuid $uuid)` method from `Bitrix24PartnerRepositoryInterface`, its in-memory stub implementation, and the `testDelete` contract test — the soft-delete flow (`markAsDeleted()` + `save()`) makes this method redundant ([#471](https://github.com/bitrix24/b24phpsdk/issues/471))
- Replaced `set*` prefix with `change*` in `Bitrix24PartnerInterface` mutator methods (`changeTitle`, `changeSite`, `changePhone`, `changeEmail`, `changeOpenLineId`, `changeExternalId`) to better express domain-level change operations ([#453](https://github.com/bitrix24/b24phpsdk/issues/453))
- Deprecated passing `ATTACH` as raw JSON `string` to `im.message.add` and `im.message.update`; prefer `AttachPayloadInterface` for typed object payloads or raw `array` payloads for backward-compatible structures ([#426](https://github.com/bitrix24/b24phpsdk/issues/426))
- Widened `Placement::bind()` `$options` parameter type to `PlacementOptionsInterface|array` — existing array callers remain fully compatible ([#437](https://github.com/bitrix24/b24phpsdk/issues/437))
- Regenerated `DialogItemResult` from the ResultItem generator payload, including imported `CarbonImmutable` PHPDoc types and live-verified nullable fields ([#425](https://github.com/bitrix24/b24phpsdk/issues/425))
- Regenerated IM `ChatItemResult`, `DialogUserItemResult`, and `DialogReadStateItemResult` from ResultItem generator payloads and extended the generator workflow for their REST docs response shapes ([#425](https://github.com/bitrix24/b24phpsdk/issues/425))
- Regenerated IM `CountersItemResult` from a live-verified ResultItem generator payload and extended REST docs payload extraction to support direct root fields in `Returned Data` tables ([#433](https://github.com/bitrix24/b24phpsdk/issues/433))
- Regenerated IM `RevisionItemResult` from a live-verified ResultItem generator payload and extended REST docs payload extraction to support `Returned Data` tables with nested `result.*` fields ([#434](https://github.com/bitrix24/b24phpsdk/issues/434))
- Changed `UserStatusResult::status()` to return `UserStatusItemResult` with enum-backed `STATUS` access via `UserStatusType`; backed-enum casting now lives in `AbstractAnnotatedItem` for all annotated result items ([#430](https://github.com/bitrix24/b24phpsdk/issues/430))
- Added Typhoon-based runtime casting for `DialogItemResult` magic getters so annotated fields such as `date_create` return their PHPDoc-declared SDK types ([#425](https://github.com/bitrix24/b24phpsdk/issues/425))
- Moved `typhoon/reflection` to runtime dependencies because annotated result-item casting uses it outside development tooling ([#425](https://github.com/bitrix24/b24phpsdk/issues/425))
- Updated `Dialog::messagesSearch()` date filters to accept `CarbonImmutable` arguments and serialize them to REST date-time strings at the service boundary ([#425](https://github.com/bitrix24/b24phpsdk/issues/425))
- Reorganized OpenAPI ResultItem generator internals into purpose-specific `Field`, `Payload`, `Provider`, `Verification`, `PhpDoc`, and `Path` namespaces ([#425](https://github.com/bitrix24/b24phpsdk/issues/425))
- Updated `b24phpsdk-maintainer` skill and `AGENTS.md`: all GitHub issues (title, body, checklists, comments) must be written in English only ([#422](https://github.com/bitrix24/b24phpsdk/issues/422))
- Updated `AGENTS.md`: limit mandatory `b24phpsdk-maintainer` usage to issue, changelog, and release-related work
- Updated `b24phpsdk-maintainer` skill: added "Implementing placements for a scope" section with directory layout, plain-class-over-enum rationale, option-builder template, shared-vs-scope-specific enum placement, and mandatory reflection-based integration test template ([#437](https://github.com/bitrix24/b24phpsdk/issues/437))
- Updated `b24phpsdk-maintainer` skill and `AGENTS.md`: require polling PR CI status via `mcp__github__get_pull_request_status` (fallback `gh pr checks --watch`) after every push to a PR branch, with ~60s cadence, until the status reaches a terminal state ([#437](https://github.com/bitrix24/b24phpsdk/issues/437))
- Updated `b24phpsdk-maintainer` skill: hardened PR creation rules — agent must auto-create the PR via `mcp__github__create_pull_request` (never forward the `pull/new/<branch>` URL as a manual-action prompt), PR base branch is strictly `v3-dev` for v3 and `dev` for v1 (never `main`), and the PR body is always composed from `.github/PULL_REQUEST_TEMPLATE.md` read fresh from disk
- Updated `b24phpsdk-maintainer` skill: generalized the placements workflow to cover `PlacementLocationCodes`, typed `Placements` facades, `PlacementLangMap`/`PlacementLangItem`, `LangCodes` placement, service-builder registration, docs links, and the full unit/integration test matrix
- Updated `b24phpsdk-maintainer` skill: moved the detailed placements workflow into a dedicated adjacent guide to keep `SKILL.md` compact while preserving the full placement implementation playbook
- Updated `b24phpsdk-maintainer` skill: require `CarbonImmutable` for public service method arguments that represent date or date-time values
- Updated `b24phpsdk-maintainer` skill: require `ApiEndpointMetadata` documentation links to use the English `https://apidocs.bitrix24.com/` site
- Updated `b24phpsdk-maintainer` skill: require SDK file generators for supported `*ItemResult`, `*SelectBuilder`, and `*ItemBuilder` files before manual edits
- Updated `b24phpsdk-maintainer` skill and `AGENTS.md`: require `make lint-rector` before reporting completed tasks, including microtasks that bypass the maintainer skill

### Fixed

- Fixed `IM\Recent\Result\RecentItemResult` PHPDoc annotations to match live `im.recent.get` payload fields and magic-getter casting ([#427](https://github.com/bitrix24/b24phpsdk/issues/427))
- Fixed `Core::call()` handling of REST API v3 HTTP 401 error responses: array-shaped `error` payloads are now routed through `ApiLevelErrorHandler` instead of triggering `Array to string conversion`, and Bitrix24 v3 access-denied errors map to `AuthForbiddenException`.
- Fixed `IM\User\Result\UserItemResult::last_activity_date` PHPDoc annotation so the magic getter casts live `im.user.get` date-time values to `CarbonImmutable`.
- Fixed IM Notify result annotations for live `im.notify.get` and `im.notify.schema.get` payloads, including uppercase schema fields and list-normalized schema items.
- Fixed `User\Service\Batch::get()` yielding `DealItemResult` instead of `UserItemResult` ([#447](https://github.com/bitrix24/b24phpsdk/issues/447))
- Fixed abstract `Bitrix24PartnerInterfaceTest` and `Bitrix24PartnerRepositoryInterfaceTest` contracts: dropped `createdAt` / `updatedAt` from the factory method signature and data provider so implementations that initialise both timestamps internally (e.g. `new CarbonImmutable()` in the constructor) no longer fail with microsecond mismatches ([#457](https://github.com/bitrix24/b24phpsdk/issues/457))

## 3.1.0

### Added

- Added `ValidationError` and `UnsuccessfulResponseError` DTOs for REST API v3 unified unsuccessful response structure ([#341](https://github.com/bitrix24/b24phpsdk/issues/341))
- Added `ValidationException` (extends `BaseException`) with `getValidationErrors(): ValidationError[]` method — thrown when REST API v3 response contains field-level validation errors ([#341](https://github.com/bitrix24/b24phpsdk/issues/341))
- Added explicit REST API v3 error detection in `ApiLevelErrorHandler`: responses with non-empty `error.validation[]` now throw `ValidationException` instead of generic `BaseException` ([#341](https://github.com/bitrix24/b24phpsdk/issues/341))
- Added `b24-dev:show-v3-builder-coverage` CLI command: audits SelectBuilder / ItemBuilder coverage for all OpenAPI v3 entities in a given scope and reports unmapped, missing, invalid, field-coverage-mismatch, and duplicate entity key cases (`make sdk-builder-coverage-v3-show`) ([#340](https://github.com/bitrix24/b24phpsdk/issues/340))
- Added `b24-dev:generate-select-builder` console command — reads the checked-in OpenAPI snapshot and generates a deterministic `*SelectBuilder` PHP class for any v3 entity; `$ref` properties are expanded one level deep using dot-notation, methods are sorted alphabetically ([#340](https://github.com/bitrix24/b24phpsdk/issues/340))
- Added `#[OaEntity]` PHP 8 attribute that links a `*ItemResult` class to its OpenAPI entity key (`entityKey`), optional `*SelectBuilder` class (`selectBuilder`), and optional `*ItemBuilder` class (`itemBuilder`); applied to `TaskItemResult` and `EventLogItemResult` ([#340](https://github.com/bitrix24/b24phpsdk/issues/340))
- Added support for events:
    - `onCrmDocumentGeneratorDocumentAdd` — fires when a document is created,
      see [event documentation](https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/events/on-crm-document-generator-document-add.html)
    - `onCrmDocumentGeneratorDocumentUpdate` — fires when a document is updated,
      see [event documentation](https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/events/on-crm-document-generator-document-update.html)
    - `onCrmDocumentGeneratorDocumentDelete` — fires when a document is deleted,
      see [event documentation](https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/events/on-crm-document-generator-document-delete.html)
- Added service `Services\CRM\Documentgenerator\Document` with support methods,
  see [crm.documentgenerator.document.* methods](https://apidocs.bitrix24.com/api-reference/crm/document-generator/documents/index.html):
    - `add` creates a new document based on a template and CRM entity, with batch calls support
    - `list` gets the list of documents, with batch calls support
    - `update` updates an existing document, with batch calls support
    - `delete` deletes a document, with batch calls support
    - `get` gets information about the document by its identifier
    - `getFields` returns the description of document fields
    - `enablePublicUrl` enables public URL for a document
    - `upload` uploads a file for a document
    - `count` count documents
- Added service `Services\CRM\Documentgenerator\Template` with support methods,
  see [crm.documentgenerator.template.* methods](https://apidocs.bitrix24.com/api-reference/crm/document-generator/templates/index.html):
    - `add` adds a new template, with batch calls support
    - `list` gets the list of templates, with batch calls support
    - `update` updates an existing template, with batch calls support
    - `delete` deletes a template, with batch calls support
    - `get` gets information about the template by its identifier
    - `getFields` returns the description of template fields
    - `count` count templates
- Added `RestServiceBuilder` with `Scope` service for `rest.scope.list` support ([#408](https://github.com/bitrix24/b24phpsdk/issues/408))
- Added `oauthServerUrl` field to `LocalAppAuth`: stored in `toArray()` as `oauth_server_url`, restored in `initFromArray()` with fallback to `DefaultOAuthServerUrl::default()` for backward compatibility ([#385](https://github.com/bitrix24/b24phpsdk/issues/385))
- Added `ItemBuilderInterface` (`src/Core/Contracts/ItemBuilderInterface.php`) and `AbstractItemBuilder` (`src/Services/AbstractItemBuilder.php`) for type-safe task field building; `Task::add()` and `Task::update()` accept `array|TaskItemBuilder` where `TaskItemBuilder extends AbstractItemBuilder implements ItemBuilderInterface`, allowing user subclasses with custom typed user-field methods ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
- Added `AbstractItemBuilder::getSupportedFieldNames()` — discovers public 1-parameter instance methods in concrete subclass via reflection, returns alphabetically sorted list; mirrors `AbstractSelectBuilder::allSystemFields()` pattern ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
- Added `OpenApiSchemaEntityReader::getWritableFields(string $schemaFile, string $operationPath): array<string, string>` — reads writable field names and OpenAPI types from `paths/{op}/post/requestBody` in the OpenAPI snapshot; `$ref` entries are mapped to `'object'` ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
- Added `ItemBuilderCodeGenerator` (`src/CodeGenerator/ItemBuilderCodeGenerator.php`) with `ItemBuilder.tpl.php` template — generates typed setter methods from OpenAPI writable-field maps, skipping `object` types; mirrors `SelectBuilderCodeGenerator` ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
- Added `b24-dev:generate-item-builder` console command (`GenerateItemBuilderCommand`) — wraps `ItemBuilderCodeGenerator` to generate `*ItemBuilder` classes from the OpenAPI snapshot; expanded `TaskItemBuilder` with all 78 writable fields from `/tasks.task.add` (`deadline`, `startPlan`, `endPlan` preserve `CarbonInterface` input; `needsControl` preserves `'Y'/'N'` serialization) ([#344](https://github.com/bitrix24/b24phpsdk/issues/344))
- Added `EventLogField` service for `main.eventlog.field.get` and `main.eventlog.field.list` support ([#394](https://github.com/bitrix24/b24phpsdk/issues/394))
- Added support for `tasks.task.access.field.get` and `tasks.task.access.field.list` via `AccessField` service ([#396](https://github.com/bitrix24/b24phpsdk/issues/396))
- Added support for `tasks.task.file.field.get` and `tasks.task.file.field.list` via `FileField` service ([#398](https://github.com/bitrix24/b24phpsdk/issues/398))
- Added support for `tasks.task.chat.message.field.*` methods ([#397](https://github.com/bitrix24/b24phpsdk/issues/397)):
  - `TaskServiceBuilder::taskChatMessageField()` — new scope accessor
  - `ChatMessageField::get(string $name, array $select = [])` → `ChatMessageFieldResult` — get a single field descriptor by code (`tasks.task.chat.message.field.get`, API v3)
  - `ChatMessageField::list(array $select = [])` → `ChatMessageFieldsResult` — list all available field descriptors (`tasks.task.chat.message.field.list`, API v3)
  - `ChatMessageFieldItemResult` — field descriptor item with properties: `name`, `type`, `title`, `description`, `validationRules`, `requiredGroups`, `filterable`, `sortable`, `editable`, `multiple`, `elementType`
- Added service `Services\Task\Service\TaskField` for v3 methods `tasks.task.field.get` and `tasks.task.field.list`, including `TaskServiceBuilder::taskField()` and typed `TaskFieldItemResult`, `TaskFieldResult`, and `TaskFieldsResult` wrappers for task field metadata responses ([#395](https://github.com/bitrix24/b24phpsdk/issues/395))
- Added `CustomBitrix24Assertions::assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(AbstractItem $item, string $resultItemClassName)` — generic assertion that reads all `@property-read` PHPDoc annotations via Typhoon Reflection and verifies each magic-getter value matches its declared PHP type (supports `string`, `bool`, `int`, `float`, `array`, nullable variants, and class types via `assertInstanceOf`)
- Added unit tests for `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations` in `tests/Unit/CustomAssertions/CustomBitrix24AssertionsTest.php` covering happy paths (all types match, nullable fields as null) and 9 failure cases via `DataProvider`
- Added integration tests for `ChatMessageFieldItemResult` in `tests/Integration/Services/Task/ChatMessageField/Result/ChatMessageFieldItemResultTest.php`:
  - `testAllFieldsAreAnnotated` — verifies every field from raw API response is covered by a `@property-read` annotation
  - `testAllFieldsHasValidTypeCastingInMagicGetters` — verifies magic getters return values matching their PHPDoc-declared types
- Added OpenAPI snapshot coverage tooling for SDK v3 with console command `b24-dev:show-oa-sdk-coverage`, Make targets `make sdk-coverage-v3-show` and `make sdk-coverage-v3-show-uncovered`, normalization of OpenAPI aliases/scopes, and uncovered-method output with documentation links built from the Bitrix24 REST v3 docs URL pattern ([#391](https://github.com/bitrix24/b24phpsdk/issues/391))

### Changed

- `ContactPersonInterface::getBitrix24UserId()` now returns `int` instead of `?int` — a ContactPerson is always linked to a Bitrix24 user ([#365](https://github.com/bitrix24/b24phpsdk/issues/365))
- Added `createRepositoryFlusherImplementation()` abstract method to `Bitrix24PartnerRepositoryInterfaceTest` and updated all 7 test methods to call `flush()` after every write operation, aligning with the contract test pattern used by `Bitrix24AccountRepositoryInterfaceTest` and `ApplicationInstallationRepositoryInterfaceTest` ([#416](https://github.com/bitrix24/b24phpsdk/issues/416))
- The repository now stores the OpenAPI schema snapshot current at release build time in `docs/open-api/openapi.json`; refresh it before implementation and release verification with `make oa-schema-build` ([#391](https://github.com/bitrix24/b24phpsdk/issues/391))
- Removed unused `cebe/php-openapi` dependency from `require` ([#418](https://github.com/bitrix24/b24phpsdk/issues/418))

### Fixed

- Synced `TaskItemSelectBuilder` with the refreshed task OpenAPI schema so `crmItems.id` is covered in generated selections ([#449](https://github.com/bitrix24/b24phpsdk/issues/449))
- Fixed remote webhook payload normalization so `RemoteEventsFactory` and event requests accept valid Bitrix24 form webhooks already parsed by Symfony request bags ([#443](https://github.com/bitrix24/b24phpsdk/issues/443))
- Fixed `Response::getResponseData()` crashing when API response lacks a `time` node (e.g. documentation endpoint): added `Time::initWithZeroValues()` factory that fills numeric fields with `0.0` and date fields with `CarbonImmutable::now()` ([#343](https://github.com/bitrix24/b24phpsdk/issues/343))
- Fixed infinite recursion in `Core::call()` when portal returns a `302` redirect to the same domain (e.g. expired-license redirect to `/bitrix/coupon_activation.php`); now throws `PortalUnavailableException` ([#372](https://github.com/bitrix24/b24phpsdk/issues/372))
- Fixed `InMemoryApplicationInstallationRepositoryImplementation::findByBitrix24AccountMemberId()` to resolve installations for non-deleted master accounts in pending install flows, including `new` accounts, while still excluding deleted installations ([#387](https://github.com/bitrix24/b24phpsdk/issues/387))
- Fixed `AttributesParser` metadata extraction for SDK methods with compound return types and migrated coverage tooling to a typed `SupportedInSdkApiMethod` contract so documentation and statistics commands no longer crash on union returns ([#391](https://github.com/bitrix24/b24phpsdk/issues/391))

## 3.0.0 - 2026.02.27

### Added

#### API v3 support: Tasks & EventLog

- Added support for Bitrix24 API v3
- Added REST 3.0 API version support:
  - `Core\Contracts\ApiVersion` - enum for API version support (`v1`, `v3`) with helper methods `isV3()` and `isV1()`
  - `Core\EndpointUrlFormatter` - formats API request URLs based on API version, handles V3 API prefix `/rest/api`, manages case-sensitive method handling, and request ID parameter placement for strict methods
- Switched Task domain methods to Bitrix24 API v3 and documented services/methods currently using v3:
    - `Services\Task\Service\Task`: `get` (`tasks.task.get`), `add` (`tasks.task.add`), `delete` (`tasks.task.delete`), `update` (`tasks.task.update`)
    - `Services\Task\Service\TaskChat`: `sendMessage` (`tasks.task.chat.message.send`)
    - `Services\Task\Service\TaskFile`: `attachExists` (`tasks.task.file.attach`)
    - `Services\Main\Service\Documentation`: `getSchema` (`documentation`)
- Added type-safe filter builder system for REST 3.0 filtering ([#338](https://github.com/bitrix24/b24phpsdk/issues/338)):
  - `FilterBuilderInterface` - contract for all filter builders
  - `AbstractFilterBuilder` - base implementation with AND/OR logic support
  - `FieldConditionBuilder` - provides all 8 REST 3.0 operators: `=`, `!=`, `>`, `>=`, `<`, `<=`, `in`, `between`
  - `TaskFilter` - type-safe filter for Task entity with 30 field accessors
  - Fluent API with method chaining: `->title()->eq('ASAP')`
  - OR logic support with callback pattern: `->or(function(TaskFilter $f) {...})`
  - User field support: `->userField('UF_CRM_TASK')->eq('value')`
  - Raw array fallback: `->raw([['field', 'operator', 'value']])`
  - Backward compatible with existing array-based filters
  - Updated `Task::list()` to accept `TaskFilter` or array via union type
  - Comprehensive unit tests with 54 test cases covering all operators and features
- Added select builder infrastructure for type-safe field selection:
  - `Core\Contracts\SelectBuilderInterface` - contract with `buildSelect()` and `withUserFields()` methods
  - `Services\AbstractSelectBuilder` - base implementation for select builders
  - `Services\Task\Service\TaskItemSelectBuilder` - type-safe select builder for Task entity with field methods: `title()`, `description()`, `creatorId()`, `creator()`, `created()`, `chat()`
- Added comprehensive filter documentation:
  - `src/Filters/docs/README.md` - unified guide covering REST 3.0 filtering principles, type-safe filter builders, all 8 operators, field type mapping, usage examples with TaskFilter, and complete migration guide from generic to type-safe approach
- Added OpenAPI schema infrastructure ([#338](https://github.com/bitrix24/b24phpsdk/issues/338)):
  - `Services\Main\Service\Documentation` - new service with `getSchema()` method for retrieving OpenAPI documentation from REST 3.0 `/documentation` endpoint
  - `OpenApi\Infrastructure\Console\SchemaBuilder` - console command `b24-dev:build-schema` for fetching and saving OpenAPI schema to `docs/open-api/openapi.json`
  - `DocumentationResult` - DTO returning raw OpenAPI payload as string
  - Integration test: `tests/Integration/Services/Main/Service/DocumentationTest.php`
- Added `Core\Contracts\SortOrder` enum (`Ascending = 'ASC'`, `Descending = 'DESC'`) —
  type-safe sort direction for use across all REST v3 API calls.
- Added service `Services\Main\Service\EventLog` with REST v3 event log methods
  (scope: `main`, requires administrator access),
  see [main.eventlog.* methods](https://github.com/bitrix24/b24phpsdk/issues/374):
  - `get(int $id, array|EventLogSelectBuilder $select)` — returns a single event log entry by ID
    ([main.eventlog.get](https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-get.html))
  - `list(array|EventLogSelectBuilder $select, array|EventLogFilter $filter, array $order, array $pagination)` — returns a list of entries with filtering and pagination
    ([main.eventlog.list](https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-list.html))
  - `tail(array|EventLogSelectBuilder $select, array|EventLogFilter $filter, EventLogTailCursor $cursor)` — returns new entries after a cursor point for polling/sync scenarios
    ([main.eventlog.tail](https://apidocs.bitrix24.com/api-reference/rest-v3/main/main-eventlog-tail.html))
- Added `Services\Main\Service\EventLogSelectBuilder` — fluent select builder for event log fields
- Added `Services\Main\Service\EventLogFilter` — type-safe filter builder with typed condition builders
  per field (`IntFieldConditionBuilder`, `DateTimeFieldConditionBuilder`, `StringFieldConditionBuilder`)
- Added `Services\Main\Service\EventLogTailCursor` — immutable value object for the tail cursor
  (`field`, `order: SortOrder`, `value`, `limit`), serialized via `toArray()`
- Typed `EventLogItemResult::$remoteAddr` as `Darsyn\IP\Version\Multi|null` instead of `string|null`.
  `darsyn/ip` was already a dependency but unused in result items.
  `Multi::factory()` auto-detects IPv4/IPv6 and returns a value object supporting CIDR range checks,
  protocol-appropriate string representation, and strict typing.
  Applies the same null/empty-string guard used by `$timestampX` to handle absent API fields safely.

#### Everything else

- Added `deptrac/deptrac` (`^3.0`) as a dev dependency — architectural layer enforcement tool.
  Rules are declared in `deptrac.yaml`; run via `make lint-deptrac` (also part of `make lint-all`).
  Layer boundaries: `Core` → nothing; `Application` → `Core`, `Services`; `Infrastructure` → `Core`, `Services`;
  `Services` → `Core`, `Application`, `Legacy`; `Legacy` → `Core`, `Application`, `Services`.
  22 pre-existing violations are recorded in `skip_violations` with `TODO` comments tracking required refactoring.
- Added `Services\AbstractSelectBuilder::allSystemFields()` — convenience method that uses reflection
  to discover and call all public zero-parameter field methods declared in the concrete subclass,
  collecting all available system fields in a single call. Supports chaining with `withUserFields()`.
  Works automatically for any existing or future `AbstractSelectBuilder` descendant without any changes to them.
- Added `src/Legacy/` namespace with `LegacyServiceBuilder` and `LegacyTaskServiceBuilder`,
  accessible via `$serviceBuilder->getLegacyServiceBuilder()->getTaskScope()->task()`.
  Preserves access to all Bitrix24 REST API v1 task methods (`list`, `fields`, `delegate`,
  `start`, `pause`, `defer`, `complete`, etc.) for users migrating to the v3 SDK.
  All classes under `Bitrix24\SDK\Legacy\` are marked `@deprecated` and will be removed
  once v3 reaches feature parity with v1.
- Added `OpenApi\Domain\OpenApiSchemaReader` for programmatic reading and navigation of the OpenAPI specification,
  with support for component schemas, field type extraction, `$ref` resolution, and request/response schema access
- Added service `Services\Lists\Lists\Service\Lists` with support methods,
  see [lists.* methods](https://github.com/bitrix24/b24phpsdk/issues/360):
    - `add` creates a universal list, with batch calls support
    - `update` updates a universal list, with batch calls support
    - `get` returns data of a universal list or an array of lists, with batch calls support
    - `delete` deletes a universal list, with batch calls support
    - `getIBlockTypeId` returns the identifier of the information block type
- Added service `Services\Lists\Field\Service\Field` with support methods,
  see [lists.field.* methods](https://github.com/bitrix24/b24phpsdk/issues/360):
    - `add` creates a field for the universal list, with batch calls support
    - `update` updates a field of the universal list, with batch calls support
    - `get` returns data about a field or list of fields
    - `delete` deletes a field from the universal list, with batch calls support
    - `types` returns a list of available field types for the list
    - `addByCode` helper method to create field by iblock code
    - `updateByCode` helper method to update field by iblock code
    - `getByCode` helper method to get field(s) by iblock code
    - `deleteByCode` helper method to delete field by iblock code
- Added service `Services\Lists\Section\Service\Section` with support methods,
  see [lists.section.* methods](https://github.com/bitrix24/b24phpsdk/issues/360):
    - `add` creates a section for the universal list, with batch calls support
    - `update` updates a section of the universal list, with batch calls support
    - `get` returns data about a section or list of sections
    - `delete` deletes a section from the universal list, with batch calls support
- Added service `Services\Lists\Element\Service\Element` with support methods,
  see [lists.element.* methods](https://github.com/bitrix24/b24phpsdk/issues/360):
    - `add` creates an element for the universal list, with batch calls support
    - `update` updates an element of the universal list, with batch calls support
    - `get` returns data about an element or list of elements, with batch calls support
    - `delete` deletes an element from the universal list, with batch calls support
    - `getFileUrl` returns file URL from element field
- Added service `Services\Landing\Site\Service\Site` with support methods,
  see [landing.site.* methods](https://github.com/bitrix24/b24phpsdk/issues/267):
    - `add` adds a site
    - `getList` retrieves a list of sites
    - `update` updates site parameters
    - `delete` deletes a site
    - `getPublicUrl` returns the full URL of the site(s)
    - `getPreview` returns the preview image URL of the site
    - `publication` publishes the site and all its pages
    - `unpublic` unpublishes the site and all its pages
    - `markDelete` marks the site as deleted
    - `markUnDelete` restores the site from the trash
    - `getAdditionalFields` returns additional fields of the site
    - `fullExport` exports the site to ZIP archive
    - `getFolders` retrieves the site folders
    - `addFolder` adds a folder to the site
    - `updateFolder` updates folder parameters
    - `publicationFolder` publishes the site's folder
    - `unPublicFolder` unpublishes the site's folder
    - `markFolderDelete` marks the folder as deleted
    - `markFolderUnDelete` restores the folder from the trash
    - `getRights` returns access permissions of the current user for the specified site
    - `setRights` sets access permissions for the site
- Added service `Services\Landing\SysPage\Service\SysPage` with support methods,
  see [landing.syspage.* methods](https://github.com/bitrix24/b24phpsdk/issues/267):
    - `set` sets a special page for the site
    - `get` retrieves the list of special pages
- Added service `Services\Landing\Role\Service\Role` with support methods,
  see [landing.role.* methods](https://github.com/bitrix24/b24phpsdk/issues/267):
    - `isEnabled` checks if role model is enabled
    - `enable` enables or disables the role model
    - `getList` retrieves a list of available roles
    - `getRights` gets role rights for sites
    - `setRights` sets role rights for sites
    - `setAccessCodes` sets access codes for a role
    - `getSpecialPage` retrieves the address of the special page on the site
    - `deleteForLanding` deletes all mentions of the page as a special one
    - `deleteForSite` deletes all special pages of the site
- Added service `Services\Landing\Page\Service\Page` with support methods,
  see [landing.landing.* methods](https://github.com/bitrix24/b24phpsdk/issues/267):
    - `add` adds a page
    - `addByTemplate` creates a page from a template
    - `copy` copies a page
    - `delete` deletes a page
    - `update` updates page parameters
    - `getList` retrieves a list of pages
    - `getAdditionalFields` returns additional fields of the page
    - `getPreview` returns the preview image URL of the page
    - `getPublicUrl` returns the full URL of the page
    - `resolveIdByPublicUrl` resolves page ID by its public URL
    - `publish` publishes the page
    - `unpublish` unpublishes the page
    - `markDeleted` marks the page as deleted
    - `markUnDeleted` restores the page from the trash
    - `move` moves a page to another site or folder
    - `removeEntities` removes entities from the page
    - `addBlock` adds a block to the page
    - `copyBlock` copies a block within the page
    - `deleteBlock` deletes a block by its identifier
    - `moveBlockDown` moves a block down on the page
    - `moveBlockUp` moves a block up on the page
    - `moveBlock` moves a block to a specific position
    - `hideBlock` hides a block on the page
    - `showBlock` shows a block on the page
    - `markBlockDeleted` marks a block as deleted
    - `markBlockUnDeleted` restores a block from the trash
    - `addBlockToFavorites` adds a block to favorites
    - `removeBlockFromFavorites` removes a block from favorites
- Added service `Services\Landing\Block\Service\Block` with support methods,
  see [landing.block.* methods](https://github.com/bitrix24/b24phpsdk/issues/267):
    - `list` retrieves a list of page blocks
    - `getById` retrieves a block by its identifier
    - `getContent` retrieves the content of a block
    - `getManifest` retrieves the manifest of a block
    - `getRepository` retrieves blocks from the repository
    - `getManifestFile` retrieves block manifest from repository
    - `getContentFromRepository` retrieves block content from repository
    - `updateNodes` updates block content
    - `updateAttrs` updates block node attributes
    - `updateStyles` updates block styles
    - `updateContent` updates block content with arbitrary content
    - `updateCards` bulk updates block cards
    - `cloneCard` clones a block card
    - `addCard` adds a card with modified content
    - `removeCard` removes a block card
    - `uploadFile` uploads and attaches image to block
    - `changeAnchor` changes anchor symbol code
    - `changeNodeName` changes tag name
- Added service `Services\Landing\Template\Service\Template` with support methods,
  see [landing.template.* methods](https://github.com/bitrix24/b24phpsdk/issues/267):
    - `getList` retrieves a list of templates
    - `getLandingRef` retrieves a list of included areas for the page
    - `getSiteRef` retrieves a list of included areas for the site
    - `setLandingRef` sets the included areas for the page
    - `setSiteRef` sets the included areas for the site
- Added service `Services\Landing\Repo\Service\Repo` with support methods,
  see [landing.repo.* methods](https://github.com/bitrix24/b24phpsdk/issues/267):
    - `getList` retrieves a list of blocks from the current application
    - `register` adds a block to the repository
    - `unregister` deletes a block from the repository
    - `checkContent` checks the content for dangerous substrings
- Added service `Services\Landing\Demos\Service\Demos` with support methods,
  see [landing.demos.* methods](https://github.com/bitrix24/b24phpsdk/issues/267):
    - `register` registers a template in the site and page creation wizard
    - `unregister` deletes the registered partner template
    - `getList` retrieves a list of available partner templates for the current application
    - `getSiteList` retrieves a list of available templates for creating sites
    - `getPageList` retrieves a list of available templates for creating pages
- Added service `Services\IMOpenLines\Connector\Service\Connector` with support methods,
  see [imconnector.* methods](https://github.com/bitrix24/b24phpsdk/issues/320):
    - `list` method returns a list of available connectors
    - `register` method registers a new connector
    - `activate` method activates or deactivates a connector
    - `unregister` method unregisters a connector
    - `status` method retrieves connector status information
    - `setData` method sets connector data
    - `sendMessages` method sends messages through the connector
    - `updateMessages` method updates messages
    - `deleteMessages` method deletes messages
    - `sendStatusDelivery` method sends message delivery status
    - `sendStatusReading` method sends message reading status
    - `setChatName` method sets chat name
- Added service `Services\IMOpenLines\Config\Service\Config` with support methods,
  see [imopenlines.config.*](https://github.com/bitrix24/b24phpsdk/issues/327):
    - `add` adds a new open line
    - `delete` deletes an open line
    - `get` retrieves an open line by Id
    - `getList` retrieves a list of open lines
    - `getPath` gets a link to the public page of open lines in the account
    - `update` modifies an open line
    - `joinNetwork` connects an external open line to the account
    - `getRevision` retrieves information about API revisions
- Added service `Services\IMOpenLines\CRMChat\Service\Chat` with support methods,
  see [imopenlines.crm.chat.*](https://github.com/bitrix24/b24phpsdk/issues/327):
    - `get` retrieves chats for a CRM object
    - `getLastId` retrieves the ID of the last chat associated with a CRM entity
    - `addUser` adds a user to a CRM entity chat
    - `deleteUser` removes a user from the CRM entity chat
- Added service `Services\IMOpenLines\Message\Service\Message` with support methods,
  see [imopenlines.crm.message.*, imopenlines.message.*](https://github.com/bitrix24/b24phpsdk/issues/327):
    - `addCrmMessage` sends a message to the open line on behalf of an employee or bot in a chat linked to a CRM entity
    - `quickSave` saves a message from the open line chat to the list of quick answers
    - `sessionStart` starts a new dialogue session based on a message
- Added service `Services\IMOpenLines\Bot\Service\Bot` with support methods,
  see [imopenlines.bot.*](https://github.com/bitrix24/b24phpsdk/issues/327):
    - `sendMessage` sends an automatic message via the chatbot
    - `transferToOperator` switches the conversation to a free operator
    - `transferToUser` transfers the conversation to a specific operator by user ID
    - `transferToQueue` transfers the conversation to another open line queue
    - `finishSession` ends the current session
- Added service `Services\IMOpenLines\Operator\Service\Operator` with support methods,
  see [imopenlines.operator.*](https://github.com/bitrix24/b24phpsdk/issues/327):
    - `answer` takes the dialog for the current operator
    - `finish` ends the dialogue by the current operator
    - `anotherFinish` finishes the dialog of another operator
    - `skip` skips the dialog for the current operator
    - `spam` marks the conversation as "spam" by the current operator
    - `transfer` transfers the dialogue to another operator or line
- Added service `Services\IMOpenLines\Session\Service\Session` with support methods,
  see [imopenlines.session.*](https://github.com/bitrix24/b24phpsdk/issues/327):
    - `createCrmLead` creates a lead based on the dialogue
    - `getDialog` retrieves information about the operator's dialogue (chat) in the open line
    - `startMessageSession` starts a new dialogue session based on a message
    - `voteHead` votes for the session head
    - `getHistory` gets session history
    - `intercept` intercepts the session
    - `join` joins the session
    - `pinAll` pins all sessions
    - `pin` pins a specific session
    - `setSilent` sets silent mode for session
    - `unpinAll` unpins all sessions
    - `open` opens a session
    - `start` starts a session
- Added service `Services\SonetGroup\Service\SonetGroup` with support methods,
  see [sonet_group.* methods](https://github.com/bitrix24/b24phpsdk/issues/331):
    - `create` creates a social network group/project
    - `update` modifies group parameters
    - `delete` deletes a social network group
    - `get` gets detailed information about a specific workgroup
    - `list` gets list of workgroups with filtering
    - `getGroups` gets list of social network groups (simpler version)
    - `getUserGroups` gets list of current user's groups
    - `addUser` adds users to group without invitation process
    - `deleteUser` removes users from group
    - `setOwner` changes group owner
- Added `isPartner(): bool` method to `ContactPersonInterface` to check if the contact person is a partner employee,
  [see details](https://github.com/bitrix24/b24phpsdk/issues/345):
    - Returns `true` if the contact person has a Bitrix24 partner ID set
    - Returns `false` if no partner ID is associated with the contact person
    - Provides a convenience method instead of checking `getBitrix24PartnerId() !== null`

### Changed

- Removed deprecated `RemoteEventsFabric` test file (`tests/Unit/Services/RemoteEventsFabricTest.php`);
  `RemoteEventsFactoryTest` already provides full coverage of the replacement class
- Removed unused `use Bitrix24\SDK\Services\RemoteEventsFabric` imports from
  `CRMServiceBuilderTest`, `IMServiceBuilderTest`, and `MainServiceBuilderTest`
- Fixed PHPUnit 12 deprecations in `RemoteEventsFactoryTest`: replaced `createStub()` + `->with()`
  (no-op combination) with `createStub()` + `->willReturn()` only
- **Breaking changes** in `Bitrix24PartnerInterface` and `Bitrix24PartnerRepositoryInterface`,
  [see details](https://github.com/bitrix24/b24phpsdk/issues/346):
    - Renamed `getBitrix24PartnerId(): int` to `getBitrix24PartnerNumber(): int` in `Bitrix24PartnerInterface` to clarify that this method returns the partner's external vendor site number (visible on bitrix24.com/partners/), not an internal database ID
    - Renamed `findByBitrix24PartnerId(int $bitrix24PartnerId)` to `findByBitrix24PartnerNumber(int $bitrix24PartnerNumber)` in `Bitrix24PartnerRepositoryInterface`
    - Migration: Replace all calls to `getBitrix24PartnerId()` with `getBitrix24PartnerNumber()` and `findByBitrix24PartnerId()` with `findByBitrix24PartnerNumber()` in `Bitrix24PartnerInterface` implementations
- Updated `Task::list()` method to accept `TaskFilter|array` via union type - backward compatible with existing array-based filters while supporting new type-safe TaskFilter instances
- Updated Symfony dependencies to support OpenAPI schema builder infrastructure
- Refactored integration tests: renamed `Fabric.php` to `Factory.php` for consistency
- `ContactPersonInterface::markMobilePhoneAsVerified()` now accepts an optional `?CarbonImmutable $verifiedAt = null`
  parameter. When omitted, the behaviour is identical to before (defaults to the current timestamp).
  Allows callers to supply a specific verification time (e.g. historical imports).

- `ContactPersonInterface::markEmailAsVerified()` now accepts an optional
  `?CarbonImmutable $verifiedAt = null` parameter.
  When `null` (default), the current timestamp is used — fully backward-compatible.
  Callers may supply an explicit date when restoring state from persistence or syncing external data.
  Updated: `ContactPersonInterface`, `ContactPersonReferenceEntityImplementation`,
  `ContactPersons.md` documentation, added `testMarkEmailAsVerifiedWithSpecificDate` unit test.


### Fixed

- Fixed handling of `scope` and `licence_family` fields.

## 1.10.1 - 2026.02.25    
### Fixed

- Fixed handling of `scope` and `licence_family` fields.

## 1.9.0 - 2025.12.01

### Added

- Added ApplicationSettings contracts for managing application configuration settings with support for multiple scopes (global, user-specific, department-specific):
  - Entity interface `ApplicationSettingsItemInterface` with methods for managing settings lifecycle
  - Repository interface `ApplicationSettingsItemRepositoryInterface` with CRUD operations and scope-based queries
  - Enum `ApplicationSettingStatus` for tracking setting state (active/deleted)
  - Events for tracking settings changes:
    - `ApplicationSettingsItemCreatedEvent` - triggered when new setting is created
    - `ApplicationSettingsItemChangedEvent` - triggered when setting value is updated (includes old/new values and change author)
    - `ApplicationSettingsItemDeletedEvent` - triggered when setting is soft-deleted
  - Exception `ApplicationSettingsItemNotFoundException` for handling missing settings
  - Comprehensive abstract test classes for entity and repository contracts
  - Documentation in `src/Application/Contracts/ApplicationSettings/Docs/ApplicationSettings.md`
- Added `VersionedScope` container class for managing multiple Scope instances with version support:
    - Readonly immutable container storing multiple `Scope` instances indexed by version number
    - Versions must be unique integers starting from 1
    - `getScope(int $version): Scope` method retrieves Scope by version number (throws `InvalidArgumentException` if version doesn't exist)
    - `getVersions(): array` method returns sorted array of all available version numbers
    - `hasVersion(int $version): bool` method checks if a specific version exists
    - Comprehensive unit tests with 13 test cases covering construction validation, version retrieval, and error handling
    - Uses standard `InvalidArgumentException` for all validation errors (no custom exceptions)
- Added MCP (Model Context Protocol) server configuration for Bitrix24 API documentation [see details](https://github.com/bitrix24/b24phpsdk/issues/126):
  - Added `.claude/mcp_settings.json` with Bitrix24 MCP server setup
  - Enables direct access to Bitrix24 REST API documentation within Claude Code
  - Provides tools for searching methods, viewing method details, and reading articles
  - Improves developer experience when working with Bitrix24 API
- Added specialized exceptions for OAuth token refresh errors, [see details](https://github.com/bitrix24/b24phpsdk/issues/284):
  - `InvalidGrantException` - thrown when refresh token is invalid or expired (requires user re-authorization)
  - `PortalDomainNotFoundException` - thrown when Bitrix24 portal domain is not found or inaccessible
  - These exceptions allow developers to implement specific error handling logic based on the actual failure cause

### Changed

- Updated `darsyn/ip` dependency constraint to support version 6.x alongside versions 4.x and 5.x, [see details](https://github.com/bitrix24/b24phpsdk/issues/236)
    - New version constraint: `^4 || ^5 || ^6`
    - Version 6.0.0 is compatible with PHP 7.1+ (exceeds project requirement of PHP 8.2+)
    - All existing code remains fully compatible with version 6.x
    - API methods like `IP::factory()` continue to work without changes

### Fixed

- Fixed `MOVED_TIME` field in `DealItemResult` and `LeadItemResult` to return `CarbonImmutable` instead of `int`,
  [see details](https://github.com/bitrix24/b24phpsdk/issues/126):
    - Moved `MOVED_TIME` from integer casting block to datetime casting block in `AbstractCrmItem::__get()`
    - Field now correctly returns `CarbonImmutable` object matching the documented type
    - Added comprehensive unit tests for `AbstractCrmItem` datetime field type casting with 8 test cases covering:
        - `MOVED_TIME` returns `CarbonImmutable` for both snake_case and camelCase variants
        - `DATE_CREATE`, `DATE_MODIFY`, `LAST_ACTIVITY_TIME` return `CarbonImmutable`
        - `MOVED_BY_ID` correctly returns `int`
        - Null handling for empty datetime and integer fields
- Fixed invalid type casting hints in `FlowItemResult`,
  [see details](https://github.com/bitrix24/b24phpsdk/issues/275):
    - Added missing `@property-read bool $active` annotation
    - Corrected nullable type annotations to match Bitrix24 API documentation for `task.flow.Flow.get` method:
        - `responsibleList`: changed from `array|null` to `array` (required field)
        - `demo`: changed from `bool|null` to `bool` (required field)
        - `responsibleCanChangeDeadline`: changed from `bool|null` to `bool` (required field)
        - `matchWorkTime`: changed from `bool|null` to `bool` (required field)
        - `taskControl`: changed from `bool|null` to `bool` (required field)
        - `notifyAtHalfTime`: changed from `bool|null` to `bool` (required field)
        - `taskCreators`: changed from `array|null` to `array` (required field)
        - `team`: changed from `array|null` to `array` (required field)
        - `trialFeatureEnabled`: changed from `bool|null` to `bool` (required field)
    - Preserved correct nullable types for notification thresholds: `notifyOnQueueOverflow`, `notifyOnTasksInProgressOverflow`, `notifyWhenEfficiencyDecreases` (int|null)
- Improved error handling during OAuth token refresh in `ApiClient::getNewAuthToken()`, [see details](https://github.com/bitrix24/b24phpsdk/issues/284):
    - Replaced generic error messages with specific exception types based on HTTP status codes and OAuth error codes
    - Added detailed error handling for different scenarios:
        - HTTP 400 with `invalid_grant` → throws `InvalidGrantException` (user re-authorization required)
        - HTTP 401 with `invalid_client` → throws `WrongClientException` (configuration issue)
        - HTTP 404 → throws `PortalDomainNotFoundException` (portal not found)
        - HTTP 5xx → throws `TransportException` with retry suggestion (server errors)
    - Enhanced error messages include both OAuth error code and description for better diagnostics
    - Developers can now distinguish between different failure causes and implement specific recovery logic
    - Added comprehensive unit tests covering all error scenarios
- Fixed `testFindByEmailWithVerifiedEmail` test in `ContactPersonRepositoryInterfaceTest` to properly mark email as verified,
  [see details](https://github.com/bitrix24/b24phpsdk/issues/316):
    - Added `markEmailAsVerified()` call for the first contact person after save and before flush
    - Ensures the test correctly validates the `findByEmail` method with `onlyVerified=true` flag
- Fixed `testFindByEmailWithVerifiedPhone` test in `ContactPersonRepositoryInterfaceTest` to properly mark phone as verified,
  [see details](https://github.com/bitrix24/b24phpsdk/issues/315):
    - Added `markMobilePhoneAsVerified()` call for the first contact person after save and before flush
    - Ensures the test correctly validates the `findByPhone` method with `onlyVerified=true` flag
- Fixed `testDelete` test in `ContactPersonRepositoryInterfaceTest` to call `flush()` after delete,
  [see details](https://github.com/bitrix24/b24phpsdk/issues/314):
    - Added `$flusher->flush()` call after `$contactPersonRepository->delete()` to persist changes
    - Ensures the test accurately reflects actual system behavior by persisting deletion before verifying the exception

## 1.8.0 - 2025.11.10

### Added

- Added service `Services\CRM\Type\Service\Type` with support methods,
  see [crm.type.* methods](https://github.com/bitrix24/b24phpsdk/issues/274):
    - `fields` method retrieves information about the custom fields of the smart process settings
    - `add` method creates a new SPA
    - `update` updates an existing SPA by its identifier id
    - `get` method retrieves information about the SPA with the identifier id
    - `getByEntityTypeId` method retrieves information about the SPA with the smart process type identifier entityTypeId
    - `list` Get a list of custom types crm.type.list
    - `delete` This method deletes an existing smart process by the identifier id
- For `AbstractCrmItem` added method `getSmartProcessItem` to get smart process item, [see details](https://github.com/bitrix24/b24phpsdk/issues/282)
- Added support for events, [see details](https://github.com/bitrix24/b24phpsdk/issues/288)
    - `onCrmContactAdd`
    - `onCrmContactUpdate`
    - `onCrmContactDelete`
- Added separated methods `RemoteEventsFactory::create` and `RemoteEventsFactory::validate` for create and validate incoming
  events, [see details](https://github.com/bitrix24/b24phpsdk/issues/291)
- Added comprehensive unit tests for `RemoteEventsFactory::create` and `RemoteEventsFactory::validate` methods with 14 test cases covering:
    - Event creation for supported event types (CRM Contact Add, Application Install)
    - Handling of unsupported events
    - Request validation
    - Token validation with `Bitrix24AccountInterface`
    - Special handling for `OnApplicationInstall` events
- Updated `ContactPersonInterface` implementation, [see details](https://github.com/bitrix24/b24phpsdk/issues/290) with new methods:
    - Added `isEmailVerified(): bool` to check email verification status
    - Added `isMobilePhoneVerified(): bool` to check mobile phone verification status
    - Changed `changeEmail(?string $email)` signature (removed optional `$isEmailVerified` parameter)
    - Changed `changeMobilePhone(?PhoneNumber $phoneNumber)` signature (removed optional `$isMobilePhoneVerified` parameter)
    - Added `getUserAgentInfo(): UserAgentInfo` to replace separate methods for user agent data
- Added comprehensive unit tests for `UTMs` class with 28 test cases covering:
    - Constructor with all, partial, and default parameters
    - URL parsing with various UTM parameter combinations
    - Case-insensitive parameter handling
    - URL encoding and special characters
    - Real-world URL examples (Google Ads, Facebook, Email, Twitter, LinkedIn, etc.)
- Added comprehensive unit tests for `UserAgentInfo` class with 33 test cases covering:
    - Constructor with IP addresses (IPv4, IPv6, localhost)
    - Various user agent strings (Chrome, Firefox, Safari, Edge, mobile browsers)
    - UTM extraction from referrer URLs
    - Real-world scenarios with complete user tracking data
- Added support for dynamic OAuth server selection based on regional endpoints:
    - `Credentials` class now supports `Endpoints` object with `authServerUrl` and `clientUrl`
    - New methods in `Credentials`: `getEndpoints()`, `getOauthServerUrl()`, `getClientUrl()`,
      `changeDomainUrl()`, [see details](https://github.com/bitrix24/b24phpsdk/issues/273)
    - `Endpoints` class gained `changeClientUrl()` method to create new instance with updated client URL (immutable)
    - `RenewedAuthToken` gained `getEndpoints()` method to create `Endpoints` object from server response
    - `CoreBuilder` gained `withEndpoints()` and `withOauthServerUrl()` methods for explicit endpoint configuration
    - OAuth server URL is automatically extracted from `server_endpoint` field in API responses
    - Default OAuth server remains `https://oauth.bitrix.info` for backward compatibility
- Added comprehensive unit tests for `Endpoints` class with 29 test cases covering:
    - Constructor validation for client and auth server URLs
    - `getClientUrl()` and `getAuthServerUrl()` methods
    - `changeClientUrl()` method with immutability checks
    - `initFromArray()` static factory method with validation
    - URL format validation (HTTP/HTTPS, ports, paths, subdomains)
    - Error handling for invalid URLs and missing required fields
    - Automatic addition of `https://` protocol when missing from client URL
    - Added automatic protocol fallback in `Endpoints` constructor: if client URL is provided without protocol (e.g., `example.bitrix24.com`), `https://` is automatically added
 
### Changed

- **Breaking changes** in `ContactPersonInterface` method signatures:
    - `changeEmail(?string $email)` - removed second parameter `?bool $isEmailVerified`. Migration path: call `markEmailAsVerified()` separately after
      `changeEmail()` if email needs to be verified
    - `changeMobilePhone(?PhoneNumber $phoneNumber)` - removed second parameter `?bool $isMobilePhoneVerified`. Migration path: call
      `markMobilePhoneAsVerified()` separately after `changeMobilePhone()` if phone needs to be verified
    - Replaced `getUserAgent()`, `getUserAgentReferer()`, `getUserAgentIp()` methods with single `getUserAgentInfo(): UserAgentInfo` method that returns
      complete user agent information object. Migration path: use `$info->userAgent`, `$info->referrer`, `$info->ip` properties instead
- Updated `RemoteEventsFactory::validate()` method signature from `validate(EventInterface $event, string $applicationToken)` to
  `validate(Bitrix24AccountInterface $bitrix24Account, EventInterface $event)`. Now uses `Bitrix24AccountInterface::isApplicationTokenValid()` for token
  validation instead of direct string comparison
- **Docker configuration updated to PHP 8.4** - Development environment now uses PHP 8.4.14 (docker/php-cli/Dockerfile):
    - Upgraded from PHP 8.3 to PHP 8.4 base image (`php:8.4-cli-bookworm`)
    - Updated Composer to version 2.8
    - Added PHP extension installer v2.4 from mlocati for easier extension management
    - Added new PHP extensions: `amqp`, `excimer`, `opcache`, `pcntl`, `yaml`, `zip`
    - Changed base OS from Alpine to Debian Bookworm for better compatibility
    - Implemented multi-stage Docker build for optimized image size
    - Added proper user/group ID mapping for www-data user (UID/GID 10001)
    - Set proper working directory ownership and non-root user execution
- **PHP 8.4 compatibility improvements**:
    - Rector configuration updated to use `LevelSetList::UP_TO_PHP_84` for PHP 8.4 feature detection
    - PHPUnit configuration updated to PHPUnit 11.0 attribute set (`PHPUnitSetList::PHPUNIT_110`)
    - Fixed all implicitly nullable parameter deprecation warnings (8 occurrences)
    - Fixed PHPStan internal errors with `random_int()` range handling
- **OAuth server selection made dynamic**:
    - `ApiClient` now uses `Credentials::getOauthServerUrl()` instead of hardcoded constant
    - `Core` automatically updates endpoints in credentials when receiving renewed auth tokens
    - OAuth server URL is preserved and updated from `server_endpoint` in token refresh responses
    - Existing code continues to work without changes (backward compatible)

### Fixed

- Fixed wrong offset in `ItemsResult` [see details](https://github.com/bitrix24/b24phpsdk/issues/279)
- Fixed wrong exception for method `crm.item.get`, now it `ItemNotFoundException` [see details](https://github.com/bitrix24/b24phpsdk/issues/282)
- Fixed added type `project` in enum `PortalLicenseFamily` [see details](https://github.com/bitrix24/b24phpsdk/issues/286)
- Fixed errors in `ContactPersonRepositoryInterfaceTest`, [see details](https://github.com/bitrix24/b24phpsdk/issues/294)
- **Breaking change**: Fixed method signature `Credentials::createFromOAuth()` - third parameter changed from `string $domainUrl` to `Endpoints $endpoints`
  object
    - Migration: Replace `Credentials::createFromOAuth($authToken, $appProfile, 'https://example.com')` with
      `Credentials::createFromOAuth($authToken, $appProfile, new Endpoints('https://example.com', 'https://oauth.bitrix.info/'))`
    - Updated all unit and integration tests to use new signature
- Fixed bug in `Endpoints` class constructor (line 35) - validation should check `$this->authServerUrl` instead of `$authServerUrl` parameter
- Fixed unit tests in `CredentialsTest.php` to properly instantiate `Endpoints` objects
- Fixed unit tests in `CoreTest.php` integration test to use `Endpoints` object

### Deprecated

- Method `RemoteEventsFactory::createEvent` marked as deprecated, use `RemoteEventsFactory::create` and `RemoteEventsFactory::validate` instead

### Statistics

```
Bitrix24 API-methods count: 1162
Supported in bitrix24-php-sdk methods count: 639
Coverage percentage: 54.99% 🚀
Supported in bitrix24-php-sdk methods with batch wrapper count: 91
```

## 1.7.0 - 2025.10.08

### Added

- Added service `Services\Sale\Delivery\Service\Delivery` with support methods,
  see [sale.delivery.* methods](https://github.com/bitrix24/b24phpsdk/issues/255):
    - `add` adds a delivery service
    - `update` updates a delivery service
    - `getlist` returns a list of delivery services
    - `delete` deletes a delivery service
    - `configUpdate` updates delivery service settings
    - `configGet` returns delivery service settings
- Added service `Services\Sale\DeliveryRequest\Service\DeliveryRequest` with support methods,
  see [sale.delivery.request.* methods](https://github.com/bitrix24/b24phpsdk/issues/255):
    - `update` updates the delivery request
    - `sendMessage` creates notifications for the delivery request
    - `delete` deletes the delivery request
- Added service `Services\Sale\DeliveryExtraService\Service\DeliveryExtraService` with support methods,
  see [sale.delivery.extra.service.* methods](https://github.com/bitrix24/b24phpsdk/issues/255):
    - `add` adds a delivery service
    - `update` updates a delivery service
    - `get` returns information about all services of a specific delivery service
    - `delete` deletes a delivery service
- Added service `Services\Sale\DeliveryHandler\Service\DeliveryHandler` with support methods,
  see [sale.delivery.handler.* methods](https://github.com/bitrix24/b24phpsdk/issues/255):
    - `add` adds a delivery service handler
    - `update` updates the delivery service handler
    - `list` returns a list of delivery service handlers
    - `delete` deletes a delivery service handler
- Added service `Services\Disk\Service\Disk` with support methods,
  see [disk service methods](https://github.com/bitrix24/b24phpsdk/issues/265):
    - `getVersion` returns the version by identifier
    - `getAttachedObject` returns information about the attached file
    - `getRightsTasks` returns a list of available access levels that can be used for assigning permissions
- Added service `Services\Disk\Storage\Service\Storage` with support methods,
  see [disk.storage.* methods](https://github.com/bitrix24/b24phpsdk/issues/265):
    - `fields` returns the description of the storage fields
    - `get` returns the storage by identifier
    - `rename` renames the storage
    - `list` returns a list of available storages
    - `getTypes` returns a list of storage types
    - `addFolder` creates a folder in the root of the storage
    - `getChildren` returns a list of files and folders in the root of the storage
    - `uploadFile` uploads a new file to the root of the storage
    - `getForApp` returns the description of the storage that the application can work with
- Added service `Services\Disk\Folder\Service\Folder` with support methods,
  see [disk.folder.* methods](https://github.com/bitrix24/b24phpsdk/issues/265):
    - `getFields` returns the description of folder fields
    - `get` returns the folder by identifier
    - `getChildren` returns a list of files and folders that are directly in the folder
    - `addSubfolder` creates a subfolder
    - `copyTo` copies the folder to the specified folder
    - `moveTo` moves the folder to the specified folder
    - `rename` renames the folder
    - `markDeleted` moves the folder to the trash
    - `restore` restores the folder from the trash
    - `deleteTree` permanently destroys the folder and all its child elements
    - `getExternalLink` returns a public link
    - `uploadFile` uploads a new file to the specified folder
- Added service `Services\Disk\File\Service\File` with support methods,
  see [disk.file.* methods](https://github.com/bitrix24/b24phpsdk/issues/265):
    - `getFields` returns the description of file fields
    - `get` returns the file by identifier
    - `rename` renames the file
    - `copyTo` copies the file to the specified folder
    - `moveTo` moves the file to the specified folder
    - `delete` permanently destroys the file
    - `markDeleted` moves the file to the trash
    - `restore` restores the file from the trash
    - `uploadVersion` uploads a new version of the file
    - `getVersions` returns a list of file versions
    - `restoreFromVersion` restores the file from a specific version
    - `getExternalLink` returns a public link to the file
- Added service `Services\CRM\Documentgenerator\Numerator` with support methods,
  see [crm.documentgenerator.numerator.* methods](https://github.com/bitrix24/b24phpsdk/issues/215):
    - `add` adds a new numerator, with batch calls support
    - `list` gets the list of numerators, with batch calls support
    - `update` updates an existing numbering with new values, with batch calls support
    - `delete` deletes a numerator, with batch calls support
    - `get` gets information about the numerator by its identifier
    - `count` count numerators
- Added service `Services\Paysystem\Handler\Service\Handler` with support methods,
  see [pay_system.handler.* methods](https://github.com/bitrix24/b24phpsdk/issues/260):
    - `add` adds a payment system handler
    - `update` updates a payment system handler
    - `list` returns a list of payment system handlers
    - `delete` deletes a payment system handler
- Added service `Services\Paysystem\Service\Paysystem` with support methods,
  see [sale.paysystem.* methods](https://github.com/bitrix24/b24phpsdk/issues/260):
    - `add` adds a payment system
    - `update` updates a payment system
    - `get` returns a payment system by its identifier
    - `list` returns a list of payment systems
    - `delete` deletes a payment system
    - `payPayment` pays a payment
    - `payInvoice` pays an invoice (legacy version)
- Added service `Services\Paysystem\Settings\Service\Settings` with support methods,
  see [sale.paysystem.settings.* methods](https://github.com/bitrix24/b24phpsdk/issues/260):
    - `get` returns the settings of the payment system
    - `update` updates the payment system settings
    - `getForPayment` returns the payment system settings for a specific payment
    - `getForInvoice` returns the payment system settings for a specific invoice (legacy version)
- Added service `Services\Sale\Shipment\Service\Shipment` with support methods,
  see [sale.shipment.* methods](https://github.com/bitrix24/b24phpsdk/issues/250):
    - `add` adds a shipment
    - `update` updates the fields of a shipment
    - `get` returns a shipment by its identifier
    - `list` returns a list of shipments
    - `delete` deletes a shipment
    - `getFields` returns the fields and settings for shipments
- Added service `Services\Sale\ShipmentProperty\Service\ShipmentProperty` with support methods,
  see [sale.shipmentproperty.* methods](https://github.com/bitrix24/b24phpsdk/issues/250):
    - `add` adds a shipment property
    - `update` updates the fields of a shipment property
    - `get` returns a shipment property by its identifier
    - `list` returns a list of shipment properties
    - `delete` deletes a shipment property
    - `getFieldsByType` returns the fields and settings for shipment properties by type
- Added service `Services\Sale\ShipmentPropertyValue\Service\ShipmentPropertyValue` with support methods,
  see [sale.shipmentpropertyvalue.* methods](https://github.com/bitrix24/b24phpsdk/issues/250):
    - `modify` updates shipment property values for a shipment
    - `get` returns a shipment property value by its identifier
    - `list` returns a list of shipment property values
    - `delete` deletes a shipment property value
    - `getFields` returns the fields and settings for shipment property values
- Added service `Services\Sale\ShipmentItem\Service\ShipmentItem` with support methods,
  see [sale.shipmentitem.* methods](https://github.com/bitrix24/b24phpsdk/issues/250):
    - `add` adds a new shipment item
    - `update` updates the fields of a shipment item
    - `get` returns a shipment item by its identifier
    - `list` returns a list of shipment items
    - `delete` deletes a shipment item
    - `getFields` returns the fields and settings for shipment items
- Added service `Services\Sale\Payment\Service\Payment` with support methods,
  see [sale.payment.* methods](https://github.com/bitrix24/b24phpsdk/issues/248):
    - `add` adds a payment
    - `update` updates the fields of a payment
    - `get` returns a payment by its identifier
    - `list` returns a list of payments
    - `delete` deletes a payment
    - `getFields` returns the fields and settings for payments
- Added service `Services\Sale\CashboxHandler\Service\CashboxHandler` with support methods,
  see [sale.cashbox.handler.* methods](https://github.com/bitrix24/b24phpsdk/issues/258):
    - `add` adds a REST cashbox handler
    - `update` updates the data of the REST cashbox handler
    - `list` returns a list of available REST cashbox handlers
    - `delete` deletes the REST cashbox handler
- Added service `Services\Sale\Cashbox\Service\Cashbox` with support methods,
  see [sale.cashbox.* methods](https://github.com/bitrix24/b24phpsdk/issues/258):
    - `add` adds a new cash register
    - `update` updates an existing cash register
    - `list` returns a list of configured cash registers
    - `delete` deletes a cash register
    - `checkApply` saves the result of printing the receipt
- Added service `Services\Calendar\Service\Calendar` with support methods,
  see [calendar.* methods](https://github.com/bitrix24/b24phpsdk/issues/263):
    - `add` adds a new calendar section
    - `update` updates a calendar section
    - `get` returns a list of calendar sections
    - `delete` deletes a calendar section
    - `getSettings` returns main calendar settings
    - `getUserSettings` returns user calendar settings
    - `setUserSettings` sets user calendar settings
- Added support for events:
    - `OnCalendarSectionAdd`
    - `OnCalendarSectionUpdate`
    - `OnCalendarSectionDelete`
- Added service `Services\Calendar\Event\Service\Event` with support methods,
  see [calendar.event.* methods](https://github.com/bitrix24/b24phpsdk/issues/263):
    - `add` adds a new calendar event, with batch calls support
    - `update` updates a calendar event, with batch calls support
    - `getById` returns calendar event by identifier
    - `get` returns a list of calendar events
    - `getNearest` returns a list of upcoming events
    - `delete` deletes a calendar event, with batch calls support
    - `getMeetingStatus` gets current user's participation status in event
    - `setMeetingStatus` sets participation status in event for current user
    - `getAccessibility` gets users' availability from list
- Added service `Services\Calendar\Resource\Service\Resource` with support methods,
  see [calendar.resource.* methods](https://github.com/bitrix24/b24phpsdk/issues/263):
    - `add` adds a new calendar resource
    - `update` updates a calendar resource
    - `list` returns a list of all resources
    - `bookingList` retrieves resource bookings based on a filter
    - `delete` deletes a calendar resource
- Added service `Services\Sale\PaymentItemBasket\Service\PaymentItemBasket` with support methods,
  see [sale.paymentitembasket.* methods](https://github.com/bitrix24/b24phpsdk/issues/253):
    - `add` adds a binding of a basket item to a payment
    - `update` updates the binding of a basket item to a payment
    - `get` returns the values of all fields for the basket item binding to payment
    - `list` returns a list of bindings of basket items to payments
    - `delete` deletes the binding of a basket item to a payment
    - `getFields` returns the available fields for payment item basket bindings
- Added service `Services\Sale\PaymentItemShipment\Service\PaymentItemShipment` with support methods,
  see [sale.paymentitemshipment.* methods](https://github.com/bitrix24/b24phpsdk/issues/253):
    - `add` adds a binding of a payment to a shipment
    - `update` updates the binding of a payment to a shipment
    - `get` returns the values of all fields for the payment binding to shipment
    - `list` returns a list of bindings of payments to shipments
    - `delete` deletes the binding of a payment to a shipment
    - `getFields` returns the available fields for payment item shipment bindings
- Added service `Services\Sale\PropertyRelation\Service\PropertyRelation` with support methods,
  see [sale.propertyRelation.* methods](https://github.com/bitrix24/b24phpsdk/issues/253):
    - `add` adds a property binding
    - `list` retrieves a list of property bindings
    - `deleteByFilter` removes the property relation
    - `getFields` returns the available fields for property binding

### Fixed

- Fixed Incorrect data loading in `Core\Batch::getTraversableList()` with desc sorting by ID [see details](https://github.com/bitrix24/b24phpsdk/issues/246)

### Statistics

```
Bitrix24 API-methods count: 1162
Supported in bitrix24-php-sdk methods count: 632
Coverage percentage: 54.39% 🚀
```

## 1.6.0 – 2025.09.01

### Added

- Added service `Services\Sale\BasketItem\Service\BasketItem` with support methods,
  see [sale.basketitems.* methods](https://github.com/bitrix24/b24phpsdk/issues/243):
    - `add` adds a new basket item, with batch calls support
    - `update` updates a basket item, with batch calls support
    - `get` returns a basket item by ID
    - `list` returns a list of basket items, with batch calls support
    - `delete` deletes a basket item, with batch calls support
    - `getFields` returns the fields of a basket item
- Added service `Services\Sale\BasketProperty\Service\BasketProperty` with support methods,
  see [sale.basketproperties.* methods](https://github.com/bitrix24/b24phpsdk/issues/243):
    - `add` adds a basket property
    - `update` updates the fields of a basket property
    - `get` returns a basket property by ID
    - `list` returns a list of basket properties
    - `delete` deletes a basket property
    - `getFields` returns the fields of basket properties
- Added service `Services\Sale\Order\Service\Order` with support methods,
  see [sale.order.* methods](https://github.com/bitrix24/b24phpsdk/issues/241):
    - `add` adds an order, with batch calls support
    - `update` modifies an order, with batch calls support
    - `get` returns order fields and fields of related objects
    - `list` returns a list of orders, with batch calls support
    - `delete` deletes an order and related objects, with batch calls support
    - `getFields` returns order fields
- Added service `Services\Sale\PropertyVariant\Service\PropertyVariant` with support methods,
  see [sale.propertyvariant.* methods](https://github.com/bitrix24/b24phpsdk/issues/234):
    - `add` adds a variant of an order property
    - `update` updates the fields of a property variant
    - `get` returns the value of a property variant by its identifier
    - `list` returns a list of property variants
    - `delete` deletes a property variant
    - `getFields` returns the fields and settings of property variants
- Added service `Services\Sale\Property\Service\Property` with support methods,
  see [sale.property.* methods](https://github.com/bitrix24/b24phpsdk/issues/234):
    - `add` adds a new order property
    - `update` updates the fields of an order property
    - `get` returns an order property by ID
    - `list` returns a list of order properties
    - `delete` deletes an order property
    - `getFieldsByType` returns the fields and settings of order properties by type
- Added service `Services\Sale\PropertyGroup\Service\PropertyGroup` with support methods,
  see [sale.propertygroup.* methods](https://github.com/bitrix24/b24phpsdk/issues/232):
    - `add` adds a new property group
    - `update` updates a property group
    - `get` returns a property group by id
    - `list` returns a list of property groups
    - `delete` deletes a property group
    - `getFields` returns available fields for property groups
- Added service `Services\Sale\Status\Service\Status` with support methods,
  see [sale.status.* methods](https://github.com/bitrix24/b24phpsdk/issues/234):
    - `add` adds a new status
    - `update` updates an existing status
    - `get` returns information about a status by ID
    - `list` returns a list of statuses with filtering and sorting options
    - `delete` deletes a status
    - `getFields` returns available fields for statuses
- Added service `Services\Sale\StatusLang\Service\StatusLang` with support methods,
  see [sale.statusLang.* methods](https://apidocs.bitrix24.com/api-reference/sale/status-lang/index.html):
    - `getListLangs` returns list of available languages
    - `add` adds a new status language
    - `list` returns a list of status languages with filtering and sorting options
    - `deleteByFilter` deletes status languages by filter
    - `getFields` returns available fields for status languages
- Added service `Services\Sale\PersonTypeStatus\Service\PersonTypeStatus` with support methods,
  see [sale.businessValuePersonDomain.* methods](https://github.com/bitrix24/b24phpsdk/issues/228):
    - `add` adds business value for person domain
    - `list` retrieves list of business values for person domain
    - `delete` deletes business values by filter
    - `getFields` gets fields description for business value person domain
- Added service `Services\Task\Service\Task` with support methods,
  see [tasks.task.* methods](https://github.com/bitrix24/b24phpsdk/issues/214):
    - `add` creates a task, with batch calls support
    - `update` updates a task, with batch calls support
    - `list` retrieves a list of tasks, with batch calls support
    - `delete` deletes a task, with batch calls support
    - `fields` retrieves available fields
    - `get` retrieves information about a task by id
    - `delegate` delegates tasks
    - `start` changes the task status to "in progress"
    - `pause` stops task execution and changes status to "waiting for execution"
    - `defer` changes the task status to "deferred"
    - `complete` changes the task status to "completed"
    - `renew` renews a task after it has been completed
    - `approve` approves a task
    - `disapprove` rejects a task
    - `startwatch` allows watching a task
    - `stopwatch` stops watching a task
    - `mute` enables "Mute" mode
    - `unmute` disables "Mute" mode
    - `addFavorite` adds tasks to favorites
    - `removeFavorite` removes tasks from favorites
    - `getCounters` retrieves user counters
    - `getAccess` checks access to a task
    - `addDependence` creates a dependency of one task on another
    - `deleteDependence` deletes a dependency of one task on another
    - `historyList` retrieves task history
- Added support for events:
    - `OnTaskAdd`
    - `OnTaskUpdate`
    - `OnTaskDelete`
- Added service `Services\Task\TaskResult\Service\Result` with support methods:
    - `addFromComment` adds a comment to the result
    - `deleteFromComment` deletes a comment from the task result
    - `list` retrieves a list of task results
- Added service `Services\Task\Checklistitem\Service\Checklistitem` with support methods:
    - `add` adds a new checklist item to a task
    - `update` updates the data of a checklist item
    - `delete` deletes a checklist item
    - `get` retrieves a checklist item by its id
    - `getList` retrieves a list of checklist items in a task
    - `moveAfterItem` retrieves a list of checklist items in a task
    - `complete` marks a checklist item as completed
    - `renew` marks a completed checklist item as active again
    - `isActionAllowed` checks if the action is allowed for the checklist item
    - `getManifest` retrieves the list of methods and their descriptions
- Added service `Services\Task\Commentitem\Service\Commentitem` with support methods:
    - `add` creates a new comment for a task
    - `update` updates the comment data
    - `delete` deletes a comment
    - `get` retrieves a comment for a task
    - `getList` retrieves a list of comments for a task
- Added service `Services\Task\Elapseditem\Service\Elapseditem` with support methods:
    - `add` adds time spent to a task
    - `update` updates the parameters of the time tracking record
    - `delete` deletes a time tracking record
    - `get` retrieves a time tracking record by its identifier
    - `getList` retrieves a list of time tracking records for a task
    - `isActionAllowed` checks if the action is allowed
    - `getManifest` retrieves the list of methods and their descriptions
- Added service `Services\Task\Userfield\Service\Userfield` with support methods:
    - `add` creates a new field
    - `get` retrieves a field by its identifier
    - `getList` retrieves a list of fields
    - `delete` deletes a field
    - `update` updates the parameters of the field
    - `getTypes` retrieves all available data types
    - `getFields` retrieves all available fields of the custom field
- Added service `Services\Task\Stage\Service\Stage` with support methods:
    - `add` adds stages to kanban or "My Planner"
    - `get` retrieves stages of kanban or "My Planner"
    - `delete` deletes stages of kanban or "My Planner"
    - `update` updates stages of kanban or "My Planner"
    - `canMoveTask` determines if the current user can move tasks in the specified object
    - `moveTask` moves tasks from one stage to another
- Added service `Services\Task\Planner\Service\Planner` with support methods:
    - `getList` retrieves a list of tasks from "The Daily Planner"
- Added service `Services\Task\Flow\Service\Flow` with support methods:
    - `add` creates a flow
    - `get` retrieves a flow
    - `delete` deletes a flow
    - `update` modifies a flow
    - `isExists` checks if a flow with that name exists
    - `activate` turns a flow on or off
    - `pin` pins or unpins a flow in the list
- Added service `Services\Log\BlogPost\Service\BlogPost` with support method:
    - `add` - Add new blog post to Live Feed with support for all parameters (title, destination, files, importance, etc.)
- Added method `User::countByFilter` [see details](https://github.com/bitrix24/b24phpsdk/issues/221)

### Fixed

- Fixed typehints in the ApplicationInfo method [see details](https://github.com/bitrix24/b24phpsdk/issues/219)

### Changed

- Added optional argument in method `Bitrix24AccountRepositoryInterface::findByMemberId` [see details](https://github.com/bitrix24/b24phpsdk/issues/223)
- Changed method name `ApplicationInstallationRepositoryInterface::findByMemberId` to
  `ApplicationInstallationRepositoryInterface::findByBitrix24AccountMemberId` [see details](https://github.com/bitrix24/b24phpsdk/issues/226)

### Statistics

```
Bitrix24 API-methods count: 1160
Supported in bitrix24-php-sdk methods count: 476
Coverage percentage: 41.03% 🚀
```

## 1.5.0 – 2025.08.01

### Added

- Added service `Services\Entity\Section\Service\Section` with support methods,
  see [crm.entity.section.* methods](https://github.com/bitrix24/b24phpsdk/issues/200):
    - `get` retrieve a list of storage sections, with batch calls support
    - `add` add a storage section, with batch calls support
    - `update` update a storage section, with batch calls support
    - `delete` delete a storage section, with batch calls support
- Added service `Services\Entity\Item\Property\Service\Property` with support methods:
    - `get` retrieve a list of additional properties of storage elements, with batch calls support
    - `add` add an additional property to storage elements, with batch calls support
    - `update` update an additional property of storage elements, with batch calls support
    - `delete` delete an additional property of storage elements, with batch calls support
- Added service `Services\Department\Service\Department` with support methods,
  see [department.* methods](https://github.com/bitrix24/b24phpsdk/issues/204):
    - `fields` gets the department fields reference
    - `get` retrieves a list of departments, with batch calls support
    - `add` creates a department, with batch calls support
    - `delete` deletes a department, with batch calls support
    - `update` modifies a department, with batch calls support
    - `countByFilter` count departments by filter
- Added service `CRM\Requisites\Service\RequisiteUserfield` with support methods,
  see [add crm.requisite.userfield.* methods](https://github.com/bitrix24/b24phpsdk/issues/188):
    - `add` add userfield to requisite
    - `get` get userfield to requisite
    - `list` list userfields
    - `delete` delete userfield
    - `update` update userfield
- Added service `CRM\Requisites\Service\RequisiteBankdetail` with support methods:
    - `add` add bank detail to requisite
    - `get` get bank detail to requisite
    - `fields` get fields for bank details
    - `list` list bank details
    - `delete` delete bank detail
    - `update` update bank detail
    - `countByFilter` count bank details by filter
- Added service `CRM\Requisites\Service\RequisiteLink` with support methods:
    - `register` registers the link between requisites and an object
    - `unregister` removes the link between requisites and an object
    - `fields` get a formal description of the fields of the requisites link
    - `get` returns the link between requisites and an object
    - `list` returns a list of links between requisites based on a filter
    - `countByFilter` count links by filter
- Added service `CRM\Requisites\Service\RequisitePresetField` with support methods:
    - `add` adds a customizable field to the requisites template
    - `get` returns the description of the custom field in the requisites template by identifier
    - `fields` returns the description of the custom field in the requisites template by identifier
    - `list` returns a list of all custom fields for a specific requisites template
    - `delete` deletes a customizable field from the requisites template
    - `update` modifies a custom field in the requisites template
    - `availabletoadd` returns fields available for addition to the specified requisites template
- Added service `Services\CRM\Status\Service\Status` with support methods,
  see [crm.status.* methods](https://github.com/bitrix24/b24phpsdk/issues/194):
    - `fields` returns descriptions of reference book fields
    - `get` returns an element of the reference book by its identifier
    - `list` returns a list of elements of the reference book by filter, with batch calls support
    - `add` creates a new element in the specified reference book, with batch calls support
    - `delete` deletes an element from the reference book, with batch calls support
    - `update` updates an existing element of the reference book, with batch calls support
    - `countByFilter` counts elements of the reference book by filter
- Added service `Services\CRM\Status\Service\StatusEntity` with support methods,
    - `items` returns elements of the reference book by its symbolic identifier
    - `types` returns descriptions of reference book types
- Added service `Services\CRM\Timeline\Service\Comment` with support methods,
  see [crm.timeline.comment.* methods](https://github.com/bitrix24/b24phpsdk/issues/196):
    - `fields` retrieves a list of timeline comment fields
    - `get` retrieves information about a comment
    - `list` retrieves a list of all comments for a CRM entity, with batch calls support
    - `add` adds a new comment to the timeline, with batch calls support
    - `delete` deletes a comment, with batch calls support
    - `update` updates a comment, with batch calls support
    - `countByFilter` count comments by filter
- Added support for events:
    - `OnCrmTimelineCommentAdd`
    - `OnCrmTimelineCommentDelete`
    - `OnCrmTimelineCommentUpdate`
- Added service `Services\CRM\Timeline\Service\Bindings` with support methods:
    - `fields` retrieves the fields of the link between CRM entities and the timeline record
    - `list` retrieves a list of links for a timeline record, with batch calls support
    - `bind` adds a link between a timeline record and a CRM entity, with batch calls support
    - `unbind` removes a link between a timeline record and a CRM entity, with batch calls support
    - `countByFilter` count links between a timeline record and CRM entities by filter
- Added service `Services\CRM\Item\Productrow\Service\Productrow` with support methods,
  see [crm.item.productrow.* methods](https://github.com/bitrix24/b24phpsdk/issues/198):
    - `fields` retrieves a list of product item fields
    - `set` associates a product item with a CRM object
    - `get` retrieves information about a product item by id
    - `list` retrieves a list of product items, with batch calls support
    - `add` adds a product item, with batch calls support
    - `delete` deletes a product item, with batch calls support
    - `update` updates a product item
    - `getAvailableForPayment` retrieves a list of unpaid products
    - `countByFilter` counts product items by filter
- Added methods to `ApplicationInstallationRepositoryInterface`, see [223](https://github.com/bitrix24/b24phpsdk/issues/223)
    - `findByMemberId`
    - `findByApplicationToken`

### Fixed

- Fixed typehints in Contact batch for method `add`, [see details](https://github.com/bitrix24/b24phpsdk/issues/202)

### Changed

- Fixed constructor arguments in tests ApplicationInstallations [see details](https://github.com/bitrix24/b24phpsdk/issues/191)
- Bump giggsey/libphonenumber-for-php version to ^8|^9

### Statistics

```
Bitrix24 API-methods count: 1166
Supported in bitrix24-php-sdk methods count: 362
Coverage percentage: 31.05%
```

## 1.4.0 – 2025.07.01

### Added

- Added service `Services\CRM\Lead\Service\LeadContact` with support methods,
  see [crm.lead.contact.* methods](https://github.com/bitrix24/b24phpsdk/issues/170):
    - `fields` get fields for lead contact connection
    - `setItems` set contacts related with lead
    - `get` get contacts related to lead
    - `deleteItems` delete all relations for lead
    - `add` add contact relation with lead
    - `delete` delete contact relation with lead
- Added service `CRM\Item\Service\ItemDetailsConfiguration` with support methods,
  see [add crm.item.details.* methods](https://github.com/bitrix24/b24phpsdk/issues/168):
    - `getPersonal` method retrieves the settings of item cards for personal user
    - `getGeneral` method retrieves the settings of item cards for all users
    - `resetPersonal` method reset for item user settings
    - `resetGeneral` method reset all card settings for all users
    - `setPersonal` method set card configuration
    - `setGeneral` method set card configuration for all users
    - `setForceCommonConfigForAll` method set common detail form for All Users
- Added service `CRM\Deal\Service\DealDetailsConfiguration` with support methods,
  see [add crm.deal.details.* methods](https://github.com/bitrix24/b24phpsdk/issues/158):
    - `getPersonal` method retrieves the settings of deal cards for personal user
    - `getGeneral` method retrieves the settings of deal cards for all users
    - `resetPersonal` method reset for item user settings
    - `resetGeneral` method reset all card settings for all users
    - `setPersonal` method set card configuration
    - `setGeneral` method set card configuration for all users
    - `setForceCommonConfigForAll` method set common detail form for All Users
- Added service `CRM\Lead\Service\LeadDetailsConfiguration` with support methods,
  see [add crm.lead.details.* methods](https://github.com/bitrix24/b24phpsdk/issues/172):
    - `getPersonal` method retrieves the settings of lead cards for personal user
    - `getGeneral` method retrieves the settings of lead cards for all users
    - `resetPersonal` method reset for item user settings
    - `resetGeneral` method reset all card settings for all users
    - `setPersonal` method set card configuration
    - `setGeneral` method set card configuration for all users
    - `setForceCommonConfigForAll` method set common detail form for All Users
- Added service `Services\CRM\Lead\Service\LeadProductRows` with support methods,
  see [add crm.lead.productrows* methods](https://github.com/bitrix24/b24phpsdk/issues/175):
    - `set` Adds products to a lead
    - `get` Returns the products of a lead
- Added service `Services\CRM\Quote\Service\Quote` with support methods,
  see [crm.quote.* methods](https://github.com/bitrix24/b24phpsdk/issues/179):
    - `fields` returns a list of fields for the quote
    - `get` returns the settings of the quote by Id
    - `list` returns a list of quote
    - `add` creates a new quote
    - `delete` deletes a quote
    - `update` modifies the quote
    - `countByFilter` count quotes by filter
- Added support for events:
    - `OnCrmQuoteAdd`
    - `OnCrmQuoteDelete`
    - `OnCrmQuoteUpdate`
    - `OnCrmQuoteUserFieldAdd`
    - `OnCrmQuoteUserFieldDelete`
    - `OnCrmQuoteUserFieldSetEnumValues`
    - `OnCrmQuoteUserFieldUpdate`
- Added service `Services\CRM\Quote\Service\QuoteUserfield` with support methods:
    - `add` add userfield to a quote
    - `get` get userfield to a quote
    - `list` list userfields
    - `delete` delete userfield
    - `update` update userfield
- Added service `Services\CRM\Quote\Service\QuoteProductRows` with support methods:
    - `set` Adds products to a quote
    - `get` Returns the products of a quote
- Added service `Services\CRM\Quote\Service\QuoteContact` with support methods,
    - `fields` get fiels for quote contact connection
    - `setItems` set contacts related with quote
    - `get` get contacts related to quote
    - `deleteItems` delete all relations for quote
    - `add` add contact relation with quote
    - `delete` delete contact relation with quote
- Added service `CRM\Lead\Service\LeadUserfield` with support methods,
  see [add crm.lead.userfield.* methods](https://github.com/bitrix24/b24phpsdk/issues/177):
    - `add` add userfield to lead
    - `get` get userfield to lead
    - `list` list userfields
    - `delete` delete userfield
    - `update` update userfield
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
- Added service `Services\CRM\Address\Service\Address` with support methods,
  see [add crm.address REST methods](https://github.com/bitrix24/b24phpsdk/issues/138):
    - `list` get item list
    - `add` add new item, with batch calls support
    - `delete` delete item, with batch calls support
    - `update` update item, with batch calls support
- Added enum `Services\CRM\Enum\OwnerType`
- Developer experience: added make command `lint-all` for run all code linters step by step, [see details](https://github.com/bitrix24/b24phpsdk/issues/183)

### Fixed

- Fixed error in arguments in service for method `placement.bind`, [see details](https://github.com/bitrix24/b24phpsdk/issues/151)
- Fixed errors in `task.elapseditem.*` call in ApiClient [see details](https://github.com/bitrix24/b24phpsdk/issues/180)

### Changed

- Changed B24-PHP-SDK useragent: added prefix `vendor`, [see details](https://github.com/bitrix24/b24phpsdk/issues/183)
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
    - added method `linkContactPerson(Uuid $uuid)`, see [change signatures](https://github.com/bitrix24/b24phpsdk/issues/166).
    - added method `linkBitrix24Partner()`, see [change signatures](https://github.com/bitrix24/b24phpsdk/issues/166).
    - added method `unlinkBitrix24Partner()`, see [change signatures](https://github.com/bitrix24/b24phpsdk/issues/166).
    - added method `unlinkContactPerson()`, see [change signatures](https://github.com/bitrix24/b24phpsdk/issues/166).
    - added method `linkBitrix24PartnerContactPerson()`, see [change signatures](https://github.com/bitrix24/b24phpsdk/issues/166).
    - added method `unlinkBitrix24PartnerContactPerson()`, see [change signatures](https://github.com/bitrix24/b24phpsdk/issues/166).
    - remove method `changeContactPerson(?Uuid $uuid)`, see [change signatures](https://github.com/bitrix24/b24phpsdk/issues/166).
    - remove method `changeBitrix24Partner(?Uuid $uuid)`, see [change signatures](https://github.com/bitrix24/b24phpsdk/issues/166).
    - remove method `changeBitrix24PartnerContactPerson(?Uuid $uuid)`, see [change signatures](https://github.com/bitrix24/b24phpsdk/issues/166).
- ❗**️️BC** Changed contract `Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Repository\ApplicationInstallationRepositoryInterface`,
  see [change signatures](https://github.com/bitrix24/b24phpsdk/issues/167):
    - change return type `findByBitrix24AccountId` from an array to `?ApplicationInstallationInterface`

### Statistics

```
Bitrix24 API-methods count: 1166
Supported in bitrix24-php-sdk methods count: 300
Coverage percentage: 25.73% 🚀
Supported in bitrix24-php-sdk methods with batch wrapper count: 45
```

## 1.3.0 – 2025.04.23

### Added

- Added service `CRM\Contact\Service\ContactDetailsConfiguration` with support methods,
  see [add crm.contact.details.* methods](https://github.com/bitrix24/b24phpsdk/issues/153):
    - `getPersonal` method retrieves the settings of contact cards for personal user
    - `getGeneral` method retrieves the settings of contact cards for all users
    - `resetPersonal` method reset for item user settings
    - `resetGeneral` method reset all card settings for all users
    - `setPersonal` method set card configuration
    - `setGeneral` method set card configuration for all users
    - `setForceCommonConfigForAll` method set common detail form for All Users
- Added **PHP 8.4** [support](https://github.com/bitrix24/b24phpsdk/issues/120) 🚀
- Added method `Bitrix24\SDK\Services\Main\Service::guardValidateCurrentAuthToken` for validate current auth token with
  api-call `app.info` on vendor OAUTH server.
- Added support new scope `entity`
- Added service `Services\Entity\Service\Item` with support methods,
  see [fix entity.item.* methods](https://github.com/bitrix24/b24phpsdk/issues/53):
    - `get` get item, with batch calls support
    - `add` add new item, with batch calls support
    - `delete` delete item
    - `update`  update item
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
- Fixed getting entity by its code, see [entity.get issue](https://github.com/bitrix24/b24phpsdk/issues/285)

<!--
### Deprecated

### Removed



### Security
-->

## 1.0

* Initial release
