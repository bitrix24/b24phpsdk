# Plan: Add missing legacy REST methods in disk scope (issue #659)

Status: implemented and locally verified; live sharing awaits an explicitly configured recipient.
Target: SDK 3.x, `v3-dev`, explicitly selected by the owner.
Branch: `codex/659-disk-methods`.
Base: `origin/v3-dev` at `2b491902449c4dbf6bc198c96c945632d7f349ce`.

## Context and evidence

Issue: https://github.com/bitrix24/b24phpsdk/issues/659

These are legacy REST v1 endpoints in SDK 3.x; the SDK release line does not change the REST version.
`make oa-schema-build` succeeded before research. None of the four candidates appears in the resulting OpenAPI snapshot; legacy documentation and controlled live requests are needed.

Research on 2026-09-30:

| Candidate | Evidence | Decision |
| --- | --- | --- |
| `disk.file.search` | Official MCP and English documentation found; live request with a unique nonexistent query and `TYPE=all` returned `[]`. | Implement; successful nonempty fixtures remain to be verified. |
| `disk.folder.shareToUser` | Official MCP resolves the camel-case name; English documentation found. | Implement after disposable-fixture verification; no live mutation performed during planning. |
| `disk.file.listallowedoperations` | Catalog entry; official MCP lookup and official-domain searches found no contract. Read-only invalid-ID probe requires `userId`, then returns `ERROR_NOT_FOUND`. | Exclude from public SDK additions pending public contract evidence. Not proven internal, unsupported or deprecated. |
| `disk.folder.listallowedoperations` | Same evidence boundary as file operations. | Same exclusion; retain as an unresolved gap. |

Sources:
- https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-search.html
- https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-share-to-user.html

Both documented endpoints require the `disk` scope. Neither documentation page marks the method deprecated. Search is permission-filtered. Record the actual permission behavior for sharing from the official page and the live disposable fixture before publishing support claims.

The existing coverage Make target reports exactly these four missing Disk endpoints and zero SDK-only Disk methods. Its global totals changed from 747/1160 (64.40%) to 760/1160 (65.52%) between runs in the shared primary checkout; do not attribute that difference to this task. Capture the authoritative before/after pair in the isolated worktree. The first noninteractive run aborted at its menu; the subsequent interactive run completed successfully.

## Design and alternatives

Recommended: extend the existing File and Folder services for the two documented contracts. Return a dedicated mixed search-item type and reuse `UpdatedItemResult` for sharing. Record the two unresolved methods without speculative wrappers.

Alternatives considered:
1. Implement all four from observed responses: rejected because catalog membership and error messages do not establish a public supported contract.
2. Block the entire issue until all four are documented: unnecessary for the two documented methods; keep unresolved items explicit instead.

The existing `FileItemResult` is file-only and extends `AbstractItem`; `FolderChildrenResult` labels every item as `FolderItemResult`. Reusing either for mixed search results would misrepresent the contract. A new search item avoids changing existing public result semantics. `FolderOperationResult::isSuccess()` expects an `ID` field and cannot represent the documented boolean sharing response. `UpdatedItemResult` reads the core-normalized scalar boolean at index zero and fits that contract. Verify both true and false in unit tests. `AddedItemResult` is not applicable.

## Files to create

All PHP files use strict types and repository headers/import conventions.

### `src/Services/Disk/File/Result/FileSearchResult.php`
Namespace `Bitrix24\SDK\Services\Disk\File\Result`.
Extends `Core\Result\AbstractResult`.
Public `items(): array` with `@return FileSearchItemResult[]`, mapping the flat `result` array. Empty results return `[]`. Retain pagination through the inherited core response (`getResponseData()->getPagination()->getNextItem()`).

### `src/Services/Disk/File/Result/FileSearchItemResult.php`
Same namespace; extends `Core\Result\AbstractAnnotatedItem` and imports `Carbon\CarbonImmutable`.
Use the documented union of file/folder fields, including search-specific links and folder identity, with nullable annotations for fields omitted on one object kind. Keep `TYPE` as the discriminator. Confirm scalar/date casts and all actual keys against controlled live results and both field metadata methods. Do not add an OpenAPI entity attribute for an entity absent from the schema.

