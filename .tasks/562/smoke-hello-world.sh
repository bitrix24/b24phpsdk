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
