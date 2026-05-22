# Plan: Add IM\Search service for im.search.* methods (issue #431)

## Context

Issue #431 requests a new `Search` service under `src/Services/IM/Search/Service/`
for Bitrix24 IM search REST methods. API version: v3 workstream, base branch `v3-dev`.
Branch: `feature/431-add-im-search-service`.

`make oa-schema-build` was run before planning and completed successfully.

### REST documentation and live API findings

Documented by Bitrix24 MCP:

| Method | Parameters | Response shape |
|---|---|---|
| `im.search.chat.list` | `FIND`, `FIND_LINES`, `OFFSET`, `LIMIT` | `result` is a list of chat objects, with `total` and optional `next` pagination fields |
| `im.search.department.list` | `FIND`, `USER_DATA`, `OFFSET`, `LIMIT` | `result` is a list of department objects, with `total` and optional `next` pagination fields |
| `im.search.last.add` | `DIALOG_ID` | `result` is `true` or `false` |
| `im.search.last.get` | `SKIP_OPENLINES`, `SKIP_CHAT`, `SKIP_DIALOG` | `result` is a list of polymorphic legacy history items |
| `im.search.last.delete` | `DIALOG_ID` | `result` is `true` or `false` |

`im.search.user.list` is not currently found by the Bitrix24 docs index, but a live
playground call confirms the method exists and returns `result` as an object keyed by user
ID, plus `total`:

```json
{
  "result": {
    "18": {
      "id": 18,
      "name": "Test",
      "first_name": "Test",
      "last_name": null,
      "work_position": null,
      "color": "#f76187",
      "avatar": "",
      "gender": "M",
      "birthday": false,
      "extranet": false,
      "network": false,
      "bot": false,
      "connector": false,
      "external_auth_id": "socservices",
      "status": null,
      "idle": false,
      "last_activity_date": false,
      "mobile_last_date": "2024-05-23T22:56:42+03:00",
      "departments": [1],
      "absent": false
    }
  },
  "total": 1
}
```

Live playground queries also confirmed:
- `im.search.chat.list` with `FIND=Test` returns chat objects.
- `im.search.department.list` with `FIND=Отд` and `USER_DATA=Y` returns department
  objects with `manager_user_data`.
- `im.search.last.add` -> `im.search.last.get` -> `im.search.last.delete` works with
  `DIALOG_ID=1` on the playground.

### Design options considered

1. Recommended: implement a typed `Search` service with dedicated result wrappers for
   chat, user, department, and legacy last-search history. Chat/user/department list items
   get `AbstractAnnotatedItem` classes and annotation tests. Legacy last-search history
   remains an array payload because items are polymorphic (`user` or `chat`) and can have
   different keys per item.
2. Minimal: expose all methods but return mostly raw arrays. This is faster, but it does
   not satisfy the issue's item annotation acceptance criteria.
3. Skip `im.search.user.list` because docs MCP does not list it. This keeps the SDK tied
   to the current docs index, but contradicts the issue and live API behavior.

Use option 1.

---

## Files to Create

### 1. `src/Services/IM/Search/Service/Search.php`

```php
namespace Bitrix24\SDK\Services\IM\Search\Service;

#[ApiServiceMetadata(new Scope(['im']))]
class Search extends AbstractService
{
    public function chatList(
        ?string $find = null,
        ?string $findLines = null,
        ?int $offset = null,
        ?int $limit = null,
    ): SearchChatsResult;

    public function userList(
        string $find,
        ?int $offset = null,
        ?int $limit = null,
    ): SearchUsersResult;

    public function departmentList(
        string $find,
        bool $userData = false,
        ?int $offset = null,
        ?int $limit = null,
    ): SearchDepartmentsResult;

    /** Legacy endpoint: previous chat UI only; current M1 UI does not display this history. */
    public function lastAdd(string $dialogId): UpdatedItemResult;

    /** Legacy endpoint: previous chat UI only; current M1 UI does not display this history. */
    public function lastGet(
        bool $skipOpenLines = false,
        bool $skipChat = false,
        bool $skipDialog = false,
    ): SearchLastItemsResult;

    /** Legacy endpoint: previous chat UI only; current M1 UI does not display this history. */
    public function lastDelete(string $dialogId): UpdatedItemResult;
}
```

`ApiEndpointMetadata` links must use `https://apidocs.bitrix24.com/`.
For `im.search.user.list`, use the best matching English docs URL if the page becomes
available; otherwise add a code comment in the plan update explaining that docs MCP does
not expose the method while live REST does.

