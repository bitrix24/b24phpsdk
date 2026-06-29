# Plan: Add support for messageservice.* methods (issue #498)

## Context

The Bitrix24 REST API provides `messageservice.*` methods for managing SMS message service providers (senders).
These methods allow applications to register, update, list, delete SMS providers and update message delivery statuses.

API documentation: https://apidocs.bitrix24.com/api-reference/messageservice/index.html

Methods covered:
- `messageservice.sender.add` — register a new SMS provider (CODE, TYPE, HANDLER, NAME required; DESCRIPTION optional)
- `messageservice.sender.update` — update an existing provider (CODE required; HANDLER, NAME, DESCRIPTION optional)
- `messageservice.sender.list` — returns an array of registered provider codes (no parameters)
- `messageservice.sender.delete` — delete a provider by CODE
- `messageservice.message.status.update` — update delivery status (CODE, MESSAGE_ID, STATUS required)

Response shape: All methods return `result: boolean` (true on success) or `result: string[]` for list.
API version: v1 (legacy endpoint style, no `result.item` / `result.items` wrapper).

The scope is new — there is no existing `Messageservice` scope in the SDK.

---

## Files to Create

### 1. `src/Services/Messageservice/Sender/Result/SenderAddResult.php`
Result wrapper for `messageservice.sender.add` — `isSuccess(): bool`

### 2. `src/Services/Messageservice/Sender/Result/SenderUpdateResult.php`
Result wrapper for `messageservice.sender.update` — `isSuccess(): bool`

### 3. `src/Services/Messageservice/Sender/Result/SendersListResult.php`
Result wrapper for `messageservice.sender.list` — `getSenderCodes(): string[]`

### 4. `src/Services/Messageservice/Sender/Result/SenderDeleteResult.php`
Result wrapper for `messageservice.sender.delete` — `isSuccess(): bool`

### 5. `src/Services/Messageservice/Sender/Service/Sender.php`
Service class wrapping all four `messageservice.sender.*` methods.

### 6. `src/Services/Messageservice/Message/Status/Result/MessageStatusUpdateResult.php`
Result wrapper for `messageservice.message.status.update` — `isSuccess(): bool`

### 7. `src/Services/Messageservice/Message/Status/Service/MessageStatus.php`
Service class wrapping `messageservice.message.status.update`.

### 8. `src/Services/Messageservice/MessageserviceServiceBuilder.php`
Scope builder exposing `sender()` and `messageStatus()` service accessors.

### 9. `tests/Unit/Services/Messageservice/Sender/Service/SenderTest.php`
Unit tests for Sender service.

### 10. `tests/Unit/Services/Messageservice/Message/Status/Service/MessageStatusTest.php`
Unit tests for MessageStatus service.

### 11. `tests/Integration/Services/Messageservice/Sender/Service/SenderTest.php`
Integration tests for Sender CRUD lifecycle.

### 12. `tests/Integration/Services/Messageservice/Message/Status/Service/MessageStatusTest.php`
Integration test for MessageStatus — verifies API error on unknown message ID.

---

## Files to Modify

### 1. `src/Services/ServiceBuilder.php`
Add `getMessageserviceScope(): MessageserviceServiceBuilder` method and import.

### 2. `phpunit.xml.dist`
Add test suites:
- `integration_tests_scope_messageservice`
- `integration_tests_messageservice_sender`
- `integration_tests_messageservice_message_status`

### 3. `Makefile`
Add make targets:
- `test-integration-scope-messageservice`
- `test-integration-messageservice-sender`
- `test-integration-messageservice-message-status`

### 4. `rector.php`
Add paths for `src/Services/Messageservice` and `tests/Integration/Services/Messageservice`.

### 5. `phpstan.neon.dist`
Add path `tests/Integration/Services/Messageservice`.

### 6. `CHANGELOG.md`
Add entry under `## 3.3.0 – UNRELEASED` → `### Added`.

---

## Deptrac compliance

New code lives in `Services` layer and depends only on `Core` layer (AbstractService, AbstractResult, Scope, etc.)
No new layer dependencies are introduced.

---

## Verification

```bash
make lint-cs-fixer
make lint-rector
make lint-phpstan
make lint-deptrac
make test-unit
make test-integration-scope-messageservice
```

