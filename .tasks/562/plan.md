# Plan: Add Bitrix24 SDK Developer Skill (issue #562)

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:test-driven-development for
> production-code changes and use superpowers:writing-skills for the skill-documentation
> RED-GREEN-REFACTOR loop. Track this plan with checkbox syntax.

**Goal:** add a distributable `b24phpsdk-developer` skill that teaches product teams how to use
`bitrix24/b24phpsdk` correctly in their own applications.

**Architecture:** keep the canonical distributable skill under `resources/skills/` and expose it
through the `llm/skills` Composer donor contract. Add small repo-local loader skills for Claude Code
and Codex so the same canonical guidance is available while working in this repository without
accidentally distributing the maintainer workflow skill.

**Tech Stack:** Markdown skill files, Composer package metadata, `llm/skills`, Claude Code project
skills, Codex project skills, PHP CLI smoke project, `bitrix24/b24phpsdk`.

---

## Context

Issue #562 requests a separate Bitrix24 SDK developer skill for product application developers.
The existing `.claude/skills/b24phpsdk-maintainer` skill remains scoped to SDK repository
maintenance: GitHub issues, PRs, release workflow, REST method research, changelog rules, and SDK
internals. The new skill must not replace or be distributed together with that maintainer workflow.

This issue does not add SDK service PHP classes, result items, select builders, or item builders.
The SDK code generators listed in `.claude/skills/b24phpsdk-maintainer/SKILL.md` do not apply.

`llm/skills` distribution findings from https://github.com/roxblnfk/skills:

- A Composer donor package declares a skill source with `composer.json`:

```json
{
  "extra": {
    "skills": { "source": "resources/skills" }
  }
}
```

- `composer skills:update` copies immediate skill directories from the donor source into the
  consumer target, defaulting to `.agents/skills`.
- Consumer `skills.json` can mirror the target to Claude Code with aliases such as
  `.claude/skills`.
- A direct Composer dependency is trusted by `llm/skills`, and an explicitly named package on
  `composer skills:update bitrix24/b24phpsdk` is also treated as trusted for that run.
- Alias creation is non-destructive; a real existing `.claude/skills` directory cannot be replaced
  by an alias. This repository therefore needs explicit local Claude/Codex loader skills instead of
  relying on an alias inside the SDK repository itself.

---

## Files to Create

### 1. `resources/skills/b24phpsdk-developer/SKILL.md`

Canonical distributable skill. It must include:

- YAML frontmatter:

```yaml
---
name: b24phpsdk-developer
description: Use when writing product application code with bitrix24/b24phpsdk, integrating Bitrix24 webhooks or OAuth, choosing SDK service calls, handling SDK results, errors, pagination, batch calls, or tests outside the SDK repository
---
```

- Scope boundaries:
  - use for product code built on top of the SDK
  - do not use for SDK repository issue/PR/release workflow
  - do not use for adding SDK internals or generated REST service wrappers
- Quick workflow:
  1. Inspect installed SDK version and available public API.
  2. Prefer `ServiceBuilderFactory` and public service builders over direct REST calls.
  3. Choose webhook initialization for backend integrations with incoming webhooks, OAuth only when
     the product owns app installation/token refresh.
  4. Keep Bitrix24 credentials in environment/config, never inline.
  5. Handle `BaseException` and `TransportException` at the application boundary.
  6. Use SDK result objects and documented accessors instead of indexing raw response arrays first.
  7. Use pagination/batch helpers deliberately; do not assume one REST call reads all records.
  8. Test product logic with fakes around the SDK boundary and keep live Bitrix24 checks as
     explicit integration tests.
- Reference routing table for the files below.

### 2. `resources/skills/b24phpsdk-developer/references/product-integration.md`

Reference for product integration patterns:

- Composer installation and version inspection.
- `ServiceBuilderFactory::initFromWebhook($webhookUrl)` usage.
- `ServiceBuilderFactory`/OAuth boundary guidance when available in the installed SDK version.
- Dependency injection recommendations: construct SDK services at the infrastructure edge, pass
  product-owned interfaces into domain code.
- Read-only CRM contact list hello-world example:

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Bitrix24\SDK\Services\ServiceBuilderFactory;

$webhookUrl = getenv('BITRIX24_WEBHOOK');
if ($webhookUrl === false || $webhookUrl === '') {
    fwrite(STDERR, "BITRIX24_WEBHOOK is required\n");
    exit(1);
}