### 2. `src/Services/IM/Search/Result/SearchChatItemResult.php`

Annotated chat search item. Start from the live `im.search.chat.list` payload:

```php
/**
 * @property-read int $id
 * @property-read int $parent_chat_id
 * @property-read int $parent_message_id
 * @property-read string $name
 * @property-read string|null $description
 * @property-read int $owner
 * @property-read bool $extranet
 * @property-read string $avatar
 * @property-read string $color
 * @property-read string $type
 * @property-read int $counter
 * @property-read int $user_counter
 * @property-read int $message_count
 * @property-read int $unread_id
 * @property-read array $restrictions
 * @property-read int $last_message_id
 * @property-read int $last_id
 * @property-read int $marked_id
 * @property-read int $disk_folder_id
 * @property-read string $entity_type
 * @property-read string $entity_id
 * @property-read string $entity_data_1
 * @property-read string $entity_data_2
 * @property-read string $entity_data_3
 * @property-read array $mute_list
 * @property-read CarbonImmutable $date_create
 * @property-read string $message_type
 * @property-read string $public
 * @property-read string $role
 * @property-read array $entity_link
 * @property-read bool $text_field_enabled
 * @property-read int|null $background_id
 * @property-read array $permissions
 * @property-read bool $is_new
 */
class SearchChatItemResult extends AbstractAnnotatedItem
{
}
```

### 3. `src/Services/IM/Search/Result/SearchChatsResult.php`

```php
class SearchChatsResult extends AbstractResult
{
    /** @return SearchChatItemResult[] */
    public function items(): array;
    public function total(): int;
    public function next(): ?int;
}
```

### 4. `src/Services/IM/Search/Result/SearchUserItemResult.php`

Annotated user search item. Use string annotations for date-like fields that can be
returned either as ISO-8601 strings or `false`, matching existing IM user-result practice.

```php
/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $first_name
 * @property-read string|null $last_name
 * @property-read string|null $work_position
 * @property-read string $color
 * @property-read string $avatar
 * @property-read string $gender
 * @property-read string $birthday
 * @property-read bool $extranet
 * @property-read bool $network
 * @property-read bool $bot
 * @property-read bool $connector
 * @property-read string $external_auth_id
 * @property-read string|null $status
 * @property-read bool $idle
 * @property-read string $last_activity_date
 * @property-read string $mobile_last_date
 * @property-read array $departments
 * @property-read bool $absent
 * @property-read array|null $phones
 */
class SearchUserItemResult extends AbstractAnnotatedItem
{
}
```

If the first live item contains extra keys such as `avatar_hr`, `desktop_last_date`,
`bot_data`, `type`, `website`, or `email`, include them and update the annotation test
against the observed response.

### 5. `src/Services/IM/Search/Result/SearchUsersResult.php`

`im.search.user.list` returns an object keyed by user ID, so `items()` must normalize with
`array_values(array_filter(..., 'is_array'))`.

```php
class SearchUsersResult extends AbstractResult
{
    /** @return SearchUserItemResult[] */
    public function items(): array;
    public function total(): int;
}
```

### 6. `src/Services/IM/Search/Result/SearchDepartmentItemResult.php`

```php
/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $full_name
 * @property-read int $manager_user_id
 * @property-read array|null $manager_user_data
 */
class SearchDepartmentItemResult extends AbstractAnnotatedItem
{
}
```

### 7. `src/Services/IM/Search/Result/SearchDepartmentsResult.php`

```php
class SearchDepartmentsResult extends AbstractResult
{
    /** @return SearchDepartmentItemResult[] */
    public function items(): array;
    public function total(): int;
    public function next(): ?int;
}
```

### 8. `src/Services/IM/Search/Result/SearchLastItemsResult.php`

Legacy last-search history is polymorphic and may return `user` or `chat` item shapes.
Return normalized raw arrays instead of an annotated item class.

```php
class SearchLastItemsResult extends AbstractResult
{
    /** @return array<int, array<string, mixed>> */
    public function items(): array;
}
```

### 9. `tests/Unit/Services/IM/Search/Service/SearchTest.php`

Use `CoreInterface` mock tests, not only instantiation, to verify parameter mapping:
- `chatList()` sends `FIND`, `FIND_LINES`, `OFFSET`, `LIMIT` and omits nulls.
- `userList()` sends `FIND`, `OFFSET`, `LIMIT`.
- `departmentList()` maps `USER_DATA` to `Y` / `N`.
- `lastAdd()` sends `DIALOG_ID`.
- `lastGet()` maps skip flags to `Y` / `N`.
- `lastDelete()` sends `DIALOG_ID`.

