# IM Message Attach Object API Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Add a typed, fluent PHP object API for `ATTACH` payloads in `im.message.add` and `im.message.update` while preserving backward compatibility for raw array and JSON-string payloads.

**Architecture:** Introduce a dedicated `Services\IM\Message\Attach` package with a root `Attach` payload object, typed block and item objects, and small string-backed enums for documented constrained values. Keep the wire format conversion localized to `build()` methods and widen the `Message` service boundary to accept either legacy raw payloads or the new `AttachPayloadInterface`.

**Tech Stack:** PHP 8.4, existing SDK service/builder conventions, PHPUnit 12, phpstan, php-cs-fixer, rector, deptrac, docker-compose based test commands.

---

## Context

Issue `#426` already added `Services\IM\Message\Service\Message` for the `im.message.*` REST methods and integration coverage for raw `ATTACH`, `MENU`, and `KEYBOARD` payloads. The current gap is DX: `ATTACH` still has to be assembled as nested associative arrays or JSON strings, which is error-prone and hard to discover in IDE autocomplete.

The approved design is stored in `.tasks/426/design.md`. The first iteration is intentionally limited to `ATTACH` only and covers these documented block types already exercised in integration tests:

- `MESSAGE`
- `LINK`
- `USER`
- `IMAGE`
- `FILE`
- `DELIMITER`
- `GRID`

Key constraints from the design:

- Keep `MENU` and `KEYBOARD` out of scope.
- Keep BB-code content as plain strings.
- Auto-select short vs full attach form inside the root `Attach` object.
- Preserve backward compatibility at `Message::add()` and `Message::update()`.

The local OpenAPI snapshot was refreshed before writing this plan with:

```bash
make oa-schema-build
```

---

## Files to Create

### 1. Contracts

- `src/Services/IM/Message/Attach/Contracts/AttachPayloadInterface.php`
- `src/Services/IM/Message/Attach/Contracts/AttachBlockInterface.php`
- `src/Services/IM/Message/Attach/Contracts/AttachItemInterface.php`

Skeleton:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Attach\Contracts;

interface AttachPayloadInterface
{
    public function build(): array;
}
```

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Attach\Contracts;

interface AttachBlockInterface
{
    public function build(): array;
}
```

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Attach\Contracts;

interface AttachItemInterface
{
    public function build(): array;
}
```

### 2. Enums

- `src/Services/IM/Message/Attach/Enums/AttachColorToken.php`
- `src/Services/IM/Message/Attach/Enums/AttachAvatarType.php`
- `src/Services/IM/Message/Attach/Enums/GridDisplay.php`

Skeleton:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Attach\Enums;

enum AttachAvatarType: string
{
    case user = 'USER';
    case chat = 'CHAT';
    case bot = 'BOT';
}
```

### 3. Root payload object

- `src/Services/IM/Message/Attach/Attach.php`

Skeleton:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Attach;

use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\DelimiterBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\MessageBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachBlockInterface;
use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachPayloadInterface;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachColorToken;

final class Attach implements AttachPayloadInterface
{
    /** @var list<AttachBlockInterface> */
    private array $blocks = [];

    private ?int $id = null;
    private ?AttachColorToken $colorToken = null;
    private ?string $color = null;

    public static function create(): self
    {
        return new self();
    }

    public function id(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function colorToken(AttachColorToken $token): self
    {
        $this->colorToken = $token;

        return $this;
    }

    public function color(string $hexColor): self
    {
        $this->color = $hexColor;

        return $this;
    }

    public function add(AttachBlockInterface $block): self
    {
        $this->blocks[] = $block;

        return $this;
    }

    public function message(string $text): self
    {
        return $this->add(MessageBlock::text($text));
    }

    public function delimiter(?int $size = null, ?string $color = null): self
    {
        $block = DelimiterBlock::create();

        if ($size !== null) {
            $block->size($size);
        }

        if ($color !== null) {
            $block->color($color);
        }

        return $this->add($block);
    }

    public function build(): array
    {
        $blocks = array_map(
            static fn(AttachBlockInterface $block): array => $block->build(),
            $this->blocks
        );

        if ($this->id === null && $this->colorToken === null && $this->color === null) {
            return $blocks;
        }

        return array_filter(
            [
                'ID' => $this->id,
                'COLOR_TOKEN' => $this->colorToken?->value,
                'COLOR' => $this->color,
                'BLOCKS' => $blocks,
            ],
            static fn(mixed $value): bool => $value !== null
        );
    }
}
```

### 4. Block classes

- `src/Services/IM/Message/Attach/Blocks/MessageBlock.php`
- `src/Services/IM/Message/Attach/Blocks/LinkBlock.php`
- `src/Services/IM/Message/Attach/Blocks/UserBlock.php`
- `src/Services/IM/Message/Attach/Blocks/ImageBlock.php`
- `src/Services/IM/Message/Attach/Blocks/FileBlock.php`
- `src/Services/IM/Message/Attach/Blocks/DelimiterBlock.php`
- `src/Services/IM/Message/Attach/Blocks/GridBlock.php`

Representative skeleton:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Attach\Blocks;

use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachBlockInterface;

final class MessageBlock implements AttachBlockInterface
{
    private function __construct(private string $text)
    {
    }

    public static function text(string $text): self
    {
        return new self($text);
    }

    public function build(): array
    {
        return ['MESSAGE' => $this->text];
    }
}
```

Representative collection skeleton:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Attach\Blocks;

use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachBlockInterface;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\GridDisplay;
use Bitrix24\SDK\Services\IM\Message\Attach\Items\GridItem;

final class GridBlock implements AttachBlockInterface
{
    /** @var list<GridItem> */
    private array $items = [];