$serviceBuilder = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhookUrl);
$contacts = $serviceBuilder
    ->getCRMScope()
    ->contact()
    ->list(['ID' => 'ASC'], [], ['ID', 'NAME', 'LAST_NAME'], 0)
    ->getContacts();

echo json_encode(
    [
        'count' => count($contacts),
        'items' => array_map(
            static fn ($contact): array => [
                'id' => $contact->ID,
                'name' => trim(sprintf('%s %s', $contact->NAME ?? '', $contact->LAST_NAME ?? '')),
            ],
            $contacts,
        ),
    ],
    JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT,
) . PHP_EOL;
```

### 3. `resources/skills/b24phpsdk-developer/references/result-handling.md`

Reference for SDK result handling:

- Prefer typed SDK result wrappers and accessors (`getContacts()`, entity result accessors, field
  result helpers) over raw `getCoreResponse()` traversal in product code.
- Use raw core responses only for diagnostics, feature gaps, logging, or temporary compatibility
  checks.
- Explain CRM v1 list shape versus REST v3 `result.items` shape at a consumer level.
- Explain pagination and batch trade-offs at a consumer level.

### 4. `resources/skills/b24phpsdk-developer/references/error-handling-and-testing.md`

Reference for error handling and tests:

- Catch SDK exceptions at application boundaries.
- Log method/scope/context without leaking webhook tokens or OAuth tokens.
- Keep retry policy outside random call sites; retry only idempotent reads or explicitly safe writes.
- Unit-test product code with adapters/fakes around SDK calls.
- Keep live Bitrix24 tests opt-in and driven by `BITRIX24_WEBHOOK`.

### 5. `.agents/skills/b24phpsdk-developer/SKILL.md`

Codex project-skill loader for this repository. It must share the same `name` and trigger-oriented
description as the canonical skill, then instruct the agent to read:

```text
resources/skills/b24phpsdk-developer/SKILL.md
```

The loader must not duplicate the long content; canonical content remains in `resources/skills/`.

### 6. `.claude/skills/b24phpsdk-developer/SKILL.md`

Claude Code project-skill loader for this repository. It must share the same `name` and
trigger-oriented description as the canonical skill, then instruct Claude Code to read:

```text
resources/skills/b24phpsdk-developer/SKILL.md
```

The loader must coexist with `.claude/skills/b24phpsdk-maintainer/`.

### 7. `.tasks/562/skill-pressure-scenarios.md`

Documentation-TDD test scenarios for the skill:

- Scenario A: "Create a PHP CLI script in a product app that reads Bitrix24 contacts using
  bitrix24/b24phpsdk."
- Scenario B: "Add error handling and logging around a Bitrix24 SDK read call without leaking
  credentials."
- Scenario C: "Write product tests for code that uses b24phpsdk without hitting live Bitrix24 in
  unit tests."

For each scenario, record baseline behavior before the new skill exists, expected behavior after
the skill exists, and any rationalizations or gaps found during testing.

### 8. `.tasks/562/smoke-hello-world.sh`

Executable smoke-test script for the empty-project verification. It must:

```bash
#!/usr/bin/env bash
set -euo pipefail

: "${BITRIX24_WEBHOOK:?Set BITRIX24_WEBHOOK to an incoming webhook with crm scope}"

SDK_REPO="${SDK_REPO:-/Users/mesilov/work/Bitrix24/b24phpsdk/.worktrees/feature-562-add-bitrix24-sdk-developer-skill}"
WORKDIR="$(mktemp -d "${TMPDIR:-/tmp}/b24phpsdk-skill-smoke.XXXXXX")"
if [[ "${KEEP_SMOKE_WORKDIR:-0}" == "1" ]]; then
    printf 'Keeping smoke workdir: %s\n' "$WORKDIR"
else
    trap 'rm -rf "$WORKDIR"' EXIT
fi

run_php_cli() {
    docker compose run --rm -T \
        --user "$(id -u):$(id -g)" \
        -e BITRIX24_WEBHOOK \
        -v "$WORKDIR:/workspace" \
        -v "$SDK_REPO:/sdk" \
        -w /workspace \
        php-cli "$@"
}

run_php_cli composer init --no-interaction --name=bitrix24/sdk-skill-smoke --type=project
run_php_cli composer config allow-plugins.llm/skills true
run_php_cli composer config minimum-stability dev
run_php_cli composer config prefer-stable true
run_php_cli composer config repositories.b24phpsdk '{"type":"path","url":"/sdk","options":{"symlink":false}}'

