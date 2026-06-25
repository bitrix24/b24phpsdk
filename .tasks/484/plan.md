# Plan: Add support for timeman.* base methods (issue #484)

## Context

The Bitrix24 REST API exposes 5 methods in the `timeman` scope for managing workday tracking:

| Method | Description |
|---|---|
| `timeman.open` | Opens a new workday or continues after pause/close |
| `timeman.pause` | Pauses the current workday |
| `timeman.close` | Closes the current workday |
| `timeman.status` | Gets the current workday status |
| `timeman.settings` | Gets user's work time settings |

**Scope**: `timeman`  
**Author**: Dmitriy Ignatenko <algonexys@gmail.com>  
**Issue**: https://github.com/bitrix24/b24phpsdk/issues/484

### API Response Structures

All four workday methods (`open`, `pause`, `close`, `status`) return the same flat object
directly in `result` (not nested in `result.item`):

```json
{
    "result": {
        "STATUS": "OPENED",
        "TIME_START": "2025-03-27T08:00:01+02:00",
        "TIME_FINISH": null,
        "DURATION": "00:00:00",
        "TIME_LEAKS": "00:00:00",
        "ACTIVE": false,
        "IP_OPEN": "",
        "IP_CLOSE": null,
        "LAT_OPEN": 53.548841,
        "LON_OPEN": 9.987274,
        "LAT_CLOSE": 0,
        "LON_CLOSE": 0,
        "TZ_OFFSET": 7200
    }
}
```

`TIME_FINISH_DEFAULT` is an optional field present only for EXPIRED status.

`timeman.settings` returns:
```json
{
    "result": {
        "UF_TIMEMAN": true,
        "UF_TM_FREE": false,
        "UF_TM_MAX_START": "09:15:00",
        "UF_TM_MIN_FINISH": "17:45:00",
        "UF_TM_MIN_DURATION": "08:00:00",
        "UF_TM_ALLOWED_DELTA": "00:15:00",
        "ADMIN": true
    }
}
```

`ADMIN` is only returned for the current user (not when querying by `USER_ID`).

### Batch methods

All timeman.* methods have `ERROR_BATCH_METHOD_NOT_ALLOWED` error defined - no Batch class needed.

### DATE/TIME fields

- `TIME_START`, `TIME_FINISH`, `TIME_FINISH_DEFAULT` → `CarbonImmutable` (nullable where applicable)
- `TIME_START` is always present (workday always has a start time)
- `TIME_FINISH` and `TIME_FINISH_DEFAULT` are nullable

---

## Files to Create

### 1. `src/Services/Timeman/Result/WorkdayItemResult.php`

```php
namespace Bitrix24\SDK\Services\Timeman\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;
use Carbon\CarbonImmutable;

/**
 * @property-read string $STATUS
 * @property-read CarbonImmutable $TIME_START
 * @property-read CarbonImmutable|null $TIME_FINISH
 * @property-read string $DURATION
 * @property-read string $TIME_LEAKS
 * @property-read bool $ACTIVE
 * @property-read string $IP_OPEN
 * @property-read string|null $IP_CLOSE
 * @property-read float $LAT_OPEN
 * @property-read float $LON_OPEN
 * @property-read float $LAT_CLOSE
 * @property-read float $LON_CLOSE
 * @property-read int $TZ_OFFSET
 * @property-read CarbonImmutable|null $TIME_FINISH_DEFAULT
 */
class WorkdayItemResult extends AbstractAnnotatedItem {}
```

### 2. `src/Services/Timeman/Result/WorkdayResult.php`

```php
namespace Bitrix24\SDK\Services\Timeman\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class WorkdayResult extends AbstractResult
{
    public function getWorkday(): WorkdayItemResult
    {
        return new WorkdayItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
```

### 3. `src/Services/Timeman/Result/TimemanSettingsItemResult.php`

