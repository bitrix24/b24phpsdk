# Plan: Add IM\Department service for im.department.* methods (issue #432)

## Context

Issue #432 requests a new `Bitrix24\SDK\Services\IM\Department\Service\Department`
service for Bitrix24 IM department REST methods.

API version: v3 workstream. Base branch: `v3-dev`.
Branch: `feature/432-add-im-department-service`.
Worktree: `.worktrees/feature-432-add-im-department-service`.

`make oa-schema-build` was run before planning and completed successfully in the main
checkout. It was then run again inside this worktree after copying the ignored
`tests/.env.local` file, and it also completed successfully.

### REST documentation findings

Bitrix24 MCP documentation was checked for each method:

| Method | Parameters | Response shape | Documentation |
|---|---|---|---|
| `im.department.get` | required `ID` array, optional `USER_DATA` (`Y`/`N`) | `result` is a list of department objects; with `USER_DATA=Y`, each item may include `manager_user_data` | `https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-get.html` |
| `im.department.colleagues.list` | optional `USER_DATA` (`Y`/`N`), `OFFSET`, `LIMIT` | `result` is a list of user IDs when `USER_DATA=N`, or user objects when `USER_DATA=Y`; response includes `total` and optional `next` | `https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-colleagues-list.html` |
| `im.department.employees.get` | required `ID` array, optional `USER_DATA` (`Y`/`N`) | `result` is an object keyed by department ID; values are employee ID lists or user object lists | `https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-employees-get.html` |
| `im.department.managers.get` | required `ID` array, optional `USER_DATA` (`Y`/`N`) | `result` is an object keyed by department ID; values are manager ID lists or user object lists | `https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-managers-get.html` |

### OpenAPI and generator-first notes

The refreshed `docs/open-api/openapi.json` snapshot does not contain
`im.department.get`, `im.department.colleagues.list`, `im.department.employees.get`, or
`im.department.managers.get`.

Before manually writing `DepartmentItemResult`, run the required generator-first command:

```bash
docker compose run --rm php-cli php bin/console b24-dev:result-item-generator im.department.get --stage=all
```

If the generator cannot produce the class because the OpenAPI snapshot does not contain
`im.department.get`, record the exact generator failure in this plan before applying the
manual fallback. The fallback source of truth is the Bitrix24 MCP documentation plus the
live payload checks below.

Generator attempt outcome:

```text
$ docker compose run --rm php-cli php bin/console b24-dev:result-item-generator im.department.get --stage=all
[ERROR] Unable to determine the current git branch
```

Root cause: the `php-cli` image does not contain `git`, and the generator fallback reads
`.git/HEAD`. In this worktree `.git` is a pointer file to
`/Users/mesilov/work/Bitrix24/b24phpsdk/.git/worktrees/feature-432-add-im-department-service`,
which is not mounted inside the container. A read-only volume workaround also failed
because Docker cannot mount that git metadata directory over the existing `.git` pointer
file:

```text
error mounting ".../.git/worktrees/feature-432-add-im-department-service" to rootfs at "/var/www/html/.git": not a directory
```

Manual fallback is required for `DepartmentItemResult`.

### Live playground findings

Live REST probes were run against the project playground webhook:

- `im.search.department.list` with `FIND=Отд`, `USER_DATA=Y`, `LIMIT=3` returned
  department objects including departments `5` and `7`.
- `im.department.get` with `ID=[1]`, `USER_DATA=Y` returned one department object with
  fields `id`, `name`, `full_name`, `manager_user_id`, and `manager_user_data`.
- `im.department.colleagues.list` with `USER_DATA=Y`, `LIMIT=3` returned user objects,
  `total`, and `next`.
- `im.department.employees.get` with `ID=[1]`, `USER_DATA=Y` returned an object keyed by
  department ID with a user-object list under key `1`.
- `im.department.managers.get` with `ID=[1]`, `USER_DATA=Y` returned an empty result on
  the playground, so integration tests must accept an empty manager map/list.

### Design options considered

1. Recommended: implement a typed `Department` service with a local
   `DepartmentItemResult`, dedicated result wrappers for department lists and user lists,
   and reuse the existing canonical `Bitrix24\SDK\Services\IM\User\Result\UserItemResult`
   for `USER_DATA=Y` payloads. Add ID-only helper methods for `USER_DATA=N` responses so
   callers do not consume mixed arrays.
2. Create a new `DepartmentUserItemResult` class. This keeps all types local to the
   `Department` namespace but duplicates the IM user payload annotations and requires an
   additional annotation integration test.
3. Return raw arrays from all non-department methods. This is faster to implement but
   gives SDK consumers weak contracts and does not match the typed IM service style.

Use option 1.

---

## Files to Create

### 1. `src/Services/IM/Department/Service/Department.php`

