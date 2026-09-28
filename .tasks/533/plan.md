# Plan: Adopt Url/LocalizedString/code value objects across SDK signatures (#533)

Status: implemented; user explicitly requested PR publication on 2026-09-29 with the OAuth integration blocker disclosed.

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/533
- Dependency #493 is closed and its Robot reference implementation is present.
- User selected SDK v3. Base: `origin/v3-dev`, commit `807c6523768e1b4e310c14fbec9d6dc6ce098104`.
- Branch: `feature/533-adopt-value-objects`.
- Stage 1 adds value-object inputs while retaining existing primitive alternatives, argument names/order/defaults, null/empty sentinels, primitive serialized output, and result envelopes. Invalid absolute URL strings will fail before transport, as explicitly requested by #533; this is stricter than previous pass-through behavior and must be documented.
- Stage 2 is planned for SDK 4.0, not implemented in this 3.x change. The release date is not set. The migration guide will carry the removal checklist, including exported application interfaces whose widening would break third-party implementers.
- `make composer-install` and `make -s oa-schema-build` passed in this worktree. The refreshed schema contains unrelated upstream changes and is excluded from this issue commit.
- Baseline `make test-unit` exits 2 before collecting tests: missing `</testsuite>` after lines 591, 593 and 598 in `phpunit.xml.dist`. Repair these three existing defects first, preserving all suites. Then rerun the baseline. Do not describe the current baseline as passing.

## Design and alternatives

Recommended: shared primitive resolver in Core plus workflow-code resolver in Services/Workflows. Callers retain their service-specific payload construction. This also serves credentials, factories and builders without importing service classes into Core.

Alternative: put all resolvers in AbstractService. Fewer new files, but credentials/builders cannot reuse them and workflow code types would be coupled to every service.

Alternative: separate conversion logic in each class. Small local patches, but repeats the exact duplication this issue is intended to remove.

No recursive conversion of arbitrary arrays: raw REST field bags, third-party application interfaces, output DTOs, event payloads and documentation metadata are distinct contracts. This change covers explicit input signatures and their forwarding facades. Record these limits in the public migration guide, rather than claiming that every arbitrary payload accepts objects.

### Source-backed boundary decisions (checked 2026-09-29)

Official Bitrix24 MCP method details were inspected for all REST methods in the table below. The event MCP entries resolve to BX24 JS wrappers, so the actual REST pages were checked separately.

| Area | Input / response contract | Decision |
| --- | --- | --- |
| `bizproc.activity.add/update/delete`, `bizproc.robot.add/update/delete` | CODE is an application-scoped code; HANDLER is an absolute callback URL; NAME/DESCRIPTION are localized maps; add/update/delete return boolean result | Use ActivityCode/RobotCode, Url and LocalizedString; retain existing result wrappers and fields |
| `placement.bind/unbind` | HANDLER is URL; unbind accepts missing or empty handler for all handlers of that placement; LANG_ALL is language -> object(TITLE, DESCRIPTION, GROUP_NAME) | Widen handler; preserve null and empty-string unbind semantics; do not replace LANG_ALL with LocalizedString |
| `userfieldtype.add/update` | HANDLER is callback URL; TITLE/DESCRIPTION are plain strings | Widen handler only |
| `event.bind/unbind` | handler URL; bind returns boolean, unbind returns result.count; offline mode has separate options | Preserve event type/options and envelopes; retain documented offline sentinels if present |
| `messageservice.sender.add/update` | HANDLER URL; NAME/DESCRIPTION accept plain strings or localized maps | Add Url and LocalizedString while keeping all existing string/array/null cases |
| `ai.engine.register` | completions_url URL | Add Url |
| `imbot.v2.Bot.register` | fields.webhookUrl URL | Widen the explicit register argument; keep the generic update fields array contract |
| `voximplant.infocall.startwithsound` | URL to MP3; returns object with call status | Widen recordUrl; verify locally without initiating paid calls |
| `voximplant.sip.update` | SERVER is a SIP registration server address, not necessarily an absolute URL | Keep string; do not apply Url validation |
| `lists.element.add` | LIST_ELEMENT_URL is a substitution template such as #list_id#/element/#section_id#/#element_id#/ | Keep template string |
| `landing.landing.resolveIdByPublicUrl` | landingUrl is a relative path such as /catalog/sale/ | Keep relative string |
| Credentials / Endpoints | client domain strings currently gain https://; auth endpoint must be URL | Preserve domain normalization before Url validation |
| IM attachment link/preview/avatar builders | Existing string links are only checked for non-emptiness | Add Url conversion, retain existing non-empty string behavior to avoid rejecting existing relative/deep links; explain URI semantics in guide |