Generator-first step, before writing the item:
```bash
php bin/console b24-dev:result-item-generator disk.file.search --stage=all
```
Inspect generated artifacts before adopting them. Current generator inspection reveals three constraints: the default target is the existing `FileItemResult.php`, sample parameters are empty (search needs QUERY), and the default documentation object selector is `result-item`. Run the generator in a disposable staging copy under the task directory to avoid overwriting the released item. Record the exact outcome here. If it cannot generate this mixed item, document the concrete failure before creating it manually; do not broaden this issue into generator development.

### Unit tests
- `tests/Unit/Services/Disk/File/Service/FileTest.php`: request method and casing, default/explicit arguments, empty/nonempty file/folder/mixed responses, paging offset, core exceptions.
- `tests/Unit/Services/Disk/File/Result/FileSearchResultTest.php`: item classes and typed access, omitted optional fields, dates, nulls, empty results, preserved pagination.
- `tests/Unit/Services/Disk/Folder/Service/FolderTest.php`: share request mapping, true/false normalized result, exception propagation.

Use `PHPUnit\Framework\TestCase`, the existing core mocks, and explicit `CoversClass` attributes. Run each new service test red before implementation.

### Integration tests
- `tests/Integration/Services/Disk/File/Service/FileSearchTest.php`: disposable folder and uploaded text file with unique searchable names; file, folder, mixed and scoped searches; empty results. Use bounded polling for indexing, fail with a clear timeout, and always clean up in `finally`. Do not silently pass an empty result for a successful-search scenario.
- `tests/Integration/Services/Disk/Folder/Service/FolderShareToUserTest.php`: share only a newly created private disposable folder to an explicitly configured active test recipient. Verify success and, where test credentials support it, recipient access. Report absence of recipient credentials separately from a passing access verification. Clean up in `finally`; never grant access to existing folders or create/invite users.
- `tests/Integration/Services/Disk/File/Result/FileSearchItemResultAnnotationsTest.php`: separate shared-assertion checks named `testAllSystemFieldsAnnotated...` and `testAllSystemFieldsHasValidTypeAnnotation...` against File and Folder `getFields()->getFieldsDescription()`, normalize metadata keys as existing Disk tests do. Add actual raw-search field completeness and magic-getter casting checks against a nonempty fixture.

## Files to modify

### `src/Services/Disk/File/Service/File.php`
Import `FileSearchResult` and add:
```php
public function search(string $query, string $type = 'file', array $filter = [], int $start = 0): FileSearchResult
```
Document `$type` as `file|folder|all` and `$filter` as optional integer `STORAGE_ID`/`FOLDER_ID` keys. Map to `QUERY`, `TYPE`, `FILTER` and lowercase `start`. Omit an empty FILTER so it is not encoded as a JSON list. Leave API validation and errors to the core; do not invent client-side truncation or normalization. Add `ApiEndpointMetadata` with canonical lowercase endpoint and the English documentation URL.

### `src/Services/Disk/Folder/Service/Folder.php`
Import `Core\Result\UpdatedItemResult` and add:
```php
public function shareToUser(int $id, int $userId, string $taskName): UpdatedItemResult
```
Map `id`, `userId`, `taskName` exactly. Document the four supported `disk_access_*` values. Register canonical lowercase `disk.folder.sharetouser` metadata and English documentation URL. Keep ordinary SDK exception propagation.

### `phpunit.xml.dist` and `Makefile`
Existing Disk/file suites already cover the proposed files. Add the missing focused folder suite and target:
```xml
<testsuite name="integration_tests_disk_folder">
    <directory>./tests/Integration/Services/Disk/Folder/</directory>
</testsuite>
```
```make
.PHONY: test-integration-disk-folder
test-integration-disk-folder:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_disk_folder
```
Use this suite or an explicit filter to limit live mutations to disposable fixtures for this task.

