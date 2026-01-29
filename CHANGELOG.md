# b24-php-sdk change log

## 3.0.0 - 2026.01.01

### Added

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
    - `deleteBlock` deletes a block from the page
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