Sources:
- https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-activity/bizproc-activity-add.html
- https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-activity/bizproc-activity-update.html
- https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-activity/bizproc-activity-delete.html
- https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-robot/bizproc-robot-add.html
- https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-robot/bizproc-robot-update.html
- https://apidocs.bitrix24.com/api-reference/bizproc/bizproc-robot/bizproc-robot-delete.html
- https://apidocs.bitrix24.com/api-reference/widgets/placement-bind.html
- https://apidocs.bitrix24.com/api-reference/widgets/placement-unbind.html
- https://apidocs.bitrix24.com/api-reference/widgets/user-field/userfieldtype-add.html
- https://apidocs.bitrix24.com/api-reference/widgets/user-field/userfieldtype-update.html
- https://apidocs.bitrix24.com/api-reference/events/event-bind.html
- https://apidocs.bitrix24.com/api-reference/events/event-unbind.html
- https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-sender-add.html
- https://apidocs.bitrix24.com/api-reference/messageservice/messageservice-sender-update.html
- https://apidocs.bitrix24.com/api-reference/ai/ai-engine-register.html
- https://apidocs.bitrix24.com/api-reference/chat-bots/chat-bots-v2/imbot.v2/bots/bot-register.html
- https://apidocs.bitrix24.com/api-reference/telephony/voximplant/voximplant-infocall-start-with-sound.html
- https://apidocs.bitrix24.com/api-reference/telephony/voximplant/sip/voximplant-sip-update.html
- https://apidocs.bitrix24.com/api-reference/lists/elements/lists-element-add.html
- https://apidocs.bitrix24.com/api-reference/landing/page/methods/landing-landing-resolve-id-by-public-url.html

## Files to Create

### Shared resolvers

`src/Core/ValueObjects/ValueObjectResolver.php`:

```php
namespace Bitrix24\SDK\Core\ValueObjects;
final class ValueObjectResolver
{
    public static function resolveUrl(string|Url $value): string
    {
        return ($value instanceof Url ? $value : new Url($value))->getUrl();
    }
    /** @param array<string, string>|LocalizedString $value
     *  @return array<string, string> */
    public static function resolveLocalizedString(array|LocalizedString $value): array
    {
        return $value instanceof LocalizedString ? $value->toArray() : $value;
    }
}
```

`src/Services/Workflows/ValueObjects/WorkflowCodeResolver.php`:

```php
namespace Bitrix24\SDK\Services\Workflows\ValueObjects;
final class WorkflowCodeResolver
{
    public static function resolveRobotCode(string|RobotCode $code): string
    {
        return ($code instanceof RobotCode ? $code : new RobotCode($code))->getCode();
    }
    public static function resolveActivityCode(string|ActivityCode $code): string
    {
        return ($code instanceof ActivityCode ? $code : new ActivityCode($code))->getCode();
    }
}
```

No result-item, select-builder or item-builder class is being generated or edited; SDK generator requirements do not apply.

### New unit tests

- `tests/Unit/Core/ValueObjects/ValueObjectResolverTest.php`: valid Url/string equivalence, invalid URL rejection, LocalizedString/map equivalence, empty map preservation.
- `tests/Unit/Services/Workflows/ValueObjects/WorkflowCodeResolverTest.php`: both code classes, raw valid code, invalid code rejection, no conflation of code classes.
- `tests/Unit/Services/Workflows/Activity/Service/ActivityTest.php`: add/update/delete payloads for both input forms, null update omission, empty-map update, invalid URL/code before transport.
- `tests/Unit/Services/Placement/Service/PlacementTest.php`: handler conversion and unbind null/empty semantics.
- `tests/Unit/Services/Placement/Service/UserFieldTypeTest.php`: add/update payload parity.
- `tests/Unit/Services/AI/Engine/Service/EngineTest.php`: completions URL conversion and rejection before transport.
- `tests/Unit/Services/IMBot/Bot/Service/BotTest.php`: explicit register webhook argument, null omission, primitive payload.
- `tests/Unit/Services/Telephony/Voximplant/InfoCall/Service/InfoCallTest.php`: mocked startWithSound payload parity; no live phone call.
- `tests/Unit/Services/ServiceBuilderFactoryTest.php`: Url inputs and retained domain/default auth behavior using mocked transport.
- `tests/Unit/Application/Local/Entity/LocalAppAuthTest.php`: constructor accepts Url, primitive getters/toArray preserved, domain strings retained.

