# Plan: Fix User Batch::get() returns DealItemResult instead of UserItemResult (issue #447)

## Context

`src/Services/User/Service/Batch.php` was copy-pasted from the CRM Deal scope and the
`DealItemResult` import was never replaced. As a result, every item yielded by `Batch::get()`
is an instance of `DealItemResult` instead of `UserItemResult`.

`UserItemResult` already exists at `src/Services/User/Result/UserItemResult.php` with all
`@property-read` annotations — no new class is needed.

---

## Files to Modify

### 1. `src/Services/User/Service/Batch.php`

- Replace `use Bitrix24\SDK\Services\CRM\Deal\Result\DealItemResult;`
  with `use Bitrix24\SDK\Services\User\Result\UserItemResult;`
- Replace `yield $key => new DealItemResult($value);`
  with `yield $key => new UserItemResult($value);`

---

## Deptrac compliance

`Services` layer uses another class within the same `Services` layer — both are under
`src/Services/User/`, so no cross-layer violation is introduced.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
```