### 10. `tests/Integration/Services/IM/Search/Service/SearchTest.php`

Live integration tests:
- `testChatList` uses `FIND=Test`, `LIMIT=1` and asserts `items()` is an array.
- `testUserList` uses `FIND=Maksim`, `LIMIT=1` and asserts at least one item on the
  playground; skip if no items on another portal.
- `testDepartmentList` uses `FIND=Отд`, `USER_DATA=true`, `LIMIT=3` and asserts items
  are arrays; skip if no departments on another portal.
- `testLastSearchCycle` calls `lastAdd('1')`, checks `lastGet()` returns an array, then
  calls `lastDelete('1')`. Use `finally` cleanup so the legacy entry is removed if an
  assertion fails.

### 11. Dedicated annotation tests in `tests/Integration/Services/IM/Search/Result/`

Create one file per annotated item:
- `SearchChatItemResultAnnotationsTest.php`
- `SearchUserItemResultAnnotationsTest.php`
- `SearchDepartmentItemResultAnnotationsTest.php`

Each file must use the repo convention:
- `testAllSystemFieldsAnnotated`
- `testAllSystemFieldsHasValidTypeAnnotation`

Each test fetches live metadata through the corresponding service method and skips only
when the portal has no data for the stable query.

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add `search(): Search` method following the `recent()`/`chatUser()` cache pattern and import
`Bitrix24\SDK\Services\IM\Search\Service\Search`.

### 2. `phpunit.xml.dist`

Add inside `<testsuites>` near the other IM suites:

```xml
<testsuite name="integration_tests_im_search">
    <directory>./tests/Integration/Services/IM/Search</directory>
</testsuite>
```

### 3. `Makefile`

Add help entry and target:

```makefile
.PHONY: test-integration-im-search
test-integration-im-search:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_search
```

### 4. `CHANGELOG.md`

Under `## 3.2.0 - UNRELEASED` / `## 3.2.0 – UNRELEASED` -> `### Added`:

```markdown
- Added `Bitrix24\SDK\Services\IM\Search\Service\Search` service wrapping `im.search.chat.list`, `im.search.user.list`, `im.search.department.list`, and legacy `im.search.last.*` methods, with typed search result wrappers and `IMServiceBuilder::search()` accessor ([#431](https://github.com/bitrix24/b24phpsdk/issues/431))
```

### 5. `docs/open-api/openapi.json`

Keep the snapshot generated by the required `make oa-schema-build` run if it differs from
the branch baseline.

---

## Generator-first compliance

Before manually creating annotated `*ItemResult.php` files after plan approval, attempt the
repo generator first:

```bash
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator im.search.chat.list --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator im.search.user.list --stage=all
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator im.search.department.list --stage=all
```

Expected risk: the current OpenAPI snapshot does not include IM search paths and the docs
MCP does not expose `im.search.user.list`. If the generator cannot produce the desired
classes or response envelope, keep the failure output in the task notes and manually create
the item classes from the live payloads recorded above.

Generator execution after plan approval:

```text
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator im.search.chat.list --stage=all
[ERROR] REST docs payload is required for "im.search.chat.list", but the documentation URL could not be resolved.

docker compose run --rm php-cli php bin/console b24-dev:result-item-generator im.search.user.list --stage=all
[ERROR] REST docs payload is required for "im.search.user.list", but the documentation URL could not be resolved.

docker compose run --rm php-cli php bin/console b24-dev:result-item-generator im.search.department.list --stage=all
[ERROR] REST docs payload is required for "im.search.department.list", but the documentation URL could not be resolved.
```

Manual annotated item classes are therefore created from live payloads captured from the
playground.

---

## Deptrac compliance

- `Search` service remains in the `Services` layer and depends only on `Core`, shared SDK
  result classes, and its local `IM\Search\Result` classes.
- Result wrappers depend on `Core\Result\AbstractResult`.
- Item result classes depend on `Core\Result\AbstractAnnotatedItem` and `CarbonImmutable`
  only where date casting is needed.
- No dependency from `Services\IM\Search` to unrelated service scopes.

---

## Verification

Phase 1:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Phase 2:

```bash
make test-integration-im-search
```

Before PR:

```bash
make oa-schema-build
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-im-search
```
