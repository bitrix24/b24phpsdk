# Plan: Add sign.b2e.* service support (issue #504)

## Context

Add support for Bitrix24 Electronic Document Management (КЭДО) REST API methods under the `sign.b2e.*` scope.

**API methods:**
- `sign.b2e.document.send` — sends a document for signing (application context only)
- `sign.b2e.document.get` — returns information about a document and its signing members
- `sign.b2e.company.provider.list` — returns list of signature providers for a company
- `sign.b2e.personal.tail` — returns signed documents list for current user (application context only)
- `sign.b2e.mysafe.tail` — returns signed documents in company safe (application context only)

**Events:**
- `OnSignB2eDocumentStatusChanged` (code `ONSIGNB2EDOCUMENTSTATUSCHANGED`) — fires when document status changes
- `OnSignB2eMemberStatusChanged` (code `ONSIGNB2EMEMBERSTATUSCHANGED`) — fires when member status changes

**Response shapes:**
- `document.send` / `document.get` → `result` is object with `uid`, `state`, `members`
- `company.provider.list` → `result` is direct array of provider objects
- `personal.tail` → `result` is direct array of `{id, title, signed_date, file_url}`
- `mysafe.tail` → `result` is direct array of `{id, title, create_date, signed_date, creator_id, member_id, role, file_url}`

**Scope:** `sign.b2e` (some methods also accept `crm` scope for company access)

**Author:** © Dmitriy Ignatenko <algonexys@gmail.com>

---

## Files to Create

### Source

1. `src/Services/Sign/SignServiceBuilder.php` — scope service builder with `#[ApiServiceBuilderMetadata(new Scope(['sign.b2e']))]`
2. `src/Services/Sign/B2e/Document/Result/DocumentItemResult.php` — `@property-read` for `uid`, `state`, `members`
3. `src/Services/Sign/B2e/Document/Result/DocumentResult.php` — wraps `DocumentItemResult` from `result`
4. `src/Services/Sign/B2e/Document/Service/Document.php` — `send()`, `get()` methods
5. `src/Services/Sign/B2e/CompanyProvider/Result/CompanyProviderItemResult.php` — `@property-read` for `code`, `uid`, `name`, `date`, `expires`
6. `src/Services/Sign/B2e/CompanyProvider/Result/CompanyProvidersResult.php` — list result
7. `src/Services/Sign/B2e/CompanyProvider/Service/CompanyProvider.php` — `list()` method
8. `src/Services/Sign/B2e/PersonalTail/Result/PersonalTailItemResult.php` — `@property-read` for `id`, `title`, `signed_date`, `file_url`
9. `src/Services/Sign/B2e/PersonalTail/Result/PersonalTailResult.php` — list result
10. `src/Services/Sign/B2e/PersonalTail/Service/PersonalTail.php` — `tail()` method
11. `src/Services/Sign/B2e/MySafeTail/Result/MySafeTailItemResult.php` — `@property-read` for `id`, `title`, `create_date`, `signed_date`, `creator_id`, `member_id`, `role`, `file_url`
12. `src/Services/Sign/B2e/MySafeTail/Result/MySafeTailResult.php` — list result
13. `src/Services/Sign/B2e/MySafeTail/Service/MySafeTail.php` — `tail()` method
14. `src/Services/Sign/Events/SignB2eEventsFactory.php`
15. `src/Services/Sign/Events/OnSignB2eDocumentStatusChanged/OnSignB2eDocumentStatusChanged.php`
16. `src/Services/Sign/Events/OnSignB2eDocumentStatusChanged/OnSignB2eDocumentStatusChangedPayload.php`
17. `src/Services/Sign/Events/OnSignB2eMemberStatusChanged/OnSignB2eMemberStatusChanged.php`
18. `src/Services/Sign/Events/OnSignB2eMemberStatusChanged/OnSignB2eMemberStatusChangedPayload.php`

### Tests

19. `tests/Integration/Services/Sign/B2e/Document/Service/DocumentTest.php`
20. `tests/Integration/Services/Sign/B2e/Document/Result/DocumentItemResultAnnotationsTest.php`
21. `tests/Integration/Services/Sign/B2e/CompanyProvider/Service/CompanyProviderTest.php`
22. `tests/Integration/Services/Sign/B2e/CompanyProvider/Result/CompanyProviderItemResultAnnotationsTest.php`
23. `tests/Integration/Services/Sign/B2e/PersonalTail/Service/PersonalTailTest.php`
24. `tests/Integration/Services/Sign/B2e/PersonalTail/Result/PersonalTailItemResultAnnotationsTest.php`
25. `tests/Integration/Services/Sign/B2e/MySafeTail/Service/MySafeTailTest.php`
26. `tests/Integration/Services/Sign/B2e/MySafeTail/Result/MySafeTailItemResultAnnotationsTest.php`
27. `tests/Unit/Services/Sign/SignServiceBuilderTest.php`

---

## Files to Modify

1. `src/Services/ServiceBuilder.php` — add `getSignScope(): SignServiceBuilder`
2. `src/Services/RemoteEventsFactory.php` — register `SignB2eEventsFactory` in `init()`
3. `phpunit.xml.dist` — add test suites for sign.b2e
4. `Makefile` — add `test-integration-scope-sign` make target
5. `CHANGELOG.md` — add entry under `## 3.4.0 – UNRELEASED → ### Added`

---

## Deptrac compliance

New code lives in `src/Services/Sign/` (Services layer) and only depends on `Core` and `Application`
namespaces — no new violations introduced.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-scope-sign
```

