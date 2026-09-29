# Design: current application installation repository contract (issue #356)

Status: approved by the user.

## Evidence

- Issue: https://github.com/bitrix24/b24phpsdk/issues/356.
- Selected SDK line: v3; PR base: `v3-dev`.
- Base commit: `1f2894e53a961d65c2bc84ec7f696ec017538064`.
- `ApplicationInstallationRepositoryInterface` does not declare `getCurrent()`.
- There is no `Unlink` class or installation-repository `getCurrent()` call in SDK source or tests. The reported caller cannot be reproduced inside this repository.
- The repository stores multiple installations and has no implicit current-installation context.
- The existing reference repository suite passed before changes: 38 tests, 38 assertions.
- OpenAPI was refreshed successfully. This task does not involve a REST endpoint.

## Approaches

1. Add the requested method to the existing interface, document explicit application context, and update the reference implementation and tests. This satisfies the exact issue request but requires all downstream implementers to add the method. Recommended, subject to approval of the compatibility impact.
2. Introduce a separate context-aware repository interface extending the existing one. This avoids changing existing implementers, but callers typed as `ApplicationInstallationRepositoryInterface` would still lack `getCurrent()` and need an application-side type change.
3. Change the reported external `Unlink` caller to use `getById()` or another existing lookup. This preserves the SDK contract but cannot be implemented or verified without the external caller and its context.

## Proposed contract

```php
public function getCurrent(): ApplicationInstallationInterface;
```

The method returns the installation selected by the application's current execution context. Implementations supply that context explicitly, for example through a constructor dependency. A context with no selected installation, or a selected installation absent from storage, raises the existing `ApplicationInstallationNotFoundException`. The contract never chooses an arbitrary first or last stored installation. It does not impose a new installation-status filter.

The SDK's in-memory reference implementation receives an optional `?Uuid $currentInstallationId = null` as its third constructor argument and resolves that ID with `getById()`. Existing two-argument construction remains valid for other repository operations.

## Components and data flow

- Production change: one method declaration and PHPDoc in the existing repository interface.
- Reference adapter: explicit current UUID -> `getById()` -> stored installation or not-found exception.
- Tests: exercise calls through the repository interface with multiple stored installations, absent context, an unknown selected ID, replacement of a stored entity, and removal of the selected installation.
- Documentation: explain context responsibility, exception behavior, and the required migration for existing repository implementations.
- Dependencies remain inside the Application layer; the reference adapter and tests are outside production layers.

## Verification and delivery

Use TDD for reference behavior; verify static calls through the interface with PHPStan, run all five maintainer quality gates, update the Unreleased changelog, then create a PR against `v3-dev` and wait for terminal CI results. No live API integration test applies to an application persistence contract.
