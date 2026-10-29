# High-level placement registrar implementation plan (issue #446)

**Goal:** Let an installer declare placement handlers once, repeat registration safely, and receive a structured created/skipped/updated/failed report.

**Architecture:** Add a placement-scoped registrar over the existing Placement service. Keep EventManager and the typed IM facades unchanged. Identify a registration by placement code, exact handler URL and user ID; never reconcile a whole placement by deleting handlers absent from the declaration list.

**Tech stack:** Existing PHP SDK services, Url value object, PlacementOptionsInterface, PHPUnit, Docker Compose.

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/446; already assigned to mesilov; milestone 3.8.0.
- Base: `origin/v3-dev` at `9a13a7d44d17fca8f2a8459a54ea13920caad80c`; branch `feature/446-placement-registrar`.
- Reusing this chat's clean isolated worktree after merging #208; no production work was started for #319.
- Read the existing EventHandlerMetadata/EventManager, Placement service/results/builder, IM typed placement options/localization, tests and maintainer placement guide.
- Official Bitrix24 MCP documentation fetched on 2026-10-28 for placement.bind, placement.unbind and placement.get. Context7 is unavailable in this session.
- `make oa-schema-build` completed successfully before implementation planning.
- Fresh baseline `make test-unit`: 2,174 tests, 11,732 assertions, exit 0; 11 existing deprecations.
- These are legacy REST endpoints within SDK 3.x. Application OAuth context is required; incoming webhooks cannot replace it.
- `placement.get` returns flat records with id, placement, userId, handler, options, title, description, langAll. The existing item DTO does not annotate id/userId; consume the raw result internally without widening that DTO or inventing a fields endpoint.
- `placement.bind` returns scalar boolean; reuse PlacementBindResult and consume isSuccess().
- `placement.unbind` returns result.count. Omitted/empty HANDLER removes all handlers for that placement. USER_ID optionally restricts removal to one user. The current wrapper lacks this USER_ID parameter; additive support is needed for isolated per-user replacement.
- No new REST endpoint or annotated entity item is introduced. No SDK file generator applies to plain declaration/report DTOs with native readonly properties.

### Application test credentials

A test application profile is configured in the original checkout and copied into this worktree's ignored tests/ApplicationBridge/.env.local. However, tests/ApplicationBridge/auth.json is absent in every existing SDK worktree. A read-only Factory::getServiceBuilder(true) probe confirms application credentials are unavailable. Do not invoke placement registration with a webhook, substitute production access, fabricate live results, or skip tests and call the feature verified. Unit implementation can proceed after plan approval; live verification requires authorizing the existing test ApplicationBridge and producing its ignored auth.json through the documented application setup.

## Design choice

1. Recommended: a separate PlacementRegistrar with explicit conflict policy and structured reports. It adds installer ergonomics without changing existing low-level calls.
2. Extending EventManager with placement logic would mix event and widget responsibilities and cannot represent placement-specific config comparison cleanly.
3. Replacing all handlers for a placement would remove unrelated URLs or users. Do not implement that behavior.

Default policy is fail on a differing configuration at the same identity. An explicit replace policy enables update. A different handler URL is a separate registration, so it is created alongside existing handlers. Migrating an old URL remains an explicit installer decision; the registrar never guesses which old URL to remove.

## Public API and files to create

### src/Services/Placement/Common/PlacementHandlerMetadata.php

```php
final readonly class PlacementHandlerMetadata
{
    public string $placementCode;
    public Url $handlerUrl;
    public array $lang;
    public array $options;
    public ?int $b24UserId;

    public function __construct(
        string $placementCode,
        string|Url $handlerUrl,
        array $lang = [],
        PlacementOptionsInterface|array $options = [],
        ?int $b24UserId = null,
    );
}
```

Normalize the placement code to uppercase and reject empty codes and empty/non-http(s) handler URLs. Accept existing Url values and validate string URLs at construction. Preserve exact URL text after validation (paths, case and query string can distinguish handlers). A null user denotes global registration; explicit positive IDs denote user-specific registration. Reject zero/negative explicit user IDs. Snapshot options via build() in the constructor so later mutation of a fluent builder cannot silently change a declaration. Keep localization compatible with Placement::bind: array<string,array{TITLE:string,DESCRIPTION?:string,GROUP_NAME?:string}>; existing IM PlacementLangMap callers pass toArray(). Do not move or duplicate the existing IM localization types in this feature.

### src/Services/Placement/PlacementConflictPolicy.php

```php
enum PlacementConflictPolicy: string
{
    case fail = 'fail';
    case replace = 'replace';
}
```

### src/Services/Placement/PlacementRegistrationStatus.php

```php
enum PlacementRegistrationStatus: string
{
    case created = 'created';
    case skipped = 'skipped';
    case updated = 'updated';
    case failed = 'failed';
}
```

