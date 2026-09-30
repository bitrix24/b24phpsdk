# Release 3.7.0 verification

Measured on 2026-09-30 (UTC), candidate based on origin/v3-dev 8be2874e with release metadata changes for #664.

| Check | Result |
|---|---|
| OpenAPI snapshot | Passed; no tracked schema change |
| REST v3 coverage | 105 / 314, 209 uncovered, 33.44% |
| Legacy REST v1 coverage | 762 / 1160, 398 uncovered, 65.69%; configured portal baseline |
| CS Fixer | Passed, zero fixable files |
| Rector | Passed |
| PHPStan | Passed |
| Deptrac | Passed; zero violations, warnings or errors; 22 existing skipped violations |
| Unit suite | Passed: 1967 tests, 6504 assertions; 11 deprecation notices |
| Main user-history integration | Passed: 26 tests, 167 assertions |
| Main event-log integration | Passed: 17 tests, 87 assertions |
| CoreTest integration smoke | Passed: 3 tests, 3 assertions |
| ApiClientDefaultImplementationTest integration smoke | Passed: 1 test, 1 assertion |
| Composer validate --strict | Passed; container cannot infer root version from host worktree metadata |
| Dependency licenses | Passed |
| Changelog structure and arithmetic | Passed: one final API coverage subsection, both baselines and valid totals/percentages |
| Historical changelog preservation | Byte-identical from 3.6.0 onward |
| Milestone inclusion | All 18 merged PR merge commits are ancestors of the candidate; 12 closed issues mapped |
| git diff --check | Passed |

All local commands exited 0 on their first run. Integration scope is 47 tests / 258 assertions with no skips; this is a focused release smoke check, not the full SDK integration suite. No fresh claim is made for the previously unverified mutation scenarios or historical Core full-suite failures in milestone-audit.md.

#632 remains unresolved and blocks release readiness pending maintainer disposition. The requested PR is prepared as a draft; no tag or release is published. Remote PR/CI results are recorded in issue #664 after delivery.
