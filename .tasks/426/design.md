# Design: Add typed object API for IM message ATTACH payloads (issue #426)

## Status

Draft

## Summary

This document proposes a typed object API for `ATTACH` payloads used by `Bitrix24\SDK\Services\IM\Message\Service\Message`.

The goal is to replace manual nested arrays with a developer-friendly fluent API while preserving full backward compatibility with the current `array|string|null` payload contract.

The first iteration covers `ATTACH` only. `MENU` and `KEYBOARD` remain unchanged and are intentionally out of scope for this task.

## Goals

- Provide a fluent PHP API for constructing `ATTACH` payloads.
- Model all currently supported attach block types used in integration tests:
  - `MESSAGE`
  - `LINK`
  - `USER`
  - `IMAGE`
  - `FILE`
  - `DELIMITER`
  - `GRID`
- Preserve backward compatibility for existing users who pass arrays or JSON strings.
- Keep the API easy to discover in IDE autocomplete.
- Keep payload serialization explicit and testable.

## Non-Goals

- No object model for `MENU`.
- No object model for `KEYBOARD`.
- No automatic BB-code builder DSL.
- No automatic file upload, image upload, or URL validation against remote resources.
- No attempt to encode every server-side Bitrix24 validation rule in PHP types.

## Current Problem

`Message::add()` and `Message::update()` currently accept:

```php
array|string|null $attach = null
```

This is flexible but produces poor DX for non-trivial payloads:

- nested associative arrays are hard to read
- required keys are not discoverable
- optional fields are easy to mistype
- developers must remember short vs full attach forms
- complex blocks such as `GRID`, `IMAGE`, and `FILE` become large inline array trees

The integration tests added for IM message payload coverage demonstrate that the current payload surface is already rich enough to justify a dedicated object model.

## Proposed Public API

### Root object

Introduce a root fluent payload object:

```php
use Bitrix24\SDK\Services\IM\Message\Attach\Attach;

$attach = Attach::create()
    ->message('Hello from SDK')
    ->delimiter()
    ->add(LinkBlock::url('https://apidocs.bitrix24.ru')->name('Docs'));
```

### Typical usage

```php
use Bitrix24\SDK\Services\IM\Message\Attach\Attach;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\GridBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\LinkBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Blocks\UserBlock;
use Bitrix24\SDK\Services\IM\Message\Attach\Enums\GridDisplay;
use Bitrix24\SDK\Services\IM\Message\Attach\Items\GridItem;

$attach = Attach::create()
    ->id(1)
    ->message('[B]Formatted[/B] text')
    ->add(
        LinkBlock::url('https://apidocs.bitrix24.ru')
            ->name('Documentation')
            ->description('Open API reference')
    )
    ->add(
        UserBlock::name('Maksim')
            ->userId(1)
            ->avatarTypeUser()
    )
    ->add(
        GridBlock::display(GridDisplay::row)
            ->item(
                GridItem::name('User')
                    ->value('[USER=1]Maksim[/USER]')
            )
            ->item(
                GridItem::name('Chat')
                    ->value('Open chat')
                    ->chatId(123)
            )
    );
```

Then:

```php
$messageService->add(
    dialogId: 'chat123',
    message: 'Payload with attach',
    attach: $attach,
);
```

## Namespace and File Layout

New package:

- `src/Services/IM/Message/Attach/Attach.php`
- `src/Services/IM/Message/Attach/Contracts/AttachPayloadInterface.php`
- `src/Services/IM/Message/Attach/Contracts/AttachBlockInterface.php`
- `src/Services/IM/Message/Attach/Contracts/AttachItemInterface.php`
- `src/Services/IM/Message/Attach/Blocks/MessageBlock.php`
- `src/Services/IM/Message/Attach/Blocks/LinkBlock.php`
- `src/Services/IM/Message/Attach/Blocks/UserBlock.php`
- `src/Services/IM/Message/Attach/Blocks/ImageBlock.php`
- `src/Services/IM/Message/Attach/Blocks/FileBlock.php`
- `src/Services/IM/Message/Attach/Blocks/DelimiterBlock.php`
- `src/Services/IM/Message/Attach/Blocks/GridBlock.php`
- `src/Services/IM/Message/Attach/Items/ImageItem.php`
- `src/Services/IM/Message/Attach/Items/FileItem.php`
- `src/Services/IM/Message/Attach/Items/GridItem.php`
- `src/Services/IM/Message/Attach/Enums/AttachColorToken.php`
- `src/Services/IM/Message/Attach/Enums/AttachAvatarType.php`
- `src/Services/IM/Message/Attach/Enums/GridDisplay.php`