### src/Services/Placement/Result/PlacementRegistrationItemResult.php

Plain readonly report DTO, not a remote entity or AbstractResult subclass. Public fields:

- declaration: PlacementHandlerMetadata
- status: PlacementRegistrationStatus
- error: ?BaseException, null for successful/skipped items
- rollbackError: ?BaseException, set only if restoration fails
- previousConfigurationRestored: ?bool, null when restoration was not attempted

### src/Services/Placement/Result/PlacementRegistrationResult.php

Plain readonly collection with constructor(array $items), getItems(): array returning list<PlacementRegistrationItemResult>, and isSuccess(): bool. Empty input succeeds; any failed item makes isSuccess false. Preserve input order and retain the original error separately from any rollback error.

### src/Services/Placement/Service/PlacementRegistrar.php

```php
final readonly class PlacementRegistrar
{
    public function __construct(private Placement $placement);
    public function register(
        array $declarations,
        PlacementConflictPolicy $conflictPolicy = PlacementConflictPolicy::fail,
    ): PlacementRegistrationResult;
}
```

Algorithm:

1. Validate the full input list and reject non-PlacementHandlerMetadata items or duplicate identities before any REST request. Empty input returns an empty successful report without requests.
2. Read placement.get once and consume the response. A global read/auth/transport failure propagates before writes; per-handler write failures are represented in the structured report.
3. Index raw registered records by uppercase placement code, exact URL and userId (absent/null means global zero). If multiple remote records share one identity, fail that declaration without deleting anything.
4. Canonicalize associative map order recursively while preserving list order. For each LANG_ALL locale normalize omitted DESCRIPTION/GROUP_NAME to empty strings; normalize locale keys to lowercase and field keys to the documented uppercase spelling. Compare OPTIONS by canonical JSON structure without dropping unknown keys or guessing scalar coercions. Verify real IM scalar normalization in the live test before claiming repeated-flow compatibility.
5. If no matching identity exists, call bind with the declaration and consume isSuccess(). Report created on true. A false result or BaseException becomes failed, and processing continues to the next independent declaration. Do not swallow arbitrary programming exceptions.
6. If identity, effective LANG_ALL and OPTIONS match, report skipped without bind/unbind.
7. If config differs and policy is fail, report failed with a descriptive BaseException; leave the existing handler intact.
8. Under replace, ensure the old raw OPTIONS/LANG_ALL/user identity are observable and restorable. If LANG_ALL is absent, or it is empty while legacy top-level title/description contain information the existing bind wrapper cannot reproduce, report failed before unbind.
9. Unbind only the exact placement, nonempty URL and exact user identity. The registrar passes USER_ID=0 for a global registration and the positive ID for a user-specific one; it never falls back to an unscoped unbind. Require count exactly one before binding the replacement; report any zero/multiple-deletion discrepancy as failed rather than asserting an update succeeded.
10. Bind the requested declaration and consume its result. On true, report updated. On failure, attempt to rebind the saved original config to the same identity, consume that result, and report failed with the original error plus restoration outcome/error. A restored original does not make the requested update successful.
11. No broad unbind, deletion of unmentioned registrations, URL migration, automatic retries, distributed locking or atomicity guarantee. Document that unbind+bind has an observable gap and concurrent installers can produce a reported conflict/error; a later installer run reconciles again.

## Files to modify

### src/Services/Placement/PlacementServiceBuilder.php

Add cached registrar(): PlacementRegistrar, constructing it from the cached placement() service.

### src/Services/Placement/Service/Placement.php

Append optional ?int $b24UserId = null to unbind(), retaining existing calls and defaults. Include USER_ID in the request only when provided, preserving explicit zero for targeted global removal. Existing null/default calls keep their old payload. Leave bind() signature and typed IM facade signatures unchanged. Update touched endpoint documentation links to https://apidocs.bitrix24.com/api-reference/widgets/placement-unbind.html and placement-get.html.

### Makefile and phpunit.xml.dist

Add a focused integration_tests_placement_registrar suite containing only the registrar lifecycle test, and a test-integration-placement-registrar target:

```make
.PHONY: test-integration-placement-registrar
test-integration-placement-registrar:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_placement_registrar
```

Retain the existing integration_tests_scope_placement suite, which discovers the new test by directory. Ensure CS Fixer, Rector and PHPStan include new Placement unit tests; production/Placement integration paths are already covered where configured. Do not add analysis exclusions.

### CHANGELOG.md

Under Unreleased / Added:

```markdown
- Added an installer-oriented placement registrar with idempotent registration, explicit replacement policy and per-handler results ([#446](https://github.com/bitrix24/b24phpsdk/issues/446))
```

### docs/EN/Services/placement-registrar.md

