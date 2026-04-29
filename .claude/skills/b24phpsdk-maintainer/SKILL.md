---
name: b24phpsdk-maintainer
description: |
  Use this skill whenever working with GitHub issues in the bitrix24/b24phpsdk repository:
  creating new issues, reading existing ones, planning implementation from an issue,
  referencing an issue in commits, branches, or CHANGELOG,
  or discovering unsupported Bitrix24 REST API methods and filing tracking issues.
  IMPORTANT: this skill MUST be invoked before doing any issue-related work.
user-invocable: true
allowed-tools: Bash, mcp__github__get_issue, mcp__github__list_issues, mcp__github__create_issue, mcp__github__add_issue_comment, mcp__github__search_issues, mcp__github__create_pull_request, mcp__github__get_pull_request, mcp__github__list_commits, mcp__bitrix24__bitrix-search, mcp__bitrix24__bitrix-method-details, mcp__bitrix24__bitrix-article-details, mcp__bitrix24__bitrix-event-details, mcp__bitrix24__bitrix-app-development-doc-details
---

# b24phpsdk Maintainer

Repository: **bitrix24/b24phpsdk** (`owner: bitrix24`, `repo: b24phpsdk`)

---

## On skill invocation: refresh the OpenAPI schema

**Required**: before doing anything else, run:

```bash
make oa-schema-build
```

This updates `docs/open-api/openapi.json` with the current Bitrix24 REST API snapshot.
Do not proceed with any workflow until the command completes successfully.

---

## SDK file code generators

Before manually creating or updating SDK PHP files that match one of the generator-supported
contracts below, use the generator first. If the generator cannot be used for the current
case, write the reason explicitly in `.tasks/<issue-number>/plan.md` before proceeding with
manual edits.

| File type | Required generator |
|---|---|
| `src/Services/**/Result/*ItemResult.php` with `@property-read` field annotations | `php bin/console b24-dev:result-item-generator <method.name> --stage=all` |
| `src/Services/**/Service/*SelectBuilder.php` | `php bin/console b24-dev:generate-select-builder <openapi-entity-key> --namespace=<namespace> --class-name=<class> --output=<path>` |
| `src/Services/**/Service/*ItemBuilder.php` | `php bin/console b24-dev:generate-item-builder <openapi-operation-path> --namespace=<namespace> --class-name=<class> --output=<path>` |

Generator usage rules:

- Run `make oa-schema-build` first; the generators rely on `docs/open-api/openapi.json`.
- Include the generator command in the issue plan for any generated SDK file.
- Review generated code against existing SDK naming, namespace, result-envelope, casting, and
  backward-compatibility patterns before committing it.
- After generating a `*ItemResult.php`, keep the mandatory live annotation/type-casting
  integration test described below.

---

## Webhook URL format for direct curl requests

When making direct `curl` calls to inspect raw API responses (e.g., during integration test
development or API discovery), use the following URL structures.

### Via incoming webhook (no OAuth required)

```
https://<portal>/rest/<userId>/<webhookToken>/<method.name>
```

The webhook base URL is stored in `tests/.env.local`:

```dotenv
BITRIX24_WEBHOOK=https://your-domain.bitrix24.com/rest/1/<webhookToken>/
```

To call a method, append the method name to the base URL:

```bash
# read base URL from env, call any method
curl -s -X POST "${BITRIX24_WEBHOOK}crm.deal.list" \
  -H "Content-Type: application/json" \
  -d '{"filter": {}, "select": ["ID", "TITLE"]}'
```

### v1 vs v3: same URL pattern, different response shape

Both API versions use the **same URL structure** — the version affects method naming and
response envelope, not the base path:

| | v1 | v3 |
|---|---|---|
| URL | `.../rest/1/<token>/tasks.task.list` | `.../rest/1/<token>/tasks.task.file.attach` |
| Single-item response | `result` contains the value directly | `result.item` |
| List response | `result` is a flat array | `result.items` |
| Parameters | flat key-value pairs | may use nested objects (`fields`, `data`) |

Knowing the response envelope is critical: the `AbstractResult` subclass must reference
the correct key (`result`, `result.item`, or `result.items`).

### Via OAuth token (no webhook)

```bash
curl -s -X POST \
  https://your-domain.bitrix24.com/rest/crm.deal.list \
  -H "Content-Type: application/json" \
  -d '{"auth": "<oauth_token>", "filter": {}, "select": ["ID"]}'
```

---

## Issue language

**Rule**: every GitHub issue in `bitrix24/b24phpsdk` — title, body, and checklists — MUST be
written in **English only**, regardless of the language used in conversation with the user.