All service tests use a mocked CoreInterface with an exact payload expectation and existing response fixtures. Invalid input tests assert `call()` is never invoked. Test methods include `testValueObjectsSerializeLikeLegacyInputs`, `testInvalidUrlDoesNotReachTransport`, and `testNullUpdateKeepsFieldAbsent`, as applicable.

### Live workflow regression test

`tests/Integration/Services/Workflows/ValueObjectMigrationTest.php`:

```php
namespace Bitrix24\SDK\Tests\Integration\Services\Workflows;
use PHPUnit\Framework\TestCase;
final class ValueObjectMigrationTest extends TestCase
{
    public function testActivityLifecycleWithValueObjects(): void;
    public function testRobotLifecycleWithValueObjects(): void;
}
```

Use `Factory::getServiceBuilder(true)` with application authorization. Register unique test codes with an inert handler under the configured application domain; use false for subscription/placement; update translations; delete only the exact created code in finally. Never trigger a workflow. Existing workflow suite directory includes this file automatically. No entity-result schema changes are introduced, so new result annotation tests are not needed for this migration.

### Migration guide

Create `docs/value-object-migration.md`: legacy/object examples, exact migrated API matrix, soft deprecation wording without runtime warnings, null/empty behavior, validation change, excluded URI/template/structured-map contracts, Stage 2 checklist explicitly targeted to SDK 4.0.

## Files to Modify