## Core Model

### Contracts

```php
interface AttachPayloadInterface
{
    public function build(): array;
}

interface AttachBlockInterface
{
    public function build(): array;
}

interface AttachItemInterface
{
    public function build(): array;
}
```

This is intentionally separate from `ItemBuilderInterface`.

Reason:

- `ATTACH` is not a generic entity-field builder
- attach payloads have nested block/item structure
- a dedicated contract keeps the intent clear

### Root object behavior

`Attach` stores:

- optional meta fields:
  - `ID`
  - `COLOR_TOKEN`
  - `COLOR`
- ordered list of blocks

It provides:

- `Attach::create(): self`
- `id(int $id): self`
- `colorToken(AttachColorToken $token): self`
- `color(string $hexColor): self`
- `add(AttachBlockInterface $block): self`
- sugar methods:
  - `message(string $text): self`
  - `delimiter(?int $size = null, ?string $color = null): self`

### Short vs full form

`Attach::build()` chooses the payload form automatically:

- short form when only block data is present
- full form when any meta field is set

Examples:

Short form:

```php
[
    ['MESSAGE' => 'Hello'],
    ['DELIMITER' => []],
]
```

Full form:

```php
[
    'ID' => 1,
    'COLOR_TOKEN' => 'primary',
    'BLOCKS' => [
        ['MESSAGE' => 'Hello'],
    ],
]
```

This removes a major usability burden from developers.

## Block Design

### MessageBlock

```php
MessageBlock::text(string $text): self
```

Produces:

```php
['MESSAGE' => '...']
```

### LinkBlock

Required constructor:

```php
LinkBlock::url(string $link): self
```

Alternative convenience constructors:

- `user(int $userId, ?string $name = null): self`
- `chat(int $chatId, ?string $name = null): self`
- `network(string $networkId, ?string $name = null): self`

Optional fluent fields:

- `name(string $name): self`
- `description(string $description): self`
- `html(string $html): self`
- `preview(string $url): self`
- `width(int $width): self`
- `height(int $height): self`

### UserBlock

Required constructor:

```php
UserBlock::name(string $name): self
```

Optional fluent fields:

- `userId(int $userId): self`
- `chatId(int $chatId): self`
- `botId(int $botId): self`
- `networkId(string $networkId): self`
- `avatar(string $url): self`
- `link(string $url): self`
- `avatarType(AttachAvatarType $type): self`

Convenience aliases:

- `avatarTypeUser(): self`
- `avatarTypeChat(): self`
- `avatarTypeBot(): self`

### ImageBlock

Factory:

```php
ImageBlock::create(): self
```

Items are added via:

```php
item(ImageItem $item): self
```

### FileBlock

Factory:

```php
FileBlock::create(): self
```

Items are added via:

```php
item(FileItem $item): self
```

### DelimiterBlock

Factory:

```php
DelimiterBlock::create(): self
```

Optional fluent fields:

- `size(int $size): self`
- `color(string $hexColor): self`

### GridBlock

Required constructor:

```php
GridBlock::display(GridDisplay $display): self
```

Optional fluent fields:

- `item(GridItem $item): self`
- `width(int $width): self`
- `colorToken(AttachColorToken $token): self`
- `color(string $hexColor): self`

Serialization note:

- `GRID` is serialized as a list of row items, matching the existing integration payloads already accepted by the live API.
- `DISPLAY` is emitted per grid row item, not as a top-level wrapper field.
- `GridBlock::width()`, `colorToken()`, and `color()` act as defaults applied to rows that do not override those fields explicitly.

## Item Design

### ImageItem

Required constructor:

```php
ImageItem::link(string $url): self
```

Optional fluent fields:

- `name(string $name): self`
- `preview(string $url): self`
- `width(int $width): self`
- `height(int $height): self`

### FileItem

Required constructor:

```php
FileItem::link(string $url): self
```

Optional fluent fields:

- `name(string $name): self`
- `size(int $bytes): self`

### GridItem

Required constructor:

```php
GridItem::name(string $name): self
```

Optional fluent fields:

- `value(string $value): self`
- `link(string $url): self`
- `userId(int $userId): self`
- `chatId(int $chatId): self`
- `width(int $width): self`
- `height(int $height): self`
- `colorToken(AttachColorToken $token): self`
- `color(string $hexColor): self`

## Enum Design

### AttachColorToken

