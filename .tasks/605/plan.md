# Plan: Add `userfieldconfig.*` support (issue #605)

## Context

Bitrix24 exposes a "universal" custom-field configuration API for CRM smart-process
types (`CRM_{entityTypeId}`), the new invoice (`CRM_SMART_INVOICE`) and the sign
document (`CRM_SMART_DOCUMENT`) entities: `userfieldconfig.add/update/get/list/delete/getTypes`.
It requires both the `crm` and `userfieldconfig` REST scopes. Four related events exist:
`onCrmTypeUserFieldAdd/Update/Delete/SetEnumValues`.

API details gathered live via the Bitrix24 REST API MCP server (english docs under
`https://apidocs.bitrix24.com/api-reference/crm/universal/userfieldconfig/`):

- `userfieldconfig.add(moduleId, field)` → `result.field` = full field descriptor
  (`id, entityId, fieldName, userTypeId, xmlId, sort, multiple, mandatory, showFilter,
  showInList, editInList, isSearchable, settings, languageId, editFormLabel,
  listColumnLabel, listFilterLabel, errorMessage, helpMessage, enum[]`).
- `userfieldconfig.update(moduleId, id, field)` → `result` (boolean-style success,
  standard Bitrix24 convention — reuse `Core\Result\UpdatedItemResult`).
- `userfieldconfig.get(moduleId, id)` → `result.field` = full descriptor **or `null`**
  when not found.
- `userfieldconfig.list(moduleId, select, order, filter, start)` → `result.fields[]` +
  standard `total`/`next` pagination.
- `userfieldconfig.delete(moduleId, id)` → `result = null` on success (confirmed by a
  live example in the docs) — **cannot** reuse `Core\Result\DeletedItemResult` (it casts
  `result[0]` to bool, which is `false` for a `null` result). A dedicated result class is
  required.
- `userfieldconfig.getTypes(moduleId)` → `result.types` = **associative dict** keyed by
  type code, each value `{userTypeId, description}` (verified live against the test
  portal webhook — see Verification notes below).

`enum` items observed live/in docs: `id, userFieldId, value, def, sort, xmlId`.

### Directory placement

- Service + Result classes → new top-level CRM scope `src/Services/CRM/Userfieldconfig/`
  (sibling of `Currency`, `Type`, `Documentgenerator`), matching "one directory per API
  scope" from `docs/architecture.md`. `userfieldconfig` is its own REST scope, distinct
  from `crm`.
- Events → `src/Services/CRM/Type/Events/`, **not** under `Userfieldconfig/`. Precedent:
  `CRM/Company/Events` owns `OnCrmCompanyUserField*`, `CRM/Quote/Events` owns
  `OnCrmQuoteUserField*` — each entity scope owns its own user-field events. The event
  code family here is literally `onCrmType...`, and `CRM/Type` already models the smart
  process "type" entity (`crm.type.*`), so it owns `OnCrmTypeUserField*`.

### Event payload convention (existing repo-wide pattern)

Every existing `*EventRequest::getPayload()` in this codebase passes
`$this->eventPayload['data']` (not `['data']['FIELDS']`) into the payload `AbstractItem`,
and payload classes declare lower-camelCase properties (`$id`, `$entityId`,
`$fieldName`) — see `CRM\Company\Events\OnCrmCompanyUserFieldAdd\*` and
`CRM\Quote\Events\OnCrmQuoteUserFieldAdd\*` (60+ occurrences repo-wide). This is
replicated verbatim for consistency with the rest of the codebase; it is not something
introduced or altered by this issue.

### Known limitation / follow-up needed from the user

The configured integration-test webhook (`tests/.env.local`) currently has the `crm`
scope but **not** `userfieldconfig`. A live `userfieldconfig.add` call returned
`"You are not allowed to create custom fields"`. `userfieldconfig.getTypes` and
`crm.type.list` worked (portal has smart-process type `entityTypeId=1034`). Integration
tests for `add/update/get/list/delete` cannot be run end-to-end until the webhook scope
is extended. This is out of my control (portal admin UI action) — flagged to the user,
not blocking source/unit-test work.

---

## Files to Create

### Service

- `src/Services/CRM/Userfieldconfig/Service/Userfieldconfig.php` — `add`, `update`,
  `get`, `list`, `delete`, `getTypes`, each with `#[ApiEndpointMetadata]` linking to the
  English docs page.

### Result

- `src/Services/CRM/Userfieldconfig/Result/UserfieldConfigItemResult.php` (extends
  `AbstractAnnotatedItem`)