This applies to:
- creating a new issue (`mcp__github__create_issue`, `gh issue create`)
- updating an existing issue body or title (`mcp__github__update_issue`)
- adding issue comments (`mcp__github__add_issue_comment`)

If the conversation is in another language, translate the content to English before writing
it to GitHub. Do not mix languages inside a single issue. Proper nouns (method names, file
paths, URLs) stay as-is.

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

## GitHub CLI fallback

When `mcp__github__*` tools return authentication errors or are unavailable, use the `gh` CLI via `Bash` as a fallback:

```bash
# Search issues (fallback for mcp__github__search_issues)
gh search issues "<query>" --repo bitrix24/b24phpsdk --state open

# List labels (use before creating issues to find exact label names)
gh label list --repo bitrix24/b24phpsdk

# Create issue
gh issue create --repo bitrix24/b24phpsdk --title "..." --label "..." --body "..."

# Create PR — body MUST follow .github/PULL_REQUEST_TEMPLATE.md (read it first!)
# cat .github/PULL_REQUEST_TEMPLATE.md
gh pr create --repo bitrix24/b24phpsdk --title "..." --body "$(cat <<'EOF'
<filled-in template content>
EOF
)" --base <branch>
```

---

## Creating a new issue

Before creating, search via `mcp__github__search_issues` (or `gh search issues` fallback) to make sure a similar issue does not already exist.

Before applying a label, run `gh label list --repo bitrix24/b24phpsdk` to verify the exact label name exists in the repository.

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

## Discovering unsupported API methods and filing issues

Use this workflow when the user wants to find Bitrix24 REST API methods that are not yet
supported by the SDK and create tracking issues for them.

### Step 1 — Choose the API version

Use `AskUserQuestion` to ask which API version to audit:

```
question: "Which API version do you want to audit for unsupported methods?"
header: "API version to audit"
options:
  - label: "v3"
    description: "REST API v3 — modern endpoints (tasks.task.*, catalog.*, etc.)"
  - label: "v1"
    description: "REST API v1 — legacy endpoints"
```

### Step 2 — Optionally narrow by scope or method pattern

Use `AskUserQuestion` to ask whether to filter:

```
question: "Do you want to limit the search to a specific scope or method pattern?"
header: "Scope filter"
options:
  - label: "All scopes"
    description: "Analyse all available methods for the chosen API version"
  - label: "Specific scope"
    description: "Filter by scope name, e.g. tasks, crm, calendar"
  - label: "Specific methods"
    description: "Filter by method pattern, e.g. tasks.task.file.*"
```

If **Specific scope** or **Specific methods** is chosen, ask for the exact value(s) via a follow-up `AskUserQuestion`.

### Step 3 — Discover methods from Bitrix24 documentation

Use `mcp__bitrix24__bitrix-search` to find all REST methods matching the scope or pattern.

For each method found, call `mcp__bitrix24__bitrix-method-details` to verify:
- exact method name
- API version (v1 or v3)
- whether it is deprecated (skip deprecated methods)

Collect the confirmed list as **«API methods from docs»**.

### Step 4 — Find what the SDK already supports

Use Grep to scan `src/Services/` for `ApiEndpointMetadata` attributes:

```
pattern: ApiEndpointMetadata
path: src/Services/
glob: *.php
```

Extract the first string argument from each `#[ApiEndpointMetadata('method.name', ...)]` — that is the REST method name.
Collect as **«SDK-supported methods»**.

### Step 5 — Compute and present the gap

```
Unsupported = «API methods from docs» − «SDK-supported methods»
```

Present the numbered list to the user before doing anything else:

```
Found N unsupported methods:
1. scope.entity.action
2. scope.entity.otheraction
...
```

### Step 6 — User confirmation

Use `AskUserQuestion`:

```
question: "Which methods should I create issues for?"
header: "Issue creation scope"
options:
  - label: "All N unsupported methods"
    description: "Create one issue per method"
  - label: "Let me choose"
    description: "I will list the method names I want"
```

If **Let me choose**, ask the user for the list explicitly before proceeding.

### Step 7 — Check for existing issues

For each confirmed method, call `mcp__github__search_issues` to avoid duplicates:

```
q: "<method.name> in:title repo:bitrix24/b24phpsdk is:open"
```

If `mcp__github__search_issues` is unavailable, use the Bash fallback:

```bash
gh search issues "<method.name>" --repo bitrix24/b24phpsdk --state open
```

Skip methods that already have an open issue. Report skipped ones to the user.

### Step 8 — Create issues

For each method without an existing issue, create via `mcp__github__create_issue`:

```
owner:  bitrix24
repo:   b24phpsdk
labels: ["enhancement"]
title:  Add support for <method.name>
body: (see template below)
```

Issue body template:

```markdown
## Problem

The Bitrix24 REST API method `<method.name>` is not yet supported by the SDK.

## Proposed solution

Add a service method that wraps `<method.name>` following the existing patterns
in `src/Services/<Scope>/Service/`.

## Acceptance criteria

- [ ] Service class implements `<method.name>` with correct parameter mapping
- [ ] Result item `@property-read` annotations cover all response fields
- [ ] Unit test passes (`make test-unit`)
- [ ] Integration test passes, including annotation and type-cast checks
- [ ] `CHANGELOG.md` is updated with an issue link
```

After all issues are created, report the list of created issue URLs to the user.

---

## Project conventions when implementing an issue

### Branch naming

```
feature/<issue-number>-<short-slug>   # new functionality
bugfix/<issue-number>-<short-slug>    # bug fix
```

Example: `feature/397-add-task-chat-fields`

### Commit message format

Imperative verb, describe what was added or fixed, reference the issue number at the end:

```
Add <ClassName> service for `<scope>.<entity>.*` support (#NNN)
Fix <what was broken> in <component> (#NNN)
```

Examples:
- `Add FileField service for tasks.task.file.field.* support (#398)`
- `Fix pagination offset in DealsResult (#412)`

Rules:
- Start with a capital letter
- No period at the end
- Maximum 72 characters
- Do not use conventional commits prefix (`feat:`, `fix:`) — this project does not use that format

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

## Implementing placements for a scope

Use this section when the user asks to add support for Bitrix24 **widget placement codes**
(see `placement.list`), typed bind/unbind helpers, and `OPTIONS` payload builders within a
scope (e.g. IM, CRM, Tasks, Calendar, Sonet).

The full playbook is intentionally split out to keep `SKILL.md` compact. Read
`placements.md` in this same folder before implementation.

That supporting guide covers:

- delivery order from `placement.list` research to final verification
- directory layout and naming rules for `PlacementLocationCodes`, `Placements`, and
  `<ScopePrefix>*PlacementOptions`
- typed localization payloads via `PlacementLangItem`, `PlacementLangMap`, and shared
  `Core\Contracts\LangCodes`
- service-builder registration, docs-link requirements, and deprecation handling
- mandatory unit and integration tests for codes, options, localization DTOs, and the typed
  facade

---

## Task folder and implementation plan

**Rule**: before writing any code, create a dedicated folder and a plan file for the issue.

> **Never** create plan files under `docs/plans/`. All plan files MUST be placed inside the task folder at `.tasks/<issue-number>/plan.md`.

### 1. Create the task folder

```
.tasks/<issue-number>/
```

Example: `.tasks/397/`

### 2. Write the plan

Create `.tasks/<issue-number>/plan.md` **before starting implementation**.
Think through the full scope of the issue and write the plan first — only start coding once the plan is agreed upon.

**Language rule**: all `plan.md` files MUST be written in **English**, regardless of the language used in conversation.

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

### 4. `CHANGELOG.md`

<Exact line to add under `## X.Y.Z Unreleased` → `### Added / Fixed / Changed`>

---

## Deptrac compliance

<Confirm which layers the new code depends on and that no new violations are introduced>

---

## Verification

\`\`\`bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-<scope>
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

### Step 2 — Expand context from Bitrix24 official documentation

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
3. Note which API version the method belongs to (v1 or v3) — this informs the base branch choice

Record findings in the **Context** section of `plan.md` so the plan is grounded in actual API behaviour, not assumptions.

### Service method date/time arguments

When adding or changing service methods, any argument that represents a date or date-time
value must be typed as `CarbonImmutable` in the public SDK method signature. Convert it to
the Bitrix24 REST payload format at the service boundary, following existing service
patterns. Do not expose raw date/time strings in service method arguments when the SDK can
accept a typed immutable date value instead.

### ApiEndpointMetadata documentation links

When adding or changing `ApiEndpointMetadata` attributes, documentation links must point to
the English Bitrix24 API documentation site under `https://apidocs.bitrix24.com/`. Do not
use localized documentation hosts for these attribute links.

### Step 3 — Determine the type

Classify the issue:

| Type | Signals |
|---|---|
| `feature` | labels `enhancement`; title starts with `Add` |
| `bugfix` | label `bug`; title starts with `Fix` |

Use `feature` if the type is ambiguous.

### Step 4 — Ask which API version

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

### Step 5 — Create the branch

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

### Step 6 — Create the task folder

```
.tasks/<issue-number>/
```

### Step 7 — Brainstorm before writing the plan

