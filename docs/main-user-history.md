# REST v3 user history

Access these read-only services through `$serviceBuilder->getMainScope()`:

| Accessor | REST methods |
|---|---|
| `userHistory()` | `main.user.history.list`, `main.user.history.tail` |
| `userHistoryChange()` | `main.user.history.fields.list` |
| `userHistoryField()` | `main.user.history.field.get`, `main.user.history.field.list` |
| `userHistoryChangeField()` | `main.user.history.fields.field.get`, `main.user.history.fields.field.list` |

These services send requests using `ApiVersion::v3`. Availability depends on the portal and the calling user's permissions. The schema baseline is [`open-api/openapi.json`](open-api/openapi.json). The official [REST v3 overview](https://apidocs.bitrix24.com/api-reference/rest-v3.html) documents transport and filtering; method-specific history documentation was not available when this implementation was verified on 2026-09-30.

## Read history and changes

```php
use Bitrix24\SDK\Core\Contracts\SortOrder;
use Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistorySelectBuilder;

$main = $serviceBuilder->getMainScope();
$userId = 7; // An existing user whose history you are permitted to read.

$history = $main->userHistory()->list(
    userId: $userId,
    select: (new UserHistorySelectBuilder())->dateInsert()->eventType()->field(),
    filter: [['id', '>', 0]],
    order: ['id' => SortOrder::Descending],
    pagination: ['limit' => 20, 'offset' => 0],
)->getUserHistoryItems();

foreach ($history as $entry) {
    // Generated select builders always include id.
    $changes = $main->userHistoryChange()->list((int)$entry->id)->getUserHistoryChanges();
    foreach ($changes as $change) {
        $fieldCode = $change->field;
        $payload = $change->data; // Preserved as returned; values need not be strings.
    }
}
```

`list()` and `tail()` require a positive user ID. `userHistoryChange()->list()` requires a positive history ID. The SDK adds the mandatory `userId` or `historyId` equality filter and combines additional filters using AND, including nested OR groups. Omitting a required identifier is a PHP argument error; zero and negative IDs raise the SDK's `InvalidArgumentException` before an API call.

Both list methods accept array selections or their generated SelectBuilder, array filters or `FilterBuilderInterface`, order directions as `SortOrder` or API strings, and page/limit/offset pagination. Tail uses a cursor instead of list pagination.

Selected history timestamps become `CarbonImmutable`; event types remain integers without an undocumented enum. Unselected properties return null. The history `field` property may be absent from a default response: explicitly select it when needed. Change `data` is `mixed`, so check its shape before interpreting `before` or `after`.

## Incremental reads

```php
use Bitrix24\SDK\Services\Main\UserHistory\Service\UserHistoryTailCursor;

// Restore a previously saved checkpoint here; zero starts from the beginning.
$checkpoint = new UserHistoryTailCursor(value: 0);
do {
    $page = $main->userHistory()->tail($userId, $checkpoint);
    foreach ($page->getUserHistoryItems() as $entry) {
        // Process each entry. Persist a new checkpoint only after processing succeeds.
    }

    $cursor = $page->getCursor();
    if ($cursor === null || $cursor['value'] === null) {
        break; // Retain the last checkpoint for the next poll.
    }

    $checkpoint = new UserHistoryTailCursor($cursor['value'], $cursor['field']);
    // Persist $checkpoint->toArray() here, using the validated integer request value.
} while ($page->hasMore());
```

`getCursor()` preserves numeric strings, integer values, and null. An empty response can contain `['field' => 'id', 'value' => null]`; do not turn that value into zero or discard your last checkpoint. Request cursors safely normalize decimal ID strings to integers because the API rejects string ID request values. They reject null, invalid ID positions, and values exceeding PHP_INT_MAX instead of truncating them. The default order is ascending; a request may explicitly use `SortOrder::Descending`. Preserve the chosen order when continuing that traversal because response cursors contain only field and value.

## Field metadata

```php
$historyFields = $main->userHistory()->fields()->getFieldsDescription();
$changeFields = $main->userHistoryChange()->fields()->getFieldsDescription();

$dateMetadata = $main->userHistoryField()->get('dateInsert')->getUserHistoryField();
$dataMetadata = $main->userHistoryChangeField()->get('data')->getUserHistoryChangeField();

$selected = $main->userHistoryField()->list(['name', 'type'])->getFieldsDescription();
```

Entity `fields()` methods delegate to their separate metadata services. `getFieldsDescription()` returns original API descriptors keyed by name; include `name` when selecting descriptor properties, otherwise this accessor raises `BaseException`. Typed descriptor getters allow omitted properties and null descriptions.

Live metadata reports both `dateInsert` and change `data` as `object`. The schema refines `dateInsert` to a date-time string and leaves `data` unconstrained. The SDK preserves the metadata while exposing the entity timestamp as `CarbonImmutable|null` and change payload as `mixed`. Dedicated annotation tests explicitly account for these two differences.

## Validation

Run `make test-integration-main-user-history` with a configured test webhook. Tests use read-only calls for the current webhook user and a dedicated NullLogger regardless of the global test log level. They report skips if no history exists or no associated changes are found among the latest 20 entries; empty data is not proof of populated-result behavior.
