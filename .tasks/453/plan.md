# Plan: Replace "set" prefix with "change" in Bitrix24Partner methods (issue #453)

## Context

The `Bitrix24PartnerInterface` has six mutator methods that use the `set*` prefix.
The issue asks to rename them all to `change*` to better express that these are
domain-level change operations (not simple property setters).

This is a pure refactoring: no logic changes, no new files, no API involvement.
The branch `claude/fix-issue-453-zr67N` already exists and is checked out.

Methods being renamed:
- `setTitle`     → `changeTitle`
- `setSite`      → `changeSite`
- `setPhone`     → `changePhone`
- `setEmail`     → `changeEmail`
- `setOpenLineId` → `changeOpenLineId`
- `setExternalId` → `changeExternalId`

---

## Files to Modify

### 1. `src/Application/Contracts/Bitrix24Partners/Entity/Bitrix24PartnerInterface.php`

Rename each method declaration and update PHPDoc summaries from "Set …" to "Change …".

### 2. `tests/Unit/Application/Contracts/Bitrix24Partners/Entity/Bitrix24PartnerReferenceEntityImplementation.php`

Rename each `#[\Override]` method from `set*` to `change*`. Body stays identical.

### 3. `tests/Application/Contracts/Bitrix24Partners/Entity/Bitrix24PartnerInterfaceTest.php`

- Rename test methods: `testSetTitle` → `testChangeTitle`, etc.
- Update `#[TestDox]` strings.
- Update all `$bitrix24Partner->set*()` call sites to `->change*()`.

### 4. `CHANGELOG.md`

Add entry under `## 3.2.0 – UNRELEASED` → `### Changed`.

---

## Deptrac compliance

All changes are within the `Application` layer. No new cross-layer imports.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```
