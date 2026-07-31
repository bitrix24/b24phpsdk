# Plan: Add mail.* v3 service - mailbox/message/recipient (issue #516)

## Context

Issue #516 requests typed SDK support for the Bitrix24 REST API v3 `mail.*` scope:

- `mail.mailbox.*` - 5 methods
- `mail.message.*` - 15 methods
- `mail.recipient.*` - 4 methods

This scope is distinct from the existing `src/Services/MailService/` scope, which wraps
legacy `mailservice.*` methods. The new implementation must live under
`src/Services/Mail/` and must be registered as `ServiceBuilder::getMailScope()` to avoid
any naming collision with `ServiceBuilder::getMailServiceScope()`.

Field metadata methods must live in separate services, not on the entity services. Follow
the existing v3 field-service pattern from `TaskServiceBuilder::taskField()`,
`TaskServiceBuilder::taskChatMessageField()`, and `MainServiceBuilder::eventLogField()`:

- `mail.mailbox.field.*` -> `MailServiceBuilder::mailboxField()`
- `mail.message.field.*` -> `MailServiceBuilder::messageField()`
- `mail.recipient.field.*` -> `MailServiceBuilder::recipientField()`

The branch is `feature/516-add-mail-v3-service`, based on `origin/v3-dev`.

Required pre-work already completed in this worktree:

```bash
make composer-install
make oa-schema-build
make test-unit
```

`make test-unit` baseline: OK, 969 tests, 2723 assertions.

### Official API documentation findings

All methods belong to module `mail`, scope `mail`, API v3. SDK calls must use
`ApiVersion::v3` in both `ApiEndpointMetadata` and `CoreInterface::call()`.

Use English documentation links in `ApiEndpointMetadata`, under
`https://apidocs.bitrix24.com/api-reference/rest-v3/mail/...`.

Mailbox methods:

| Method | Parameters | Result envelope |
|---|---|---|
| `mail.mailbox.field.get` | `name` required, `select` optional | `result.item` field metadata |
| `mail.mailbox.field.list` | `select` optional | `result.items` field metadata |
| `mail.mailbox.get` | `id` required, `select` optional | `result.item` mailbox |
| `mail.mailbox.list` | docs mention `name`, `email`, `pagination`; OpenAPI snapshot exposes `select`, `filter`, `order`, `pagination` | `result.items` mailboxes |
| `mail.mailbox.senders` | `pagination` optional | `result.items` sender records |

Message methods:

| Method | Parameters | Result envelope |
|---|---|---|
| `mail.message.createcalendarevent` | `messageId`, `dateFrom`, `dateTo` required; `name`, `description` optional | `result` object: `success`, `eventId`, `messageId` |
| `mail.message.createchat` | `messageId` required | `result` object: `success`, `chatId`, `messageId`, `existing` |
| `mail.message.createcrmactivity` | `messageId` required | plain boolean `result` |
| `mail.message.createfeedpost` | `messageId` required; `title` optional | `result` object: `success`, `postId`, `messageId` |
| `mail.message.createtask` | `messageId` required; `title`, `responsibleId`, `description` optional | `result` object: `success`, `taskId`, `messageId` |
| `mail.message.field.get` | `name` required, `select` optional | `result.item` field metadata |
| `mail.message.field.list` | `select` optional | `result.items` field metadata |
| `mail.message.forward` | `forwardMessageId`, `from`, `to`, `subject`, `body` required; `cc`, `bcc` optional | `result` object: `success`, `to` |
| `mail.message.get` | `id` required, `select` optional | `result.item` message |
| `mail.message.list` | `mailboxId` required in docs; `searchQuery`, `dateFrom`, `dateTo`, `isSeen`, `hasAttachments`, `folder`, `pagination` optional | `result.items` messages |
| `mail.message.movetofolder` | `messageIds`, `action` required; `folder` optional for `move` | `result` object: `success`, `movedCount`, `action` |
| `mail.message.removecrmactivity` | `messageId` required | plain boolean `result` |
| `mail.message.reply` | `replyToMessageId`, `from`, `to`, `subject`, `body` required; `cc`, `bcc` optional | `result` object: `success`, `to` |
| `mail.message.send` | `from`, `to`, `subject`, `body` required; `cc`, `bcc` optional | `result` object: `success`, `to` |
| `mail.message.thread` | `id` required, `limit` optional | plain array `result[]` thread messages |

