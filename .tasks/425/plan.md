# Plan: Add IM\Dialog service for im.dialog.* support (issue #425)

## Context

Issue `#425` adds a new IM scope service for the Bitrix24 REST API methods
`im.dialog.*` on the `v3-dev` line.

The local OpenAPI snapshot was refreshed with `make oa-schema-build` before
planning. The Bitrix24 REST documentation confirms these response shapes:

- `im.dialog.get` returns a single dialog object in `result`
- `im.dialog.messages.get` returns an aggregate object with `chat_id`,
  `messages`, `users`, `files`
- `im.dialog.messages.search` returns an aggregate object with `messages`,
  `users`, `files`, `additionalMessages`, `copilot`, `stickers`, `reactions`,
  `tariffRestrictions`, `usersShort`
- `im.dialog.read` returns a short state object with `dialogId`, `chatId`,
  `lastId`, `counter`
- `im.dialog.read.all`, `im.dialog.unread`, and `im.dialog.writing` return a
  top-level boolean
- `im.dialog.users.list` returns a paginated top-level array in `result` plus
  `total` and optional `next`

The implementation must follow existing IM patterns:

- new service classes live under `src/Services/IM/<Domain>/Service/`
- new result classes live under `src/Services/IM/<Domain>/Result/`
- `IMServiceBuilder` exposes one cached accessor per service
- unit tests validate exact REST method names and parameter maps
- integration tests use live portal data
- annotation tests must be dedicated per `*ItemResult`

One design refinement is required beyond the original issue text: the existing
generic success wrappers in `src/Core/Result/` mostly read `result[0]`, but the
`im.dialog.read.all`, `im.dialog.unread`, and `im.dialog.writing` endpoints
return a scalar boolean in `result`. To avoid an incorrect wrapper, this plan
introduces a local `DialogActionResult` with `isSuccess(): bool`.

---

## Files to Create

### 1. `src/Services/IM/Dialog/Service/Dialog.php`

Purpose: new IM Dialog service with one typed method per REST endpoint.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogActionResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogMessageSearchResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogMessagesResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogReadResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogUsersResult;

#[ApiServiceMetadata(new Scope(['im']))]
class Dialog extends AbstractService
{
    #[ApiEndpointMetadata('im.dialog.get', 'https://apidocs.bitrix24.ru/api-reference/chats/im-dialog-get.html', 'Get dialog information')]
    public function get(string $dialogId): DialogResult {}

    #[ApiEndpointMetadata('im.dialog.messages.get', 'https://apidocs.bitrix24.ru/api-reference/chats/messages/im-dialog-messages-get.html', 'Get dialog messages')]
    public function messagesGet(
        string $dialogId,
        ?int $lastId = null,
        ?int $firstId = null,
        ?int $limit = null,
    ): DialogMessagesResult {}

    #[ApiEndpointMetadata('im.dialog.messages.search', 'https://apidocs.bitrix24.ru/api-reference/chats/messages/im-dialog-messages-search.html', 'Search messages in a chat')]
    public function messagesSearch(
        int $chatId,
        ?string $searchMessage = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $date = null,
        ?array $order = null,
        ?int $limit = null,
        ?int $lastId = null,
    ): DialogMessageSearchResult {}

    #[ApiEndpointMetadata('im.dialog.read', 'https://apidocs.bitrix24.ru/api-reference/chats/messages/im-dialog-read.html', 'Mark dialog messages as read')]
    public function read(string $dialogId, ?int $messageId = null): DialogReadResult {}

    #[ApiEndpointMetadata('im.dialog.read.all', 'https://apidocs.bitrix24.ru/api-reference/chats/special-operations/im-dialog-read-all.html', 'Mark all current user dialogs as read')]
    public function readAll(): DialogActionResult {}

    #[ApiEndpointMetadata('im.dialog.unread', 'https://apidocs.bitrix24.ru/api-reference/chats/messages/im-dialog-unread.html', 'Mark dialog messages as unread from a message')]
    public function unread(string $dialogId, int $messageId): DialogActionResult {}

    #[ApiEndpointMetadata('im.dialog.users.list', 'https://apidocs.bitrix24.ru/api-reference/chats/chat-users/im-dialog-users-list.html', 'List dialog participants')]
    public function usersList(
        string $dialogId,
        bool $skipExternal = false,
        ?string $skipExternalExceptTypes = null,
        ?int $limit = null,
        ?int $lastId = null,
        ?int $offset = null,
    ): DialogUsersResult {}

