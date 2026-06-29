# Plan: Add support for mailservice.* methods (issue #495)

## Context

The Bitrix24 REST API `mailservice` scope provides 6 methods for managing mail services (IMAP mail
service integrations) on a Bitrix24 portal:

| Method              | Returns                      | Notes                              |
|---------------------|------------------------------|------------------------------------|
| `mailservice.add`   | `integer` (new ID)           | Required param: NAME               |
| `mailservice.update`| `boolean`                    | Required param: ID                 |
| `mailservice.get`   | `object` (single item)       | Required param: ID; result is flat |
| `mailservice.list`  | `array` of objects           | No pagination                      |
| `mailservice.delete`| `boolean`                    | Required param: ID                 |
| `mailservice.fields`| `object` (label map only)    | Returns {FIELD: "Label"} pairs     |

Response envelopes (v1 style):
- `mailservice.get` → `result` is the item object directly
- `mailservice.list` → `result` is a flat array of item objects
- `mailservice.add` → `result` is an integer
- `mailservice.update` / `mailservice.delete` → `result` is a boolean

Item fields (from `mailservice.get` live response):
- `ID` (string → int), `SITE_ID` (string), `ACTIVE` (Y/N → bool), `SORT` (string → int)
- `NAME` (string), `SERVER` (string), `PORT` (string → int), `ENCRYPTION` (Y/N → bool)
- `LINK` (string), `ICON` (string|null), `SMTP_SERVER` (string|null), `SMTP_PORT` (string → int|null)
- `SMTP_LOGIN_AS_IMAP` (Y/N → bool), `SMTP_PASSWORD_AS_IMAP` (Y/N → bool)
- `SMTP_ENCRYPTION` (Y/N/null → bool|null), `UPLOAD_OUTGOING` (Y/N/null → bool|null)

`mailservice.fields` returns localized label-only pairs (like Department) — no type info.
Type annotation tests use runtime type-cast validation instead.

The `mailservice.update` sends `ID` + flat fields (not nested under `fields` key), which requires
a custom `Batch` class extending `\Bitrix24\SDK\Core\Batch` to override `updateEntityItems`, same
as in `src/Services/Department/Batch.php`.

Author: © Dmitriy Ignatenko <algonexys@gmail.com>
Issue: https://github.com/bitrix24/b24phpsdk/issues/495

---

## Files to Create

### 1. `src/Services/MailService/Result/MailServiceItemResult.php`

AbstractAnnotatedItem subclass with `@property-read` annotations for all 16 item fields.

### 2. `src/Services/MailService/Result/MailServiceResult.php`

Single-item result for `mailservice.get` wrapping `getResult()` in a `MailServiceItemResult`.

### 3. `src/Services/MailService/Result/MailServicesResult.php`

List result for `mailservice.list` returning `MailServiceItemResult[]` from `getResult()`.

### 4. `src/Services/MailService/Batch.php`

Custom Batch extending `\Bitrix24\SDK\Core\Batch`, overriding `updateEntityItems` to pass `ID`
as a top-level key (same pattern as Department\Batch).

### 5. `src/Services/MailService/Service/MailService.php`

Service class with methods: `add`, `update`, `get`, `list`, `delete`, `fields`, `count`.

### 6. `src/Services/MailService/Service/Batch.php`

Batch service class with batch versions: `list`, `add`, `update`, `delete`.

### 7. `src/Services/MailService/MailServiceServiceBuilder.php`

Scope service builder with `mailService()` factory method.

### 8. `tests/Integration/Services/MailService/Service/MailServiceTest.php`

Integration test for all MailService methods + `testAllSystemFieldsAnnotated`.

### 9. `tests/Integration/Services/MailService/Service/BatchTest.php`

Integration test for batch operations.

### 10. `tests/Integration/Services/MailService/Result/MailServiceItemResultAnnotationsTest.php`

Dedicated annotations test: `testAllSystemFieldsAnnotated` + `testAllSystemFieldsHasValidTypeAnnotation`.

---

## Files to Modify

### 1. `src/Services/ServiceBuilder.php`

Add `getMailServiceScope(): MailServiceServiceBuilder` method and import.

### 2. `rector.php`

Add MailService src and test paths.

### 3. `phpunit.xml.dist`

Add:
```xml
<testsuite name="integration_tests_mailservice">
    <directory>./tests/Integration/Services/MailService/</directory>
</testsuite>
```

### 4. `Makefile`

Add:
```makefile
.PHONY: test-integration-mailservice
test-integration-mailservice:
    docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_mailservice
```

### 5. `CHANGELOG.md`

Add under `## 3.3.0 – UNRELEASED` → `### Added`.

---

## Deptrac compliance

New code lives under `src/Services/MailService` which is part of the `Services` layer.
It depends on `Core` (AbstractResult, AbstractAnnotatedItem, AbstractService, etc.) — allowed.
No cross-service dependencies. No new violations expected.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-mailservice
```

