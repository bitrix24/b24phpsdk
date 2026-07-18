# Plan: Add humanresources.* v3 service - org structure (issue #517)

## Context

Issue #517 adds typed SDK support for the Bitrix24 REST API v3 `humanresources.*` scope.
The worktree is `/private/tmp/b24phpsdk-517-add-humanresources-scope` on branch
`feature/517-add-humanresources-scope`, based on `origin/v3-dev`.

The local OpenAPI snapshot was refreshed with:

```bash
make -s oa-schema-build BITRIX24_WEBHOOK="<local webhook from tests/.env.local>"
```

Baseline unit tests passed before implementation:

```bash
make test-unit
# OK (969 tests, 2723 assertions)
```

Official Bitrix24 documentation was checked for all 24 methods. The OpenAPI snapshot is not
complete enough to drive implementation blindly:

- several write/count methods have empty parameter schemas in `docs/open-api/openapi.json`
- `humanresources.employee.search`, `humanresources.node.list`, `humanresources.node.search`,
  `humanresources.node.children`, and all field-list methods are documented as `result.items`
  even when the snapshot exposes a flat array
- `humanresources.node.list` and `humanresources.node.search` require `type`
- field `get` methods return `result.item`
- count/multidepartment/subordinates/communication/member mutation methods return named payload
  objects directly under `result`

Use official docs as the source of truth for method parameters and result envelopes.

Generator rule:

- Run `php bin/console b24-dev:result-item-generator <method.name> --stage=all` before manual
  edits for generated `*ItemResult.php` classes.
- Use the generated result-item files as starting points, then correct names, namespaces,
  nullability, and response-envelope wrappers against the official docs.

## API Surface

### Employee service

Methods:

- `count(): EmployeeCountResult` for `humanresources.employee.count`
- `multidepartment(): EmployeeMultidepartmentResult`
- `search(string $name, ?int $nodeId = null, array $select = []): EmployeesResult`
- `subordinates(int $id): EmployeeSubordinatesResult`

### Node service

Methods:

- `add(string $type, string $name, int $parentId, array $optional = []): NodeResult`
- `children(int $id, array $select = []): NodesResult`
- `count(): NodeCountResult`
- `edit(int $id, array $fields): NodeResult`
- `get(int $id, array $select = []): NodeResult`
- `list(string $type, array $select = [], array $pagination = []): NodesResult`
- `move(int $id, int $parentId): NodeResult`
- `search(string $type, string $name, ?int $parentId = null, array $pagination = []): NodesResult`

### Field metadata services

`*.field.get` and `*.field.list` methods live in dedicated field services, not in the
primary entity services:

- `EmployeeField::get(string $name, array $select = []): EmployeeFieldResult`
- `EmployeeField::list(array $select = []): EmployeeFieldsResult`
- `NodeField::get(string $name, array $select = []): NodeFieldResult`
- `NodeField::list(array $select = []): NodeFieldsResult`
- `NodeMemberField::get(string $name, array $select = []): NodeMemberFieldResult`
- `NodeMemberField::list(array $select = []): NodeMemberFieldsResult`

### NodeCommunication service

Methods:

- `edit(int $nodeId, string $communicationType, array $options = []): NodeCommunicationEditResult`
- `list(int $id): NodeCommunicationResult`

### NodeMember service

Methods:

- `add(int $nodeId, array $userIds, string $role): NodeMemberOperationResult`
- `move(int $nodeId, array $userIds, ?string $role = null): NodeMemberOperationResult`
- `remove(int $nodeId, array $userIds): NodeMemberRemoveResult`
- `set(int $nodeId, array $userIds): NodeMemberOperationResult`

Batch support:

- Add `Service/Batch.php` for list/add methods that are safe for batch in this issue:
  `employee.search`, `node.children`, `node.list`, `node.search`, and `node.member.add`.
- Do not batch methods whose docs explicitly return `ERROR_BATCH_METHOD_NOT_ALLOWED` during
  live verification; document any exclusions in this plan before implementation changes.

## Files to Create

### 1. `src/Services/HumanResources/HumanResourcesServiceBuilder.php`

