# Plan: Complete legacy log scope coverage in SDK v3 (#643)

## Context and design
Implement seven confirmed, non-deprecated legacy methods on v3-dev. Extend existing BlogPost rather than replacing its public add API; add BlogComment service and cached builder accessor. Preserve flat request and response envelopes. get returns a paginated list even for POST_ID. Use CarbonImmutable for newly exposed date arguments. Share accepts explicit recipients; live tests never share with others. Existing unsafe all-user integration fixtures will become current-user-only with cleanup.

Official MCP method documentation verified log.blogcomment.add/delete and log.blogpost.delete/get/getusers.important/share/update. English links use https://apidocs.bitrix24.com/api-reference/log/. log.comment.delete has no official method details or matching documentation in the official source tree; exclude it as undocumented, without guessing its parameters or aliasing it. BlogPost has no fields endpoint: dedicated AnnotationsTest compares actual response keys and annotated casting using shared assertions.

## Files to create
- BlogComment/Service/BlogComment.php: add(int $postId, string $text, ?array $files = null, ?int $userId = null): AddedItemResult; delete(int $commentId, ?int $userId = null): DeletedItemResult.
- BlogPost/Result/BlogPostItemResult.php extends AbstractAnnotatedItem, documented system and standard UF object fields.
- BlogPost/Result/BlogPostsResult.php: getBlogPosts(): array, retaining pagination.
- BlogPost/Result/BlogPostUpdateResult.php: isSuccess() and getId() for integer result.
- BlogPost/Result/ImportantUsersResult.php: getUserIds(): array.
- Unit parameter/result tests for seven methods, empty results and builder caching.
- Dedicated BlogPostItemResultAnnotationsTest and private CRUD integration tests.

## Files to modify
BlogPost service: get, update, delete, share, getUsersImportant; retain add and add getId to its result.
LogServiceBuilder: blogComment accessor. Existing scope suite and make test-integration-scope-log already cover all Log files; no duplicate suite required.
Add Log to lint source/integration configurations where absent. CHANGELOG Added entry links #643.

## Generator
Run docker compose run --rm php-cli php bin/console b24-dev:result-item-generator log.blogpost.get --stage=all before ItemResult implementation; document any unsupported generation contract before manual fallback.

## Deptrac compliance
Services depend on core contracts, result wrappers and attributes; result items only depend on core annotated results and Carbon. No skip violations.

## Verification
TDD RED before production implementations, GREEN via make test-file path=tests/Unit/Services/Log.
Ordered gates: make lint-cs-fixer; make lint-rector; make lint-phpstan; make lint-deptrac; make test-unit; make test-integration-scope-log.
Before/after coverage via make -s sdk-coverage-v1-show; document remaining undocumented endpoint.
Commit only; parent handles review, push, PR, terminal CI.

## Plan review
- Unambiguity: public methods, payload mapping and result shape specified.
- Non-contradiction: existing add remains compatible and suite reused.
- No gaps: docs dispositions, generator, TDD, safe integration, coverage and changelog included.
User explicitly authorized autonomous implementation without an additional plan approval.

Generator attempt failed: Unable to determine the current git branch even with the main .git mounted read-only. Manual ItemResult implementation is required; fields are taken from official get documentation.

Live contract findings: CRUD/share/comment lifecycle passed. Annotation checks exposed nullable HAS_PROPS and omission of FILES on attachment-free posts. Keep FILES nullable and include this documented optional field in the completeness assertion; unknown live fields still fail.

## Completed verification and coverage
- make oa-schema-build passed.
- Seven new supported endpoints, no officially deprecated endpoints. Undocumented log.comment.delete remains excluded.
- Legacy Make CLI baseline: 731/1160 overall (scope baseline from issue 1/9). After implementation: 738/1160 overall, log 8/9 (88.89%), one undocumented method remaining.
- Ordered light gates passed; unit suite 1603 tests, 5080 assertions, unchanged 11 baseline deprecations before review corrections.
- Live integration suite passed 3 tests, 34 assertions, including self-only sharing and cleanup.
- Formatter and Rector initially required standard formatting, naming and Override adjustments. PHPStan and Deptrac passed first run.
- Review corrections: nullable DATE_PUBLISH and optional UF objects; completeness checks fixed system fields while accepting portal-specific UF names. Required final gates rerun below.

## Final status
All implementation steps complete. Independent specification and quality reviews passed after O1/O2 corrections.
Final ordered quality gates: lint-cs-fixer passed (0 files needing changes), lint-rector passed, lint-phpstan passed, lint-deptrac passed (0 violations/errors), test-unit passed (1604 tests, 5084 assertions, unchanged 11 deprecations), test-integration-scope-log passed (3 tests, 34 assertions). CHANGELOG updated.
All new PHP files attribute © Maksim Mesilov <mesilov.maxim@gmail.com>.
Implementation committed for parent review/delivery; no push or PR performed by this subtask. Keep issue #643 open for the undocumented method disposition unless maintainers accept the exclusion.

## Review follow-up: reuse core operation results
User approved making BlogPostAddResult extend Core\Result\AddedItemResult, removing duplicated getId while preserving the existing return class and isSuccess contract. Extend the existing add-result regression to assert core result/interface compatibility, numeric-string ID casting and isSuccess. Add a reusable maintainer decision rule distinguishing core operation wrappers, compatibility subclasses, custom envelopes and annotated entity items; add the required skill CHANGELOG entry. Run the ordered gates and relevant integration before pushing PR651; wait for current-head CI.

Follow-up verified: regression failed on missing AddedItemResult compatibility before the change, then passed with inherited numeric-string ID casting/interface and preserved isSuccess. Maintainer decision table independently checked against five cases (plain ID, compatible legacy class, UUID/nested envelope, scalar list, annotated entity). All ordered gates passed first run; units1604/5087 with11existing deprecations, integration3/34. Skill and code CHANGELOG entries included.
