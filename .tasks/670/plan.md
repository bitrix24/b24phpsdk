# Plan: Add REST v3 support for crm.form.* (issue #670)

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/670 — REST 3.0 wrappers for 32 documented
  `crm.form.*` methods, scope `crm`. The issue itself marks the group as larger than the focused
  3.8.0 increment; it is now in milestone 3.8.0 (due 2026-10-31).
- The SDK has **no** existing `crm.form.*`, `crm.webform.*` or `crm.site.form.*` wrappers, so there is
  no legacy accessor to preserve. `CRMServiceBuilder` has no REST v3 services yet.
- Research done on 2026-10-29: refreshed OpenAPI snapshot (42 `crm.form.*` paths) + live calls on the
  test portal (one disposable form, id 12, created and removed; system forms were not modified).

### Proposed accessor (decision needed, see "Open questions")

Mirror the catalog program: `CRMServiceBuilder::v3(): CRMV3ServiceBuilder` with classes under
`Services\CRM\V3\Form\*`. Accessors: `form()`, `formField()`, `formEmbedding()`,
`formEmbeddingField()`, `formConfiguration()`, `formConfigurationField()`, `formEntityField()`,
`formEntityFieldField()`, `formScenario()`, `formScenarioField()`, `formScenarioCategory()`,
`formScenarioCategoryField()`, `formSiteButton()`, `formSiteButtonField()`, `formOpenLine()`,
`formOpenLineField()`.

---

## Verified contracts

### Form CRUD and lifecycle (`crm.form.*`)

| Method | Request | Response | Notes |
|---|---|---|---|
| `get` | `{id, select?}` | `result.item` (`bitrix.crm.formdto`) | **Without `select` only 11 scalar fields are returned**; nested blocks must be selected explicitly |
| `list` | `{select?, filter?, order?, pagination?{page,limit,offset}}` | `result.items[]` | order by `id`, `name`, `active`, `createdTime`, `updatedTime` |
| `add` | `{fields{...}}` (free-form, `name` + `crm.entityScheme` required) | `result.id` | **returns only the ID**, not the DTO |
| `update` | `{id, updatedTime*, fields{...}}` partial | `result.result = true` | `updatedTime` **required** |
| `delete` | `{id, updatedTime?, confirmed?}` | `result.result = true` | `confirmed` is only needed for an **active** form (`CRM_FORM_DELETE_CONFIRMATION_REQUIRED`); an inactive form is deleted with `confirmed=false` (verified) |
| `activate` / `deactivate` | `{id, updatedTime?}` | `result{active, updatedTime}` | `updatedTime` optional; when passed must be a valid ISO date-time |
| `validate` | `{id?, fields}` | `result{valid, violations[{field, message}]}` | works for a new form (no id) and for a change of an existing one; never persists |
| `field.get` / `field.list` | `{name, select?}` / `{select?}` | `bitrix.crm.crmfieldmetadatadto` | **different descriptor**: no `editableGroups`; date fields reported as `date-time` |

Form DTO: `id:int`, `code`, `name`, `active:bool`, `system:bool`, `languageId`, `templateId`,
`createdTime`/`updatedTime`/`activeChangedTime` (ISO date-time), `activeChangedById:int`, plus 13 nested
blocks: `appearance{title, description, buttonCaption, buttonTextColor, buttonBackgroundColor,
backgroundImageId, copyrightRemoved}`, `crm{entityScheme, duplicateMode, dealCategoryId,
dealDuplicateControlEnabled, smartProcessCategoryId, smartProcessDuplicateControlEnabled}`,
`dependencies[]`, `presetFields[]`, `responsible{userIds[], checkWorkTime}`, `agreements{enabled, items[]}`,
`submission{success, failure, redirectDelay, refill}`, `payment{enabled, disabledPaySystemsId[]}`,
`callback{enabled, from, text}`, `booking{enabled}`, `analytics{googleAnalyticsId,
googleAnalyticsPageViewEnabled, yandexMetricaId}`, `spamProtection{enabled}`,
`integrations{whatsAppEnabled, ads}`, `fields[{name, type, label, sort, required, multiple,
placeholder, valueType, value, ...}]`. Booleans are native (unlike catalog sections).

### Optimistic concurrency (issue requirement)

- A stale `updatedTime` fails with `CRM_FORM_VERSION_MISMATCH` and the error body carries
  **`currentUpdatedTime`** (verified):
  `{"error":{"code":"CRM_FORM_VERSION_MISMATCH","message":"…","currentUpdatedTime":"2026-10-29T14:16:55+03:00"}}`.
- `crm.form.embedding.update` **bumps the form's `updatedTime`**; a form update with the pre-embedding
  version then fails with `CRM_FORM_VERSION_MISMATCH` (verified). `activate`/`deactivate` also return the
  new `updatedTime`.
