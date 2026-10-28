# Plan: Add RenewedAuthToken in Credentials compatibly (issue #117)

## Context

[Issue #117](https://github.com/bitrix24/b24phpsdk/issues/117), SDK 3.8.0; base `v3-dev`.
The maintainer requested a usable class in `Core\Credentials` and deprecation of the existing name.
The legacy readonly DTO is used by public interfaces, events, storage implementations and user subclasses.
The user approved implementation of all five issue plans on 2026-10-28.

## Final compatibility design

Expose the Credentials name as an eager Composer-loaded alias of the existing DTO.
Keep the old implementation, constructors, factories, declared return/parameter types,
canonical runtime name and serialized identity. This makes both names interchangeable,
including a legacy-created token passed to a new-name parameter.

The originally planned subtype failed Deptrac because inheritance propagates its parent's
pre-existing ApplicationStatus dependency into the new Core class. No suppression is added.
Changing the legacy factory to new static also breaks existing subclasses with custom
constructors. Both findings are covered by compatibility tests. An eager alias solves the
namespace objective without moving ApplicationStatus or expanding the architecture change.

## Files to Create

- `src/Core/Credentials/RenewedAuthToken.php`: register `class_alias` from the existing DTO to the Credentials name.
- `tests/Unit/Core/Credentials/RenewedAuthTokenTest.php`: both names' constructors/factories, readonly/token/status/endpoints, legacy storage/interface/subclass compatibility, serialization identity and a fresh-process eager-loading regression.

## Files to Modify

- `composer.json`: register the alias file in `autoload.files`; lazy PSR-4 alone cannot satisfy parameter checks when the object was first created through the legacy name.
- `src/Core/Response/DTO/RenewedAuthToken.php`: PHPDoc deprecation pointing at the new name; keep all behavior intact.
- `src/Core/ApiClient.php`: use the new name for token construction while retaining its legacy declared return type.
- `tests/Unit/Core/ApiClientTest.php`: refreshed tokens satisfy both names.
- `tests/Unit/Core/CoreTest.php`: both names remain usable in refresh events and credential updates.
- `CHANGELOG.md`: Added namespace alias and Deprecated legacy-name entries, both with #117 links.

No generator applies to this compatibility alias. No REST wrapper, service entity or new integration suite is introduced.

## Steps

- [x] Reproduce the missing namespace and add failing compatibility tests.
- [x] Preserve published method signatures and legacy subclass constructor behavior.
- [x] Verify that a lazy alias fails new-name parameter checks, then register it eagerly through Composer.
- [x] Run focused tests and scoped static/style/Rector checks.
- [x] Complete the global quality gate and core integration.
- [x] Add changelog entries and prepare PR against v3-dev.
- [ ] After merge, assess a separate dev backport; no merge/backport is performed in this work package.

## Deptrac compliance

The alias creates no new class inheritance or Core-to-Application dependency. The existing
legacy exception remains unchanged; no new skip_violations entry or layer reclassification.

## Verification

```bash
docker compose run --rm -T php-cli composer dump-autoload --no-scripts
docker compose run --rm -T php-cli vendor/bin/phpunit tests/Unit/Core/Credentials/RenewedAuthTokenTest.php tests/Unit/Core/ApiClientTest.php tests/Unit/Core/CoreTest.php
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-core
```

## Implementation verification outcome

Constructor/factory/interface/subclass/serialization and fresh-process eager-alias tests pass. Focused live Core calls pass. The broad ScopeTest failure reproduces on clean original v3-dev. All shared light gates are run on the combined candidate; PR CI verifies each isolated branch. Broader scope-catalog readiness is recorded separately and PRs remain drafts.
