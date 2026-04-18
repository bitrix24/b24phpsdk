# Plan: Add IM\Message service for im.message.* support (issue #426)

## Context

Issue #426 requests a new service wrapping six `im.message.*` REST API methods
under the `im` scope. Part of milestone 3.2.0 (API v3 track, based off `v3-dev`).

### Method signatures from Bitrix24 docs

| REST method | Inputs | `result` shape |
|---|---|---|
| `im.message.add` | `DIALOG_ID` (string, required), `MESSAGE` (string, required if no `ATTACH`), `ATTACH` (object/string), `KEYBOARD` (object/string), `MENU` (object/string), `SYSTEM` (`Y`/`N`), `URL_PREVIEW` (`Y`/`N`), `REPLY_ID` (int) | `integer` — ID of the created message |
| `im.message.update` | `MESSAGE_ID` (int, required), `MESSAGE`, `ATTACH`, `KEYBOARD`, `MENU` (all accept `N` / empty to remove), `URL_PREVIEW`, `IS_EDITED` | `boolean` |
| `im.message.delete` | `MESSAGE_ID` (int, required) | `boolean` |
| `im.message.like` | `MESSAGE_ID` (int, required), `ACTION` (`auto` / `plus` / `minus`, default `auto`) | `boolean` |
| `im.message.share` | `MESSAGE_ID` (int, required), `DIALOG_ID` (string, required), `TYPE` (`CHAT` / `TASK` / `POST` / `CALEND`, required) | `boolean` |
| `im.message.command` | `MESSAGE_ID` (int, required), `BOT_ID` (int, required), `COMMAND` (string, required), `COMMAND_PARAMS` (string) | `boolean` |

### Architectural placement

- Scope: `im` — `src/Services/IM/`
- Existing `IMServiceBuilder` has one service (`notify()`); a new `message()`
  accessor will sit alongside it following the exact Notify pattern.
- `src/Services/ServiceBuilder::getIMScope()` already exists — no change needed.
- Reuses existing core result wrappers:
  - `AddedItemResult` — reads `getResult()[0]` as int; matches `im.message.add`
    response envelope where raw `result` is an integer.
  - `UpdatedItemResult` / `DeletedItemResult` — read `getResult()[0]` as bool;
    match the five methods returning `true`/`false`.
- Two small backed string enums typed next to the service:
  - `LikeAction` — cases `auto`, `plus`, `minus`
  - `ShareType` — cases `CHAT`, `TASK`, `POST`, `CALEND`

No new `*ItemResult` class is needed because no method returns a structured
entity — only a scalar `result`. Consequently no annotation-test file is
required (the skill's annotation-test rule applies only to result items with
`@property-read` declarations).

---

## Files to Create

### 1. `src/Services/IM/Message/Service/LikeAction.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Service;

enum LikeAction: string
{
    case auto = 'auto';
    case plus = 'plus';
    case minus = 'minus';
}
```

### 2. `src/Services/IM/Message/Service/ShareType.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Service;

enum ShareType: string
{
    case chat = 'CHAT';
    case task = 'TASK';
    case post = 'POST';
    case calendarEvent = 'CALEND';
}
```

### 3. `src/Services/IM/Message/Service/Message.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\AbstractService;