```php
namespace Bitrix24\SDK\Services\IM\Department\Service;

#[ApiServiceMetadata(new Scope(['im']))]
class Department extends AbstractService
{
    /**
     * @param int[] $departmentIds
     */
    #[ApiEndpointMetadata(
        'im.department.get',
        'https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-get.html',
        'Get IM department data by IDs'
    )]
    public function get(array $departmentIds, bool $userData = false): DepartmentsResult;

    #[ApiEndpointMetadata(
        'im.department.colleagues.list',
        'https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-colleagues-list.html',
        'List colleagues of the current user'
    )]
    public function colleaguesList(
        bool $userData = false,
        ?int $offset = null,
        ?int $limit = null,
    ): DepartmentUsersResult;

    /**
     * @param int[] $departmentIds
     */
    #[ApiEndpointMetadata(
        'im.department.employees.get',
        'https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-employees-get.html',
        'Get employees for IM departments'
    )]
    public function employeesGet(array $departmentIds, bool $userData = false): DepartmentUsersByDepartmentResult;

    /**
     * @param int[] $departmentIds
     */
    #[ApiEndpointMetadata(
        'im.department.managers.get',
        'https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-managers-get.html',
        'Get managers for IM departments'
    )]
    public function managersGet(array $departmentIds, bool $userData = false): DepartmentUsersByDepartmentResult;
}
```

Payload rules:

- `get()` maps `ID => $departmentIds`, `USER_DATA => 'Y'|'N'`.
- `colleaguesList()` maps `USER_DATA => 'Y'|'N'`, and includes `OFFSET`/`LIMIT` only when
  they are not `null`.
- `employeesGet()` and `managersGet()` map `ID => $departmentIds`,
  `USER_DATA => 'Y'|'N'`.

### 2. `src/Services/IM/Department/Result/DepartmentItemResult.php`

Create via `b24-dev:result-item-generator im.department.get --stage=all` first. If the
generator cannot complete, manually add the class with the live/docs field set:

```php
namespace Bitrix24\SDK\Services\IM\Department\Result;

use Bitrix24\SDK\Core\Result\AbstractAnnotatedItem;

/**
 * @property-read int $id
 * @property-read string $name
 * @property-read string $full_name
 * @property-read int $manager_user_id
 * @property-read array|null $manager_user_data
 */
class DepartmentItemResult extends AbstractAnnotatedItem
{
}
```

### 3. `src/Services/IM/Department/Result/DepartmentsResult.php`

```php
namespace Bitrix24\SDK\Services\IM\Department\Result;

class DepartmentsResult extends AbstractResult
{
    /**
     * @return DepartmentItemResult[]
     */
    public function items(): array;
}
```

`items()` must read `getResponseData()->getResult()`, filter array items, and return a
zero-based list with `array_values()`.

### 4. `src/Services/IM/Department/Result/DepartmentUsersResult.php`

Wraps `im.department.colleagues.list`.

```php
namespace Bitrix24\SDK\Services\IM\Department\Result;

use Bitrix24\SDK\Services\IM\User\Result\UserItemResult;

class DepartmentUsersResult extends AbstractResult
{
    /**
     * @return int[]
     */
    public function userIds(): array;

    /**
     * @return UserItemResult[]
     */
    public function users(): array;

    public function total(): int;

    public function next(): ?int;
}
```

`userIds()` filters integer result values. `users()` filters array result values and wraps
them in `UserItemResult`.

### 5. `src/Services/IM/Department/Result/DepartmentUsersByDepartmentResult.php`

Wraps `im.department.employees.get` and `im.department.managers.get`.

```php
namespace Bitrix24\SDK\Services\IM\Department\Result;

use Bitrix24\SDK\Services\IM\User\Result\UserItemResult;

class DepartmentUsersByDepartmentResult extends AbstractResult
{
    /**
     * @return array<int, int[]>
     */
    public function userIdsByDepartment(): array;

    /**
     * @return array<int, UserItemResult[]>
     */
    public function usersByDepartment(): array;
}
```

Both methods must normalize numeric string department keys to integer keys.
`userIdsByDepartment()` filters integer list items. `usersByDepartment()` filters array
list items and wraps them in `UserItemResult`.

### 6. `tests/Unit/Services/IM/Department/Service/DepartmentTest.php`

`#[CoversClass(Department::class)]`.

Test methods:

- `testGetMapsDepartmentIdsAndUserDataFlag()` — asserts `im.department.get` with
  `ID => [1, 5]`, `USER_DATA => 'Y'`, returns `DepartmentsResult`.
- `testColleaguesListMapsUserDataAndPaginationArguments()` — asserts
  `im.department.colleagues.list` with `USER_DATA => 'Y'`, `OFFSET`, `LIMIT`, returns
  `DepartmentUsersResult`.
- `testColleaguesListOmitsNullPaginationArguments()` — asserts only `USER_DATA => 'N'`.
- `testEmployeesGetMapsDepartmentIdsAndUserDataFlag()` — asserts
  `im.department.employees.get`.
