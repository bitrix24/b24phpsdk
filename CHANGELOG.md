# b24-php-sdk change log

## 3.0.0 - 2026.01.01

### Added
- Added support for Bitrix24 API v3
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

- **Breaking changes** in `Bitrix24PartnerInterface` and `Bitrix24PartnerRepositoryInterface`,
  [see details](https://github.com/bitrix24/b24phpsdk/issues/346):
    - Renamed `getBitrix24PartnerId(): int` to `getBitrix24PartnerNumber(): int` in `Bitrix24PartnerInterface` to clarify that this method returns the partner's external vendor site number (visible on bitrix24.com/partners/), not an internal database ID
    - Renamed `findByBitrix24PartnerId(int $bitrix24PartnerId)` to `findByBitrix24PartnerNumber(int $bitrix24PartnerNumber)` in `Bitrix24PartnerRepositoryInterface`
    - Migration: Replace all calls to `getBitrix24PartnerId()` with `getBitrix24PartnerNumber()` and `findByBitrix24PartnerId()` with `findByBitrix24PartnerNumber()` in `Bitrix24PartnerInterface` implementations

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
