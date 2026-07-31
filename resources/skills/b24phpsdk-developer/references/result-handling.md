# Result Handling

## Prefer SDK Result Accessors

Use SDK result wrappers and accessors in product code:

```php
$result = $serviceBuilder
    ->getCRMScope()
    ->contact()
    ->list(['ID' => 'ASC'], [], ['ID', 'NAME', 'LAST_NAME'], 0);

$contacts = $result->getContacts();
```

Avoid raw response traversal for normal product logic:

```php
$raw = $result->getCoreResponse()->getResponseData()->getResult();
```

Raw core responses are acceptable for diagnostics, temporary compatibility checks, logging metadata,
or investigation of an SDK feature gap. Keep those uses isolated and remove them once a typed SDK API
covers the need.

## Response Shapes

The SDK hides most REST response envelope differences, but consumers still need to know the impact:

- Legacy CRM v1 list methods such as `crm.contact.list` return a flat `result` array from REST and
  SDK result objects expose typed item collections such as `getContacts()`.
- REST v3 list methods often return data under `result.items`; SDK service result wrappers expose
  the relevant item accessors for that service.
- Single-item methods may return `result`, `result.item`, or a service-specific key. Prefer the SDK
  result method instead of encoding these details into product code.

## Pagination

`list()` methods read a page, not necessarily the full dataset. The final integer argument on many
legacy CRM list services is the start offset. Use it deliberately when reading a specific page.

For full scans, prefer the service's batch helper when it exists:

```php
foreach ($serviceBuilder->getCRMScope()->contact()->batch->list(
    ['ID' => 'ASC'],
    [],
    ['ID', 'NAME', 'LAST_NAME'],
    500,
) as $contact) {
    // Process ContactItemResult.
}
```

Batch reads reduce boilerplate for multi-page traversal, but they still call Bitrix24 REST under the
hood. Keep limits bounded and design processing to resume safely after partial failure.

## Batch Trade-Offs

Use batch helpers for large read workloads or grouped operations that the SDK already supports. Do
not use batch automatically for every call:

- Batch requests make error handling less local because one batch can contain several operations.
- Large writes must be idempotent or have a clear reconciliation strategy.
- Reads should still select only fields the product needs.