- Today `ApiLevelErrorHandler` maps all `CRM_FORM_*` codes to a plain `BaseException` and drops
  `currentUpdatedTime` (`UnsuccessfulResponseError` keeps only `code`, `message`, `validation`).

Documented domain errors: `CRM_FORM_VERSION_MISMATCH`, `CRM_FORM_DELETE_CONFIRMATION_REQUIRED`,
`CRM_FORM_SYSTEM_IMMUTABLE` (system forms cannot be changed or deleted), `CRM_FORM_ACTIVATION_LIMIT_REACHED`,
`CRM_FORM_WRITE_RESTRICTED`, `CRM_FORM_PHONE_VERIFICATION_REQUIRED`, `CRM_FORM_PARTIALLY_CREATED`,
`CRM_FORM_SIDE_EFFECT_FAILED`. The system-form and plan-limit codes are taken from the docs: the test portal's
system forms were deliberately not mutated.

### Embedding (`crm.form.embedding.*`)

- `get {id, variant?, buttonText?, buttonType?, delay?}` → `result.item{formActive, resourcesReady,
  publicUrl, inline, click, auto}`; each variant `{code, previewUrl, settings, options}`; `delay` is valid only
  with `variant=auto` (validation error otherwise).
- `update {id, updatedTime*, fields{click?, auto?}}` → `result.result`; only `settings` is writable
  (`click.options` is rejected); `inline` is read-only.
- `settings` example — click: `{type, position, vertical, button{use, text, font, align, plain, rounded,
  outlined, decoration, color{text, textHover, background, backgroundHover}}}`; auto: `{type, position,
  vertical, delay}`; `options` lists allowed values per setting.

### Directories (configuration, entity fields, scenarios)

| Method | Request | Response |
|---|---|---|
| `configuration.get` | `{}` | `result.item` — 13 blocks (`entitySchemes`, `duplicateModes`, `lead`, `dealCategories`, `smartProcesses`, `languages`, `currencies`, `fieldTypes`, `paySystems`, `permissions`, `limits`, `product`, `region`), ~44 KB |
| `entityfield.list` | `{filter*}` — **filter is required** (`entityName`, e.g. `["entityName","in",["LEAD","CONTACT"]]`) | `result.items[{name, label, type, required, multiple, entityName, entityLabel, items[{id,label}]}]` |
| `scenario.list` | `{}` | `result.items[{id, title, description, categoryId, iconUrl, sections[], expertSections[], defaultSection, openable, soon, actions[]}]` |
| `scenariocategory.list` | `{}` | `result.items[{id, title}]` |
| `*.field.get` / `*.field.list` | standard | `bitrix.rest.dtofielddto` (with `editableGroups`) |

### Placements (site buttons, Open Channels)

| Method | Request | Response |
|---|---|---|
| `sitebutton.list` | `{filter*, order?, pagination?}` — **filter by `formId` required** | `result.items[{formId, id, name, system, bound, formsId[], previewUrl}]` |
| `openline.list` | `{filter*, order?, pagination?}` — **filter by `formId` required** | `result.items[{formId, id, name, bound, welcomeFormEnabled, welcomeFormId, welcomeFormDelayed, previewUrl}]` |

