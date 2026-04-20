# Plan: Add IM\Chat service for im.chat.* support (issue #423)

## Context

Bitrix24 REST exposes a group of `im.chat.*` methods that manage chat lifecycle and chat-level
settings. The SDK has an IM scope (`src/Services/IM/`) that currently wraps only `im.notify.*`
(see `Bitrix24\SDK\Services\IM\Notify\Service\Notify`) and `placement.*` helpers for IM widgets.
There is no service yet for chat management. This task adds a new `Chat` service under the IM
scope that covers eight endpoints from https://apidocs.bitrix24.com/api-reference/chats/.

### REST methods to wrap

| Method | Request shape | Response shape (`result`) |
|---|---|---|
| `im.chat.add` | `USERS[]` + optional `TYPE`, `TITLE`, `DESCRIPTION`, `COLOR`, `MESSAGE`, `AVATAR`, `ENTITY_TYPE`, `ENTITY_ID`, `COPILOT_MAIN_ROLE` | scalar `int` — new chat ID |
| `im.chat.get` | `ENTITY_TYPE`, `ENTITY_ID` | `{"ID": int}` when found, `null` when not found |
| `im.chat.leave` | `CHAT_ID` | scalar `bool` |
| `im.chat.mute` | either `CHAT_ID` or `DIALOG_ID`, optional `MUTE` (`Y`/`N`) | scalar `bool` |
| `im.chat.setOwner` | `CHAT_ID`, `USER_ID` | scalar `bool` |
| `im.chat.updateAvatar` | `CHAT_ID`, `AVATAR` (base64) | scalar `bool` |
| `im.chat.updateColor` | `CHAT_ID`, `COLOR` | scalar `bool` |
| `im.chat.updateTitle` | `CHAT_ID`, `TITLE` | scalar `bool` |

Response-envelope rules (from `src/Core/Response/Response.php`):

- When `result` is scalar (int / bool), it is wrapped into `[result]`, so
  `AddedItemResult::getId()` → `getResult()[0]` and `UpdatedItemResult::isSuccess()` → `getResult()[0]` work.
- When `result` is an associative array (`{"ID": 1437}`), it is kept as-is, so
  `getResult()` returns `["ID" => 1437]`.
- When `result` is `null` (chat not found), it is wrapped into `[null]`, so
  `getResult()[0]` is `null`.

### Design decisions (agreed with user)

- `im.chat.mute` gets **two** service methods: `mute(int $chatId, bool $mute)` and
  `muteByDialog(string $dialogId, bool $mute)` — each endpoint shape stays explicit.
- Chat-specific string parameters (`TYPE`, `COLOR`, `ENTITY_TYPE`) are modelled as
  scope-local PHP 8.1 string-backed enums under `Bitrix24\SDK\Services\IM\Chat`:
  `ChatType`, `ChatColor`, `ChatEntityType`. Placed inside the Chat folder (not reused from
  `Services\IM\Placements\PlacementColor`) because semantics differ — a placement color
  applies to a widget, a chat color applies to a chat.
- `ChatEntityType` covers the **union** of values from both `im.chat.add` and `im.chat.get`:
  `VIDEOCONF`, `AI_ASSISTANT_PRIVATE`, `LINES`, `LIVECHAT`, `ANNOUNCEMENT`, `CALENDAR`,
  `MAIL`, `CRM`, `SONET_GROUP`, `TASKS`, `TASKS_TASK`, `CALL`. The extra `TASKS_TASK`
  only appears in `im.chat.get` documentation; keeping it in one shared enum avoids a
  duplicate type.
- `im.chat.get` returns `null` when no chat matches — `ChatResult::chat()` returns
  `?ChatItemResult` (nullable) to reflect that.
- Service method `im.chat.add` is exposed as `add()` with typed parameters (positional
  required `users`, then optional nullable parameters). No DTO/builder — keeps parity with
  the existing `Notify::fromSystem()` style in the same scope.

---

## Files to Create

### 1. `src/Services/IM/Chat/ChatType.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Chat;

enum ChatType: string
{
    case Open = 'OPEN';
    // API value 'CHAT' denotes a closed / non-public chat.
    case Closed = 'CHAT';
}
```

### 2. `src/Services/IM/Chat/ChatColor.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Chat;

