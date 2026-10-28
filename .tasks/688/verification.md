# Verification: note.file.list (#688)

Date: 2026-10-28. Branch: `feature/688-note-file-list`; base: `v3-dev`.

## Environment

The published PHP container was 8.4.26 and fresh Composer resolution rejected it through roave/security-advisories. Rebuilding the existing project Dockerfile with `--pull` produced PHP 8.4.27; fresh Composer installation then passed without manifest changes or ignored platform requirements.

Local checks used temporary Compose override `/private/tmp/b24phpsdk-688-fresh-compose.yaml`, selecting image `b24phpsdk-688:php-cli` and bridge networking. The worktree has its own vendor directory. Test credentials were reused privately through ignored `tests/.env.local`; no credentials are committed.

## Results

| Check | Result |
| --- | --- |
| `make -s oa-schema-build` | Passed; current portal snapshot contains note.file.list |
| New Note unit tests before implementation | Failed for missing new classes/methods; RED captured |
| `make lint-cs-fixer` | Passed |
| Scoped Note PHP-CS-Fixer with path override | Passed; standard finder excludes this scope |
| `make lint-rector` | Passed after applying suggestions in new tests |
| Scoped Rector on new/changed integration files | Passed |
| `make lint-phpstan` | Passed |
| Scoped PHPStan on Note File integration tests | Passed; unmatched unrelated ignore patterns disabled only in an external temporary config |
| `make lint-deptrac` | Passed; no skips added |
| `make test-unit` | Passed: 1,991 tests, 6,623 assertions; 11 pre-existing PHPUnit deprecations |
| `make lint-allowed-licenses` | Passed |
| `make test-integration-note-file` | Passed: 14 tests, 84 assertions |
| `make -s sdk-coverage-v3-show-uncovered` | Passed; note.file.list no longer uncovered |
| `git diff --check` | Passed |
| Independent spec review | Approved |
| Independent code-quality review, including live fixes | Approved |

## Live contracts verified

- Empty document: no items and null cursor.
- Two uploaded files: page size one, unchanged nextCursor continuation, ascending IDs without duplicates, bounded termination.
- File metadata and immutable attachment dates.
- Accessible archived and recycled document attachments.
- Missing-document v3 error propagation through the existing core exception model.
- Dedicated metadata facade agrees with released field accessors.
- Every system file field is annotated and checked with shared assertions; actual getter casts are checked independently on get/list responses.

The portal identifies createdAt as object in field metadata, while OpenAPI and list values identify a date-time string. The annotation test normalizes only that local descriptor to datetime after asserting its observed type, following the existing EventLog convention. Production metadata is returned unchanged. Get omits createdAt; it remains a nullable property, and its raw fields/values are checked against the strictly validated list record.

Live descriptors introduced editableGroups; its nullable array annotation was added after a failing existing live completeness test. Both mandatory generator attempts failed because the container lacks Git and its branch fallback does not support linked-worktree .git files; the reason was recorded before manual changes.

No separately configured restricted-user identity was available. Permission denial is not claimed as a live second-identity test; core exception propagation is unit-tested, and missing-document behavior is live-tested.

## Delivery

Feature PR targets v3-dev. No merge is authorized. PR: https://github.com/bitrix24/b24phpsdk/pull/689. Initial push jobs failed during dependency installation on published PHP 8.4.26. The quality workflows were updated to build from the current Dockerfile with fresh base images; final CI status is recorded in the task response after the corrected head completes checks.