1. `phpunit.xml.dist`: add three missing closing tags described above. No suite removal or renaming.
2. `src/Core/Credentials/WebhookUrl.php`: constructor `string|Url`; delegate validation to ValueObjectResolver. Preserve protected string storage and string getter for subclass compatibility. Preserve exception class and webhook-specific diagnostic context without another filter_var.
3. `src/Core/Credentials/Endpoints.php`: widen constructor and client URL mutator/factory; normalize legacy client domains first, then use shared Url validation. Keep string getters and error context.
4. `src/Core/Credentials/Credentials.php`: widen changeDomainUrl and optional OAuth URL in createFromPlacementRequest; unwrap Url before existing domain operations.
5. `src/Services/ServiceBuilderFactory.php`: widen explicit domain/webhook/OAuth URL parameters in initFromAccount, init, initFromWebhook, createServiceBuilderFromWebhook and createServiceBuilderFromPlacementRequest; forward objects to the shared credential boundary.
6. `src/Application/Local/Entity/LocalAppAuth.php`: constructor URL arguments accept Url; domain string compatibility and serialized shape stay unchanged.
7. `src/Services/Workflows/Robot/Service/Robot.php`: replace local resolvers with shared calls; widen delete code to string|RobotCode. Keep already object-only placementHandlerUrl as-is.
8. `src/Services/Workflows/Activity/Service/Activity.php`: add/update code -> string|ActivityCode, handler -> string|Url (nullable on update), localizedName/Description -> array|LocalizedString (nullable on update); delete -> string|ActivityCode. Preserve parameter names, requiredness, payload keys and result wrappers.
9. `src/Services/Main/Service/Event.php`: bind/unbind handler -> string|Url; preserve options merge behavior and offline event semantics.
10. `src/Services/Main/Common/EventHandlerMetadata.php`: constructor handler -> string|Url, store normalized public string so isInstalled comparisons and public reads remain compatible.
11. `src/Services/Placement/Service/Placement.php`: widen bind/unbind handler; convert only nonempty nonnull unbind handler.
12. `src/Services/Placement/Service/UserFieldType.php`: widen add/update handler.
13. `src/Services/IM/Placements/Placements.php`: widen five bind and five unbind forwarding methods; preserve PlacementLangMap.
14. `src/Services/Messageservice/Sender/Service/Sender.php`: handler unions; name/description add LocalizedString while preserving string/array/null; serialize object inputs before core->call.
15. `src/Services/AI/Engine/Service/Engine.php`: register completionsUrl -> string|Url.
16. `src/Services/IMBot/Bot/Service/Bot.php`: register webhookUrl -> string|Url|null.
17. `src/Services/Telephony/Voximplant/InfoCall/Service/InfoCall.php`: startWithSound recordUrl -> string|Url.
18. IM attachment files under `src/Services/IM/Message/Attach/`: `Blocks/LinkBlock.php` (url, preview), `Blocks/UserBlock.php` (avatar, link), `Items/FileItem.php` (link), `Items/ImageItem.php` (link, preview), `Items/GridItem.php` (link). Add string|Url; unwrap object and apply existing non-empty guard to strings. Do not alter target-mode validation.
19. Existing unit tests: `tests/Unit/Core/Credentials/{WebhookUrl,Endpoints,Credentials}Test.php`, `tests/Unit/Services/Workflows/Robot/Service/RobotTest.php`, `tests/Unit/Services/Main/Service/EventTest.php`, `tests/Unit/Services/IM/Placements/PlacementsTest.php`, `tests/Unit/Services/Messageservice/Sender/Service/SenderTest.php`, `tests/Unit/Services/IM/Message/Attach/Blocks/{SimpleBlocks,CollectionBlocks}Test.php`.
20. Existing live suites: `tests/Integration/Services/Placement/Service/PlacementTest.php`, `tests/Integration/Services/IM/Placements/PlacementsTest.php`, `tests/Integration/Services/Messageservice/Sender/Service/SenderTest.php`, `tests/Integration/Services/AI/Engine/Service/EngineTest.php`, `tests/Integration/Services/IMBot/Bot/Service/BotTest.php`: exercise an object input in existing lifecycle cases while retaining legacy cases and cleanup.
21. `CHANGELOG.md`, after checks pass, under `## X.Y.Z Unreleased` / `### Changed`:
   `- Added Url, LocalizedString and workflow-code inputs across SDK services and credential factories while retaining legacy inputs; scheduled primitive removal for SDK 4.0 ([#533](https://github.com/bitrix24/b24phpsdk/issues/533))`
22. `Makefile`: existing test targets suffice; no new target required because no new service or separate suite is introduced.
23. `docs/open-api/openapi.json`: refreshed research baseline; inspect generated-only changes before selecting final commit scope.

## Deptrac compliance

Core resolver depends only on Core value objects and exceptions. WorkflowCodeResolver lives in Services and imports only workflow value objects. Services and Application may depend on Core. No new skip_violations entries. Public application interfaces stay unchanged to avoid breaking external implementers; their major-version migration is recorded in the guide.

## Execution and verification

For each numbered implementation group: write the explicit payload/edge regression first, run it and confirm the intended failure, implement conversion, rerun to green, then continue. Group order: baseline XML repair; resolvers; credentials/factory; Robot/Activity; callback services/facades; IM attachment builders; documentation/integration coverage.

- [x] Load issue and dependency; confirm v3; isolate checkout.
- [x] Refresh schema, install dependencies, inspect baseline and official API docs.
- [x] Obtain plan approval, including baseline XML prerequisite and explicit input scope.
- [x] Repair XML and rerun baseline unit tests; baseline passed with 1268 tests / 3496 assertions after restoring CatalogServiceBuilder::section().
- [x] Execute migration groups with TDD and exact primitive payload assertions.
- [x] Run phase 1 in this order (1371 tests / 3882 assertions; all four linters passed):

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

- [ ] Only after phase 1 passes, run relevant live suites:

```bash
make test-file path=tests/Integration/Services/Workflows/ValueObjectMigrationTest.php
make test-file path=tests/Integration/Services/Placement/Service/PlacementTest.php
make test-file path=tests/Integration/Services/Main/Service/EventTest.php
make test-file path=tests/Integration/Services/Messageservice/Sender/Service/SenderTest.php
make test-file path=tests/Integration/Services/AI/Engine/Service/EngineTest.php
make test-file path=tests/Integration/Services/IMBot/Bot/Service/BotTest.php
make test-file path=tests/Integration/Services/IM/Placements/PlacementsTest.php
make test-file path=tests/Integration/Services/IM/Message/Service/MessageAttachObjectApiTest.php
```