Creates and caches:

- `employee(): Service\Employee`
- `employeeField(): EmployeeField\Service\EmployeeField`
- `node(): Service\Node`
- `nodeField(): NodeField\Service\NodeField`
- `nodeCommunication(): Service\NodeCommunication`
- `nodeMember(): Service\NodeMember`
- `nodeMemberField(): NodeMemberField\Service\NodeMemberField`

Add `#[ApiServiceBuilderMetadata(new Scope(['humanresources']))]`.

### 2. `src/Services/HumanResources/Service/*.php`

Create:

- `Service/Employee.php`
- `Service/Node.php`
- `Service/NodeCommunication.php`
- `Service/NodeMember.php`
- `Service/Batch.php`
- `EmployeeField/Service/EmployeeField.php`
- `NodeField/Service/NodeField.php`
- `NodeMemberField/Service/NodeMemberField.php`

Each service must use `ApiVersion::v3` in `core->call(...)`.
Every method must have `#[ApiEndpointMetadata(...)]` with the English docs URL.

### 3. `src/Services/HumanResources/Result/*.php`

Create result wrappers and item classes:

- `EmployeeItemResult.php`, `EmployeesResult.php`
- `EmployeeCountResult.php`
- `EmployeeMultidepartmentResult.php`
- `EmployeeSubordinatesResult.php`
- `NodeItemResult.php`, `NodeResult.php`, `NodesResult.php`, `NodeCountResult.php`
- `NodeMemberItemResult.php`
- `NodeCommunicationResult.php`, `NodeCommunicationEditResult.php`
- `NodeMemberOperationResult.php`, `NodeMemberRemoveResult.php`
- `EmployeeField/Result/EmployeeFieldItemResult.php`, `EmployeeField/Result/EmployeeFieldResult.php`,
  `EmployeeField/Result/EmployeeFieldsResult.php`
- `NodeField/Result/NodeFieldItemResult.php`, `NodeField/Result/NodeFieldResult.php`,
  `NodeField/Result/NodeFieldsResult.php`
- `NodeMemberField/Result/NodeMemberFieldItemResult.php`,
  `NodeMemberField/Result/NodeMemberFieldResult.php`,
  `NodeMemberField/Result/NodeMemberFieldsResult.php`

Generator commands to run before manual result-item edits:

```bash
php bin/console b24-dev:result-item-generator humanresources.employee.search --stage=all
php bin/console b24-dev:result-item-generator humanresources.node.get --stage=all
php bin/console b24-dev:result-item-generator humanresources.node.member.field.get --stage=all
php bin/console b24-dev:result-item-generator humanresources.employee.field.get --stage=all
```

Generator status:

- `docker compose run --rm -e BITRIX24_WEBHOOK="<local webhook>" php-cli php bin/console b24-dev:result-item-generator humanresources.employee.search --stage=all`
  failed with `Unable to determine the current git branch`.
- The `php-cli` image does not contain `git`, and the generator fallback expects `.git/HEAD`
  to exist as a directory path. This worktree uses the standard git-worktree `.git` file
  pointing to the main repository metadata, so the fallback cannot resolve the branch.
- Result item classes are therefore created manually from the official API response
  descriptions and will be verified by the mandatory annotation integration tests.
- Live portal verification note: the current test webhook portal has no `humanresources`
  scope in `app.info` and returns `ERROR_METHOD_NOT_FOUND` for
  `humanresources.employee.field.list`, `humanresources.node.field.list`, and
  `humanresources.node.member.field.list`. Integration tests must therefore skip with an
  explicit message on portals where this scope is not available.

### 4. Unit tests

Create:

- `tests/Unit/Services/HumanResources/HumanResourcesServiceBuilderTest.php`
- `tests/Unit/Services/HumanResources/Service/EmployeeTest.php`
- `tests/Unit/Services/HumanResources/Service/EmployeeFieldTest.php`
- `tests/Unit/Services/HumanResources/Service/NodeTest.php`
- `tests/Unit/Services/HumanResources/Service/NodeFieldTest.php`
- `tests/Unit/Services/HumanResources/Service/NodeCommunicationTest.php`
- `tests/Unit/Services/HumanResources/Service/NodeMemberTest.php`
- `tests/Unit/Services/HumanResources/Service/NodeMemberFieldTest.php`