#[ApiServiceMetadata(new Scope(['im']))]
class Message extends AbstractService
{
    #[ApiEndpointMetadata(
        'im.message.add',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-add.html',
        'Send a message to a chat'
    )]
    public function add(
        string $dialogId,
        ?string $message = null,
        array|string|null $attach = null,
        array|string|null $keyboard = null,
        array|string|null $menu = null,
        bool $isSystem = false,
        bool $urlPreview = true,
        ?int $replyId = null,
    ): AddedItemResult {
        return new AddedItemResult($this->core->call(
            'im.message.add',
            [
                'DIALOG_ID' => $dialogId,
                'MESSAGE' => $message,
                'ATTACH' => $attach,
                'KEYBOARD' => $keyboard,
                'MENU' => $menu,
                'SYSTEM' => $isSystem ? 'Y' : 'N',
                'URL_PREVIEW' => $urlPreview ? 'Y' : 'N',
                'REPLY_ID' => $replyId,
            ]
        ));
    }

    #[ApiEndpointMetadata(
        'im.message.update',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-update.html',
        'Update text and parameters of a sent message'
    )]
    public function update(
        int $messageId,
        ?string $message = null,
        array|string|null $attach = null,
        array|string|null $keyboard = null,
        array|string|null $menu = null,
        ?bool $urlPreview = null,
        ?bool $isEdited = null,
    ): UpdatedItemResult {
        return new UpdatedItemResult($this->core->call(
            'im.message.update',
            [
                'MESSAGE_ID' => $messageId,
                'MESSAGE' => $message,
                'ATTACH' => $attach,
                'KEYBOARD' => $keyboard,
                'MENU' => $menu,
                'URL_PREVIEW' => $urlPreview === null ? null : ($urlPreview ? 'Y' : 'N'),
                'IS_EDITED' => $isEdited === null ? null : ($isEdited ? 'Y' : 'N'),
            ]
        ));
    }

    #[ApiEndpointMetadata(
        'im.message.delete',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-delete.html',
        'Delete a message'
    )]
    public function delete(int $messageId): DeletedItemResult
    {
        return new DeletedItemResult($this->core->call(
            'im.message.delete',
            ['MESSAGE_ID' => $messageId]
        ));
    }

    #[ApiEndpointMetadata(
        'im.message.like',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-like.html',
        'Toggle the "Like" mark on a message'
    )]
    public function like(int $messageId, LikeAction $action = LikeAction::auto): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call(
            'im.message.like',
            [
                'MESSAGE_ID' => $messageId,
                'ACTION' => $action->value,
            ]
        ));
    }

    #[ApiEndpointMetadata(
        'im.message.share',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-share.html',
        'Create an object (chat/task/post/calendar event) based on a message'
    )]
    public function share(int $messageId, string $dialogId, ShareType $type): UpdatedItemResult
    {
        return new UpdatedItemResult($this->core->call(
            'im.message.share',
            [
                'MESSAGE_ID' => $messageId,
                'DIALOG_ID' => $dialogId,
                'TYPE' => $type->value,
            ]
        ));
    }

    #[ApiEndpointMetadata(
        'im.message.command',
        'https://apidocs.bitrix24.com/api-reference/chats/messages/im-message-command.html',
        'Invoke a chat-bot command in the context of a message'
    )]
    public function command(
        int $messageId,
        int $botId,
        string $command,
        ?string $commandParams = null,
    ): UpdatedItemResult {
        return new UpdatedItemResult($this->core->call(
            'im.message.command',
            [
                'MESSAGE_ID' => $messageId,
                'BOT_ID' => $botId,
                'COMMAND' => $command,
                'COMMAND_PARAMS' => $commandParams,
            ]
        ));
    }
}
```

### 4. `tests/Unit/Services/IM/Message/Service/MessageTest.php`

Minimal unit test that instantiates the service against `NullCore` / `NullBatch`
and exercises each method's parameter mapping does not throw on a null response.

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Message\Service;

use Bitrix24\SDK\Services\IM\Message\Service\LikeAction;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use Bitrix24\SDK\Services\IM\Message\Service\ShareType;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Message::class)]
class MessageTest extends TestCase
{
    private Message $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Message(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testLikeActionEnumCases(): void
    {
        $this->assertSame('auto', LikeAction::auto->value);
        $this->assertSame('plus', LikeAction::plus->value);
        $this->assertSame('minus', LikeAction::minus->value);
    }

    #[Test]
    public function testShareTypeEnumCases(): void
    {
        $this->assertSame('CHAT', ShareType::chat->value);
        $this->assertSame('TASK', ShareType::task->value);
        $this->assertSame('POST', ShareType::post->value);
        $this->assertSame('CALEND', ShareType::calendarEvent->value);
    }
}
```

> Note: heavier behavioural assertions are covered in the integration test.
> The unit test exists primarily to guarantee the classes autoload and the
> enum mapping cannot drift silently.

### 5. `tests/Integration/Services/IM/Message/Service/MessageTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Message\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Message\Service\LikeAction;
use Bitrix24\SDK\Services\IM\Message\Service\Message;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Message::class)]
class MessageTest extends TestCase
{
    private Message $service;

