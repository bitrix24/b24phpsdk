# Plan: Add batch support to landing scope (issue #616)

## Context

Batch calls must be added for the following `landing` REST methods:

- `landing.site.add|update|delete|getList`
- `landing.landing.add|update|delete|getList`
- `landing.block.getlist`
- `landing.demos.register|unregister|getList`
- `landing.repo.register|unregister|getList`
- `landing.repowidget.register|unregister|getlist`
- `landing.role.getList`
- `landing.syspage.set`
- `landing.template.getlist`

API facts (verified via Bitrix24 REST docs MCP and live webhook calls):

- `*.getList` methods of the landing scope wrap `select`, `filter`, `order` into a `params` object and
  support ORM-level `params.limit` / `params.offset`. The response has **no** `total` / `next`
  pagination keys, so the core `Batch::getTraversableList()` (ID filter + `total`) cannot be used.
- `landing.block.getlist` has no select/filter/limit; it takes a page id `lid` and `params` (`edit_mode`, `deleted`, `get_content`).
- `landing.role.getList` has no selection parameters; the only argument is optional `scope`.
- `landing.site.delete` / `landing.site.update` use `id`; `landing.landing.delete` / `landing.landing.update` use `lid`.
- `*.unregister` methods use string `code`.
- `landing.demos.register` returns an array of template ids.
- `landing.syspage.set` takes `id` (site), `type`, optional `lid` (page) and returns boolean.

## Design

### `src/Services/Landing/Batch.php` (extends `Core\Batch`)

- `getTraversableListByOffset(string $apiMethod, array $params = [], ?int $limit = null)`:
  offset pagination inside `params` (`limit` = 50, `offset` = n * 50). The first page is requested directly;
  if it is full, the next pages are requested with batch packets (up to 50 commands per packet).
  Iteration stops on the first page that contains fewer than 50 elements or when `$limit` is reached.
- `getTraversableListByCommands(string $apiMethod, array $commandsParameters)`: runs one list command per
  parameter set and yields every element of every result (used by `landing.block.getlist` and `landing.role.getList`).
- `processEntityItems(string $apiMethod, array $entityItems)`: runs one command per parameter set and yields raw `ResponseData`
  (used by `landing.syspage.set`).
- `deleteEntityItems()` override: `lid` for `landing.landing.delete`, string `code` for `*.unregister`, `id` otherwise.
- `updateEntityItems()` override: `lid` for `landing.landing.update`, `id` otherwise; `fields` key required.

### `Service\Batch` per entity

`Site`, `Page`, `Block`, `Demos`, `Repo`, `RepoWidget`, `Role`, `SysPage`, `Template` get
`src/Services/Landing/<Entity>/Service/Batch.php` with `ApiBatchServiceMetadata` / `ApiBatchMethodMetadata` attributes.
Each primary service receives `public Batch $batch` as the first constructor argument (SDK convention),
`LandingServiceBuilder` creates a dedicated `Landing\Batch` instance per service.

New result: `Demos\Result\DemoRegisteredBatchResult::getIds(): int[]`.

## Tests

- `tests/Unit/Services/Landing/BatchTest.php` - offset pagination, command parameters, key mapping.
- `tests/Unit/Services/Landing/RepoWidget/Service/RepoWidgetTest.php` - constructor update.
- `tests/Integration/Services/Landing/<Entity>/Service/BatchTest.php` for every entity.

Suites/linters already cover `src/Services/Landing`, `tests/Integration/Services/Landing` and `tests/Unit`.

## Files to Modify

- `src/Services/Landing/LandingServiceBuilder.php`
- `src/Services/Landing/*/Service/*.php` (constructor)
- `CHANGELOG.md` (`## Unreleased` -> `### Added`, `### Changed`)

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-scope-landing
```