Represents documented semantic color tokens. Initial enum values should cover only values confirmed by current docs and tests.

### AttachAvatarType

Values:

- `user`
- `chat`
- `bot`

Serialized to the uppercase values expected by REST:

- `USER`
- `CHAT`
- `BOT`

### GridDisplay

Values:

- `block`
- `line`
- `row`
- `table`

Serialized to:

- `BLOCK`
- `LINE`
- `ROW`
- `TABLE`

## Serialization Rules

### General rules

- `build()` returns REST-ready arrays
- `null` fields are removed
- optional fields not explicitly set are omitted
- block and item order is preserved

### Validation rules

Validation should be practical, not exhaustive.

We validate:

- required constructor arguments are non-empty where applicable
- `width`, `height`, `size`, and similar numeric fields are positive
- `ImageBlock`, `FileBlock`, and `GridBlock` contain at least one item before `build()`
- `color()` accepts only valid hex notation used by the API

We do not validate:

- whether a URL is reachable
- whether a referenced `USER_ID`, `CHAT_ID`, or `BOT_ID` exists
- whether BB-code content is semantically correct for Bitrix24

### Error strategy

Use `\InvalidArgumentException` for invalid local API usage.

This is sufficient because these objects are local payload builders, not transport-level domain services.

## Integration into Message Service

Update `Message::add()` and `Message::update()` signatures to accept:

```php
array|string|AttachPayloadInterface|null $attach = null
```

Before sending the REST call:

```php
if ($attach instanceof AttachPayloadInterface) {
    $attach = $attach->build();
}
```

Backward compatibility remains intact:

- existing raw arrays still work
- existing JSON strings still work, but are deprecated
- new object API becomes an opt-in improvement

### Escape hatch for vendor extensions

Add a dedicated `RawAttach implements AttachPayloadInterface` wrapper for unsupported or newly introduced
vendor payload shapes.

This keeps the main `Attach` class focused on typed fluent construction for supported blocks while still
allowing callers to stay inside the object contract when they need to pass through raw arrays unchanged.

Examples:

```php
$attach = RawAttach::fromArray([
    ['MESSAGE' => 'Known block'],
    ['VENDOR_NEW_BLOCK' => ['FLAG' => 'Y']],
]);
```

`AttachPayloadInterface` remains intentionally minimal:

- `build(): array`

It does not gain `fromArray()` because reverse-construction is not a natural responsibility for every
typed payload implementation.

## DX Principles

The design optimizes for:

- autocomplete-first API discovery
- minimal boilerplate for common payloads
- explicit typed objects for complex nested structures
- explicit raw escape hatch for unsupported vendor extensions
- readable test code
- low migration cost from existing arrays

The design intentionally avoids a single giant builder object. Complex blocks such as `GRID`, `IMAGE`, and `FILE` remain isolated and understandable.

## Testing Strategy

### Unit tests

Add unit tests for:

- `Attach` short-form serialization
- `Attach` full-form serialization
- each block `build()` method
- each nested item `build()` method
- local validation failures

### Integration tests

Keep the current array-based integration tests as backward compatibility coverage.

Add or migrate a representative subset of integration tests to the new object API:

- one simple attach
- one full-form attach
- one nested block example such as `GRID`

This ensures:

- the fluent API serializes correctly
- the object API works against the real portal
- backward compatibility is preserved

## Rollout Plan

Phase 1:

- add object model, enums, and contracts
- integrate `AttachPayloadInterface` into `Message::add()` and `Message::update()`

Phase 2:

- add unit tests for serialization and validation
- migrate a small subset of integration tests to the new API

Phase 3:

- document usage examples in code comments or SDK docs if the repository already has the right location for that

## Trade-Offs

### Why not a single fluent mega-builder

A single builder would look compact initially but would quickly become overloaded with block-specific methods and branching logic.

Separating blocks into typed objects:

- improves discoverability
- keeps files focused
- makes nested payloads easier to test
- gives a stable foundation for future extension

### Why not immutable value objects everywhere

The repository already uses mutable fluent builders such as `TaskItemBuilder`.

Using the same style here reduces surprise for maintainers and aligns the DX with the rest of the SDK.

## Open Questions Resolved by This Design

- Scope is limited to `ATTACH` only.
- `MENU` and `KEYBOARD` remain out of scope.
- Short vs full form is handled automatically by `Attach`.
- BB-code remains plain string content.
- Backward compatibility is preserved at the service boundary.

## Implementation Readiness

This design is small enough for a single implementation plan and does not require decomposition into separate subsystems.