```php
namespace Bitrix24\SDK\Services\Timeman\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read bool $UF_TIMEMAN
 * @property-read bool $UF_TM_FREE
 * @property-read string $UF_TM_MAX_START
 * @property-read string $UF_TM_MIN_FINISH
 * @property-read string $UF_TM_MIN_DURATION
 * @property-read string $UF_TM_ALLOWED_DELTA
 * @property-read bool|null $ADMIN
 */
class TimemanSettingsItemResult extends AbstractAnnotatedItem {}
```

### 4. `src/Services/Timeman/Result/TimemanSettingsResult.php`

```php
namespace Bitrix24\SDK\Services\Timeman\Result;

use Bitrix24\SDK\Core\Result\AbstractResult;

class TimemanSettingsResult extends AbstractResult
{
    public function getSettings(): TimemanSettingsItemResult
    {
        return new TimemanSettingsItemResult($this->getCoreResponse()->getResponseData()->getResult());
    }
}
```

### 5. `src/Services/Timeman/Service/Timeman.php`

Methods: `open()`, `pause()`, `close()`, `status()`, `settings()`.
`open()` and `close()` accept optional `CarbonImmutable $time` and format it as ISO-8601 before passing to the API.

### 6. `src/Services/Timeman/TimemanServiceBuilder.php`

```php
#[ApiServiceBuilderMetadata(new Scope(['timeman']))]
class TimemanServiceBuilder extends AbstractServiceBuilder
{
    public function timeman(): Timeman\Service\Timeman { ... }
}
```

### 7. `tests/Unit/Services/Timeman/Service/TimemanTest.php`

Unit test verifying service can be instantiated and each method calls the correct REST endpoint name.

### 8. `tests/Integration/Services/Timeman/Service/TimemanTest.php`

Integration test calling live API: open, pause, close, status, settings.

### 9. `tests/Integration/Services/Timeman/Result/WorkdayItemResultAnnotationsTest.php`

Annotation completeness + type cast matching test for `WorkdayItemResult`.

### 10. `tests/Integration/Services/Timeman/Result/TimemanSettingsItemResultAnnotationsTest.php`

Annotation completeness + type cast matching test for `TimemanSettingsItemResult`.

---

## Files to Modify

### 1. `src/Services/ServiceBuilder.php`

Add `getTimemanScope(): TimemanServiceBuilder` method following existing patterns.

### 2. `phpunit.xml.dist`

Add suite:
```xml
<testsuite name="integration_tests_scope_timeman">
    <directory>./tests/Integration/Services/Timeman/</directory>
</testsuite>
```

### 3. `Makefile`

Add target:
```makefile
.PHONY: test-integration-scope-timeman
test-integration-scope-timeman:
    docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_timeman
```

### 4. `rector.php`

Add paths:
```php
__DIR__ . '/src/Services/Timeman',
__DIR__ . '/tests/Integration/Services/Timeman',
```

### 5. `phpstan.neon.dist`

Add paths:
```yaml
- tests/Integration/Services/Timeman
```

### 6. `CHANGELOG.md`

Add under `## 3.2.0 – UNRELEASED` → `### Added`:
```markdown
- Added `Services\Timeman` service with support for workday tracking methods,
  see [timeman.* methods](https://apidocs.bitrix24.com/api-reference/timeman/index.html):
    - `open` — starts a new workday or continues after pause/close
    - `pause` — pauses the current workday
    - `close` — closes the current workday
    - `status` — gets current workday status
    - `settings` — gets user's work time settings
  ([#484](https://github.com/bitrix24/b24phpsdk/issues/484))
```

---

## Deptrac compliance

The new `Timeman` service depends only on:
- `Bitrix24\SDK\Core\*` (allowed — SDK core layer)
- `Bitrix24\SDK\Services\AbstractService` and `AbstractServiceBuilder` (allowed)
- `Bitrix24\SDK\Attributes\*` (allowed)
- `Carbon\CarbonImmutable` (external vendor, allowed)

No violations expected.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-scope-timeman
```