cat > "$WORKDIR/skills.json" <<'JSON'
{
  "$schema": "https://raw.githubusercontent.com/roxblnfk/skills/master/resources/skills.schema.json",
  "target": ".agents/skills",
  "aliases": [".claude/skills"],
  "auto-sync": true
}
JSON

run_php_cli composer require --no-interaction --dev llm/skills
run_php_cli composer require --no-interaction bitrix24/b24phpsdk:@dev
run_php_cli composer skills:update bitrix24/b24phpsdk

test -f "$WORKDIR/.agents/skills/b24phpsdk-developer/SKILL.md" || {
    printf 'Missing Codex skill in %s\n' "$WORKDIR/.agents/skills" >&2
    exit 1
}
run_php_cli sh -lc 'test -f .claude/skills/b24phpsdk-developer/SKILL.md' || {
    printf 'Missing Claude Code skill via alias in %s\n' "$WORKDIR/.claude/skills" >&2
    exit 1
}

cat > "$WORKDIR/hello-world.php" <<'PHP'
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Bitrix24\SDK\Services\ServiceBuilderFactory;

$webhookUrl = getenv('BITRIX24_WEBHOOK');
if ($webhookUrl === false || $webhookUrl === '') {
    fwrite(STDERR, "BITRIX24_WEBHOOK is required\n");
    exit(1);
}

$contacts = ServiceBuilderFactory::createServiceBuilderFromWebhook($webhookUrl)
    ->getCRMScope()
    ->contact()
    ->list(['ID' => 'ASC'], [], ['ID', 'NAME', 'LAST_NAME'], 0)
    ->getContacts();

echo json_encode(['count' => count($contacts)], JSON_THROW_ON_ERROR) . PHP_EOL;
PHP

run_php_cli php hello-world.php
```

---

## Files to Modify

### 1. `composer.json`

Add donor-side `llm/skills` metadata:

```json
"extra": {
  "skills": {
    "source": "resources/skills"
  }
}
```

Do not add `llm/skills` as an SDK runtime or dev dependency. Product consumers install the plugin
in their own project; the SDK only declares where its distributable skills live.

### 2. `CHANGELOG.md`

Under `## Unreleased` -> `### Changed`, add:

```markdown
- Updated `b24phpsdk-developer` skill: added distributable SDK consumer guidance for product
  application developers, with Claude Code and Codex project loaders plus `llm/skills` Composer
  metadata
  ([#562](https://github.com/bitrix24/b24phpsdk/issues/562))
```

If any `.claude/skills/**/*.md` or `.agents/skills/**/*.md` file is created or modified, this
CHANGELOG entry is mandatory under repository rules.

### 3. GitHub issue #562

After this plan is written, update the issue body with a concise "Implementation plan summary"
section that names:

- canonical skill source path
- Claude Code loader path
- Codex loader path
- Composer `extra.skills.source`
- empty-project hello-world smoke test

---

## Deptrac Compliance

No PHP SDK source classes are added or modified. Deptrac should remain unaffected because the
change is Markdown documentation, Composer metadata, and task verification scripts.

Still run `make lint-deptrac` as part of the required issue quality gate.

---

## Verification

Run these commands from the issue worktree:

```bash
make oa-schema-build
python3 /Users/mesilov/.codex/skills/.system/skill-creator/scripts/quick_validate.py resources/skills/b24phpsdk-developer
make composer "validate --strict"
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
BITRIX24_WEBHOOK=<webhook-with-crm-scope> .tasks/562/smoke-hello-world.sh
```

Expected results:

- `quick_validate.py` exits 0 for the canonical distributable skill.
- Composer validation exits 0 after adding `extra.skills.source`.
- All four lint targets and `make test-unit` exit 0.
- The smoke script creates a temporary empty Composer project, installs the SDK from this checkout,
  installs `llm/skills`, runs `composer skills:update bitrix24/b24phpsdk`, verifies both
  `.agents/skills/b24phpsdk-developer/SKILL.md` and `.claude/skills/b24phpsdk-developer/SKILL.md`,
  then runs `hello-world.php` and prints a JSON object with a `count` key.

---

## Plan Review

✓ Unambiguity — file paths, distribution source, loader responsibilities, and smoke-test commands
are explicit.

✓ Non-contradiction — the distributable source is `resources/skills`, while local Claude/Codex
loaders only point to that canonical source and do not change maintainer-skill distribution.

✓ No gaps — the plan covers issue requirements, `llm/skills` distribution, Claude Code/Codex pickup,
CHANGELOG, validation, local quality gates, and the empty-project contact-reading smoke test.