**Required**: invoke `superpowers:brainstorming` before writing the plan.

Use the issue body, API documentation gathered in Step 2, and existing SDK patterns as input.
The brainstorming output informs the Context and design decisions in `plan.md`.
Do not start writing the plan until brainstorming is complete.

### Step 8 — Write the plan draft

Create `.tasks/<issue-number>/plan.md` using the structure defined in the
**«Task folder and implementation plan»** section above.

### Step 9 — Self-review the plan, then present for approval

Before showing the plan to the user, check it against three criteria:

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
Check that `CHANGELOG.md` is listed under **Files to Modify**.
If a step depends on another that is not in the plan — add the missing step.

If any criterion fails, fix the plan first, then re-run the check.

**Required**: report the review results in this format before presenting the plan:

```
Plan review:
✓ Unambiguity — <one sentence: what was checked and result>
✓ Non-contradiction — <one sentence: what was checked and result>
✓ No gaps — <one sentence: what was checked and result>
```

Then present the plan and **wait for explicit approval** before writing any production code.

### Step 10 — Apply TDD during implementation

**Required**: invoke `superpowers:test-driven-development` after plan approval, before writing any production code.

Follow the RED-GREEN-REFACTOR cycle for each service method in the plan:
- RED: write failing unit test first
- GREEN: write minimal production code to pass it
- REFACTOR: clean up while keeping tests green

Do not write production code before having a failing test. This applies to every method in the plan, not just the first one.

---

## Post-implementation quality gate

After all files from the plan are written and the plan is marked complete,
run checks in two phases. **Do not start phase 2 until phase 1 is fully green.**

Completion invariant: every completed implementation task must run `make lint-rector`
before it is reported as finished. This applies even when a narrower check set is chosen
for a small or targeted change.

### Phase 1 — Light checks (linters + unit tests)

Run in this order:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Rules for phase 1:
- If any command fails, invoke `superpowers:systematic-debugging` before attempting a fix — diagnose root cause first, then fix.
- Do not add entries to `deptrac.yaml` → `skip_violations` to silence a new violation — fix the import instead.
- Only proceed to phase 2 when all five commands pass without errors.

### Phase 2 — Heavy checks (integration tests)

Run only after phase 1 is fully green:

```bash
make test-integration-<scope>   # the suite added for this issue
```

Rules for phase 2:
- If the suite fails, invoke `superpowers:systematic-debugging` before attempting a fix.
- Do not skip or comment out failing tests — fix the root cause.

### Phase 3 — Update CHANGELOG.md

After both phases are green, add an entry to `CHANGELOG.md` under `## X.Y.Z Unreleased`:

```markdown
### Added
- <Description of what was added> ([#NNN](https://github.com/bitrix24/b24phpsdk/issues/NNN))
```

Use `### Fixed` for bug fixes, `### Changed` for changes. Commit the CHANGELOG update together with the last implementation commit or as a separate commit:

```
Update CHANGELOG.md for #NNN
```

### Final report

Report the status to the user:
- Which commands passed on the first run.
- Which required fixes, and a one-line summary of what was fixed.
- Confirmation that both phases are green and CHANGELOG is updated.

---

## Creating a Pull Request after a green quality gate

Run this step **only after both phases of the quality gate are fully green and CHANGELOG is updated**.

### Core rules (non-negotiable)

- **Auto-create the PR yourself** via `mcp__github__create_pull_request` (or `gh pr create` as fallback). **Never** respond with a "click this URL to create the PR" message — the agent opens the PR, not the user. The URL returned by `git push` (`.../pull/new/<branch>`) is informational; do not forward it as a manual-action prompt.
- **Base branch is fixed by API version:**

    | API version | PR base branch |
    |---|---|
    | v3 | `v3-dev` |
    | v1 | `dev` |

    Never open a PR against `main`. The base must match the base branch chosen in Step 4 of the start-of-work protocol.
- **PR body comes from the repo template.** Always read `.github/PULL_REQUEST_TEMPLATE.md` fresh from disk with `cat` immediately before composing the body. Do not reuse a memorised layout; the template evolves.

**Required before starting:**
1. Invoke `superpowers:verification-before-completion` — run all quality gate commands again, capture actual output, confirm every command passes. Do not create the PR based on remembered results.
2. Read the PR template from disk: `cat .github/PULL_REQUEST_TEMPLATE.md` — the PR body MUST follow this template. Do not use a memorised or hardcoded structure.

### Step 1 — Push the branch

```bash
git push -u origin <branch-name>
```

### Step 2 — Determine the assignee

Call `mcp__github__list_commits` on the feature branch to find the author of the most recent commit:

```
owner: bitrix24
repo:  b24phpsdk
sha:   <branch-name>
per_page: 1
```

Use the `author.login` from the first returned commit as the assignee.
If the call fails or returns no commits, omit the `assignees` field — do not guess.

### Step 3 — Find the nearest open milestone

Determine the milestone prefix from the base branch chosen in Step 4 of the start-of-work protocol:

| Base branch | Milestone prefix |
|---|---|
| `v3-dev` | `3.*` |
| `dev` | `1.*` |

Fetch open milestones via the GitHub REST API:

```
GET /repos/bitrix24/b24phpsdk/milestones?state=open&sort=due_on&direction=asc
```

From the returned list, pick the milestone whose `title` starts with the prefix (e.g. `3.` or `1.`)
and has the **nearest due date** (first in the sorted list after filtering).
If no matching milestone exists, omit the `milestone` field.

### Step 4 — Create the Pull Request

**Before composing the PR body**, read the template fresh from disk:

```bash
cat .github/PULL_REQUEST_TEMPLATE.md
```

Use its exact structure as the PR body. Fill in every placeholder and replace every
comment block with real content derived from the implementation and the issue.
Do NOT use a memorised or hardcoded body structure — always re-read the file.

After filling in the template, append the quality gate results and the issue closing keyword:

```
## Test plan

- [x] `make lint-cs-fixer` — passed
- [x] `make lint-rector` — passed
- [x] `make lint-phpstan` — passed
- [x] `make lint-deptrac` — passed
- [x] `make test-unit` — passed
- [x] `make test-integration-<scope>` — passed

Closes #<issue-number>

🤖 Generated with [Claude Code](https://claude.ai/claude-code)
```

**Why `Closes #NNN` outside the table**: GitHub only activates automatic issue linking and
the "Linked issues" sidebar when the closing keyword appears as plain text in the body —
not inside Markdown tables, code blocks, or HTML comments.

Use `mcp__github__create_pull_request` (preferred) or `gh pr create` with the following parameters:

```
owner:     bitrix24
repo:      b24phpsdk
title:     <issue title, max 72 characters>
head:      <branch-name>
base:      <base-branch>   # v3-dev or dev — same as when the branch was created
body:      <filled-in template + quality gate results + Closes #NNN>
assignees: [<author login from step 2>]
milestone: <milestone number from step 3>
```

### Step 5 — Return the PR URL

After the PR is created, output the PR URL so the user can open it directly.

### Step 6 — Poll the PR status until CI completes

Right after the PR is created (or after any subsequent `git push` to an existing PR
branch), poll the PR status via MCP until all required checks finish. Do **not** declare
the PR "pushed" or "updated" until CI has reported back — a green local quality gate does
not guarantee green CI.

Call `mcp__github__get_pull_request_status`:

```
owner: bitrix24
repo:  b24phpsdk
pullNumber: <PR number>
```

Interpret the response:

| `state` | Meaning | Action |
|---|---|---|
| `pending` | One or more checks still running | Wait, then poll again |
| `success` | All required checks passed | Report success to the user |
| `failure` / `error` | At least one required check failed | Fetch the failing run's logs, diagnose, fix, push again, and restart polling |

**Polling cadence**: wait ~60 seconds between polls. Do not spam the API.

**If MCP is unavailable**, use the `gh` fallback:

```bash
gh pr checks <PR number> --repo bitrix24/b24phpsdk --watch
```

### Step 7 — Report the final CI state to the user

Once polling terminates, report one of:

- ✅ All checks green — include the PR URL and the list of passed checks.
- ❌ Failing checks — include the PR URL, the names of the failed jobs, and a one-line
  summary of what the failure indicates. Do not auto-merge or mark the work "done" in
  this state.

---

## Pushing to an existing PR branch

Any `git push` to a branch that already has an open PR MUST be followed by a PR status
poll, using the same procedure as **Step 6** of the PR creation workflow above.

**Rule**: after `git push origin <branch>`:

1. Resolve the PR number for the branch (e.g. via `gh pr view <branch> --json number` or
   `mcp__github__list_pull_requests` filtered by `head`).
2. Call `mcp__github__get_pull_request_status` with that PR number.
3. Poll until `state` is no longer `pending`.
4. Report the final state to the user — do not consider the push "done" until CI has
   reported back.

---

## Finishing a development branch

When `superpowers:finishing-a-development-branch` is invoked and presents the 4 options,
**always select option 2 — Push and create Pull Request** without asking the user to choose.

Do not display the list of options and do not prompt for a choice — proceed directly to
pushing the branch and creating the PR following the steps in the
**«Creating a Pull Request after a green quality gate»** section above.
