---
name: b24phpsdk-developer
description: Use when writing product application code with bitrix24/b24phpsdk, integrating Bitrix24 webhooks or OAuth, choosing SDK service calls, handling SDK results, errors, pagination, batch calls, or tests outside the SDK repository
---

# Bitrix24 PHP SDK Developer

## Scope

Use this skill for product applications that consume `bitrix24/b24phpsdk`.

Do not use it for SDK repository maintenance: GitHub issue triage, release work, changelog policy,
OpenAPI coverage, generated SDK service wrappers, result-item annotation tests, or other SDK
internals. In the SDK repository, those tasks belong to `b24phpsdk-maintainer`.

## Workflow

1. Inspect the installed package version and public API first: read `composer.lock`, run
   `composer show bitrix24/b24phpsdk`, and search `vendor/bitrix24/b24phpsdk/src/Services` before
   writing integration code.
2. Prefer `ServiceBuilderFactory` and public service builders over direct REST calls.
3. Use incoming webhook initialization for server-side integrations that can rely on a fixed portal
   webhook. Use OAuth only when the product owns Bitrix24 app installation, token storage, and token
   refresh.
4. Keep webhook URLs, OAuth tokens, client secrets, and portal domains in environment or application
   configuration. Never inline them in code, docs, test fixtures, or logs.
5. Catch SDK exceptions at the application boundary. Do not scatter low-level SDK error handling
   through domain code.
6. Prefer SDK result objects and accessors such as `getContacts()` over raw response-array indexing.
7. Treat list calls as paginated. Use batch helpers deliberately when reading many records.
8. Test product logic with fakes around your own SDK boundary. Keep live Bitrix24 checks explicit,
   opt-in, and driven by `BITRIX24_WEBHOOK`.

## Reference Routing

Read only the reference or references that match the task:

| Task | Reference |
| --- | --- |
| Install the SDK, initialize webhook/OAuth access, or build a hello-world script | `references/product-integration.md` |
| Work with result objects, raw responses, pagination, or batch reads | `references/result-handling.md` |
| Add error handling, logging, retry policy, or tests around SDK usage | `references/error-handling-and-testing.md` |

## Product Boundary Pattern

Keep SDK-specific code in an infrastructure adapter. Domain and application services should depend
on product-owned interfaces, not on SDK service classes directly. This makes unit tests cheap and
keeps credential, pagination, and exception policy in one place.

```php
interface ContactDirectory
{
    /**
     * @return list<array{id: int, name: string}>
     */
    public function recentContacts(int $limit): array;
}
```

Implement the interface with the SDK in infrastructure code, then fake the interface in unit tests.

## Common Mistakes

- Calling REST endpoints directly before checking whether a typed SDK service already exists.
- Indexing into `getCoreResponse()->getResponseData()->getResult()` for normal product logic.
- Assuming `list()` returns every record in one request.
- Logging full webhook URLs or OAuth tokens.
- Letting domain services construct `ServiceBuilderFactory` or know about Bitrix24 credentials.
- Running live Bitrix24 calls in unit tests.
