# Plan: Fix InstantMessenger value type (issue #156)

## Context

[Issue #156](https://github.com/bitrix24/b24phpsdk/issues/156), SDK 3.8.0; base `v3-dev`.
`InstantMessenger::__get('VALUE_TYPE')` calls `EmailValueType::from()` and its PHPDoc names `PhoneValueType`.
An isolated PHP probe on the base commit reproduces a `ValueError` with `TELEGRAM`.
`InstantMessengerValueType` already contains the supported IM values; no new enum or REST wrapper is needed.

## Files to Create

`tests/Unit/Services/CRM/Common/Result/SystemFields/Types/InstantMessengerTest.php`:

```php
namespace Bitrix24\SDK\Tests\Unit\Services\CRM\Common\Result\SystemFields\Types;

use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessenger;
use Bitrix24\SDK\Services\CRM\Common\Result\SystemFields\Types\InstantMessengerValueType;
use PHPUnit\Framework\TestCase;

final class InstantMessengerTest extends TestCase
{
    public function testEverySupportedMessengerType(): void
    {
        foreach (InstantMessengerValueType::cases() as $expected) {
            $item = new InstantMessenger(['ID' => '7', 'VALUE' => 'example', 'VALUE_TYPE' => $expected->value]);
            self::assertSame($expected, $item->VALUE_TYPE);
            self::assertSame(7, $item->ID);
            self::assertSame('example', $item->VALUE);
        }
    }
}
```

Add a separate invalid-value case preserving the existing `ValueError` contract; also assert the PHPDoc names the correct enum using reflection.

## Files to Modify

- `src/Services/CRM/Common/Result/SystemFields/Types/InstantMessenger.php`: use `InstantMessengerValueType::from()` and annotate `VALUE_TYPE` as `InstantMessengerValueType`. Keep `VALUE`, `ID`, unknown-property behavior and inheritance unchanged.
- `tests/Integration/Services/CRM/Contact/Service/ContactTest.php`: strengthen the existing messenger test to send the enum value TELEGRAM, assert the returned enum and clean up its own contact.
- `CHANGELOG.md`: add a Fixed entry under Unreleased with the #156 link after verification.

No generator applies: this file is a multifield value object, not a generated `*ItemResult`, select builder or item builder. No service, suite or Make target is added.

## Steps

- [x] Add tests and run the file; confirm the supported-enum test fails on `TELEGRAM`/other IM-only values.
- [x] Make the two enum/PHPDoc substitutions and rerun the test.
- [x] Run the common quality gate and the focused live CRM messenger regression.
- [x] Add the changelog entry, review the exact diff, commit and prepare the PR.
- [ ] Track a `dev` backport after the v3 PR merges; the current SDK 1.x source also contains the defect.

## Deptrac compliance

Only Services-to-Core and same-namespace enum dependencies; no new layer dependencies or suppressions.

## Verification

```bash
docker compose run --rm -T php-cli vendor/bin/phpunit tests/Unit/Services/CRM/Common/Result/SystemFields/Types/InstantMessengerTest.php
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-scope-crm
```

Run integration only after the five light gates pass. No live integration success has been established during planning.

## Implementation verification outcome

The enum unit suite and the live CRM messenger round trip pass. The live test removes its own contact. All shared light gates are run on the combined candidate; PR CI verifies each isolated branch. Broader scope-catalog readiness is recorded separately and PR readiness is tracked by CI.