- `testManagersGetMapsDepartmentIdsAndUserDataFlag()` — asserts
  `im.department.managers.get`.

### 7. `tests/Integration/Services/IM/Department/Service/DepartmentTest.php`

`#[CoversClass(Department::class)]`.

Use `Factory::getServiceBuilder()->getIMScope()->department()`.

Test methods:

- `testGet()` — call `get([1], true)`, assert `DepartmentItemResult` and department ID
  `1`; skip if the playground no longer exposes department `1`.
- `testColleaguesList()` — call `colleaguesList(userData: true, limit: 3)`, assert
  `users()` returns an array, `total() >= 0`, and `next()` is `null` or non-negative; if
  users are present, assert the first item is `UserItemResult`.
- `testEmployeesGet()` — call `employeesGet([1], true)`, assert
  `usersByDepartment()` is an array; if department `1` has users, assert the first user is
  `UserItemResult`.
- `testManagersGet()` — call `managersGet([1], true)`, assert
  `usersByDepartment()` is an array and allow an empty result.

### 8. `tests/Integration/Services/IM/Department/Result/DepartmentItemResultAnnotationsTest.php`

Use the repo annotation-test naming convention (`*AnnotationsTest`), even though issue
#432 listed the file as `DepartmentItemResultTest.php`.

`#[CoversClass(DepartmentItemResult::class)]`.

Test methods:

- `testAllSystemFieldsAnnotated()` — fetch raw first item from
  `department()->get([1], true)->getCoreResponse()->getResponseData()->getResult()`, skip
  if there is no department payload, and assert all raw keys are annotated.
- `testAllSystemFieldsHasValidTypeAnnotation()` — fetch first `DepartmentItemResult` from
  `department()->get([1], true)->items()`, skip if no item is available, and assert magic
  getter types match annotations.

---

## Files to Modify

### 1. `src/Services/IM/IMServiceBuilder.php`

Add import:

```php
use Bitrix24\SDK\Services\IM\Department\Service\Department;
```

Add cached accessor near the other IM service accessors:

```php
public function department(): Department
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Department($this->core, $this->log);
    }

    return $this->serviceCache[__METHOD__];
}
```

### 2. `tests/Unit/Services/IM/IMServiceBuilderTest.php`

Add `Department` import and `testGetDepartmentService()` asserting:

- `$this->serviceBuilder->department()` is an instance of `Department`.
- The accessor returns the cached instance on repeated calls.

### 3. `phpunit.xml.dist`

Add a dedicated suite:

```xml
<testsuite name="integration_tests_im_department">
    <directory>./tests/Integration/Services/IM/Department/</directory>
</testsuite>
```

Place it next to the other IM suites.

### 4. `Makefile`

Add a help line:

```makefile
@echo "test-integration-im-department - run IM Department integration tests"
```

Add target:

```makefile
.PHONY: test-integration-im-department
test-integration-im-department:
	docker compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_im_department
```

### 5. `CHANGELOG.md`

After implementation and green checks, add under `## 3.2.0 – UNRELEASED` → `### Added`:

```markdown
- Added `Bitrix24\SDK\Services\IM\Department\Service\Department` service wrapping `im.department.get`, `im.department.colleagues.list`, `im.department.employees.get`, and `im.department.managers.get`, with typed department/user result wrappers and `IMServiceBuilder::department()` accessor ([#432](https://github.com/bitrix24/b24phpsdk/issues/432))
```

---

## Deptrac compliance

New production code stays inside the `Services` layer and depends only on:

- `Core` classes (`AbstractService`, `AbstractResult`, `AbstractAnnotatedItem`, `Scope`,
  `BaseException`, `TransportException`)
- existing `Services\IM\User\Result\UserItemResult`

The dependency is Services-to-Services inside the same layer, allowed by `deptrac.yaml`.
Do not add entries to `deptrac.yaml` `skip_violations`.

---

## Verification

Targeted checks during implementation:

```bash
make test-file path=tests/Unit/Services/IM/Department/Service/DepartmentTest.php
make test-file path=tests/Unit/Services/IM/IMServiceBuilderTest.php
make test-file path=tests/Integration/Services/IM/Department/
```

Post-implementation phase 1:

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```

Post-implementation phase 2:

```bash
make test-integration-im-department
```

`make lint-rector` is mandatory before reporting the task as complete.

---

## Plan review

✓ Unambiguity — every source file, test file, builder method, REST method, payload key, and
result-wrapper method is named explicitly.
✓ Non-contradiction — namespaces and return types are consistent across the service,
result wrappers, builder accessor, unit tests, integration tests, Makefile target, and
phpunit suite.
✓ No gaps — all issue #432 acceptance criteria are covered, including docs-backed method
mapping, generator-first ResultItem handling, builder exposure, unit tests, integration
tests, annotation checks, Makefile/phpunit wiring, CHANGELOG, and the required quality
gate.
