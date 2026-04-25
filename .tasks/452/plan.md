# Plan: Add logo support to Bitrix24Partner contract (issue #452)

## Context

Issue #452 asks to extend the `Bitrix24PartnerInterface` (located at
`src/Application/Contracts/Bitrix24Partners/Entity/Bitrix24PartnerInterface.php`)
with logo URL support. The partner entity already has optional string properties
such as `site`, `email`, and `openLineId`, all following the same getter/setter
pattern. Logo URL is an optional string (nullable) — it may not be set on creation.

Per the issue, the public API is:
- `getLogoUrl(): ?string` — returns the current logo URL or null if not set
- `changeLogoUrl(?string $logoUrl): void` — updates the logo URL; throws
  `InvalidArgumentException` on empty string; accepts null to clear

A corresponding domain event `Bitrix24PartnerLogoUrlChangedEvent` is created for
consistency with the existing events directory.

Target branch: `claude/fix-issue-452-PRBE2` based on `v3-dev`.

---

## Files to Create

### 1. `src/Application/Contracts/Bitrix24Partners/Events/Bitrix24PartnerLogoUrlChangedEvent.php`

```php
class Bitrix24PartnerLogoUrlChangedEvent extends Event
{
    public function __construct(
        public readonly Uuid            $bitrix24PartnerId,
        public readonly CarbonImmutable $timestamp,
        public readonly ?string         $previousLogoUrl,
        public readonly ?string         $currentLogoUrl)
    {}
}
```

---

## Files to Modify

### 1. `src/Application/Contracts/Bitrix24Partners/Entity/Bitrix24PartnerInterface.php`

Add after `setOpenLineId()`:
```php
public function getLogoUrl(): ?string;
public function changeLogoUrl(?string $logoUrl): void;
```

### 2. `tests/Unit/Application/Contracts/Bitrix24Partners/Entity/Bitrix24PartnerReferenceEntityImplementation.php`

- Add `private ?string $logoUrl` constructor parameter
- Implement `getLogoUrl()` and `changeLogoUrl()` with empty-string validation

### 3. `tests/Application/Contracts/Bitrix24Partners/Entity/Bitrix24PartnerInterfaceTest.php`

- Add `?string $logoUrl` to abstract factory method signature and all test method
  parameter lists
- Update `bitrix24PartnerDataProvider` to include `$logoUrl`
- Add `testGetLogoUrl` and `testChangeLogoUrl` test methods

### 4. `tests/Unit/Application/Contracts/Bitrix24Partners/Entity/Bitrix24PartnerInterfaceReferenceImplementationTest.php`

- Add `?string $logoUrl` parameter to `createBitrix24PartnerImplementation` override
- Pass `$logoUrl` to the reference implementation constructor

### 5. `CHANGELOG.md`

Under `## 3.2.0 – UNRELEASED` → `### Added`:
```
- Added `getLogoUrl()` and `changeLogoUrl()` methods to `Bitrix24PartnerInterface` and reference implementation ([#452](https://github.com/bitrix24/b24phpsdk/issues/452))
```

---

## Deptrac compliance

All new code lives in `Application\Contracts` (Application layer) which may only depend
on `Core`. The event class depends only on `Carbon`, `Symfony\Uid`, and
`Symfony\Contracts\EventDispatcher` — all allowed.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```
