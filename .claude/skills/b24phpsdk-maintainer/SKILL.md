---
name: b24phpsdk-maintainer
description: |
  Use this skill whenever working with GitHub issues in the bitrix24/b24phpsdk repository:
  creating new issues, reading existing ones, planning implementation from an issue,
  or referencing an issue in commits, branches, or CHANGELOG.
  IMPORTANT: this skill MUST be invoked before doing any issue-related work.
user-invocable: true
allowed-tools: mcp__github__get_issue, mcp__github__list_issues, mcp__github__create_issue, mcp__github__add_issue_comment, mcp__github__search_issues, mcp__bitrix24__bitrix-search, mcp__bitrix24__bitrix-method-details, mcp__bitrix24__bitrix-article-details, mcp__bitrix24__bitrix-event-details, mcp__bitrix24__bitrix-app-development-doc-details
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

## Closing an issue: start-of-work protocol

When the user asks to implement (close) an issue and provides a link or number,
execute the following steps **in strict order** before writing any code.

### Step 1 — Load the issue

Fetch the issue via `mcp__github__get_issue` and read the full title, body, and labels.

#### Step 1.5 — Expand context from Bitrix24 official documentation

Use the **bitrix24 MCP server** to fetch up-to-date API documentation for every REST method
mentioned in the issue or required for the implementation.

Available tools:

| Tool | When to use |
|---|---|
| `mcp__bitrix24__bitrix-search` | find methods, articles, or events by keyword when the exact name is unknown |
| `mcp__bitrix24__bitrix-method-details` | fetch full description of a specific REST method (parameters, response shape, errors) |
| `mcp__bitrix24__bitrix-article-details` | fetch a documentation article (overview pages, concept guides) |
| `mcp__bitrix24__bitrix-event-details` | fetch details of a specific Bitrix24 event |
| `mcp__bitrix24__bitrix-app-development-doc-details` | fetch application development documentation |

For each REST method involved in the issue:
1. Call `mcp__bitrix24__bitrix-method-details` to get the exact parameter names, types, and response structure
2. Note the real response key names (e.g. `result.item` vs `result.items`) — they must match the `AbstractResult` implementation
3. Note which API version the method belongs to (v1 or v3) — this determines the base branch

Record findings in the **Context** section of `plan.md` so the plan is grounded in actual API behaviour, not assumptions.

### Step 2 — Determine the type

Classify the issue:

| Type | Signals |
|---|---|
| `feature` | labels `enhancement`; title starts with `Add` |
| `bugfix` | label `bug`; title starts with `Fix` |

Use `feature` if the type is ambiguous.

### Step 3 — Ask which API version

Ask the user explicitly before creating the branch using the `AskUserQuestion` tool
with the following question and options (do NOT ask via plain text):

```
question: "Which API version does this issue target?"
header: "API version"
options:
  - label: "v3"
    description: "REST API v3 — base branch: v3-dev"
  - label: "v1"
    description: "REST API v1 — base branch: dev"
```

Branch off from the corresponding base branch:

| API version | Base branch |
|---|---|
| v1 | `dev` |
| v3 | `v3-dev` |

Do not assume — always wait for the user's answer.

### Step 4 — Create the branch

Name the branch according to the issue type and number:

```
feature/<issue-number>-<short-slug>   # for features
bugfix/<issue-number>-<short-slug>    # for bug fixes
```

Example: `feature/397-add-task-chat-fields` branched from `v3-dev`.

Create it with:

```bash
git checkout <base-branch>
git pull
git checkout -b <branch-name>
```

### Step 5 — Create the task folder

```
.tasks/<issue-number>/
```

### Step 6 — Write the plan and wait for approval

Create `.tasks/<issue-number>/plan.md` using the structure defined in the
**«Task folder and implementation plan»** section above.

Present the plan to the user and **wait for explicit approval** before writing any production code.

### Step 7 — Review the plan before approval

Before presenting the plan to the user, self-review it against three criteria:

**1. Unambiguity** — every instruction has exactly one possible interpretation.
Check each step: could a developer unfamiliar with the codebase read it differently?
If yes — rewrite it to be explicit (add file paths, method names, exact values).

**2. Non-contradiction** — no two instructions conflict with each other.
Check: do the files to create match what the files to modify expect?
Do the namespace, class names, and method names stay consistent throughout the plan?
Do the test skeletons reference the same class names as the source skeletons?

**3. No gaps** — the plan covers the full path from empty branch to passing linters and tests.
Walk through the acceptance criteria from the issue and verify each one is addressed by at least one step in the plan.
Check that the Verification section lists all relevant make targets for the changed scope.
If a step depends on another that is not in the plan — add the missing step.

Only after all three criteria are satisfied, present the plan to the user.

**Required**: before presenting the plan, explicitly report the review results in this format:

```
Plan review:
✓ Unambiguity — <one sentence: what was checked and result>
✓ Non-contradiction — <one sentence: what was checked and result>
✓ No gaps — <one sentence: what was checked and result>
```

If any criterion fails, fix the plan first, then re-run the check and report again.

---

## Post-implementation quality gate

After all files from the plan are written and the plan is marked complete,
run checks in two phases. **Do not start phase 2 until phase 1 is fully green.**

### Phase 1 — Light checks (linters + unit tests)

Run in this order:

```bash
make lint-phpstan
make lint-deptrac
make test-unit
```

Rules for phase 1:
- If any command fails, fix the errors and re-run **that command** until it passes before continuing to the next.
- Do not add entries to `deptrac.yaml` → `skip_violations` to silence a new violation — fix the import instead.
- Only proceed to phase 2 when all three commands pass without errors.

### Phase 2 — Heavy checks (integration tests)

Run only after phase 1 is fully green:

```bash
make test-integration-<scope>   # the suite added for this issue
```

Rules for phase 2:
- If the suite fails, fix the root cause and re-run until it passes.
- Do not skip or comment out failing tests — fix the root cause.

### Final report

Report the status to the user:
- Which commands passed on the first run.
- Which required fixes, and a one-line summary of what was fixed.
- Confirmation that both phases are green.
