---
name: b24phpsdk-maintainer
description: |
  Use this skill whenever working with GitHub issues in the bitrix24/b24phpsdk repository:
  creating new issues, reading existing ones, planning implementation from an issue,
  or referencing an issue in commits, branches, or CHANGELOG.
  IMPORTANT: this skill MUST be invoked before doing any issue-related work.
user-invocable: true
allowed-tools: mcp__github__get_issue, mcp__github__list_issues, mcp__github__create_issue, mcp__github__add_issue_comment, mcp__github__search_issues
---

# b24phpsdk Maintainer

Repository: **bitrix24/b24phpsdk** (`owner: bitrix24`, `repo: b24phpsdk`)

---

## Working with an existing issue

When given an issue number, always load it first via `mcp__github__get_issue`:

```
owner: bitrix24
repo:  b24phpsdk
issue_number: <N>
```

Read the title, body, and labels — they define the scope and context of the work.

---

## Creating a new issue

Before creating, search via `mcp__github__search_issues` to make sure a similar issue does not already exist.

### Issue body structure

```markdown
## Problem

<Clear description of the problem or missing functionality>

## Proposed solution

<Description of the proposed solution>

## Acceptance criteria

- [ ] <criterion 1>
- [ ] <criterion 2>
- [ ] <criterion 3>
```

### Title rules

- New functionality: `Add <feature description>`
- Bug fix: `Fix <what is broken>`
- Refactoring: `Refactor <what and why>`
- Maximum 72 characters

### Labels

| Label | When to use |
|---|---|
| `enhancement` | new functionality |
| `bug` | bug fix |
| `documentation` | documentation only |
| `refactoring` | internal changes without API changes |

---

## Project conventions when implementing an issue

### Branch naming

```
feature/<issue-number>-<short-slug>   # new functionality
bugfix/<issue-number>-<short-slug>    # bug fix
```

Example: `feature/397-add-task-chat-fields`

### CHANGELOG.md references

All entries under `## X.Y.Z Unreleased` → `### Added / Fixed / Changed` must end with an issue link:

```markdown
- Added something useful ([#NNN](https://github.com/bitrix24/b24phpsdk/issues/NNN))
```

### Test file structure

When adding a new service for an issue, the following files are mandatory:
- `tests/Unit/Services/<Scope>/Service/<Name>Test.php` — unit test for the service
- `tests/Integration/Services/<Scope>/Service/<Name>Test.php` — integration test
- `tests/Integration/Services/<Scope>/Result/<Name>ItemResultTest.php` — result item test (see below)
- Add a suite to `phpunit.xml.dist` and a make target to `Makefile`

See also: `docs/architecture.md`, `docs/testing.md`

---

### Mandatory integration test for every *ItemResult

**Rule**: every `*ItemResult.php` file that contains `@property-read` PHPDoc annotations
MUST have a corresponding integration test at
`tests/Integration/Services/<Scope>/Result/<Name>ItemResultTest.php`.

The test must contain exactly two methods:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\<Scope>\Result;

use Bitrix24\SDK\Services\<Scope>\Result\<Name>ItemResult;
use Bitrix24\SDK\Services\<Scope>\Service\<ServiceName>;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(<Name>ItemResult::class)]
class <Name>ItemResultTest extends TestCase
{
    use CustomBitrix24Assertions;

    private <ServiceName> $<serviceNameCamel>;

    #[\Override]
    protected function setUp(): void
    {
        $this-><serviceNameCamel> = Factory::getServiceBuilder()->get<Scope>Scope()-><serviceAccessor>();
    }

    #[Test]
    #[TestDox('all fields in <Name>ItemResult are annotated in phpdoc and match with raw api response')]
    public function testAllFieldsAreAnnotated(): void
    {
        // fetch a raw single item array from the real API response
        $rawItem = $this-><serviceNameCamel>-><fetchMethod>()->getCoreResponse()
            ->getResponseData()->getResult()['<resultKey>'];

        $this->assertBitrix24AllResultItemFieldsAnnotated(
            array_keys($rawItem),
            <Name>ItemResult::class
        );
    }

    #[Test]
    #[TestDox('all fields in <Name>ItemResult have valid type casting in magic getters')]
    public function testAllFieldsHasValidTypeCastingInMagicGetters(): void
    {
        $<nameItemResult> = $this-><serviceNameCamel>-><fetchMethod>()-><itemAccessor>();
        $this->assertBitrix24ResultItemFieldsTypeCastMatchAnnotations(
            $<nameItemResult>,
            <Name>ItemResult::class
        );
    }
}
```

**Template notes:**
- `assertBitrix24AllResultItemFieldsAnnotated` — verifies that every key from the raw API response is covered by a `@property-read` annotation in the result item class
- `assertBitrix24ResultItemFieldsTypeCastMatchAnnotations` — verifies that every magic getter returns a value whose PHP type matches the PHPDoc annotation (uses Typhoon Reflection internally)
- Both methods live in the trait `Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions`
- `#[CoversClass]` must point to `*ItemResult`, not to the service class

**Live example**: `tests/Integration/Services/Task/ChatMessageField/Result/ChatMessageFieldItemResultTest.php`

---

## Task folder and implementation plan

**Rule**: before writing any code, create a dedicated folder and a plan file for the issue.

### 1. Create the task folder

```
.tasks/<issue-number>/
```

Example: `.tasks/397/`

### 2. Write the plan

Create `.tasks/<issue-number>/plan.md` **before starting implementation**.
Think through the full scope of the issue and write the plan first — only start coding once the plan is agreed upon.

#### Plan file structure

```markdown
# Plan: <issue title> (issue #NNN)

## Context

<Background: what the issue is about, relevant API details, how it fits
into the existing SDK structure, any constraints or decisions made upfront>

---

## Files to Create

### 1. `src/...`

<Full class skeleton with namespace, imports, and key method signatures>

### 2. `tests/Unit/...`

<Test class skeleton>

### 3. `tests/Integration/...`

<Integration test class skeleton>

---

## Files to Modify

### 1. `src/Services/ServiceBuilder.php` (or scope builder)

<Exact method to add, line reference if known>

### 2. `phpunit.xml.dist`

<Exact XML block to add>

### 3. `Makefile`

<Exact make target to add>

---

## Deptrac compliance

<Confirm which layers the new code depends on and that no new violations are introduced>

---

## Verification

\`\`\`bash
make test-unit
make test-integration-<scope>
make lint-phpstan
make lint-deptrac
\`\`\`
```

### 3. Work from the plan

Execute the plan step by step. Do not start a new step until the previous one is complete.
Update the plan file if scope changes during implementation.

---

## Loading issue details at the start of work

If the user mentions an issue number without explicitly calling `/b24phpsdk-maintainer`, still execute these steps:

1. Load the issue via `mcp__github__get_issue`
2. Output a short summary: what needs to be done
3. Create `.tasks/<issue-number>/` and write `plan.md`
4. Propose the plan for review before starting implementation