Recipient methods:

| Method | Parameters | Result envelope |
|---|---|---|
| `mail.recipient.field.get` | `name` required, `select` optional | `result.item` field metadata |
| `mail.recipient.field.list` | `select` optional | `result.items` field metadata |
| `mail.recipient.listcontacts` | `query`, `pagination` optional | `result.items` contacts |
| `mail.recipient.listemployees` | `query` required, `pagination` optional | `result.items` employees |

Date/time SDK rule: public SDK method parameters representing date or date-time values must
use `CarbonImmutable`. Therefore `mail.message.createcalendarevent()` must accept
`CarbonImmutable $dateFrom` and `CarbonImmutable $dateTo` and format them as `Y-m-d H:i:s`;
`mail.message.list()` must accept optional `CarbonImmutable $dateFrom` and
`CarbonImmutable $dateTo` and format them as `CarbonImmutable::ATOM`.

### Generator usage

Before manually adding result item classes for generated DTO-backed results, run:

```bash
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator mail.mailbox.get --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator mail.mailbox.field.get --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator mail.message.get --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator mail.message.field.get --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator mail.recipient.listcontacts --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator mail.recipient.field.get --stage=all
```

Review generator output before keeping it. If a command does not produce the exact desired
file path/class name for this new scope, keep the generated annotations as the source of
truth and document the manual adjustment in this file before editing the generated class.

Generator fallback note: running
`docker compose run --rm php-cli php bin/console b24-dev:result-item-generator mail.mailbox.get --stage=all`
from the isolated `/private/tmp/b24phpsdk-516-add-mail-v3-service` worktree failed before
generation with `Unable to determine the current git branch`. Root cause: the Docker image
does not include `git`, and its fallback reads `.git/HEAD`; in a Git worktree `.git` is a
file pointing to the main repository metadata at a host path that is not mounted in the
container. For this issue, result item annotations will be created manually from the
official Bitrix24 method documentation and `docs/open-api/openapi.json`.

Do not use `generate-select-builder` or `generate-item-builder` unless implementation
discovers an established local pattern requiring dedicated builders for this scope; the
official docs expose only simple `select` arrays and request payloads for this issue.

## Files to Create

### 1. `src/Services/Mail/MailServiceBuilder.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail;

use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractServiceBuilder;
use Bitrix24\SDK\Services\Mail\MailboxField\Service\MailboxField;
use Bitrix24\SDK\Services\Mail\MessageField\Service\MessageField;
use Bitrix24\SDK\Services\Mail\RecipientField\Service\RecipientField;
use Bitrix24\SDK\Services\Mail\Service\Mailbox;
use Bitrix24\SDK\Services\Mail\Service\Message;
use Bitrix24\SDK\Services\Mail\Service\Recipient;

#[ApiServiceBuilderMetadata(new Scope(['mail']))]
class MailServiceBuilder extends AbstractServiceBuilder
{
    public function mailbox(): Mailbox;

    public function mailboxField(): MailboxField;

    public function message(): Message;

    public function messageField(): MessageField;

    public function recipient(): Recipient;

    public function recipientField(): RecipientField;
}
```

### 2. `src/Services/Mail/Service/Mailbox.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Mail\Result\MailboxItemResult;
use Bitrix24\SDK\Services\Mail\Result\MailboxResult;
use Bitrix24\SDK\Services\Mail\Result\MailboxesResult;
use Bitrix24\SDK\Services\Mail\Result\SendersResult;

#[ApiServiceMetadata(new Scope(['mail']))]
class Mailbox extends AbstractService
{
    public Batch $batch;

    public function get(int $id, array $select = []): MailboxResult;

    public function list(
        ?string $name = null,
        ?string $email = null,
        array $pagination = [],
        array $select = [],
        array $filter = [],
        array $order = [],
    ): MailboxesResult;

