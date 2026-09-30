# Issue #659: Disk contract and verification evidence

Date: 2026-09-30. SDK target: 3.x (`v3-dev`), REST methods: legacy v1.
Base commit: `2b491902449c4dbf6bc198c96c945632d7f349ce`.

## Public contracts

Official sources retrieved through Bitrix24 documentation MCP and English documentation:
- https://apidocs.bitrix24.com/api-reference/disk/file/disk-file-search.html
- https://apidocs.bitrix24.com/api-reference/disk/folder/disk-folder-share-to-user.html

`disk.file.search`: scope `disk`, readable objects only. Confirmed with a newly created personal-drive folder, nested folder and text file. The response is a flat array containing both `TYPE=file` and `TYPE=folder`. Search-specific links and kind-specific fields are represented by a dedicated annotated item. A unique missing query returned an empty array. File/folder filtering, combined storage/folder scoping and offset 1 passed live tests. Search indexing is bounded by a 30-second retry window.

`disk.folder.shareToUser`: documented scope `disk`, requires the caller to have Share permission and forbids granting a higher level than the caller holds. Not marked deprecated. The documented scalar boolean fits `Core\Result\UpdatedItemResult`; unit tests cover true/false, exact request names and exception propagation. No live success or recipient-side authorization claim: `BITRIX24_DISK_TEST_RECIPIENT_ID` was not supplied, so its live test skipped before creating objects. It requires an explicitly configured active recipient different from the fixture owner, grants only read access and deletes the disposable parent tree in `finally`.

All created research/search fixtures were removed through `disk.folder.deletetree` and returned true. Tests fail when cleanup reports false. Setup-failure cleanup preserves the original error as the exception cause.

## Unresolved methods

`disk.file.listallowedoperations` and `disk.folder.listallowedoperations` remain excluded from SDK public wrappers under the approved plan. Official MCP exact-name/broad searches and official-domain web searches did not resolve public documentation. Neither absence of documentation nor catalog presence proves deprecation, internal status or supported integration availability.

Controlled probes on our disposable objects, with the current user ID, both returned an array of operation names: `disk_add`, `disk_create_wf`, `disk_delete`, `disk_destroy`, `disk_edit`, `disk_read`, `disk_restore`, `disk_rights`, `disk_settings`, `disk_sharing`, `disk_start_bp`. Invalid-ID calls established a required `userId` and returned `ERROR_NOT_FOUND` once supplied. These observations do not establish cross-user permissions, stable public guarantees or deprecation status. Public support evidence is still required before exposing these APIs.

## Generator

Attempted `php bin/console b24-dev:result-item-generator disk.file.search --stage=all` in a disposable Docker staging directory, before writing the new item. Failed with `Unable to determine the current git branch`: the PHP image does not contain git; host PHP is absent. An attempted staging git initialization reported `git: not found`. No installed packages or generator source were changed to work around this.

Source inspection identified further constraints: only feature/bugfix branch names are parsed; the documentation URL resolver discovers existing SDK metadata, the default sample lacks required QUERY, and the inferred output would overwrite the existing file-only FileItemResult. The approved dedicated mixed item was written manually after recording these limitations in the plan. Existing FileItemResult remains unchanged.

## Measured coverage

Measured using repository Make targets, not hand-counting implementations.

| Metric | Before | After |
| --- | ---: | ---: |
| Live legacy REST v1 portal methods | 1160 | 1160 |
| Covered legacy methods | 760 | 762 |
| Uncovered legacy methods | 400 | 398 |
| Legacy coverage | 65.52% | 65.69% |
| SDK-only legacy inventory | 441 | 441 |
| Missing Disk methods | 4 | 2 |

`make -s sdk-coverage-v1-show` ran interactively in the isolated worktree. The final Disk missing-method list contains exactly the two allowed-operations methods. Disk SDK-only inventory is zero; endpoint casing is normalized by the coverage utility.

`make -s sdk-coverage-v3-show`: 314 OpenAPI methods, 98 covered, 216 uncovered, 4 SDK-only, 31.21%. Disk REST v3: 18 methods, zero covered. The legacy additions are not presented as REST v3 implementations. The refreshed OpenAPI snapshot has no entry for any of the four legacy candidates.

## Verification

- Fresh dependencies: `make -s composer-install` succeeded; PHP 8.4.21 / PHPUnit 12.5.37.
- `make -s oa-schema-build` succeeded after copying existing ignored local test settings into the isolated worktree. The first attempt had an empty webhook; no source change was required.
- Baseline `make test-unit`: 1857 tests / 6029 assertions, 11 existing deprecations, exit 0.
- TDD red: 7 new unit scenarios failed because the methods/results did not exist. Green: 7 tests / 45 assertions.
- `make lint-cs-fixer`: passed first run.
- `make lint-rector`: passed, including after test adjustments.
- `make lint-phpstan`: passed, including after test adjustments.
- `make lint-deptrac`: zero violations.
- `make test-unit`: 1864 tests / 6074 assertions, same 11 existing deprecations, exit 0.
- Targeted integration command below: 5 tests / 70 assertions, 4 passed, 1 explicitly skipped (sharing recipient missing). Initially two annotation tests failed because the shared helper requires equality; fixed the mixed-entity test to use actual field unions, then reran successfully.
- No broad legacy Disk CRUD suite was run: this task uses only reviewed disposable fixtures.
- Independent read-only review: no production blockers; cleanup diagnostic suggestion implemented.

```bash
make test-file path='tests/Integration/Services/Disk/File/Service/FileSearchTest.php tests/Integration/Services/Disk/File/Result/FileSearchItemResultAnnotationsTest.php tests/Integration/Services/Disk/Folder/Service/FolderShareToUserTest.php'
```

## Remaining validation

Configure `BITRIX24_DISK_TEST_RECIPIENT_ID` locally and rerun the sharing test. Verify recipient access separately with appropriate recipient credentials. Obtain a public contract for both allowed-operation endpoints before adding SDK wrappers. No release, merge or full four-endpoint support is claimed.
