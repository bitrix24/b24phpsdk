# Plan: Sync TaskItemSelectBuilder with refreshed OpenAPI task schema (issue #449)

## Context

Issue [#449](https://github.com/bitrix24/b24phpsdk/issues/449) tracks a unit-test
regression after refreshing the local OpenAPI snapshot with `make oa-schema-build`.
The `bitrix.tasks.taskdto` schema now includes the nested field `crmItems.id`,
but `Bitrix24\SDK\Services\Task\Service\TaskItemSelectBuilder` does not select it.

The existing unit coverage already expresses the required behavior:

- `Bitrix24\SDK\Tests\Unit\CustomAssertions\SelectBuilderAssertionsTest::testPassesWhenBuilderCoversAllSchemaFields`
- `Bitrix24\SDK\Tests\Unit\Services\Task\Service\TaskItemSelectBuilderTest::testCoversAllOpenApiSchemaFields`

The repository guidance and the failing assertion both point to the same fix path:
regenerate `TaskItemSelectBuilder` from the refreshed OpenAPI snapshot instead of
patching the builder by hand.

---

## Files to Modify

### 1. `src/Services/Task/Service/TaskItemSelectBuilder.php`

Regenerate the builder via:

```bash
docker compose run --rm php-cli php bin/console b24-dev:generate-select-builder bitrix.tasks.taskdto
```

Expected outcome:

- `crmItems.id` is included in the builder output
- any other generated adjustments remain schema-derived and deterministic

### 2. `CHANGELOG.md`

Add a `### Fixed` entry under `## 3.2.0 - UNRELEASED`:

```markdown
- Synced `TaskItemSelectBuilder` with the refreshed task OpenAPI schema so `crmItems.id` is covered in generated selections ([#449](https://github.com/bitrix24/b24phpsdk/issues/449))
```

---

## Verification

Run the narrow regression checks first:

```bash
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/Services/Task/Service/TaskItemSelectBuilderTest.php --display-warnings
docker compose run --rm php-cli vendor/bin/phpunit tests/Unit/CustomAssertions/SelectBuilderAssertionsTest.php --filter testPassesWhenBuilderCoversAllSchemaFields --display-warnings
```

Then run the full unit suite required by the issue:

```bash
make test-unit
```