    public function senders(array $pagination = []): SendersResult;
}
```

### 3. `src/Services/Mail/Service/Message.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Mail\Result\BooleanResult;
use Bitrix24\SDK\Services\Mail\Result\CreateCalendarEventResult;
use Bitrix24\SDK\Services\Mail\Result\CreateChatResult;
use Bitrix24\SDK\Services\Mail\Result\CreateFeedPostResult;
use Bitrix24\SDK\Services\Mail\Result\CreateTaskResult;
use Bitrix24\SDK\Services\Mail\Result\MessageResult;
use Bitrix24\SDK\Services\Mail\Result\MessagesResult;
use Bitrix24\SDK\Services\Mail\Result\MessageThreadResult;
use Bitrix24\SDK\Services\Mail\Result\MoveToFolderResult;
use Bitrix24\SDK\Services\Mail\Result\SendMessageResult;
use Carbon\CarbonImmutable;

#[ApiServiceMetadata(new Scope(['mail']))]
class Message extends AbstractService
{
    public Batch $batch;

    public function createCalendarEvent(
        int $messageId,
        CarbonImmutable $dateFrom,
        CarbonImmutable $dateTo,
        ?string $name = null,
        ?string $description = null,
    ): CreateCalendarEventResult;

    public function createChat(int $messageId): CreateChatResult;

    public function createCrmActivity(int $messageId): BooleanResult;

    public function createFeedPost(int $messageId, ?string $title = null): CreateFeedPostResult;

    public function createTask(
        int $messageId,
        ?string $title = null,
        ?int $responsibleId = null,
        ?string $description = null,
    ): CreateTaskResult;

    public function forward(
        int $forwardMessageId,
        string $from,
        array $to,
        string $subject,
        string $body,
        array $cc = [],
        array $bcc = [],
    ): SendMessageResult;

    public function get(int $id, array $select = []): MessageResult;

    public function list(
        int $mailboxId,
        ?string $searchQuery = null,
        ?CarbonImmutable $dateFrom = null,
        ?CarbonImmutable $dateTo = null,
        ?bool $isSeen = null,
        ?bool $hasAttachments = null,
        ?string $folder = null,
        array $pagination = [],
    ): MessagesResult;

    public function moveToFolder(array $messageIds, string $action, ?string $folder = null): MoveToFolderResult;

    public function removeCrmActivity(int $messageId): BooleanResult;

    public function reply(
        int $replyToMessageId,
        string $from,
        array $to,
        string $subject,
        string $body,
        array $cc = [],
        array $bcc = [],
    ): SendMessageResult;

    public function send(string $from, array $to, string $subject, string $body, array $cc = [], array $bcc = []): SendMessageResult;

    public function thread(int $id, ?int $limit = null): MessageThreadResult;
}
```

### 4. `src/Services/Mail/Service/Recipient.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Mail\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Services\AbstractService;
use Bitrix24\SDK\Services\Mail\Result\RecipientsResult;

#[ApiServiceMetadata(new Scope(['mail']))]
class Recipient extends AbstractService
{
    public Batch $batch;

    public function listContacts(?string $query = null, array $pagination = []): RecipientsResult;

    public function listEmployees(string $query, array $pagination = []): RecipientsResult;
}
```

### 5. Field metadata services

Create three separate field services:

- `src/Services/Mail/MailboxField/Service/MailboxField.php`
- `src/Services/Mail/MessageField/Service/MessageField.php`
- `src/Services/Mail/RecipientField/Service/RecipientField.php`

Each service follows the same shape as `Task\TaskField\Service\TaskField`:

```php
#[ApiServiceMetadata(new Scope(['mail']))]
class MailboxField extends AbstractService
{
    public function get(string $name, array $select = []): MailboxFieldResult;