### `CHANGELOG.md`
Under the existing top-level Unreleased / Added section:
`- Added Disk file/folder search and folder sharing to users; undocumented allowed-operation endpoints remain unresolved ([#659](https://github.com/bitrix24/b24phpsdk/issues/659)).`
Do not rename the repository's existing `## Unreleased` heading. No agent or skill configuration changes are planned.

### Task evidence
Add `.tasks/659/research.md` with sanitized live findings, generator outcome, public-contract exclusions, before/after coverage, test commands and limitations. Do not commit credentials, raw portal URLs containing webhook secrets, download links, or user data.

## Deptrac compliance

Dependencies remain Services -> Core/Attributes and existing external Carbon/PSR contracts. Tests depend on the existing factory/custom assertions. No new cross-scope dependency or skipped violation is necessary. Existing DiskServiceBuilder accessors already expose both services; no builder edit is needed.

## Execution and verification

1. Install dependencies in the isolated worktree, refresh schema there with `make -s oa-schema-build` while suppressing secret-bearing command echoes, and capture clean baseline unit results and coverage.
2. Verify public contracts on disposable fixtures. Stop implementing any endpoint whose observed response contradicts its documentation; record the evidence and resolve the discrepancy first.
3. Write failing unit tests, run generator in staging, implement the two wrappers/results, then add live tests and changelog.
4. Run the light gates sequentially; fix failures before starting integrations:
```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```
5. Run new targeted Disk integration tests and annotation checks. Run broader Disk suites only after reviewing their existing fixture safety. Report skipped/unavailable scenarios explicitly.
6. Re-run `make -s sdk-coverage-v1-show` interactively (missing methods -> disk -> exit) on the same isolated checkout, and `make -s sdk-coverage-v3-show`. Expect two resolved Disk gaps; the two unverified allowed-operation methods remain. Legacy additions must not be misreported as REST v3 coverage.
7. Run `git diff --check`, inspect tracked/untracked files, review the complete diff and evidence. Follow the repository PR template; any push to a PR requires polling CI to terminal status. Do not merge or claim a release/live authorization scenario without its evidence.

## Plan review

- Unambiguity: selected SDK branch, exact API mappings, result responsibilities, test locations and exclusion policy are explicit.
- Non-contradiction: REST v1 remains on the owner-selected SDK 3.x branch; mixed records use a dedicated result and boolean sharing uses the compatible core wrapper.
- No gaps: every candidate has an evidence-backed disposition or explicit unresolved status; generators, metadata, fixtures, annotation tests, changelog, coverage and quality gates are included.

## Generator execution evidence

The required CLI was attempted before manual result-item creation in a disposable Docker staging directory. It failed with `Unable to determine the current git branch`; the repository PHP Docker image has no `git` executable. Host PHP is unavailable. Source inspection additionally confirms that the URL resolver requires an existing endpoint metadata attribute, default sample parameters omit required QUERY, and the target would overwrite the existing file-only FileItemResult. Manual creation of the dedicated mixed FileSearchItemResult is therefore used without changing generator infrastructure.

## Implementation adjustments

- Added `tests/Integration/Services/Disk/TemporaryDiskFixture.php` to own disposable personal-drive objects and checked cleanup, shared by the new integration tests.
- Added optional `BITRIX24_DISK_TEST_RECIPIENT_ID` to `tests/.env`; no user is selected implicitly.
- The shared completeness assertion compares exact field sets. The mixed search-item tests therefore compare the union of live file/folder metadata and observed search fields, and separately the union of observed mixed-search fields. Type validation still checks both metadata contracts independently.
- No recipient credentials were supplied: the integration scenario for assigning permissions is explicitly skipped, and recipient-side authorization is unverified. This does not weaken the request/boolean/error unit tests.
- See `research.md` for measured results and generator limitations. The two public wrappers are implemented; the undocumented operations remain unresolved, not classified as deprecated/internal based on missing documentation alone.