Document OAuth application context, identity rules, omitted declarations, statuses, default conflict handling, replacement/restoration failures and non-atomic updates. Include this installer-oriented example and an explicit replace example:

```php
$registrar = $serviceBuilder->getPlacementScope()->registrar();
$result = $registrar->register([
    new PlacementHandlerMetadata(
        PlacementLocationCodes::IM_TEXTAREA,
        new Url('https://example.com/widgets/textarea'),
        PlacementLangMap::empty()
            ->with(LangCodes::EN, new PlacementLangItem('My widget'))
            ->toArray(),
        (new ImTextareaPlacementOptions('fa-comment'))->width(400)->height(160),
    ),
]);
foreach ($result->getItems() as $item) {
    // created / skipped / updated / failed; error and restoration details are explicit.
    echo $item->status->value;
}
```

Link the guide from the existing English documentation index without reorganizing unrelated pages.

## Tests and execution order

### Task 1: immutable declarations, comparison and reports

Create tests/Unit/Services/Placement/Common/PlacementHandlerMetadataTest.php and Result/PlacementRegistrationResultTest.php. Write failing tests first for URL/code/user validation, builder snapshot immutability, identity preservation, empty success and aggregate failure. Implement plain DTOs/enums only after RED is observed.

### Task 2: low-level per-user removal

Create/update a unit test at tests/Unit/Services/Placement/Service/PlacementTest.php. Assert old unbind calls retain the exact payload with no USER_ID, and explicit user IDs produce the additional parameter. Use a real Response fixture to verify result.count. Observe RED, then make the narrow optional-parameter change.

### Task 3: registrar and builder

Create tests/Unit/Services/Placement/Service/PlacementRegistrarTest.php. Use the real Placement wrapper with mocked CoreInterface and real Response/MockResponse fixtures to verify the actual request sequence and result parsing.

Cover: empty input/no calls; validation of all declarations before writes; duplicate declarations; one get for nonempty input; created then same-declaration skipped on a second run; map key order/default language empties; different options/title; independent URLs at one code; global vs per-user identity; unknown/multiple remote records; fail policy/no mutation; explicit replace; exact URL/USER_ID unbind; false bind; API and transport failures; one failed declaration followed by a successful independent declaration; global initial-read failure before writes; replacement failure with successful restoration; replacement failure with failed restoration; builder caching and existing Placement calls unchanged. Preserve nonmatching handlers in every relevant assertion.

Observe RED before production registrar logic, then implement the minimal state machine and keep tests GREEN.

### Task 4: real application lifecycle

Create tests/Integration/Services/Placement/Service/PlacementRegistrarTest.php using Factory::getServiceBuilder(true). Authorize the existing test application before running it. Use unique handler URLs under its configured application domain and try/finally cleanup of only those exact handlers.

- Create an IM_TEXTAREA registration using existing language/option builders.
- Register the same declaration again and assert skipped, without duplicate-binding error.
- Change label/options; default policy reports failed and preserves the old config.
- Repeat under replace and assert updated; next repeat is skipped.
- Register a second URL at the same code and verify the first remains.
- Verify real options/lang normalization, including numeric width/height and omitted optional language strings.
- Verify documented USER_ID filtering, including explicit zero for a global handler, before enabling replacement delivery. If the portal does not isolate global removal with USER_ID=0, stop replacement verification and revise the design; never silently use an unscoped deletion.
- Run an isolated per-user PAGE_BACKGROUND_WORKER case only when the configured test app supports that placement; otherwise require deterministic unit coverage and document the portal-specific unverified branch rather than claiming a live check.
- Exercise restoration/error branches deterministically in unit tests instead of forcing disruptive portal failures.
- Remove only SDK-owned handlers by nonempty URL; confirm their absence afterwards.

No new annotated remote item classes means no new fields()/annotation generator tests are required. Preserve current DTO public contracts.

### Task 5: quality gate and delivery