- `src/Services/CRM/Userfieldconfig/Result/UserfieldConfigEnumItemResult.php` (extends
  `AbstractAnnotatedItem`, nested via `array<UserfieldConfigEnumItemResult>` on `enum`)
- `src/Services/CRM/Userfieldconfig/Result/AddedUserfieldConfigItemResult.php`
- `src/Services/CRM/Userfieldconfig/Result/UserfieldConfigResult.php` (nullable
  `field()`)
- `src/Services/CRM/Userfieldconfig/Result/UserfieldConfigsResult.php`
- `src/Services/CRM/Userfieldconfig/Result/DeletedUserfieldConfigItemResult.php`
  (`isSuccess()` checks for `null`, not boolean cast)
- `src/Services/CRM/Userfieldconfig/Result/UserfieldConfigTypeItemResult.php` (extends
  `AbstractAnnotatedItem`)
- `src/Services/CRM/Userfieldconfig/Result/UserfieldConfigTypesResult.php` (returns
  `array<string, UserfieldConfigTypeItemResult>` keyed by type code)

### Events (CRM/Type)

- `src/Services/CRM/Type/Events/CrmTypeEventsFactory.php`
- `src/Services/CRM/Type/Events/OnCrmTypeUserFieldAdd/{OnCrmTypeUserFieldAdd,OnCrmTypeUserFieldAddPayload}.php`
- `src/Services/CRM/Type/Events/OnCrmTypeUserFieldUpdate/{...}.php`
- `src/Services/CRM/Type/Events/OnCrmTypeUserFieldDelete/{...}.php`
- `src/Services/CRM/Type/Events/OnCrmTypeUserFieldSetEnumValues/{...}.php`

### Tests

- `tests/Unit/Services/CRM/Userfieldconfig/Service/UserfieldconfigTest.php` (mocked
  `CoreInterface`, asserts request params + return types — pattern from
  `tests/Unit/Services/Catalog/PriceType/Service/PriceTypeTest.php`)
- `tests/Unit/Services/CRM/Type/Events/CrmTypeEventsFactoryTest.php` (isSupport/create
  round-trip for all 4 event codes + payload field extraction)
- `tests/Integration/Services/CRM/Userfieldconfig/Service/UserfieldconfigTest.php`
  (creates a scratch SPA type via `crm.type.add`, exercises add/get/list/update/delete,
  cleans up the type in `tearDown`)
- `tests/Integration/Services/CRM/Userfieldconfig/Service/UserfieldconfigGetTypesTest.php`
  (or folded into the above) for `getTypes(moduleId: 'crm')`
- `tests/Integration/Services/CRM/Userfieldconfig/Result/UserfieldConfigItemResultTest.php`
  — mandatory two-method annotation/type-cast test per `docs/testing.md`

---

## Files to Modify

- `src/Services/CRM/CRMServiceBuilder.php` — add `userfieldConfig(): Userfieldconfig\Service\Userfieldconfig`
- `src/Services/RemoteEventsFactory.php` — import + register `new CrmTypeEventsFactory()`
- `phpunit.xml.dist` — add `integration_tests_crm_userfieldconfig` (dir
  `tests/Integration/Services/CRM/Userfieldconfig/`) and `integration_tests_crm_type`
  (dir `tests/Integration/Services/CRM/Type/`, currently un-suited)
- `Makefile` — `test-integration-crm-userfieldconfig` and `test-integration-crm-type`
  targets
- `phpstan.neon.dist` — add `tests/Integration/Services/CRM/Userfieldconfig` and
  `tests/Integration/Services/CRM/Type` to `paths` (src/ already blanket-covered)
- `rector.php` — add `src/Services/CRM/Userfieldconfig` + its test dir, and
  `src/Services/CRM/Type` + its test dir, to `withPaths`
- `.php-cs-fixer.php` — add `->in(__DIR__ . '/src/Services/CRM/Userfieldconfig/')` and
  `->in(__DIR__ . '/src/Services/CRM/Type/')`
- `CHANGELOG.md` — new `### Added` entries under `## Unreleased`, referencing
  `[#605](https://github.com/bitrix24/b24phpsdk/issues/605)`, for both the service and
  the 4 events

---

## Deptrac compliance

All new code lives in `Services` (depends on `Core`, `Application`, `Legacy` — allowed).
No new cross-service imports. `Events` classes only depend on
`Application\Requests\Events\AbstractEventRequest` and `Core\Result\AbstractItem` /
`Core\Contracts\Events\*`, consistent with existing event classes elsewhere in the tree.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-crm-userfieldconfig   # needs userfieldconfig scope on webhook
make test-integration-crm-type              # events unit-tested; this covers TypeTest.php too
```