    public function list(array $select = []): MailboxFieldsResult;
}
```

Use equivalent class names and return types for `MessageField` and `RecipientField`.
Each `get()` method must guard against an empty field name, build `['name' => $name]`,
append `select` only when non-empty, and call the corresponding v3 method:

- `mail.mailbox.field.get`
- `mail.mailbox.field.list`
- `mail.message.field.get`
- `mail.message.field.list`
- `mail.recipient.field.get`
- `mail.recipient.field.list`

### 6. `src/Services/Mail/Service/Batch.php`

Create a batch facade for read/list/send-style methods that official docs do not mark as
batch-forbidden. Key methods:

```php
public function mailboxList(array $items): Generator;
public function messageList(array $items): Generator;
public function recipientListContacts(array $items): Generator;
public function recipientListEmployees(array $items): Generator;
public function messageSend(array $items): Generator;
```

If a method returns `ERROR_BATCH_METHOD_NOT_ALLOWED` in integration discovery, remove it
from the batch facade and document the reason in this plan before implementation continues.

### 7. Result classes under `src/Services/Mail/Result/`

Create result wrappers and item classes:

- `MailboxItemResult.php`
- `MailboxResult.php`
- `MailboxesResult.php`
- `SenderItemResult.php`
- `SendersResult.php`
- `MessageItemResult.php`
- `MessageResult.php`
- `MessagesResult.php`
- `MessageThreadResult.php`
- `RecipientItemResult.php`
- `RecipientsResult.php`
- `BooleanResult.php`
- `CreateCalendarEventItemResult.php`
- `CreateCalendarEventResult.php`
- `CreateChatItemResult.php`
- `CreateChatResult.php`
- `CreateFeedPostItemResult.php`
- `CreateFeedPostResult.php`
- `CreateTaskItemResult.php`
- `CreateTaskResult.php`
- `MoveToFolderItemResult.php`
- `MoveToFolderResult.php`
- `SendMessageItemResult.php`
- `SendMessageResult.php`

Create separate field metadata result classes:

- `src/Services/Mail/MailboxField/Result/MailboxFieldItemResult.php`
- `src/Services/Mail/MailboxField/Result/MailboxFieldResult.php`
- `src/Services/Mail/MailboxField/Result/MailboxFieldsResult.php`
- `src/Services/Mail/MessageField/Result/MessageFieldItemResult.php`
- `src/Services/Mail/MessageField/Result/MessageFieldResult.php`
- `src/Services/Mail/MessageField/Result/MessageFieldsResult.php`
- `src/Services/Mail/RecipientField/Result/RecipientFieldItemResult.php`
- `src/Services/Mail/RecipientField/Result/RecipientFieldResult.php`
- `src/Services/Mail/RecipientField/Result/RecipientFieldsResult.php`

`*ItemResult.php` classes with annotations must extend `AbstractAnnotatedItem` when type
casting is needed by annotation tests; otherwise follow the current generated item pattern.

### 8. Unit tests under `tests/Unit/Services/Mail/`

Create:

- `tests/Unit/Services/Mail/MailServiceBuilderTest.php`
- `tests/Unit/Services/Mail/Service/MailboxTest.php`
- `tests/Unit/Services/Mail/Service/MessageTest.php`
- `tests/Unit/Services/Mail/Service/RecipientTest.php`
- `tests/Unit/Services/Mail/Service/BatchTest.php`
- `tests/Unit/Services/Mail/MailboxField/Service/MailboxFieldTest.php`
- `tests/Unit/Services/Mail/MessageField/Service/MessageFieldTest.php`
- `tests/Unit/Services/Mail/RecipientField/Service/RecipientFieldTest.php`

Each service test must assert the exact REST method name, payload keys, and `ApiVersion::v3`
argument passed to `CoreInterface::call()`.

### 9. Integration tests under `tests/Integration/Services/Mail/`

Create:

- `tests/Integration/Services/Mail/Service/MailboxTest.php`
- `tests/Integration/Services/Mail/Service/MessageTest.php`
- `tests/Integration/Services/Mail/Service/RecipientTest.php`
- `tests/Integration/Services/Mail/Service/BatchTest.php`
- `tests/Integration/Services/Mail/Result/MailboxItemResultAnnotationsTest.php`
- `tests/Integration/Services/Mail/Result/SenderItemResultAnnotationsTest.php`
- `tests/Integration/Services/Mail/Result/MessageItemResultAnnotationsTest.php`
- `tests/Integration/Services/Mail/Result/RecipientItemResultAnnotationsTest.php`
- `tests/Integration/Services/Mail/MailboxField/Service/MailboxFieldTest.php`
- `tests/Integration/Services/Mail/MailboxField/Result/MailboxFieldItemResultAnnotationsTest.php`
- `tests/Integration/Services/Mail/MessageField/Service/MessageFieldTest.php`
- `tests/Integration/Services/Mail/MessageField/Result/MessageFieldItemResultAnnotationsTest.php`
- `tests/Integration/Services/Mail/RecipientField/Service/RecipientFieldTest.php`
- `tests/Integration/Services/Mail/RecipientField/Result/RecipientFieldItemResultAnnotationsTest.php`

Annotation test method names must be:

```php
public function testAllSystemFieldsAnnotated(): void;
public function testAllSystemFieldsHasValidTypeAnnotation(): void;
```

For integration tests that require a real mailbox/message, first discover whether the test
portal has mail data. If no mailbox/message exists, limit destructive/send tests to
non-destructive methods and mark the exact portal data blocker in the final report instead
of weakening unit coverage.

## Files to Modify

### 1. `src/Services/ServiceBuilder.php`

Add:

```php
use Bitrix24\SDK\Services\Mail\MailServiceBuilder;