    #[ApiEndpointMetadata('im.dialog.writing', 'https://apidocs.bitrix24.ru/api-reference/chats/messages/im-dialog-writing.html', 'Send the typing indicator')]
    public function writing(string $dialogId): DialogActionResult {}
}
```

Implementation notes:

- send `DIALOG_ID` or `CHAT_ID` exactly as documented
- include optional parameters only when non-null
- map `skipExternal` to `SKIP_EXTERNAL => 'Y'|'N'`
- preserve `ORDER` as the caller-provided associative array

### 2. `src/Services/IM/Dialog/Result/DialogActionResult.php`

Purpose: dedicated wrapper for top-level boolean `result` payloads.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class DialogActionResult extends AbstractResult
{
    public function isSuccess(): bool {}
}
```

### 3. `src/Services/IM/Dialog/Result/DialogResult.php`

Purpose: wrapper for `im.dialog.get`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DialogResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function dialog(): ?DialogItemResult {}
}
```

### 4. `src/Services/IM/Dialog/Result/DialogMessagesResult.php`

Purpose: wrapper for `im.dialog.messages.get`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DialogMessagesResult extends AbstractResult
{
    /**
     * @return MessageItemResult[]
     * @throws BaseException
     */
    public function messages(): array {}

    public function chatId(): ?int {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function users(): array {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function files(): array {}
}
```

### 5. `src/Services/IM/Dialog/Result/DialogMessageSearchResult.php`

