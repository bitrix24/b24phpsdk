# Plan: Type `remoteAddr` as `Darsyn\IP\Version\Multi` in `EventLogItemResult`

## Context

The `remoteAddr` field in `EventLogItemResult` currently returns a raw `string|null`.
`darsyn/ip` (`"^4 || ^5 || ^6"`) is **already** a `composer.json` dependency but unused in result items.
Using `Darsyn\IP\Version\Multi` provides a typed IP value object that auto-detects IPv4/IPv6,
enabling range checks, CIDR lookups, and protocol-appropriate string representation instead of raw strings.

---

## Data flow: before → after

```mermaid
flowchart LR
    subgraph Before
        A1["Bitrix24 REST API\nremoteAddr: '192.168.1.1'"] -->|raw JSON| B1["EventLogItemResult\n__get('remoteAddr')"]
        B1 -->|default branch| C1["string|null\n'192.168.1.1'"]
    end

    subgraph After
        A2["Bitrix24 REST API\nremoteAddr: '192.168.1.1'"] -->|raw JSON| B2["EventLogItemResult\n__get('remoteAddr')"]
        B2 -->|'remoteAddr' case| C2["Multi::factory()"]
        C2 --> D2["Multi|null\n(typed IP value object)"]
    end

    style C1 fill:#fdd,stroke:#c00
    style D2 fill:#dfd,stroke:#090
```

---

## `__get()` dispatch logic

```mermaid
flowchart TD
    Start(["\$offset passed to __get()"]) --> M{match\$offset}

    M -->|'id'| ID["(int)\$data[\$offset]"]
    M -->|'userId'\n'guestId'| UG{"empty or null?"}
    M -->|'timestampX'| TX{"empty or null?"}
    M -->|'remoteAddr'| RA{"empty or null?"}
    M -->|default| DEF["\$data[\$offset] ?? null"]

    UG -->|yes| NullInt["null"]
    UG -->|no| Int["(int)\$data[\$offset]"]

    TX -->|yes| NullCarbon["null"]
    TX -->|no| Carbon["CarbonImmutable::createFromFormat(DATE_ATOM, …)"]

    RA -->|yes| NullIP["null"]
    RA -->|no| IP["Multi::factory(\$data[\$offset])"]

    style IP fill:#dfd,stroke:#090
    style NullIP fill:#eee,stroke:#999
```

---

## Class relationship

```mermaid
classDiagram
    class AbstractItem {
        #array data
        +__get(offset) mixed
    }

    class EventLogItemResult {
        +__get(offset) int|CarbonImmutable|Multi|null
        ---
        @property-read int $id
        @property-read CarbonImmutable|null $timestampX
        @property-read Multi|null $remoteAddr
        @property-read int|null $userId
        @property-read int|null $guestId
        @property-read string|null $severity
        @property-read string|null $description
    }

    class Multi {
        <<darsyn/ip>>
        +factory(ip: string) Multi
        +getShortAddress() string
        +getExpandedAddress() string
        +isVersion4() bool
        +isVersion6() bool
        +inRange(subnet, mask) bool
    }

    AbstractItem <|-- EventLogItemResult
    EventLogItemResult ..> Multi : creates via factory()
```

---

## Null-guard: why both `!== ''` and `!== null`?

```mermaid
flowchart LR
    subgraph "Bitrix24 API responses for absent field"
        R1["field omitted entirely\n→ array key missing\n→ ?? null catches it"]
        R2["field present, value null\n→ \$data['remoteAddr'] === null"]
        R3["field present, value empty string\n→ \$data['remoteAddr'] === ''"]
    end

    R1 --> Guard["null/empty guard\n!== '' && !== null"]
    R2 --> Guard
    R3 --> Guard

    Guard -->|any truthy match| Factory["Multi::factory()\nwould throw on '' or null"]
    Guard -->|all false| Safe["return null ✓"]

    style Factory fill:#fdd,stroke:#c00
    style Safe fill:#dfd,stroke:#090
```

---

## Files to modify

### 1. `src/Services/Main/Result/EventLogItemResult.php`

**Add import:**
```php
use Darsyn\IP\Version\Multi;
```

**Update `@property-read` annotation:**
```php
- * @property-read string|null          $remoteAddr
+ * @property-read Multi|null           $remoteAddr
```

**Add `remoteAddr` case in `__get()` match:**
```php
'remoteAddr' => ($this->data[$offset] !== '' && $this->data[$offset] !== null)
    ? Multi::factory($this->data[$offset])
    : null,
```

Full updated match:
```php
return match ($offset) {
    'id' => (int)$this->data[$offset],
    'userId', 'guestId' => ($this->data[$offset] !== null && $this->data[$offset] !== '')
        ? (int)$this->data[$offset]
        : null,
    'timestampX' => ($this->data[$offset] !== '' && $this->data[$offset] !== null)
        ? CarbonImmutable::createFromFormat(DATE_ATOM, $this->data[$offset])
        : null,
    'remoteAddr' => ($this->data[$offset] !== '' && $this->data[$offset] !== null)
        ? Multi::factory($this->data[$offset])
        : null,
    default => $this->data[$offset] ?? null,
};
```

---

### 2. `tests/Integration/Services/Main/Service/EventLogTest.php`

Update `testGet()`: add `->remoteAddr()` to the select builder, add assertion:
```php
$eventLogItemResult = $this->eventLog->get(
    $id,
    (new EventLogSelectBuilder())
        ->timestampX()
        ->severity()
        ->auditTypeId()
        ->moduleId()
        ->userId()
        ->remoteAddr()          // ← add
        ->description()
)->eventLogItem();

$this->assertSame($id, $eventLogItemResult->id);
$this->assertInstanceOf(CarbonImmutable::class, $eventLogItemResult->timestampX);
$this->assertIsString($eventLogItemResult->severity);
// ← add:
if ($eventLogItemResult->remoteAddr !== null) {
    $this->assertInstanceOf(Multi::class, $eventLogItemResult->remoteAddr);
}
```

Add import at the top of the test file:
```php
use Darsyn\IP\Version\Multi;
```

---

## Critical reference files

| File | Purpose |
|------|---------|
| `src/Services/Main/Result/EventLogItemResult.php` | File to modify |
| `tests/Integration/Services/Main/Service/EventLogTest.php` | Integration test to update |
| `vendor/darsyn/ip/src/Version/Multi.php` | `Multi::factory(string $ip)` — auto-detects IPv4/IPv6 |

---

## Verification

```bash
make test-unit                       # must pass (no unit test changes)
make lint-phpstan                    # no errors
make lint-cs-fixer                   # no style issues
make lint-rector                     # no rector suggestions
make test-integration-main-eventlog  # 4 tests pass, remoteAddr assertInstanceOf(Multi)
```