    private function __construct(private GridDisplay $display)
    {
    }

    public static function display(GridDisplay $display): self
    {
        return new self($display);
    }

    public function item(GridItem $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    public function build(): array
    {
        if ($this->items === []) {
            throw new \InvalidArgumentException('GRID block must contain at least one item');
        }

        return [
            'GRID' => array_map(
                fn(GridItem $item): array => $this->buildItem($item),
                $this->items
            ),
        ];
    }
}
```

### 5. Nested item classes

- `src/Services/IM/Message/Attach/Items/ImageItem.php`
- `src/Services/IM/Message/Attach/Items/FileItem.php`
- `src/Services/IM/Message/Attach/Items/GridItem.php`

Skeleton:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Services\IM\Message\Attach\Items;

use Bitrix24\SDK\Services\IM\Message\Attach\Contracts\AttachItemInterface;

final class ImageItem implements AttachItemInterface
{
    private function __construct(private string $link)
    {
    }

    public static function link(string $link): self
    {
        return new self($link);
    }

    public function build(): array
    {
        return ['LINK' => $this->link];
    }
}
```

### 6. Unit tests for serialization and validation

- `tests/Unit/Services/IM/Message/Attach/AttachTest.php`
- `tests/Unit/Services/IM/Message/Attach/Blocks/SimpleBlocksTest.php`
- `tests/Unit/Services/IM/Message/Attach/Blocks/CollectionBlocksTest.php`

Representative test skeleton:

```php
#[Test]
public function buildReturnsShortAttachFormWhenNoMetaFieldsPresent(): void
{
    $attach = Attach::create()->message('Hello');

    self::assertSame(
        [
            ['MESSAGE' => 'Hello'],
        ],
        $attach->build()
    );
}
```

### 7. Integration smoke coverage for object API

- `tests/Integration/Services/IM/Message/Service/MessageAttachObjectApiTest.php`

Skeleton:

```php
#[Test]
public function addAcceptsAttachObjectShortForm(): void
{
    $messageId = $this->sendMessage(
        message: 'Attach object short form',
        attach: Attach::create()->message('Short object payload'),
    );

    self::assertGreaterThan(0, $messageId);
}
```

---

## Files to Modify

### 1. `src/Services/IM/Message/Service/Message.php`

Change both `add()` and `update()` signatures from:

```php
array|string|null $attach = null
```

to:

```php
array|string|AttachPayloadInterface|null $attach = null
```

and normalize before `core->call()`:

```php
if ($attach instanceof AttachPayloadInterface) {
    $attach = $attach->build();
}
```

### 2. `tests/Unit/Services/IM/Message/Service/MessageTest.php`

Add service-level unit coverage proving the new object payload is serialized before transport:

```php
#[Test]
public function addAcceptsAttachPayloadInterface(): void
{
    $result = $this->service->add(
        'chat1',
        'Message',
        Attach::create()->message('payload')
    );

    self::assertInstanceOf(AddedItemResult::class, $result);
}
```

### 3. `CHANGELOG.md`

Add under `## 3.2.0 – UNRELEASED` → `### Added`:

```markdown
- Added typed fluent `Services\IM\Message\Attach` payload builders for `ATTACH` blocks in `im.message.add` and `im.message.update`, with backward-compatible support for raw arrays and JSON strings preserved ([#426](https://github.com/bitrix24/b24phpsdk/issues/426))
```

### 4. `.tasks/426/design.md`

If implementation reveals any mismatch between real code constraints and the approved design, update the design doc in the same task folder before or alongside the code change. Do not leave the design stale.

---

## Deptrac Compliance

- New `Attach` classes live under `src/Services/IM/Message/Attach`, which keeps them inside the Services layer next to the existing IM message service.
- The new contracts and enums remain inside the same subtree, so no new forbidden cross-layer dependencies should be introduced.
- `Message.php` may import `AttachPayloadInterface` from `Services\IM\Message\Attach\Contracts`; this is a same-layer dependency and should be allowed.
- Tests must not introduce production-only dependencies back into `src/`.

Expected deptrac-safe dependency direction:

- `Service\Message` -> `Attach\Contracts`
- `Attach\Attach` -> `Attach\Blocks`, `Attach\Enums`, `Attach\Contracts`
- `Attach\Blocks\*` -> `Attach\Items`, `Attach\Enums`, `Attach\Contracts`
- `Attach\Items\*` -> `Attach\Enums`, `Attach\Contracts`

---

## Tasks

### Task 1: Root attach contracts, enums, and short/full form serialization

**Files:**
- Create: `src/Services/IM/Message/Attach/Contracts/AttachPayloadInterface.php`
- Create: `src/Services/IM/Message/Attach/Contracts/AttachBlockInterface.php`
- Create: `src/Services/IM/Message/Attach/Contracts/AttachItemInterface.php`
- Create: `src/Services/IM/Message/Attach/Enums/AttachColorToken.php`
- Create: `src/Services/IM/Message/Attach/Enums/AttachAvatarType.php`
- Create: `src/Services/IM/Message/Attach/Enums/GridDisplay.php`
- Create: `src/Services/IM/Message/Attach/Attach.php`
- Test: `tests/Unit/Services/IM/Message/Attach/AttachTest.php`

- [ ] **Step 1: Write the failing root serialization test**

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Message\Attach;

use Bitrix24\SDK\Services\IM\Message\Attach\Attach;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\AttachColorToken;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AttachTest extends TestCase
{
    #[Test]
    public function buildReturnsShortAttachFormWhenNoMetaFieldsPresent(): void
    {
        $attach = Attach::create()->message('Hello');

        self::assertSame(
            [
                ['MESSAGE' => 'Hello'],
            ],
            $attach->build()
        );
    }

    #[Test]
    public function buildReturnsFullAttachFormWhenMetaFieldsPresent(): void
    {
        $attach = Attach::create()
            ->id(1)
            ->colorToken(AttachColorToken::primary)
            ->message('Hello');

        self::assertSame(
            [
                'ID' => 1,
                'COLOR_TOKEN' => 'primary',
                'BLOCKS' => [
                    ['MESSAGE' => 'Hello'],
                ],
            ],
            $attach->build()
        );
    }
}
```

- [ ] **Step 2: Run the root unit test to verify it fails**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Message/Attach/AttachTest.php
```

Expected: FAIL because `Attach` and related contracts/enums do not exist yet.

- [ ] **Step 3: Implement contracts, enums, and the root `Attach` object**

```php
public function build(): array
{
    $blocks = array_map(
        static fn(AttachBlockInterface $block): array => $block->build(),
        $this->blocks
    );

    if ($this->id === null && $this->colorToken === null && $this->color === null) {
        return $blocks;
    }

    $payload = [
        'ID' => $this->id,
        'COLOR_TOKEN' => $this->colorToken?->value,
        'COLOR' => $this->color,
        'BLOCKS' => $blocks,
    ];

    return array_filter(
        $payload,
        static fn(mixed $value): bool => $value !== null
    );
}
```

- [ ] **Step 4: Run the root unit test to verify it passes**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Message/Attach/AttachTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/Services/IM/Message/Attach tests/Unit/Services/IM/Message/Attach/AttachTest.php
git commit -m "feat: add root attach payload builder"
```

### Task 2: Simple scalar blocks for message, link, user, and delimiter

**Files:**
- Create: `src/Services/IM/Message/Attach/Blocks/MessageBlock.php`
- Create: `src/Services/IM/Message/Attach/Blocks/LinkBlock.php`
- Create: `src/Services/IM/Message/Attach/Blocks/UserBlock.php`
- Create: `src/Services/IM/Message/Attach/Blocks/DelimiterBlock.php`
- Test: `tests/Unit/Services/IM/Message/Attach/Blocks/SimpleBlocksTest.php`

- [ ] **Step 1: Write failing tests for scalar blocks**

```php
#[Test]
public function linkBlockSerializesOptionalFields(): void
{
    $block = LinkBlock::url('https://apidocs.bitrix24.ru')
        ->name('Docs')
        ->description('Open docs');

    self::assertSame(
        [
            'LINK' => [
                'LINK' => 'https://apidocs.bitrix24.ru',
                'NAME' => 'Docs',
                'DESC' => 'Open docs',
            ],
        ],
        $block->build()
    );
}

#[Test]
public function delimiterBlockSerializesEmptyPayloadWhenNoFieldsSet(): void
{
    self::assertSame(
        ['DELIMITER' => []],
        DelimiterBlock::create()->build()
    );
}
```

- [ ] **Step 2: Run the scalar block test to verify it fails**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Message/Attach/Blocks/SimpleBlocksTest.php
```

Expected: FAIL because the block classes do not exist yet.

- [ ] **Step 3: Implement scalar block classes with small local validation**

```php
public static function url(string $link): self
{
    if ($link === '') {
        throw new \InvalidArgumentException('LINK must not be empty');
    }

    return new self($link);
}

public function build(): array
{
    return [
        'LINK' => array_filter(
            [
                'LINK' => $this->link,
                'NAME' => $this->name,
                'DESC' => $this->description,
                'HTML' => $this->html,
                'PREVIEW' => $this->preview,
                'WIDTH' => $this->width,
                'HEIGHT' => $this->height,
            ],
            static fn(mixed $value): bool => $value !== null
        ),
    ];
}
```

- [ ] **Step 4: Run the scalar block tests to verify they pass**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Message/Attach/Blocks/SimpleBlocksTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/Services/IM/Message/Attach/Blocks tests/Unit/Services/IM/Message/Attach/Blocks/SimpleBlocksTest.php
git commit -m "feat: add scalar attach block builders"
```

### Task 3: Collection blocks and nested items for image, file, and grid

**Files:**
- Create: `src/Services/IM/Message/Attach/Items/ImageItem.php`
- Create: `src/Services/IM/Message/Attach/Items/FileItem.php`
- Create: `src/Services/IM/Message/Attach/Items/GridItem.php`
- Create: `src/Services/IM/Message/Attach/Blocks/ImageBlock.php`
- Create: `src/Services/IM/Message/Attach/Blocks/FileBlock.php`
- Create: `src/Services/IM/Message/Attach/Blocks/GridBlock.php`
- Test: `tests/Unit/Services/IM/Message/Attach/Blocks/CollectionBlocksTest.php`

- [ ] **Step 1: Write failing tests for collection blocks**

```php
#[Test]
public function imageBlockSerializesMultipleItems(): void
{
    $block = ImageBlock::create()
        ->item(ImageItem::link('https://example.com/1.png')->name('One'))
        ->item(ImageItem::link('https://example.com/2.png')->name('Two'));

    self::assertSame(
        [
            'IMAGE' => [
                ['LINK' => 'https://example.com/1.png', 'NAME' => 'One'],
                ['LINK' => 'https://example.com/2.png', 'NAME' => 'Two'],
            ],
        ],
        $block->build()
    );
}

#[Test]
public function gridBlockRequiresAtLeastOneItem(): void
{
    $this->expectException(\InvalidArgumentException::class);

    GridBlock::display(GridDisplay::row)->build();
}
```

- [ ] **Step 2: Run the collection block test to verify it fails**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Message/Attach/Blocks/CollectionBlocksTest.php
```

Expected: FAIL because collection blocks and items do not exist yet.

- [ ] **Step 3: Implement nested items and collection block builders**

```php
public function build(): array
{
    if ($this->items === []) {
        throw new \InvalidArgumentException('GRID block must contain at least one item');
    }

    return [
        'GRID' => array_map(
            fn(GridItem $item): array => $this->buildItem($item),
            $this->items
        ),
    ];
}
```

`GridBlock::width()`, `colorToken()`, and `color()` should behave as defaults inherited by rows that do not define those values themselves. `DISPLAY` is serialized per row, matching the existing raw integration payloads already accepted by the live API.

- [ ] **Step 4: Run the collection block tests to verify they pass**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Message/Attach/Blocks/CollectionBlocksTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/Services/IM/Message/Attach/Items src/Services/IM/Message/Attach/Blocks tests/Unit/Services/IM/Message/Attach/Blocks/CollectionBlocksTest.php
git commit -m "feat: add collection attach blocks and items"
```

### Task 4: Integrate attach objects into the IM message service boundary

**Files:**
- Modify: `src/Services/IM/Message/Service/Message.php`
- Modify: `tests/Unit/Services/IM/Message/Service/MessageTest.php`

- [ ] **Step 1: Add a failing service-level test for object payload support**

```php
#[Test]
public function addAcceptsAttachPayloadObject(): void
{
    $result = $this->service->add(
        dialogId: 'chat1',
        message: 'Message',
        attach: Attach::create()->message('payload')
    );

    self::assertInstanceOf(AddedItemResult::class, $result);
}
```

- [ ] **Step 2: Run the message service unit test to verify it fails**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Message/Service/MessageTest.php
```

Expected: FAIL on a type error because `Message::add()` and `Message::update()` do not yet accept `AttachPayloadInterface`.

- [ ] **Step 3: Widen the service boundary and normalize object payloads**

```php
public function add(
    string $dialogId,
    ?string $message = null,
    array|string|AttachPayloadInterface|null $attach = null,
    array|string|null $keyboard = null,
    array|string|null $menu = null,
    bool $isSystem = false,
    bool $urlPreview = true,
    ?int $replyId = null,
): AddedItemResult {
    if ($attach instanceof AttachPayloadInterface) {
        $attach = $attach->build();
    }

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
```

Mirror the same normalization in `update()`.

- [ ] **Step 4: Run the message service unit test to verify it passes**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Message/Service/MessageTest.php
```

Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add src/Services/IM/Message/Service/Message.php tests/Unit/Services/IM/Message/Service/MessageTest.php
git commit -m "feat: support attach payload objects in message service"
```

### Task 5: Integration smoke tests, changelog, and final verification

**Files:**
- Create: `tests/Integration/Services/IM/Message/Service/MessageAttachObjectApiTest.php`
- Modify: `CHANGELOG.md`
- Modify: `.tasks/426/design.md` if implementation details changed

- [ ] **Step 1: Add failing integration smoke tests for the object API**

```php
#[Test]
public function addAcceptsAttachObjectShortForm(): void
{
    $messageId = $this->sendMessage(
        message: 'Attach object short form',
        attach: Attach::create()->message('Short object payload')
    );

    self::assertGreaterThan(0, $messageId);
}

#[Test]
public function addAcceptsAttachObjectFullForm(): void
{
    $messageId = $this->sendMessage(
        message: 'Attach object full form',
        attach: Attach::create()
            ->id(1)
            ->colorToken(AttachColorToken::primary)
            ->message('Full object payload')
    );

    self::assertGreaterThan(0, $messageId);
}
```

- [ ] **Step 2: Run the integration smoke test to verify it fails before wiring is complete**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Integration/Services/IM/Message/Service/MessageAttachObjectApiTest.php
```

Expected: FAIL until `Message::add()` accepts object payloads.

- [ ] **Step 3: Add changelog entry and keep the design doc synchronized**

```markdown
- Added typed fluent `Services\IM\Message\Attach` payload builders for `ATTACH` blocks in `im.message.add` and `im.message.update`, with backward-compatible support for raw arrays and JSON strings preserved ([#426](https://github.com/bitrix24/b24phpsdk/issues/426))
```

If any implementation detail deviates from `.tasks/426/design.md`, update the design doc in the same commit.

- [ ] **Step 4: Run the focused and full verification suite**

Run:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Message/Attach
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/IM/Message/Service/MessageTest.php
docker compose run --rm php-cli vendor/bin/phpunit tests/Integration/Services/IM/Message/Service/MessageAttachObjectApiTest.php
docker compose run --rm php-cli vendor/bin/phpunit tests/Integration/Services/IM/Message/Service
make lint-all
```

Expected:

- all new unit tests PASS
- object API integration smoke tests PASS
- the full IM message integration directory remains green except for the existing documented skips
- `make lint-all` returns success

- [ ] **Step 5: Commit**

```bash
git add CHANGELOG.md .tasks/426/design.md tests/Integration/Services/IM/Message/Service/MessageAttachObjectApiTest.php
git commit -m "test: add attach object API coverage"
```

---

## Self-Review

Spec coverage check:

- Root payload object and short/full form handling: covered by Task 1.
- Typed block and item classes: covered by Tasks 2 and 3.
- Service integration and backward compatibility: covered by Task 4.
- Unit and integration verification plus changelog: covered by Task 5.

Placeholder scan:

- No `TODO`, `TBD`, or “similar to previous task” markers remain.

Type consistency:

- The plan consistently uses `AttachPayloadInterface` at the service boundary.
- The root payload exposes `build(): array`.
- Block and item classes serialize to arrays only.

This plan matches the approved design in `.tasks/426/design.md` and is scoped to a single implementation stream.