Use the configured application bridge for methods requiring application authorization. Missing credentials/scopes are blockers, never successful/skipped verification. Telephony conversion is fully mocked to avoid initiating paid calls. IM attachment behavior is checked by exact serialization unit tests and the existing message suite.

- [ ] Update changelog and guide; run `git diff --check`; inspect final scope and verify no secret configuration is staged.
- [ ] Re-run the required final quality gate, read the current PR template, commit issue-scoped files, push and create PR to `v3-dev`.
- [ ] Attach PR to this chat; poll every pushed revision to terminal CI status; report failures or confirmed green status. Do not merge automatically.

## Plan review

- Unambiguity: explicit migrated signatures, exceptions, payload invariants, file paths, and SDK 4.0 boundary are specified.
- Non-contradiction: primitive compatibility includes domain normalization and null/empty sentinels; strict absolute URL validation is documented as the intended validation change; nested language structures remain separate.
- No gaps: each acceptance criterion maps to resolver, credential, service, test or migration-guide work; baseline XML repair and live-auth gates are visible prerequisites, not hidden assumptions.

## Implementation findings

- After XML repair, the baseline ran 1268 tests and exposed a pre-existing truncated CatalogServiceBuilder::section() at line 285. Restored the missing constructor arguments/closing block following adjacent service accessors; the existing ServiceBuilderCacheTest is the failing regression. This minimal prerequisite is included to restore the required quality gate.

- Service unit tests were consolidated into `tests/Unit/Services/ValueObjectInputsTest.php` to share exact payload assertions without duplicating Core mock setup. Existing facade tests cover all five bind/unbind pairs. Public factory and credential tests remain separate.
- `Credentials::changeDomainUrl()` discarded the immutable Endpoints replacement. The new object/string regression exposed it and the method now saves the changed endpoints.
- Rector's PHP 8.4 deprecated-attribute conversion would introduce runtime warnings for pre-existing documentation-only deprecations. Disabled that conversion in `rector.php`; all other Rector rules remain active.
- Independent spec review found offline empty handlers and options precedence; fixed and regression-tested. Quality review subsequently checked forwarding through EventHandlerMetadata/EventManager.
- No application OAuth `tests/ApplicationBridge/auth.json` exists in either checkout. User was asked to authorize the bridge or provide an existing test environment. Application-authorized live suites remain unverified until then.

- Added an isolated Main Event Url lifecycle test and wired it into the existing suite; execute that file directly to avoid unrelated offline-queue mutation. The IM object payload live case now includes LinkBlock with Url.

## Verification checkpoint — 2026-09-29

- Final phase 1 after all code/test changes: CS Fixer, Rector, PHPStan, Deptrac and unit tests passed. Unit result: 1371 tests, 3882 assertions. `git diff --check` passed.
- Independent spec and quality reviews completed without outstanding findings before the final integration-test additions.
- Phase 2 used focused Make `test-file` targets for the changed services to avoid unrelated test fixture side effects. AI Engine: 3 tests / 6 assertions passed. IM Attach object API: 2 tests / 2 assertions passed, including an object Url in LinkBlock.
- Workflows (2 tests), Placement (4), Main Event (1), Sender (4), IMBot (4), and IM Placements (1) all failed in setup with `Application credentials for integration tests are not available`. No successful live coverage is claimed for those 16 tests. Authorize ApplicationBridge to unblock reruns.
- Refreshed OpenAPI snapshot includes broad upstream changes outside #533. Keep it as the local research baseline; exclude it from the future issue commit unless separately requested.
- Public migration guide and changelog are ready. The user explicitly requested opening the PR despite the disclosed OAuth blocker; this overrides the default requirement to wait for all live suites before publication. OAuth-dependent tests remain incomplete and must not be described as passing.

- Before PR publication, merged current `origin/v3-dev` (2f9b6ed9). Its baseline XML/Catalog repairs and Rector deprecated-attribute exclusion supersede those prerequisites here; the final PR diff only adds the Event test entry to PHPUnit configuration and contains no Catalog or Rector change. The changelog conflict was resolved by preserving all entries.