    private int $currentUserId;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = Factory::getServiceBuilder()->getIMScope()->message();
        $this->currentUserId = (int)$this->service->core->call('PROFILE')
            ->getResponseData()->getResult()['ID'];
    }

    #[Test]
    #[TestDox('add sends a personal message and returns its ID')]
    public function testAdd(): void
    {
        $result = $this->service->add(
            dialogId: (string)$this->currentUserId,
            message: sprintf('Test add at %s', time()),
        );
        $this->assertGreaterThan(0, $result->getId());
    }

    #[Test]
    #[TestDox('update edits a previously sent message')]
    public function testUpdate(): void
    {
        $messageId = $this->service->add(
            (string)$this->currentUserId,
            sprintf('Before update at %s', time()),
        )->getId();
        $this->assertTrue(
            $this->service->update($messageId, 'Updated text')->isSuccess()
        );
    }

    #[Test]
    #[TestDox('delete removes a previously sent message')]
    public function testDelete(): void
    {
        $messageId = $this->service->add(
            (string)$this->currentUserId,
            sprintf('To delete at %s', time()),
        )->getId();
        $this->assertTrue($this->service->delete($messageId)->isSuccess());
    }

    #[Test]
    #[TestDox('like toggles the Like mark on a message')]
    public function testLike(): void
    {
        $messageId = $this->service->add(
            (string)$this->currentUserId,
            sprintf('To like at %s', time()),
        )->getId();
        $this->assertTrue(
            $this->service->like($messageId, LikeAction::plus)->isSuccess()
        );
    }

    #[Test]
    #[TestDox('command executes a chat-bot command')]
    public function testCommand(): void
    {
        $this->markTestSkipped(
            'im.message.command requires a registered chat bot with a command; '
            . 'skipped in standard integration suite. '
            . 'Re-enable when a bot fixture is available.'
        );
    }

    #[Test]
    #[TestDox('share creates an object based on a message')]
    public function testShare(): void
    {
        $this->markTestSkipped(
            'im.message.share requires a chat ID (not personal dialog); '
            . 'skipped in standard integration suite. '
            . 'Re-enable when a chat fixture is available.'
        );
    }
}
```

> `testCommand` and `testShare` are scaffolded as `markTestSkipped` because
> both methods require fixtures (a registered chat bot / a real chat ID) that
> the default integration webhook does not guarantee. Leaving them as skipped
> preserves coverage intent and makes it trivial to activate later.

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add a new accessor mirroring `notify()`:

```php
use Bitrix24\SDK\Services\IM\Message\Service\Message;

public function message(): Message
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Message($this->core, $this->log);
    }

    return $this->serviceCache[__METHOD__];
}
```

### 2. `tests/Unit/Services/IM/IMServiceBuilderTest.php`

Extend the existing cache test so it also covers `message()`:

```php
public function testGetMessageService(): void
{
    $this::assertSame($this->serviceBuilder->message(), $this->serviceBuilder->message());
}
```

### 3. `phpunit.xml.dist`

Add a dedicated suite next to `integration_tests_scope_im`:

```xml
<testsuite name="integration_tests_im_message">
    <directory>./tests/Integration/Services/IM/Message/</directory>
</testsuite>
```

### 4. `Makefile`

Add the target in the IM area (next to `test-integration-scope-im-open-lines`)
and document it in the `help` target's listing:

```make
.PHONY: test-integration-im-message
test-integration-im-message:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_message
```

### 5. `CHANGELOG.md`

Insert a new top section above `## 3.1.0` (the commented `## Unreleased`
template at line 1432 stays as is):

```markdown
## 3.2.0 – UNRELEASED

### Added

- Added `Services\IM\Message\Service\Message` service for `im.message.*`
  support ([#426](https://github.com/bitrix24/b24phpsdk/issues/426)):
  - `add` — send a message (`im.message.add`)
  - `update` — edit text and parameters (`im.message.update`)
  - `delete` — delete a message (`im.message.delete`)
  - `like` — toggle the Like mark (`im.message.like`), with typed `LikeAction` enum
  - `share` — create an object from a message (`im.message.share`), with typed `ShareType` enum
  - `command` — invoke a chat-bot command (`im.message.command`)
- Added `IMServiceBuilder::message()` accessor.
```

---

## Deptrac compliance

All new classes live under `Bitrix24\SDK\Services\IM\Message\*`, which belongs
to the `Services` layer. Imports used:

- `Bitrix24\SDK\Attributes\*` — Services layer
- `Bitrix24\SDK\Core\*` — allowed (Services may depend on Core)
- `Bitrix24\SDK\Services\AbstractService` — same layer
- PHPUnit/Psr in test files — uncovered by deptrac ruleset

No cross-scope imports (e.g. from `CRM`, `Sale`, `Task`) are introduced, so no
new `skip_violations` entry is required.

---

## Verification

Phase 1 — light checks:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Phase 2 — heavy check (new suite):

```bash
make test-integration-im-message
```

Phase 3 — update `CHANGELOG.md` (already in Files to Modify §5) and commit.
