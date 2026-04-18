# Design: IM Disk folder getter for issue #435

## Goal

Add SDK support for the Bitrix24 REST method `im.disk.folder.get` in a minimal way that matches the scope of issue `#435`.

## Scope

In scope:
- add a new IM Disk service class under `src/Services/IM/Disk/Service/Disk.php`
- add one public method for `im.disk.folder.get`
- expose the service through `IMServiceBuilder::disk()`
- add unit coverage for the new service and builder accessor
- add integration coverage for the IM Disk service
- add a dedicated PHPUnit suite and `make test-integration-im-disk` target
- add a `CHANGELOG.md` entry under `3.2.0 – UNRELEASED` / `### Added`

Out of scope:
- adding any other `im.disk.*` methods
- introducing result-item abstractions beyond what is needed for the single returned folder id
- refactoring unrelated IM services

## Design

### Service layout

Create `Bitrix24\SDK\Services\IM\Disk\Service\Disk` as a regular `AbstractService` subclass with:
- `#[ApiServiceMetadata(new Scope(['im']))]`
- one method `folderGet(): IntResult`

The method calls `im.disk.folder.get` without parameters and returns `Bitrix24\SDK\Core\Result\IntResult`.

This keeps the implementation aligned with the existing service pattern in the SDK while avoiding speculative expansion for future `im.disk.*` methods.

### Service builder integration

Extend `Bitrix24\SDK\Services\IM\IMServiceBuilder` with:
- import for the new IM Disk service
- cached accessor `disk(): \Bitrix24\SDK\Services\IM\Disk\Service\Disk`

The accessor should follow the same caching behavior already used by `notify()` and `placements()`.

### Tests

Unit coverage:
- extend `tests/Unit/Services/IM/IMServiceBuilderTest.php` with an assertion that `disk()` returns the new service and is cached
- add `tests/Unit/Services/IM/Disk/Service/DiskTest.php` to verify the REST method name and empty parameter mapping for `folderGet()`

Integration coverage:
- add `tests/Integration/Services/IM/Disk/Service/DiskTest.php`
- verify `Factory::getServiceBuilder()->getIMScope()->disk()->folderGet()` returns a positive folder id

Test execution wiring:
- add a dedicated suite to `phpunit.xml.dist` for the new integration test path
- add `make test-integration-im-disk` to run only that suite

## Error handling

No custom error handling is needed. The new service should reuse the existing transport and API exception flow provided by `AbstractService` and `Core`.

## Verification

Minimum verification for the implementation phase:
- targeted unit tests for the new service and builder
- targeted integration test for `im.disk.folder.get`
- changelog entry references issue `#435`

## Risks

- The REST response shape must match `IntResult` expectations. If the endpoint returns a different envelope than other integer-returning methods, the integration test should surface it immediately.
- The new PHPUnit suite name and Make target must follow existing repository conventions to avoid drift in local developer workflows.
