# Plan: Add getCurrent() to ApplicationInstallationRepositoryInterface (issue #356)

> Execute this plan in the current session after explicit user approval, using `superpowers:executing-plans` and `superpowers:test-driven-development`.

**Status:** Approved by the user; implementation in progress.

**Goal:** Expose `getCurrent(): ApplicationInstallationInterface` on the application installation repository and provide a tested reference implementation with explicit context.

**Architecture:** The public interface defines the lookup and its not-found behavior. The application supplies the current installation context. The in-memory reference adapter accepts the selected UUID and delegates lookup to `getById()`.

**Tech stack:** PHP 8.4/8.5, PHPUnit, Symfony Uuid, Docker-backed Make targets.

## Context

- Issue: https://github.com/bitrix24/b24phpsdk/issues/356.
- User selected v3. Branch: `feature/356-add-current-installation`; PR base: `v3-dev`.
- Fresh base commit: `1f2894e53a961d65c2bc84ec7f696ec017538064`.
- Design and alternatives: `.tasks/356/design.md`.
- The interface is missing the requested method. Its only concrete implementation inside this checkout is the in-memory test reference adapter.
- The `Unlink` use case mentioned in the issue is not present in SDK source or tests; its reported PHPStan error cannot be reproduced here. Tests and PHPStan will verify that an SDK consumer typed as the interface can call the new method.
- There are no REST methods, response envelopes, field metadata contracts, result item classes, or generated builders involved. REST documentation research, SDK generators, live annotation tests, and new integration suites do not apply.
- `make oa-schema-build` succeeded in the primary checkout before planning. Refresh again in the dedicated worktree after dependency setup. Preserve the refreshed snapshot locally; the generated unrelated REST changes do not belong to the feature commit.
- Existing repository baseline: 38 tests, 38 assertions in the primary checkout; rerun in the dedicated worktree.

## Compatibility and selected semantics

Adding an abstract method to an existing interface requires downstream implementations to implement it. Document this requirement in the installation-contract documentation and the Unreleased changelog. Approval of this plan includes this compatibility impact.

`getCurrent()` returns the explicitly selected installation from the application's current execution context. If there is no context, or its UUID does not resolve to a stored installation, throw `ApplicationInstallationNotFoundException`. Do not infer context from save order or installation status. The reference adapter uses an optional third constructor argument so its existing two-argument test construction remains valid.

## Files to Create

### 1. `tests/Unit/Application/Contracts/ApplicationInstallations/Repository/CurrentApplicationInstallationRepositoryTest.php`

Use this complete test skeleton, retaining repository copyright headers:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Application\Contracts\ApplicationInstallations\Repository;

use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationInterface;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Entity\ApplicationInstallationStatus;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Exceptions\ApplicationInstallationNotFoundException;
use Bitrix24\SDK\Application\Contracts\ApplicationInstallations\Repository\ApplicationInstallationRepositoryInterface;
use Bitrix24\SDK\Application\Contracts\Bitrix24Accounts\Repository\Bitrix24AccountRepositoryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Uid\Uuid;

#[CoversClass(ApplicationInstallationRepositoryInterface::class)]
#[CoversClass(InMemoryApplicationInstallationRepositoryImplementation::class)]
class CurrentApplicationInstallationRepositoryTest extends TestCase
{
    public function testGetCurrentReturnsSelectedInstallationAmongSeveral(): void
    {
        $id = Uuid::v7();
        $repository = $this->createRepository($id);
        $current = $this->createInstallation($id);
        $repository->save($this->createInstallation(Uuid::v7()));
        $repository->save($current);
        $repository->save($this->createInstallation(Uuid::v7()));

        $this->assertSame($current, $repository->getCurrent());
    }

    public function testGetCurrentWithoutContextThrowsEvenWhenStorageIsNotEmpty(): void
    {
        $repository = $this->createRepository(null);
        $repository->save($this->createInstallation(Uuid::v7()));

        $this->expectException(ApplicationInstallationNotFoundException::class);
        $repository->getCurrent();
    }