Purpose: wrapper for `im.dialog.messages.search`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DialogMessageSearchResult extends AbstractResult
{
    /**
     * @return MessageItemResult[]
     * @throws BaseException
     */
    public function messages(): array {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function users(): array {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function files(): array {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function additionalMessages(): array {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function stickers(): array {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function reactions(): array {}

    /**
     * @return array<int, array<string, mixed>>
     */
    public function usersShort(): array {}

    /**
     * @return array<string, mixed>|null
     */
    public function copilot(): ?array {}

    /**
     * @return array<string, mixed>|null
     */
    public function tariffRestrictions(): ?array {}
}
```

### 6. `src/Services/IM/Dialog/Result/DialogReadResult.php`

Purpose: wrapper for `im.dialog.read`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DialogReadResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function readState(): ?DialogReadStateItemResult {}
}
```

### 7. `src/Services/IM/Dialog/Result/DialogUsersResult.php`

Purpose: wrapper for `im.dialog.users.list`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class DialogUsersResult extends AbstractResult
{
    /**
     * @return DialogUserItemResult[]
     * @throws BaseException
     */
    public function users(): array {}

    public function total(): int {}

    public function next(): ?int {}
}
```

### 8. `src/Services/IM/Dialog/Result/DialogItemResult.php`

Purpose: immutable typed item for `im.dialog.get`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

class DialogItemResult extends AbstractItem
{
}
```

Implementation notes:

- populate the PHPDoc with the full live field set returned by
  `im.dialog.get`
- use scalar and array types that match the runtime types exposed by
  `AbstractItem::__get()`

### 9. `src/Services/IM/Dialog/Result/MessageItemResult.php`

Purpose: immutable typed item for the `messages` arrays returned by
`im.dialog.messages.get` and `im.dialog.messages.search`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

class MessageItemResult extends AbstractItem
{
}
```

Implementation notes:

- use the union of fields observed in both endpoints
- annotate nested payloads such as `params`, `forward`, and `replaces` as
  arrays or nullable arrays based on runtime values

### 10. `src/Services/IM/Dialog/Result/DialogUserItemResult.php`

Purpose: immutable typed item for `im.dialog.users.list`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

class DialogUserItemResult extends AbstractItem
{
}
```

### 11. `src/Services/IM/Dialog/Result/DialogReadStateItemResult.php`

Purpose: immutable typed item for the short object returned by
`im.dialog.read`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Dialog\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read string|null $dialogId
 * @property-read int|null $chatId
 * @property-read int|null $lastId
 * @property-read int|null $counter
 */
class DialogReadStateItemResult extends AbstractItem
{
}
```

### 12. `tests/Unit/Services/IM/Dialog/Service/DialogTest.php`

Purpose: unit coverage for method names, payload maps, boolean normalization,
and returned wrapper classes.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Dialog\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogActionResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogMessageSearchResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogMessagesResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogReadResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogResult;
use Bitrix24\SDK\Services\IM\Dialog\Result\DialogUsersResult;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class DialogTest extends TestCase
{
    private Dialog $service;
    private CoreInterface&MockObject $coreMock;

    protected function setUp(): void {}

    public function testGetCallsCorrectApiMethod(): void {}
    public function testMessagesGetMapsOptionalPaginationArguments(): void {}
    public function testMessagesSearchMapsSearchFiltersAndOrder(): void {}
    public function testReadMapsOptionalMessageId(): void {}
    public function testReadAllCallsCorrectApiMethod(): void {}
    public function testUnreadCallsCorrectApiMethod(): void {}
    public function testUsersListMapsSkipExternalAndPagination(): void {}
    public function testWritingCallsCorrectApiMethod(): void {}
}
```

### 13. `tests/Integration/Services/IM/Dialog/Service/DialogChatTestCase.php`

Purpose: local reusable IM helper for dialog integration tests and annotation
tests without coupling to `tests/Integration/Services/IM/Message/Service/MessageChatTestCase.php`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Chat\ChatType;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;

abstract class DialogChatTestCase extends TestCase
{
    protected Dialog $dialogService;
    protected Chat $chatService;
    protected Message $messageService;
    protected int $currentUserId;

    protected function setUp(): void {}

    protected function createChat(): int {}

    protected function createDialogId(int $chatId): string {}

    /**
     * @return list<int>
     */
    protected function seedMessages(string $dialogId, array $messages): array {}
}
```

### 14. `tests/Integration/Services/IM/Dialog/Service/DialogTest.php`

Purpose: happy-path live coverage for every public service method.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service;

use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;

#[CoversClass(Dialog::class)]
final class DialogTest extends DialogChatTestCase
{
    #[Test]
    #[TestDox('im.dialog.get returns dialog information for a created chat')]
    public function testGet(): void {}

    #[Test]
    #[TestDox('im.dialog.messages.get returns seeded dialog messages')]
    public function testMessagesGet(): void {}

    #[Test]
    #[TestDox('im.dialog.messages.search returns messages matching the search text')]
    public function testMessagesSearch(): void {}

    #[Test]
    #[TestDox('im.dialog.read returns the updated read state')]
    public function testRead(): void {}

    #[Test]
    #[TestDox('im.dialog.read.all returns success')]
    public function testReadAll(): void {}

    #[Test]
    #[TestDox('im.dialog.unread returns success for a seeded message')]
    public function testUnread(): void {}

    #[Test]
    #[TestDox('im.dialog.users.list returns participants and pagination metadata')]
    public function testUsersList(): void {}

    #[Test]
    #[TestDox('im.dialog.writing returns success for a dialog')]
    public function testWriting(): void {}
}
```

### 15. `tests/Integration/Services/IM/Dialog/Result/DialogItemResultAnnotationsTest.php`

Purpose: annotation completeness and runtime type validation for
`DialogItemResult`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Result;

use Bitrix24\SDK\Services\IM\Dialog\Result\DialogItemResult;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service\DialogChatTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(DialogItemResult::class)]
#[CoversMethod(Dialog::class, 'get')]
final class DialogItemResultAnnotationsTest extends DialogChatTestCase
{
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void {}
    public function testAllSystemFieldsHasValidTypeAnnotation(): void {}
}
```

Implementation notes:

- derive the field list from the live `dialog()->getIterator()` keys because
  this IM endpoint has no dedicated `fields()` metadata service

### 16. `tests/Integration/Services/IM/Dialog/Result/MessageItemResultAnnotationsTest.php`

Purpose: annotation completeness and runtime type validation for
`MessageItemResult`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Result;

use Bitrix24\SDK\Services\IM\Dialog\Result\MessageItemResult;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service\DialogChatTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(MessageItemResult::class)]
#[CoversMethod(Dialog::class, 'messagesGet')]
#[CoversMethod(Dialog::class, 'messagesSearch')]
final class MessageItemResultAnnotationsTest extends DialogChatTestCase
{
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void {}
    public function testAllSystemFieldsHasValidTypeAnnotation(): void {}
}
```

Implementation notes:

- derive the field list from the union of one live item from `messagesGet()`
  and one live item from `messagesSearch()`
- run runtime type validation on both item instances or on a merged canonical
  sample that includes every annotated field

### 17. `tests/Integration/Services/IM/Dialog/Result/DialogUserItemResultAnnotationsTest.php`

Purpose: annotation completeness and runtime type validation for
`DialogUserItemResult`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Result;

use Bitrix24\SDK\Services\IM\Dialog\Result\DialogUserItemResult;
use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Services\IM\Dialog\Service\DialogChatTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversClass(DialogUserItemResult::class)]
#[CoversMethod(Dialog::class, 'usersList')]
final class DialogUserItemResultAnnotationsTest extends DialogChatTestCase
{
    use CustomBitrix24Assertions;

    public function testAllSystemFieldsAnnotated(): void {}
    public function testAllSystemFieldsHasValidTypeAnnotation(): void {}
}
```

Implementation notes:

- derive the field list from the first participant item returned by
  `usersList()`

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add:

- `use Bitrix24\SDK\Services\IM\Dialog\Service\Dialog;`
- cached `public function dialog(): Dialog`

Expected shape:

```php
public function dialog(): Dialog
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Dialog($this->core, $this->log);
    }

    return $this->serviceCache[__METHOD__];
}
```

Place the method near `chat()` / `message()` so IM service accessors stay grouped.

### 2. `tests/Unit/Services/IM/IMServiceBuilderTest.php`

Add `testGetDialogService()` mirroring the existing cache assertions for
`chat()` and `message()`.

### 3. `phpunit.xml.dist`

Add a dedicated IM Dialog testsuite immediately after the existing IM Chat and
IM Message suites:

```xml
<testsuite name="integration_tests_im_dialog">
    <directory>./tests/Integration/Services/IM/Dialog/</directory>
</testsuite>
```

### 4. `Makefile`

Modify two places:

- the help section near the other IM integration targets:

```make
	@echo "test-integration-im-dialog - run IM Dialog integration tests"
```

- the phony target block near `test-integration-im-chat` and
  `test-integration-im-message`:

```make
.PHONY: test-integration-im-dialog
test-integration-im-dialog:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_dialog
```

### 5. `CHANGELOG.md`

Add this line under `## 3.2.0 – UNRELEASED` -> `### Added` near the other IM
entries:

```markdown
- Added `Bitrix24\SDK\Services\IM\Dialog\Service\Dialog` service for `im.dialog.*` support, with typed result wrappers, `IMServiceBuilder::dialog()`, and dedicated unit/integration/annotation coverage ([#425](https://github.com/bitrix24/b24phpsdk/issues/425))
```

---

## Execution Order

### 1. Builder and result scaffolding

- add the new result wrapper and item-result classes under
  `src/Services/IM/Dialog/Result/`
- add the `Dialog` service class under `src/Services/IM/Dialog/Service/`
- add `IMServiceBuilder::dialog()`

### 2. Unit tests first

- write `tests/Unit/Services/IM/Dialog/Service/DialogTest.php`
- extend `IMServiceBuilderTest` with `testGetDialogService()`
- run the new unit tests and fix the service payload mapping until green

### 3. Integration helper and service tests

- add `DialogChatTestCase`
- add `DialogTest`
- ensure the helper creates dialogs and seed messages deterministically enough
  for `messagesGet`, `messagesSearch`, `read`, and `unread`

### 4. Annotation tests

- implement one dedicated `*AnnotationsTest` per item result class
- build field lists from live response items because this scope has no
  `fields()->getFieldsDescription()` endpoint
- verify both completeness and runtime type compatibility

### 5. Test entry points and changelog

- add the `integration_tests_im_dialog` suite
- add `make test-integration-im-dialog`
- add the changelog entry with the issue link

---

## Deptrac compliance

The new code stays inside the existing `Services` layer and should only depend
on:

- `Bitrix24\SDK\Attributes\*`
- `Bitrix24\SDK\Core\*`
- `Bitrix24\SDK\Services\AbstractService`
- sibling IM result and service classes used only from integration tests

No new `Application`, `Infrastructure`, or `Legacy` dependencies are required.
The test helper must remain under `tests/Integration` and not introduce runtime
dependencies into production code.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-im-dialog
```
