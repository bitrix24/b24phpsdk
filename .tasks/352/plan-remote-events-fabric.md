# Plan: Replace RemoteEventsFabric usages with RemoteEventsFactory

## Context

`RemoteEventsFabric` is deprecated (`@deprecated wrong class name, class will be deleted, use RemoteEventsFactory`).
`RemoteEventsFactory` is the current replacement with a cleaner API: `create()` + `validate()` instead of the combined `createEvent(request, token)`.

A comprehensive `RemoteEventsFactoryTest.php` already exists and fully covers the new class.

## Files to change

### 1. Delete `tests/Unit/Services/RemoteEventsFabricTest.php`
- Tests only the deprecated class
- `RemoteEventsFactoryTest.php` already covers the replacement fully (9 tests vs 1)
- Action: **delete the file**

### 2. Remove unused import from `tests/Unit/Services/Main/MainServiceBuilderTest.php`
- Line 16: `use Bitrix24\SDK\Services\RemoteEventsFabric;` — imported but never used
- Action: **remove the line**

### 3. Remove unused import from `tests/Unit/Services/IM/IMServiceBuilderTest.php`
- Line 16: `use Bitrix24\SDK\Services\RemoteEventsFabric;` — imported but never used
- Action: **remove the line**

### 4. Remove unused import from `tests/Unit/Services/CRM/CRMServiceBuilderTest.php`
- Line 17: `use Bitrix24\SDK\Services\RemoteEventsFabric;` — imported but never used
- Action: **remove the line**

## What is NOT changed

- `src/Services/RemoteEventsFabric.php` — the deprecated class itself stays (backward compatibility until removal)
- `src/Services/RemoteEventsFactory.php` — current class, no changes needed
- `tests/Unit/Services/RemoteEventsFactoryTest.php` — already comprehensive, no changes needed

## Verification

```bash
make test-unit
make lint-cs-fixer
```

Both must pass with zero errors. After deletion of `RemoteEventsFabricTest.php`, coverage for the deprecated class drops — that is expected and desired.