public function getMailScope(): MailServiceBuilder
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new MailServiceBuilder(
            $this->core,
            $this->batch,
            $this->bulkItemsReader,
            $this->log
        );
    }

    return $this->serviceCache[__METHOD__];
}
```

Do not change `getMailServiceScope()`.

### 2. `phpunit.xml.dist`

Add a suite:

```xml
<testsuite name="integration_tests_scope_mail">
    <directory>./tests/Integration/Services/Mail/</directory>
</testsuite>
```

### 3. `Makefile`

Add a target:

```make
.PHONY: test-integration-scope-mail
test-integration-scope-mail:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_scope_mail
```

Add the target to the help output near other integration scope targets.

### 4. `CHANGELOG.md`

After phase 1 and phase 2 quality gates are green, add under
`## X.Y.Z Unreleased` -> `### Added`:

```markdown
- Added REST API v3 `mail.*` scope services for mailbox, message, and recipient methods ([#516](https://github.com/bitrix24/b24phpsdk/issues/516))
```

## Deptrac compliance

The new code must stay in the `Services` layer and depend only on:

- `Bitrix24\SDK\Attributes`
- `Bitrix24\SDK\Core\Contracts`
- `Bitrix24\SDK\Core\Credentials`
- `Bitrix24\SDK\Core\Exceptions`
- `Bitrix24\SDK\Core\Result`
- `Bitrix24\SDK\Services`
- `Carbon\CarbonImmutable`
- `Psr\Log`

Do not add `deptrac.yaml` skip violations. If `make lint-deptrac` reports a new violation,
fix the import or class location.

## Implementation steps

1. Keep the worktree clean except ignored `vendor/`, `composer.lock`, and `tests/.env.local`.
2. Run the result item generator commands listed above and inspect the generated output.
3. Add RED unit tests for `MailServiceBuilder`, `Mailbox`, `Message`, `Recipient`,
   `MailboxField`, `MessageField`, `RecipientField`, and `Batch`.
4. Implement `MailServiceBuilder` and register `getMailScope()` without touching
   `getMailServiceScope()`.
5. Implement `Mailbox`, `Message`, and `Recipient` service methods with v3 metadata and
   v3 `core->call()` calls; do not put field metadata methods on these services.
6. Implement `MailboxField`, `MessageField`, and `RecipientField` as separate metadata
   services with `get()` and `list()` methods.
7. Implement result wrappers for `result.item`, `result.items`, plain object `result`, plain
   boolean `result`, and plain array `result[]`; keep `mail.message.thread` on
   `MessageThreadResult` so it is not confused with `mail.message.list`.
8. Add focused integration tests for read-only methods and mandatory annotation/type tests
   for every annotated result item class.
9. Add `phpunit.xml.dist` suite and `Makefile` target.
10. Run phase 1 quality gate. Fix failures only after root-cause investigation.
11. Run phase 2 mail integration target. Fix failures only after root-cause investigation.
12. Update `CHANGELOG.md` only after both phases are green.

## Verification

Phase 1:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Phase 2:

```bash
make test-integration-scope-mail
```

Local phase 2 status:

- After updating `tests/.env.local` with a webhook that includes the runtime `mail` scope,
  `make test-integration-scope-mail` passed: 27 tests, 40 assertions, 11 skipped.
- Skipped tests are data-dependent checks for empty mailbox/contact/message collections on
  the test portal, not authorization failures.

Final pre-PR verification:

```bash
make lint-all
make test-unit
make test-integration-scope-mail
```
