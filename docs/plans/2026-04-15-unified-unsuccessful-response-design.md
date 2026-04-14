# Design: Unified Unsuccessful Response Support (Issue #341)

## Context

REST API v3 returns error responses in a unified format distinct from v1:

**v3 format:**
```json
{
  "error": {
    "code": "string",
    "message": "string",
    "validation": [
      { "message": "string", "field": "string" }
    ]
  }
}
```

**v1 format:**
```json
{
  "error": "ERROR_CODE",
  "error_description": "Human readable message"
}
```

The current `ApiLevelErrorHandler` partially handles v3 (extracts `code`/`message`) but:
- Ignores the `validation` array entirely
- Detects v3 errors implicitly via "no `result` key" condition
- Has no DTO for the v3 error structure
- Has no `ValidationException` to surface field-level validation details

---

## Approach: Full Implementation (Approach A)

### Components

**New files:**

| File | Purpose |
|---|---|
| `src/Core/Response/DTO/ValidationError.php` | Value object: `field` + `message` |
| `src/Core/Response/DTO/UnsuccessfulResponseError.php` | Value object: `code` + `message` + `ValidationError[]` |
| `src/Core/Exceptions/ValidationException.php` | Exception extending `BaseException`, carries `ValidationError[]` |

**Modified files:**

| File | Change |
|---|---|
| `src/Core/ApiLevelErrorHandler.php` | Explicit v3 detection, build DTOs, throw `ValidationException` |
| `tests/Unit/Core/ApiLevelErrorHandlerTest.php` | New test cases for v3 validation errors |

---

## Data Flow

### Detection in `handle()`

```
Incoming $responseBody
        │
        ├─ has 'error' AND 'error_description'      → v1 single error  → handleError()
        ├─ has 'error' AND is_array($error)          → v3 error         → handleError()  [NEW]
        ├─ has 'error' AND no 'result'               → v1 token error   → handleError()
        ├─ has 'result.result_error'                 → batch error      → handleError() per cmd
        └─ otherwise                                 → success, no-op
```

### Routing in `handleError()`

```
$error = $responseBody['error']
        │
        ├─ is_array($error)  → UnsuccessfulResponseError::fromArray($error)
        │                         extracts: code, message, ValidationError[]
        │
        └─ is_string($error) → v1: errorCode = $error, errorDescription = error_description

After extraction → switch($errorCode):
        ├─ known codes         → specific exceptions (unchanged)
        └─ validation[] present → ValidationException($msg, $validationErrors)  [NEW]
           default              → BaseException
```

---

## Class Contracts

### `ValidationError`

```php
readonly class ValidationError {
    public function __construct(
        public string $field,
        public string $message,
    ) {}
}
```

### `UnsuccessfulResponseError`

```php
readonly class UnsuccessfulResponseError {
    /** @param ValidationError[] $validation */
    public function __construct(
        public string $code,
        public string $message,
        public array $validation = [],
    ) {}

    public static function fromArray(array $data): self { ... }
}
```

### `ValidationException`

```php
class ValidationException extends BaseException {
    /** @param ValidationError[] $validationErrors */
    public function __construct(
        string $message,
        private readonly array $validationErrors = [],
        int $code = 0,
        ?\Throwable $previous = null,
    ) {}

    /** @return ValidationError[] */
    public function getValidationErrors(): array { ... }
}
```

---

## Test Coverage

New cases in `ApiLevelErrorHandlerTest` data provider:

| Case | Input | Expected |
|---|---|---|
| v3 validation error (single field) | `error.validation[0] = {field: title, message: Required}` | `ValidationException` with 1 `ValidationError` |
| v3 validation error (multiple fields) | `error.validation` with 2 items | `ValidationException` with 2 `ValidationError` |
| v3 error without validation | `error = {code: ACCESS_DENIED, message: ...}` | `AuthForbiddenException` (unchanged) |
| v3 unknown code, no validation | `error = {code: SOME_NEW_CODE, message: ...}` | `BaseException` |

---

## Deptrac Compliance

- `ValidationError`, `UnsuccessfulResponseError` — in `Core` layer, no SDK imports
- `ValidationException` — in `Core/Exceptions`, no SDK imports
- `ApiLevelErrorHandler` — in `Core`, uses only `Core` types
- No new layer violations introduced