enum ChatColor: string
{
    case Red = 'RED';
    case Green = 'GREEN';
    case Mint = 'MINT';
    case LightBlue = 'LIGHT_BLUE';
    case DarkBlue = 'DARK_BLUE';
    case Purple = 'PURPLE';
    case Aqua = 'AQUA';
    case Pink = 'PINK';
    case Lime = 'LIME';
    case Brown = 'BROWN';
    case Azure = 'AZURE';
    case Khaki = 'KHAKI';
    case Sand = 'SAND';
    case Marengo = 'MARENGO';
    case Gray = 'GRAY';
    case Graphite = 'GRAPHITE';
}
```

### 3. `src/Services/IM/Chat/ChatEntityType.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Chat;

enum ChatEntityType: string
{
    case VideoConf = 'VIDEOCONF';
    case AiAssistantPrivate = 'AI_ASSISTANT_PRIVATE';
    case Lines = 'LINES';
    case LiveChat = 'LIVECHAT';
    case Announcement = 'ANNOUNCEMENT';
    case Calendar = 'CALENDAR';
    case Mail = 'MAIL';
    case Crm = 'CRM';
    case SonetGroup = 'SONET_GROUP';
    case Tasks = 'TASKS';
    case TasksTask = 'TASKS_TASK';
    case Call = 'CALL';
}
```

### 4. `src/Services/IM/Chat/Result/ChatItemResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Chat\Result;

use Bitrix24\SDK\Core\Result\AbstractItem;

/**
 * @property-read int $ID chat identifier returned by im.chat.get
 */
class ChatItemResult extends AbstractItem
{
}
```

### 5. `src/Services/IM/Chat/Result/ChatResult.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Chat\Result;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AbstractResult;

class ChatResult extends AbstractResult
{
    /**
     * @throws BaseException
     */
    public function chat(): ?ChatItemResult
    {
        $result = $this->getCoreResponse()->getResponseData()->getResult();

        if (!array_key_exists('ID', $result)) {
            return null;
        }

        return new ChatItemResult($result);
    }
}
```

### 6. `src/Services/IM/Chat/Service/Chat.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Chat\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\IM\Chat\ChatColor;
use Bitrix24\SDK\Services\IM\Chat\ChatEntityType;
use Bitrix24\SDK\Services\IM\Chat\ChatType;
use Bitrix24\SDK\Services\IM\Chat\Result\ChatResult;