Use TDD: write a failing test for each method before production code.
Each test must assert the exact REST method name, parameters, `ApiVersion::v3`, and result class.

### 5. Integration tests

Create:

- `tests/Integration/Services/HumanResources/Service/EmployeeTest.php`
- `tests/Integration/Services/HumanResources/Service/EmployeeFieldTest.php`
- `tests/Integration/Services/HumanResources/Service/NodeTest.php`
- `tests/Integration/Services/HumanResources/Service/NodeFieldTest.php`
- `tests/Integration/Services/HumanResources/Service/NodeCommunicationTest.php`
- `tests/Integration/Services/HumanResources/Service/NodeMemberTest.php`
- `tests/Integration/Services/HumanResources/Service/NodeMemberFieldTest.php`
- `tests/Integration/Services/HumanResources/Result/EmployeeItemResultAnnotationsTest.php`
- `tests/Integration/Services/HumanResources/Result/NodeItemResultAnnotationsTest.php`
- `tests/Integration/Services/HumanResources/Result/NodeMemberItemResultAnnotationsTest.php`
- `tests/Integration/Services/HumanResources/Result/EmployeeFieldItemResultAnnotationsTest.php`
- `tests/Integration/Services/HumanResources/Result/NodeFieldItemResultAnnotationsTest.php`
- `tests/Integration/Services/HumanResources/Result/NodeMemberFieldItemResultAnnotationsTest.php`

Integration tests must avoid destructive org-structure changes unless they create and clean up
their own test node. For mutation methods, prefer unit coverage first and keep live integration
coverage narrow enough to be repeatable on the shared test portal.

## Files to Modify

### 1. `src/Services/ServiceBuilder.php`

Add import for `Bitrix24\SDK\Services\HumanResources\HumanResourcesServiceBuilder`.
Add `getHumanResourcesScope(): HumanResourcesServiceBuilder`.

### 2. `phpunit.xml.dist`

Add a suite:

```xml
<testsuite name="integration_tests_scope_humanresources">
    <directory>./tests/Integration/Services/HumanResources/</directory>
</testsuite>
```

### 3. `Makefile`

Add help line and target:

```make
test-integration-scope-humanresources:
	docker compose run --rm php-cli $(PHPUNIT) --testsuite integration_tests_scope_humanresources
```

### 4. `CHANGELOG.md`

Add under `## X.Y.Z Unreleased` -> `### Added`:

```markdown
- Added v3 `humanresources.*` SDK services for company org structure, employees,
  node communications, and node members ([#517](https://github.com/bitrix24/b24phpsdk/issues/517))
```

## Deptrac compliance

The new code stays inside the existing Services layer:

- services depend on Core contracts, credentials scope, exceptions, result wrappers, attributes, and logger
- result classes depend only on Core result abstractions and value classes such as `CarbonImmutable`
- tests use existing Unit stubs and Integration `Factory`

No dependency from Services to Tests, Infrastructure, or OpenApi runtime code is allowed.

## Verification

Run targeted RED/GREEN tests during implementation, then the full gate:

```bash
make test-file FILE=tests/Unit/Services/HumanResources/Service/EmployeeTest.php
make test-file FILE=tests/Unit/Services/HumanResources/Service/EmployeeFieldTest.php
make test-file FILE=tests/Unit/Services/HumanResources/Service/NodeTest.php
make test-file FILE=tests/Unit/Services/HumanResources/Service/NodeFieldTest.php
make test-file FILE=tests/Unit/Services/HumanResources/Service/NodeCommunicationTest.php
make test-file FILE=tests/Unit/Services/HumanResources/Service/NodeMemberTest.php
make test-file FILE=tests/Unit/Services/HumanResources/Service/NodeMemberFieldTest.php
make test-unit
make test-integration-scope-humanresources
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make lint-all
```
