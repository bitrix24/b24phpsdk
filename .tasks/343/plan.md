# Plan: Fix missing `time` node handling in Response (issue #343)

## Context

`Response::getResponseData()` parses the raw API response and builds a `ResponseData` object
containing a `Time` DTO. Some Bitrix24 API calls (e.g. documentation/v3 endpoint) return a
response **without** a `time` node.

Existing workaround in `Response.php` (lines 92–94):
```php
// fix inconsistent response format for /documentation api call for v3
if (!array_key_exists('time', $responseResult)) {
    $responseResult['time'] = [];
}
```
This sets `time` to an empty array and then calls `Time::initFromResponse([])`, which tries
`(float)$response['start']`, `(float)$response['finish']` etc. on a missing key — producing
PHP errors/warnings for undefined array keys.

**Approach:**
Add a static factory `Time::initWithZeroValues(): self` that creates a `Time` struct with
zero float fields and `CarbonImmutable::now()` for date fields (current timestamp, not epoch).
In `Response.php`, replace the workaround: when `time` is absent or an empty array, call
`Time::initWithZeroValues()` instead of `Time::initFromResponse([])`.

`ResponseData`, `getTime()`, and the 4 callers in Batch/BulkItemsReader — **unchanged**.

---

## Files to Create

### 1. `tests/Unit/Core/Response/DTO/ResponseDataTest.php`

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Core\Response\DTO;

use Bitrix24\SDK\Core\Response\DTO\Pagination;
use Bitrix24\SDK\Core\Response\DTO\ResponseData;
use Bitrix24\SDK\Core\Response\DTO\Time;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResponseData::class)]
class ResponseDataTest extends TestCase
{
    #[Test]
    #[TestDox('getTime() returns a zero-value Time when constructed with initWithZeroValues')]
    public function testGetTimeReturnsZeroTimeWhenAbsent(): void
    {
        $responseData = new ResponseData([], Time::initWithZeroValues(), new Pagination(null, null));
        $this->assertSame(0.0, $responseData->getTime()->start);
        $this->assertSame(0.0, $responseData->getTime()->finish);
        $this->assertSame(0.0, $responseData->getTime()->duration);
    }
}
```

---

## Files to Modify

### 1. `src/Core/Response/DTO/Time.php`

Add a new static factory method after `initFromResponse()`:

```php
/**
 * Create a Time instance with zero numeric values and current timestamp for date fields.
 * Used as a fallback when the API response omits the time node (e.g. documentation endpoint).
 */
public static function initWithZeroValues(): self
{
    $now = CarbonImmutable::now();
    return new self(
        0.0,
        0.0,
        0.0,
        0.0,
        0.0,
        $now,
        $now,
        null
    );
}
```

### 2. `src/Core/Response/Response.php`

Replace the existing workaround (lines 92–100):

**Before:**
```php
// fix inconsistent response format for /documentation api call for v3
if (!array_key_exists('time', $responseResult)) {
    $responseResult['time'] = [];
}

$this->responseData = new ResponseData(
    $responseResult['result'],
    DTO\Time::initFromResponse($responseResult['time']),
    new DTO\Pagination($nextItem, $total)
);
```

**After:**
```php
// Some API endpoints (e.g. documentation/v3) omit the time node entirely.
$time = (array_key_exists('time', $responseResult) && $responseResult['time'] !== [])
    ? DTO\Time::initFromResponse($responseResult['time'])
    : DTO\Time::initWithZeroValues();

$this->responseData = new ResponseData(
    $responseResult['result'],
    $time,
    new DTO\Pagination($nextItem, $total)
);
```

### 3. `tests/Unit/Core/Response/DTO/TimeTest.php`

Add new test method to the existing class:

```php
#[Test]
#[TestDox('initWithZeroValues() creates a Time with all-zero numeric fields and current-time dates')]
public function testInitWithZeroValues(): void
{
    $before = CarbonImmutable::now();
    $time = Time::initWithZeroValues();
    $after = CarbonImmutable::now();

    $this->assertSame(0.0, $time->start);
    $this->assertSame(0.0, $time->finish);
    $this->assertSame(0.0, $time->duration);
    $this->assertSame(0.0, $time->processing);
    $this->assertSame(0.0, $time->operating);
    $this->assertNull($time->operatingResetAt);
    $this->assertTrue($time->dateStart->greaterThanOrEqualTo($before));
    $this->assertTrue($time->dateStart->lessThanOrEqualTo($after));
    $this->assertTrue($time->dateFinish->greaterThanOrEqualTo($before));
    $this->assertTrue($time->dateFinish->lessThanOrEqualTo($after));
}
```

### 4. `CHANGELOG.md`

Under `## 3.1.0 Unreleased` → `### Fixed`:
```markdown
- Fixed `Response::getResponseData()` crashing when API response lacks a `time` node (e.g. documentation endpoint): added `Time::initWithZeroValues()` factory that fills numeric fields with `0.0` and date fields with `CarbonImmutable::now()` ([#343](https://github.com/bitrix24/b24phpsdk/issues/343))
```

---

## Deptrac compliance

All changes are within the `Core` layer.
`Time` (`Core\Response\DTO`) depends on `CarbonImmutable` (vendor) — already the case.
No new cross-layer imports introduced.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

No integration tests required — this is a Core-layer fix for an edge case
(documentation API endpoint omits `time`).
