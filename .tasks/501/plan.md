# Plan: Add support for landing.repowidget.* methods (issue #501)

## Context

This issue adds SDK support for the `landing.repowidget.*` REST API methods used to manage
Vibe widgets in Bitrix24. These methods are in the `landing` scope, which already has an
existing `LandingServiceBuilder` and several services (Site, Page, SysPage, Template, Block,
Repo, Demos, Role).

The `RepoWidget` service is a new sub-scope under `Landing`, analogous to the existing `Repo`
service but for Vibe widgets (not content blocks).

### REST methods

| Method                          | Returns              | Description                                |
|---------------------------------|----------------------|--------------------------------------------|
| `landing.repowidget.register`   | `int` (widget ID)    | Registers or updates a Vibe widget         |
| `landing.repowidget.unregister` | `bool`               | Removes a Vibe widget                      |
| `landing.repowidget.getlist`    | `array` of widgets   | Gets the list of widgets for the app       |
| `landing.repowidget.debug`      | `bool`               | Enables/disables debug mode for widgets    |

### Response shape for `landing.repowidget.getlist`

```json
{
    "result": [
        {
            "ID": "4",
            "XML_ID": "my_widget",
            "APP_CODE": "app.code",
            "ACTIVE": "Y",
            "NAME": "My widget",
            "DESCRIPTION": null,
            "SECTIONS": "widgets_company_life",
            "SITE_TEMPLATE_ID": null,
            "PREVIEW": "https://my-app.com/main_preview.jpg",
            "MANIFEST": { ... },
            "CONTENT": "{{desc}}",
            "CREATED_BY_ID": "1",
            "MODIFIED_BY_ID": "1",
            "DATE_CREATE": "10.10.2024 15:55:30",
            "DATE_MODIFY": "16.10.2024 16:12:57"
        }
    ]
}
```

---

## Files to Create

### 1. `src/Services/Landing/RepoWidget/Result/RepoWidgetItemResult.php`

`AbstractAnnotatedItem` subclass with `@property-read` annotations for all widget fields.
DATE_CREATE and DATE_MODIFY are typed as `?CarbonImmutable`.

### 2. `src/Services/Landing/RepoWidget/Result/RepoWidgetGetListResult.php`

`AbstractResult` subclass exposing `getRepoWidgetItems(): RepoWidgetItemResult[]`.

### 3. `src/Services/Landing/RepoWidget/Result/RepoWidgetDebugResult.php`

`AbstractResult` subclass exposing `isEnabled(): bool` for the debug mode response.

### 4. `src/Services/Landing/RepoWidget/Service/RepoWidget.php`

Service class with four public methods:
- `register(string $code, array $fields): AddedItemResult`
- `unregister(string $code): DeletedItemResult`
- `getList(array $select = [], array $filter = []): RepoWidgetGetListResult`
- `debug(bool $enable): RepoWidgetDebugResult`

### 5. `tests/Unit/Services/Landing/RepoWidget/Service/RepoWidgetTest.php`

Unit tests using `NullCore` to verify return types for all four methods.

### 6. `tests/Integration/Services/Landing/RepoWidget/Service/RepoWidgetTest.php`

Integration tests covering register, unregister, getList, and debug against a live portal.

---

## Files to Modify

### 1. `src/Services/Landing/LandingServiceBuilder.php`

Add `repoWidget(): RepoWidget\Service\RepoWidget` factory method.

### 2. `phpunit.xml.dist`

Add test suite:
```xml
<testsuite name="integration_tests_landing_repowidget">
    <directory>./tests/Integration/Services/Landing/RepoWidget/</directory>
</testsuite>
```

### 3. `Makefile`

Add make target:
```makefile
.PHONY: test-integration-landing-repowidget
test-integration-landing-repowidget:
    docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_landing_repowidget
```

### 4. `CHANGELOG.md`

Add entry under `## 3.3.0 – UNRELEASED` → `### Added`:
```
- Added service `Services\Landing\RepoWidget` with support for `landing.repowidget.*` methods ([#501](https://github.com/bitrix24/b24phpsdk/issues/501)):
    - `register` registers or updates a Vibe widget, returns widget ID
    - `unregister` removes a Vibe widget, returns boolean success flag
    - `getList` gets the list of widgets for the current app
    - `debug` enables or disables debug mode for all widgets of the current app
```

---

## Deptrac compliance

The new code lives in `src/Services/Landing/RepoWidget/` — same layer as other Landing
sub-scopes. It depends only on `Bitrix24\SDK\Core\*` and `Bitrix24\SDK\Services\AbstractService`,
which are all in the same or lower layer. No new deptrac violations expected.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-landing-repowidget
```