So `list(int $formId, …)` takes the form ID as a mandatory argument (same approach as `iblockId` in #672).

### `crm.form.form.*` aliases (issue: "investigate")

OpenAPI publishes 10 undocumented `crm.form.form.*` paths (activate, add, deactivate, delete, field.get,
field.list, get, list, update, validate). They are live and return the same payloads as `crm.form.*`
(verified for `list` and `get`). Per the issue they are **not** wrapped as public methods. The OA coverage
command has no alias support, so they will keep appearing as uncovered; the final PR documents this, and
an alias allowlist for `ShowOaSdkCoverageCommand` is proposed as a separate follow-up issue.

---

## Delivery: 4 PRs, all into `v3-dev`, branches stacked in this order

Branch base: `v3-dev`. PR 1 is independent of the catalog PRs (#705–#708). PRs 2–4 depend on PR 1
(sub-builder, shared exception, shared field-descriptor classes) but not on each other.

### PR 1 — Core concurrency errors + form CRUD/lifecycle (10 methods) — `feature/670-crm-form-v3-core`

- Core (`src/Core`):
  - `UnsuccessfulResponseError` keeps unknown error keys in a new `array $details` property.
  - `ApiLevelErrorHandler`: `crm_form_version_mismatch` → new `Core\Exceptions\VersionMismatchException`
    (extends `BaseException`, exposes `getCurrentVersion(): ?CarbonImmutable` from `currentUpdatedTime`).
    Other `CRM_FORM_*` codes keep `BaseException` with the code in the message (no BC change).
  - Unit tests for both, including batch error responses.
- `CRMServiceBuilder::v3()` + `Services\CRM\V3\CRMV3ServiceBuilder` (`form()`, `formField()`).
- `Services\CRM\V3\Form\Service\Form`: `get(int $id, array|FormSelectBuilder $select = [])`,
  `list(filter, select, order, ?FormListPagination)`, `add(array|FormItemBuilder $fields): AddedItemResult`
  (core result — the API returns only `result.id`; verify normalization in a unit test),
  `update(int $id, CarbonImmutable $updatedTime, array|FormItemBuilder $fields): UpdatedFormResult`,
  `delete(int $id, ?CarbonImmutable $updatedTime = null, bool $confirmed = false): DeletedFormResult`,
  `activate(int $id, ?CarbonImmutable $updatedTime = null): FormActivityResult` (and `deactivate`),
  `validate(array|FormItemBuilder $fields, ?int $id = null): FormValidationResult` (`isValid()`,
  `getViolations(): FormViolation[]`). `updatedTime` is sent as ISO 8601 (`toIso8601String()`).
- `FormSelectBuilder` (generated, `bitrix.crm.formdto`), `FormItemBuilder` (generated from
  `/crm.form.add`; the schema is a free-form object, so the builder is likely hand-written — record it), `FormFilter`.
- `FormItemResult` (`AbstractAnnotatedItem`) with typed nested items: `FormAppearanceItemResult`,
  `FormCrmSettingsItemResult`, `FormResponsibleItemResult`, `FormAgreementsItemResult`,
  `FormSubmissionItemResult`, `FormPaymentItemResult`, `FormCallbackItemResult`, `FormBookingItemResult`,
  `FormAnalyticsItemResult`, `FormSpamProtectionItemResult`, `FormIntegrationsItemResult`,
  `array<FormFieldItemResult> $fields`; `dependencies` / `presetFields` stay `array` (empty on the
  test portal; shape not verifiable live).
- `FormField` service on `crm.form.field.*` with its own `FormFieldMetadataItemResult` (crm descriptor,
  no `editableGroups`).
- Integration: full lifecycle on a disposable form (add → get with full select → validate → update →
  stale update throws `VersionMismatchException` with the current version → activate → delete without
  confirmation throws on the active form → delete confirmed); `FormItemResultAnnotationsTest` +
  nested-item casting; no mutation of system forms.
- CHANGELOG: one `### Added` entry for the form services, one `### Added` entry for `VersionMismatchException`.

### PR 2 — Embedding (4 methods) — `feature/670-crm-form-v3-embedding`

- `formEmbedding()`: `get(int $formId, ?EmbeddingVariant $variant = null, ?string $buttonText = null,
  ?string $buttonType = null, ?int $delay = null)` (guard: `delay` only with `EmbeddingVariant::auto`),
  `update(int $formId, CarbonImmutable $updatedTime, array $click = [], array $auto = []): UpdatedEmbeddingResult`.
- `EmbeddingItemResult`, `EmbeddingVariantItemResult` (`code`, `previewUrl`, `settings`/`options` as arrays —
  their keys differ per variant); `EmbeddingVariant` enum (`inline`, `click`, `auto`).
- `formEmbeddingField()` on `crm.form.embedding.field.*`.
- Integration: embedding update bumps the form `updatedTime`; a form update with the old version throws
  `VersionMismatchException` (issue requirement).

### PR 3 — Directories (12 methods) — `feature/670-crm-form-v3-directories`

- `formConfiguration()->get()` → `ConfigurationItemResult` (top-level blocks typed as `array`, plus typed
  `entitySchemes`, `duplicateModes`, `languages`, `currencies` items — the blocks a form builder needs).
- `formEntityField()->list(array $entityNames)` (filter required; builds `['entityName','in',…]`),
  `EntityFieldItemResult` + `EntityFieldOptionItemResult` (`items[{id,label}]`).
- `formScenario()->list()`, `formScenarioCategory()->list()` with item results.
- Field services: `formConfigurationField()`, `formEntityFieldField()`, `formScenarioField()`,
  `formScenarioCategoryField()`.
- Read-only integration tests + `*AnnotationsTest` per entity.

### PR 4 — Placements (6 methods) — `feature/670-crm-form-v3-placements`

- `formSiteButton()->list(int $formId, order, pagination)`, `formOpenLine()->list(int $formId, …)` with
  item results; field services `formSiteButtonField()`, `formOpenLineField()`.
- Read-only integration tests on the system forms' existing bindings (no mutation) + annotation tests.
- Closes #670; documents the `crm.form.form.*` aliases in the PR and in the CHANGELOG entry.

PRs 1–3 reference "Part of #670"; PR 4 closes it.

---

## Shared conventions (all PRs)

- Every method: `#[ApiEndpointMetadata(..., ApiVersion::v3)]`, links under
  `https://apidocs.bitrix24.com/api-reference/crm/form/…` exactly as in the issue checklist.
- Date/time parameters (`updatedTime`) are `CarbonImmutable`.
- TDD per method; `make lint-*`, `make test-unit`, a dedicated `test-integration-crm-v3-form-*` suite per PR,
  `make sdk-coverage-v3-show-uncovered` after each PR.
- Integration tests create only disposable forms (`sdk v3 form …`), deactivate before delete, and clean up in
  `tearDown`; they never modify system forms (`system = true`).

## Deptrac compliance

`VersionMismatchException` lives in `Core\Exceptions`, so the Core layer does not depend on `Services`.
Services depend on Core/Attributes/Filters only. No new `skip_violations`.

## Out of scope / follow-ups

- Mapping `BITRIX_REST_V3_EXCEPTION_ENTITYNOTFOUNDEXCEPTION` to `ItemNotFoundException` (currently
  `BaseException` for all v3 services) — changes behaviour across the SDK; propose a separate issue.
- Alias allowlist for the OA coverage report (`crm.form.form.*` and similar).

## Open questions

1. Accessor: `getCRMScope()->v3()->form()` (recommended, consistent with catalog) or directly
   `getCRMScope()->form()` (no legacy name conflict today).
2. Nested form blocks: typed nested `*ItemResult` classes (recommended, matches "annotated entity items")
   or plain `array` properties.
3. Milestone: PR 1 is realistic for 3.8.0 (due 2026-10-31); PRs 2–4 are proposed for the next 3.x milestone.

---

## PR 1 implementation notes (2026-10-29)

- Branch `feature/670-crm-form-v3-core` from `v3-dev`; independent of the catalog PRs #705–#708.
- `FormSelectBuilder` generated; it selects nested blocks by dotted paths (`appearance.title`, …), which
  the API accepts (verified live). `FormItemBuilder` written manually: the item-builder generator fails
  with "no writable fields" because `/crm.form.add` describes `fields` as a free-form object.
  `FormItemResult` and nested items written manually (result-item generator cannot run in a worktree container).
- Core additions beyond the plan: `AbstractAnnotatedItem` now casts a single nested `*ItemResult` annotated
  on a property (resolved before the `str_contains` scalar checks, because class names may contain `int`,
  `string`, …). Required for the typed nested form blocks; covered by `AbstractAnnotatedItemTest`.
- `add()` returns `AddedFormResult` (implements `AddedItemIdResultInterface`, reads `result.id`) — the core
  `AddedItemResult` reads `result[0]`. `validate()` reuses `Core\Response\DTO\ValidationError` for violations.
- `FormFilter::active()` uses the generic `FieldConditionBuilder`: forms validate `active` as a native bool.
- `dependencies` and `integrations.ads` stay `array` (empty on the test portal, nested rule shape not verifiable).
- Quality gate: cs-fixer, rector, phpstan, deptrac, `make test-unit` (2202 tests) and
  `make test-integration-crm-v3-form` (12 tests, live portal: lifecycle incl. stale-version rejection with
  `getCurrentVersion()`, delete confirmation for an active form, nested block annotations/casting) pass;
  no leftover forms. The 10 PR 1 methods are no longer in `make sdk-coverage-v3-show-uncovered`.

## PR 3 implementation notes (2026-10-29)

- Branch `feature/670-crm-form-v3-directories`, stacked on PR 1 (#711) — not on PR 2; both add accessors to
  `CRMV3ServiceBuilder`, so the second one to merge needs a trivial conflict resolution.
- Deviation from the plan: every configuration block is typed (not only entity schemes, duplicate modes,
  languages and currencies) — the blocks are small and fully verified live: `ConfigurationEntitySchemeItemResult`,
  `NamedCodeItemResult`, `NamedIdItemResult`, `ConfigurationLeadItemResult`, `ConfigurationSmartProcessItemResult`,
  `ConfigurationCurrencyItemResult`, `ConfigurationFieldTypeItemResult`, `ConfigurationPermissionsItemResult`,
  `ConfigurationLimitsItemResult` / `ConfigurationActiveFormsLimitItemResult`, `ConfigurationProductItemResult`.
- `formEntityField()->list(array $entityNames)` builds `['entityName', 'in', …]` and rejects an empty list.
- Test helpers: `tests/Unit/Services/CRM/V3/V3ServiceTestTrait.php` (core double + endpoint metadata check) and
  `tests/Integration/Services/CRM/V3/NestedItemAnnotationsTrait.php` (nested item annotations vs raw payload).
- Quality gate: cs-fixer, rector, phpstan, deptrac, `make test-unit` (2230 tests),
  `make test-integration-crm-v3-form-directories` (27 tests, read-only) and `make test-integration-crm-v3-form`
  (12 tests) pass; the 12 directory methods are no longer uncovered.