    public function testGetCurrentWithUnknownSelectedIdThrows(): void
    {
        $repository = $this->createRepository(Uuid::v7());
        $repository->save($this->createInstallation(Uuid::v7()));

        $this->expectException(ApplicationInstallationNotFoundException::class);
        $repository->getCurrent();
    }

    public function testGetCurrentReturnsLatestStoredVersionOfSelectedInstallation(): void
    {
        $id = Uuid::v7();
        $repository = $this->createRepository($id);
        $repository->save($this->createInstallation($id));
        $replacement = $this->createInstallation($id);
        $repository->save($replacement);

        $this->assertSame($replacement, $repository->getCurrent());
    }

    public function testGetCurrentAfterSelectedInstallationIsRemovedThrows(): void
    {
        $id = Uuid::v7();
        $repository = $this->createRepository($id);
        $repository->save($this->createInstallation($id, ApplicationInstallationStatus::deleted));
        $repository->save($this->createInstallation(Uuid::v7()));
        $repository->delete($id);

        $this->expectException(ApplicationInstallationNotFoundException::class);
        $repository->getCurrent();
    }

    private function createRepository(?Uuid $currentInstallationId): ApplicationInstallationRepositoryInterface
    {
        return new InMemoryApplicationInstallationRepositoryImplementation(
            $this->createStub(Bitrix24AccountRepositoryInterface::class),
            new NullLogger(),
            $currentInstallationId
        );
    }

    private function createInstallation(
        Uuid $id,
        ApplicationInstallationStatus $status = ApplicationInstallationStatus::active
    ): ApplicationInstallationInterface {
        $installation = $this->createStub(ApplicationInstallationInterface::class);
        $installation->method('getId')->willReturn($id);
        $installation->method('getStatus')->willReturn($status);

        return $installation;
    }
}
```

The interface return type in `createRepository()` ensures PHPStan checks the consumer call against the public contract. Unit test discovery already includes this directory.

## Files to Modify

### 1. `src/Application/Contracts/ApplicationInstallations/Repository/ApplicationInstallationRepositoryInterface.php`

Insert after `getById()`:

```php
/**
 * Get the installation selected by the application's current execution context.
 *
 * The implementation must supply the current installation context explicitly.
 *
 * @throws ApplicationInstallationNotFoundException When no installation is selected or the selected installation does not exist.
 */
public function getCurrent(): ApplicationInstallationInterface;
```

### 2. `tests/Unit/Application/Contracts/ApplicationInstallations/Repository/InMemoryApplicationInstallationRepositoryImplementation.php`

Append the optional constructor parameter:

```php
public function __construct(
    private readonly Bitrix24AccountRepositoryInterface $bitrix24AccountRepository,
    private readonly LoggerInterface $logger,
    private readonly ?Uuid $currentInstallationId = null
) {
}
```

Add the reference implementation after `getById()`:

```php
#[\Override]
public function getCurrent(): ApplicationInstallationInterface
{
    if ($this->currentInstallationId === null) {
        throw new ApplicationInstallationNotFoundException('current application installation is not selected');
    }

    return $this->getById($this->currentInstallationId);
}
```

### 3. `src/Application/Contracts/ApplicationInstallations/Docs/ApplicationInstallations.md`

Under Repository methods, add:

```markdown
- `public function getCurrent(): ApplicationInstallationInterface;`
    - Returns the installation selected by the application's current execution context.
    - Implementations must provide this context explicitly, for example by receiving its UUID or a context provider through dependency injection.
    - Throws `ApplicationInstallationNotFoundException` when no current installation is selected or the selected installation does not exist.
    - The method does not select an arbitrary installation from storage.

### Migration for existing repository implementations

Implementations of `ApplicationInstallationRepositoryInterface` must add `getCurrent(): ApplicationInstallationInterface`. Resolve the application's explicitly selected installation using the existing lookup methods. Raise `ApplicationInstallationNotFoundException` when the context is missing or cannot be resolved. The in-memory reference implementation demonstrates UUID selection through an optional third constructor argument.
```

### 4. `CHANGELOG.md`

After all applicable gates pass, add under the existing `## Unreleased` heading:

```markdown
### Added

- Added `ApplicationInstallationRepositoryInterface::getCurrent()` to retrieve the explicitly selected current application installation; existing repository implementations must add this method ([#356](https://github.com/bitrix24/b24phpsdk/issues/356))
```

`Makefile`, `phpunit.xml.dist`, service builders, and shared abstract contract-test factories need no changes: this feature adds no REST service or suite, and the existing unit suite discovers the new file. No agent configuration file is being modified.

## Deptrac compliance

The production declaration uses the existing Application entity and exception imports. Documentation introduces no dependencies. The test adapter already uses Application and Core types. No new cross-layer dependency or skipped violation is required.

## Execution sequence

- [x] Obtain explicit approval of this plan and its compatibility impact.
- [x] Invoke `superpowers:executing-plans` and `superpowers:test-driven-development`.
- [x] Finish worktree dependency setup, run `make oa-schema-build`, and rerun the 38-test baseline (38 tests, 38 assertions, exit 0).
- [ ] Add only `testGetCurrentReturnsSelectedInstallationAmongSeveral()` and its helpers first. Run `make test-file path=tests/Unit/Application/Contracts/ApplicationInstallations/Repository/CurrentApplicationInstallationRepositoryTest.php` and confirm failure for the missing `getCurrent()` method.
- [ ] Add the interface declaration, optional reference-context parameter, and minimal successful lookup. Rerun the focused test and confirm it passes.
- [ ] Add the absent-context test, observe its failure, then implement the explicit not-found guard. Rerun and confirm it passes.
- [ ] Add the unknown-ID, replacement, and removal regression scenarios one at a time. Their behavior delegates to the existing repository lookup/storage operations; retain the tests even when existing behavior immediately satisfies them.
- [ ] Run the whole repository test directory and verify all existing 38 tests and the five new scenarios pass.
- [ ] Add the repository contract and migration documentation.
- [ ] Run the five phase-1 quality gates below in order. Diagnose any failure before fixing it.
- [ ] Record phase 2 as not applicable: this change only affects the application persistence contract and its local reference adapter; no live REST behavior changes.
- [ ] Add the changelog entry, update task status/evidence, and run `git diff --check`.
- [ ] Invoke `superpowers:verification-before-completion`, inspect fresh gate evidence, commit the feature files with `Add current application installation repository lookup (#356)`.
- [ ] Read `.github/PULL_REQUEST_TEMPLATE.md` fresh, push the branch, create a PR against `v3-dev` using the template and `Closes #356`, attach it to this chat, and wait for terminal CI results after each push.
- [ ] Report the PR URL and actual final CI state. Merging and a v1 backport are outside the approved v3 feature scope.

## Verification

Run the focused tests first:

```bash
make test-file path=tests/Unit/Application/Contracts/ApplicationInstallations/Repository/CurrentApplicationInstallationRepositoryTest.php
make test-file path=tests/Unit/Application/Contracts/ApplicationInstallations/Repository
```

Phase 1, in this exact order:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Phase 2: not applicable for this internal application contract. No live integration target is added or run.

Final scope check:

```bash
git diff --check
git diff --stat
git status --short
```

## Plan review

- Unambiguity: the exact signature, explicit UUID context, exception cases, migration requirement, paths, and test scenarios are specified.
- Non-contradiction: the production contract, reference adapter, typed consumer tests, and documentation use the same method signature and not-found semantics.
- No gaps: the plan covers the requested method, the only local implementation, compatibility documentation, TDD, all applicable quality gates, changelog, PR creation, and CI monitoring.

## Preparation evidence

- Dedicated worktree dependencies installed successfully after retrying a transient archive extraction failure.
- Worktree `make oa-schema-build`: exit 0; schema built successfully.
- Worktree repository baseline: exit 0; 38 tests, 38 assertions.
- Plan self-review found no unspecified signatures, mismatched test/helper types, or missing applicable gates.
- The user approved the plan and requested a draft PR before implementation. Publish the approved plan first, then update that PR with verified implementation and CI results.
