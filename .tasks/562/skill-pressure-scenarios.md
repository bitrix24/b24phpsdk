# Skill Pressure Scenarios for issue #562

These scenarios drive documentation-TDD for `b24phpsdk-developer`. Baseline behavior was inspected
before adding the new skill files: the repository only exposed `.claude/skills/b24phpsdk-maintainer`
and had no `.agents/skills` or `resources/skills` consumer skill source.

## Scenario A: Create a PHP CLI script that reads Bitrix24 contacts

Prompt:

```text
Create a PHP CLI script in a product app that reads Bitrix24 contacts using bitrix24/b24phpsdk.
```

Baseline risk before the skill:

- The agent may call REST directly with `curl` or an HTTP client instead of using SDK service
  builders.
- The agent may inline a webhook URL or omit environment-based credential handling.
- The agent may guess the contact list signature instead of inspecting the installed SDK.
- The agent may index raw response arrays instead of using `getContacts()`.

Expected behavior after the skill:

- Inspect installed `bitrix24/b24phpsdk` version and public service API.
- Initialize with `ServiceBuilderFactory::createServiceBuilderFromWebhook($webhookUrl)`.
- Read `BITRIX24_WEBHOOK` from environment.
- Call `getCRMScope()->contact()->list(...)->getContacts()`.

## Scenario B: Add error handling and logging around a Bitrix24 SDK read

Prompt:

```text
Add error handling and logging around a Bitrix24 SDK read call without leaking credentials.
```

Baseline risk before the skill:

- The agent may catch broad `Throwable` at random call sites.
- The agent may log full request URLs, webhook tokens, OAuth tokens, or raw payloads.
- The agent may add retries to every failure, including authorization or validation failures.

Expected behavior after the skill:

- Catch `TransportException` and `BaseException` at the product infrastructure boundary.
- Log method/scope and safe operational context only.
- Keep retry policy centralized and limited to idempotent reads or explicitly safe writes.

## Scenario C: Write product tests for code that uses b24phpsdk

Prompt:

```text
Write product tests for code that uses b24phpsdk without hitting live Bitrix24 in unit tests.
```

Baseline risk before the skill:

- The agent may run live Bitrix24 calls from unit tests.
- The agent may mock deep SDK internals instead of defining a product-owned boundary.
- The agent may require `BITRIX24_WEBHOOK` for every test run.

Expected behavior after the skill:

- Unit-test domain/application code using fakes around product-owned interfaces.
- Keep live Bitrix24 checks as explicit integration tests.
- Skip integration tests cleanly when `BITRIX24_WEBHOOK` is not set.