#[ApiServiceMetadata(new Scope(['im']))]
class Chat extends AbstractService
{
    /**
     * @param positive-int[] $users
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.add',
        'https://apidocs.bitrix24.com/api-reference/chats/im-chat-add.html',
        'Create a new chat'
    )]
    public function add(
        array $users,
        ?ChatType $type = null,
        ?string $title = null,
        ?string $description = null,
        ?ChatColor $color = null,
        ?string $message = null,
        ?string $avatar = null,
        ?ChatEntityType $entityType = null,
        ?string $entityId = null,
        ?string $copilotMainRole = null,
    ): AddedItemResult {
        $payload = ['USERS' => $users];
        if ($type !== null) {
            $payload['TYPE'] = $type->value;
        }
        if ($title !== null) {
            $payload['TITLE'] = $title;
        }
        if ($description !== null) {
            $payload['DESCRIPTION'] = $description;
        }
        if ($color !== null) {
            $payload['COLOR'] = $color->value;
        }
        if ($message !== null) {
            $payload['MESSAGE'] = $message;
        }
        if ($avatar !== null) {
            $payload['AVATAR'] = $avatar;
        }
        if ($entityType !== null) {
            $payload['ENTITY_TYPE'] = $entityType->value;
        }
        if ($entityId !== null) {
            $payload['ENTITY_ID'] = $entityId;
        }
        if ($copilotMainRole !== null) {
            $payload['COPILOT_MAIN_ROLE'] = $copilotMainRole;
        }

        return new AddedItemResult($this->core->call('im.chat.add', $payload));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.get',
        'https://apidocs.bitrix24.com/api-reference/chats/im-chat-get.html',
        'Get chat id by linked entity type and id'
    )]
    public function get(ChatEntityType $entityType, string $entityId): ChatResult
    {
        return new ChatResult($this->core->call('im.chat.get', [
            'ENTITY_TYPE' => $entityType->value,
            'ENTITY_ID' => $entityId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.leave',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-users/im-chat-leave.html',
        'Remove the current user from a chat'
    )]
    public function leave(int $chatId): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.leave', [
            'CHAT_ID' => $chatId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.mute',
        'https://apidocs.bitrix24.com/api-reference/chats/special-operations/im-chat-mute.html',
        'Mute/unmute notifications in a chat by chat id'
    )]
    public function mute(int $chatId, bool $mute = true): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.mute', [
            'CHAT_ID' => $chatId,
            'MUTE' => $mute ? 'Y' : 'N',
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.mute',
        'https://apidocs.bitrix24.com/api-reference/chats/special-operations/im-chat-mute.html',
        'Mute/unmute notifications in a chat by dialog id'
    )]
    public function muteByDialog(string $dialogId, bool $mute = true): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.mute', [
            'DIALOG_ID' => $dialogId,
            'MUTE' => $mute ? 'Y' : 'N',
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.setOwner',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-update/im-chat-set-owner.html',
        'Change chat owner'
    )]
    public function setOwner(int $chatId, int $userId): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.setOwner', [
            'CHAT_ID' => $chatId,
            'USER_ID' => $userId,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.updateAvatar',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-update/im-chat-update-avatar.html',
        'Update chat avatar'
    )]
    public function updateAvatar(int $chatId, string $avatar): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.updateAvatar', [
            'CHAT_ID' => $chatId,
            'AVATAR' => $avatar,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.updateColor',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-update/im-chat-update-color.html',
        'Update chat color (mobile app)'
    )]
    public function updateColor(int $chatId, ChatColor $color): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.updateColor', [
            'CHAT_ID' => $chatId,
            'COLOR' => $color->value,
        ]));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'im.chat.updateTitle',
        'https://apidocs.bitrix24.com/api-reference/chats/chat-update/im-chat-update-title.html',
        'Update chat title'
    )]
    public function updateTitle(int $chatId, string $title): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call('im.chat.updateTitle', [
            'CHAT_ID' => $chatId,
            'TITLE' => $title,
        ]));
    }
}
```

### 7. `tests/Unit/Services/IM/Chat/Service/ChatTest.php`

Unit test covering the service can be constructed with stubs and the public surface is
callable. Follows the `NullCore` pattern from `docs/testing.md`.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Chat\Service;

use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Chat::class)]
class ChatTest extends TestCase
{
    private Chat $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Chat(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testServiceInstantiates(): void
    {
        $this->assertInstanceOf(Chat::class, $this->service);
    }
}
```

### 8. `tests/Integration/Services/IM/Chat/Service/ChatTest.php`

Integration test that exercises the full CRUD-style flow against a real portal.
`setUp()` captures current user id via `PROFILE` and creates a fresh chat per test
(or reuses one via `setUpBeforeClass`-style caching if API rate-limits hit).
`tearDown()` calls `$service->leave($chatId)` to let the current user exit any chat that
survived so the portal is not polluted.

Covered flows:

- `testAdd` → creates a chat, asserts `getId() > 0`
- `testGet` → creates a chat with `ENTITY_TYPE = CALENDAR` + unique `ENTITY_ID`, calls
  `get()` and asserts the returned `ChatItemResult::$ID` matches the created id
- `testGetReturnsNullForUnknownEntity` → `get()` with a random `ENTITY_ID`; asserts
  `chat()` returns `null`
- `testMute` / `testMuteByDialog` → asserts `isSuccess()` is true
- `testSetOwner` → creates a chat with another member; calls `setOwner(chatId, memberId)`;
  asserts `isSuccess()` is true
- `testUpdateAvatar` → sends a tiny base64 PNG; asserts `isSuccess()`
- `testUpdateColor` → updates to each of a couple of `ChatColor` cases
- `testUpdateTitle` → updates to a unique string; asserts `isSuccess()`
- `testLeave` → creates a chat, leaves it, asserts `isSuccess()`

Users for the `USERS` parameter are resolved in `setUp()` via `PROFILE` (current user id);
at minimum the test uses `[currentUserId]`. The API deduplicates the creator automatically.

### 9. `tests/Integration/Services/IM/Chat/Result/ChatItemResultTest.php`

Dedicated result-item annotation test, following the mandatory template from
`b24phpsdk-maintainer` skill.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Chat\Result;

use Bitrix24\SDK\Services\IM\Chat\ChatEntityType;
use Bitrix24\SDK\Services\IM\Chat\Result\ChatItemResult;
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ChatItemResult::class)]
class ChatItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private Chat $chatService;
    private int $createdChatId = 0;
    private string $entityId = '';

    #[\Override]
    protected function setUp(): void
    {
        $this->chatService = Factory::getServiceBuilder()->getIMScope()->chat();

        $currentUserId = (int)$this->chatService->core
            ->call('PROFILE')->getResponseData()->getResult()['ID'];

        $this->entityId = sprintf('ANNOTATION_%s', uniqid('', true));
        $this->createdChatId = $this->chatService->add(
            users: [$currentUserId],
            title: sprintf('Annotation test chat %s', uniqid('', true)),
            entityType: ChatEntityType::Calendar,
            entityId: $this->entityId,
        )->getId();
    }

    #[\Override]
    protected function tearDown(): void
    {
        if ($this->createdChatId > 0) {
            $this->chatService->leave($this->createdChatId);
        }
    }

    #[Test]
    #[TestDox('all fields in ChatItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        $rawItem = $this->chatService->get(
            ChatEntityType::Calendar,
            $this->entityId,
        )->getCoreResponse()->getResponseData()->getResult();

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            ChatItemResult::class,
        );
    }

    #[Test]
    #[TestDox('all fields in ChatItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $chatItem = $this->chatService->get(
            ChatEntityType::Calendar,
            $this->entityId,
        )->chat();

        $this->assertNotNull($chatItem);
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $chatItem,
            ChatItemResult::class,
        );
    }
}
```

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add a `chat()` method alongside the existing `notify()`:

```php
use Bitrix24\SDK\Services\IM\Chat\Service\Chat;

public function chat(): Chat
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Chat($this->core, $this->log);
    }

    return $this->serviceCache[__METHOD__];
}
```

### 2. `tests/Unit/Services/IM/IMServiceBuilderTest.php`

Extend the existing `testGetIMService` (or add `testGetChatService`) to also assert
that `$this->serviceBuilder->chat()` returns the same instance across calls (caching).

### 3. `phpunit.xml.dist`

Add a new test suite after `integration_tests_scope_im_open_lines_connector` (placed
next to other IM suites):

```xml
<testsuite name="integration_tests_im_chat">
    <directory>./tests/Integration/Services/IM/Chat/</directory>
</testsuite>
```

### 4. `Makefile`

Add a target next to the other IM targets (after the IM-OpenLines block, or right after
the `integration_tests_scope_im` usage):

```makefile
.PHONY: test-integration-im-chat
test-integration-im-chat:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_chat
```

Also add the corresponding help line at the top of `Makefile` (the `@echo` block near
line 78 that documents IM-related targets).

### 5. `CHANGELOG.md`

Under `## 3.2.0 – UNRELEASED` → `### Added`, append:

```markdown
- Added `Bitrix24\SDK\Services\IM\Chat\Service\Chat` service wrapping `im.chat.add`, `im.chat.get`, `im.chat.leave`, `im.chat.mute`, `im.chat.setOwner`, `im.chat.updateAvatar`, `im.chat.updateColor`, `im.chat.updateTitle`, with enums `ChatType`, `ChatColor`, `ChatEntityType`, `ChatItemResult`/`ChatResult`, and `IMServiceBuilder::chat()` accessor ([#423](https://github.com/bitrix24/b24phpsdk/issues/423))
```

---

## Deptrac compliance

All new classes live in the Services layer (`Bitrix24\SDK\Services\IM\Chat\*`) and depend
only on Core (`Bitrix24\SDK\Core\*`) and sibling Services classes (`Bitrix24\SDK\Services\AbstractService`).
No imports from Application or Infrastructure. No new `skip_violations` entries needed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-im-chat
```

All must pass before the PR is created.