Run these in order, keeping live integration after all light checks:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-placement-registrar
make test-integration-scope-placement
```

Run existing IM placement tests if the changed low-level unbind contract or live payload evidence affects that path. Review spec compliance and code quality, fix actionable findings, update changelog and docs, read the PR template, commit and create a PR against v3-dev with milestone 3.8.0 and Closes #446. Attach the PR and wait for terminal CI status. Do not merge automatically.

## Deptrac compliance

Placement services/DTOs depend only on Core value objects/exceptions and their own Placement namespace. Typed IM options enter through PlacementOptionsInterface; the registrar does not import the IM namespace. Examples/tests may import the existing IM types. No Infrastructure imports or new deptrac exceptions.

## Preparation checklist

- [x] Issue/context/official docs and current SDK patterns reviewed.
- [x] Clean existing worktree reused; feature branch created from current v3-dev.
- [x] OpenAPI snapshot refreshed.
- [x] Application credential availability checked without exposing secrets.
- [x] User approved the explained written plan on 2026-10-28: "ок, бери в работу".
- [x] Complete TDD, implementation, docs and light checks.
- [ ] Authorize the configured test app and complete live integration checks.
- [ ] Publish PR and report final CI outcome.

## Plan review

- Unambiguity: exact declaration identity, comparison rules, conflict policy, API calls, statuses and restoration outcomes are fixed.
- Non-contradiction: global and per-user registrations are isolated; missing handler URLs never reach unbind; restored old config is still a failed requested update.
- No gaps: covers input validation, DTOs, orchestration, the missing USER_ID unbind argument, builder, TDD, OAuth prerequisites, live cleanup, docs, changelog, PR and CI.

## Updated delivery authorization

The user explicitly chose: no test OAuth access now; prepare a draft PR. Complete code, unit tests, light quality gates, docs and review. Exercise the live test command to record its missing-credential prerequisite without skipping or claiming live success. Publish the PR as draft with OAuth/config-normalization/USER_ID-isolation verification unchecked; do not mark it ready or merge until those checks are completed. This user instruction overrides the normal all-green live-integration gate for publishing a reviewable draft.

## Implementation verification and review log

- Initial TDD: declaration/unbind/accessor tests and registrar tests were observed RED before production classes/behavior were added. Focused core suite reached 57 tests / 336 assertions after a rollback regression: stored global user IDs 0/null must bind the restored configuration with null, while targeted unbind keeps explicit zero.
- First full light gate: CS Fixer, Rector, PHPStan and Deptrac passed; 2,231 unit tests / 12,068 assertions passed with the same 11 pre-existing deprecations.
- Attempted `make test-integration-placement-registrar`: 2 errors, 0 assertions, both in Factory::getServiceBuilder(true) because application credentials are absent. No portal writes were reached. Existing full Placement live suite is not repeated against the same missing prerequisite. User explicitly authorized a draft PR under this condition.
- Spec compliance review approved the implementation. Independent quality review found three P2 edge cases to address before publishing: validate optional localization strings before any network calls; reject normalized locale/field collisions instead of silently dropping input; preserve list versus map classification in nested option comparisons. Regression tests and corrections are required before the final gate.
- Style scope explicitly includes all new/touched Placement PHP files and the new unit/integration tests, leaving unrelated pre-existing Placement formatting outside the feature diff.

Review corrections are complete: all optional localization fields are validated, ambiguous normalized locale/field names are rejected before requests, declaration binds use the same normalized localization semantics as comparison, and comparison-only tagged structures retain map/list identity and deterministic map order. Rollback reuses the saved raw language configuration. The public named parameter is `conflictPolicy`, matching this plan. Regression failures were observed before each correction; focused final Placement suite: 80 tests / 416 assertions passed.

## Final local evidence and draft readiness

- `make lint-cs-fixer`: passed on all new/touched Placement files and existing configured source paths.
- `make lint-rector`: passed (existing deprecated-rule configuration warnings remain).
- `make lint-phpstan`: passed, no errors.
- `make lint-deptrac`: passed, zero violations/errors.
- `make test-unit`: passed, 2,254 tests / 12,148 assertions; the same 11 baseline deprecations remain.
- New focused Placement unit suite: 80 tests / 416 assertions.
- Spec compliance review approved final refinements; independent code-quality reviewer rechecked all three regression cases and confirmed no remaining findings.
- Focused live test command was attempted: two prerequisite errors in Factory, zero assertions, no portal calls/writes. OAuth `auth.json` is absent. The broader existing Placement live suite is pending the same prerequisite. No skipped or incomplete test is counted as a successful live verification.

Publish only as a draft under the user's explicit instruction. Before ready/merge: authorize the test app, run the focused registrar and existing Placement suites, confirm real OPTIONS/LANG_ALL round trips and repeat skipping, verify global USER_ID=0 versus user-specific isolation (PAGE_BACKGROUND_WORKER), and resolve any portal discrepancies. Replacement remains non-atomic and restoration best effort as documented. No release or merge is authorized by draft publication.

## Synchronization with current v3-dev

Draft PR creation detected a conflict because v3-dev advanced during implementation to `96a8fdf3`. Merged that base into the feature branch without rewriting published history. The only conflict was adjacent Added entries in CHANGELOG; retained both the placement registrar entry and the incoming OpenSSF Scorecard entry. Incoming shared CI workflow and Core Batch changes remain intact. Repeat the full light gate on the merged tree before pushing the resolution.

Post-merge full light gate passed: 2,255 unit tests / 12,151 assertions, unchanged 11 pre-existing deprecations; CS Fixer, Rector, PHPStan and Deptrac all passed. The extra baseline test comes from the incoming Core Batch work. Draft PR: https://github.com/bitrix24/b24phpsdk/pull/702. Live OAuth prerequisites remain unchanged.
