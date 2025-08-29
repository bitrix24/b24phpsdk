# AI-README.md - Architectural Analysis of Bitrix24 PHP SDK

## Project Overview

Bitrix24 PHP SDK is an official library for working with Bitrix24 REST API. The project represents a high-level SDK with typed methods, auto-renewal tokens support and efficient work with large data volumes through batch operations.

## Project Architecture

### Directory Structure

```
src/
├── Application/          # Application contracts
├── Attributes/           # PHP attributes for metadata
├── Core/                 # Core SDK components
├── Deprecations/         # Deprecated methods handling  
├── Events/               # Event system
├── Infrastructure/       # Infrastructure components
└── Services/             # Services for API work
```

### Abstraction Levels

1. **HTTP/JSON protocol** - basic communication level
2. **Symfony HTTP Client** - HTTP client for requests
3. **Core\ApiClient** - work with REST API endpoints (input: arrays/strings, output: Response)
4. **Services** - work with Bitrix24 entities (input: arrays/strings, output: typed DTO)

## Key Components

### 1. Services

Main components for working with different API scopes:

#### Currently Implemented Services

- **AI/** - AI services work
- **CRM/** - CRM management (contacts, deals, companies, leads, products, etc.)
- **Catalog/** - product catalog management
- **Department/** - departments work
- **Entity/** - universal data storage  
- **IM/** - instant messages
- **IMOpenLines/** - open lines
- **Main/** - main system methods
- **Placement/** - application placements
- **Telephony/** - telephony
- **User/** - user management
- **UserConsent/** - user consents
- **Workflows/** - business processes

#### Abstract Base Classes

- **AbstractService** - base class for all services
- **AbstractBatchService** - base class for batch operations
- **AbstractServiceBuilder** - base class for service builders

### 2. Attributes System

PHP 8+ attributes are used for metadata:

- **ApiServiceMetadata** - service metadata (scope, version)
- **ApiEndpointMetadata** - endpoint metadata (method name, documentation, description)
- **ApiBatchMethodMetadata** - metadata for batch methods
- **ApiBatchServiceMetadata** - metadata for batch services

### 3. Results System

All methods return typed results:

- **AbstractResult** - base class for all results
- **FieldsResult** - fields retrieval result
- **AddedItemResult** - item addition result
- **UpdatedItemResult** - item update result
- **DeletedItemResult** - item deletion result

### 4. Core Components

- **CoreInterface** - interface for API work
- **Scope** - permissions (scopes) management
- **BatchOperationsInterface** - interface for batch operations

## Wrapper Classes Creation Principles

### Analysis Example: CRM/Item

Service structure for CRM items work:

```
src/Services/CRM/Item/
├── Productrow/          # Subservice for product rows
├── Result/              # Result classes
│   ├── ItemResult.php       # Single item result
│   ├── ItemsResult.php      # Items list result
│   └── ItemItemResult.php   # Typed item DTO
└── Service/             # Main services
    ├── Item.php            # Main service
    ├── Batch.php          # Batch operations  
    └── ItemDetailsConfiguration.php # Details configuration
```

### Implementation Patterns

#### 1. Main Service (Service/Item.php)

```php
#[ApiServiceMetadata(new Scope(['crm']))]
class Item extends AbstractService
{
    public function __construct(public Batch $batch, CoreInterface $core, LoggerInterface $logger)
    {
        parent::__construct($core, $logger);
    }

    #[ApiEndpointMetadata(
        'crm.item.add',
        'https://apidocs.bitrix24.com/api-reference/crm/universal/crm-item-add.html',
        'Method creates new SPA item with entityTypeId.'
    )]
    public function add(int $entityTypeId, array $fields): ItemResult
    {
        return new ItemResult(
            $this->core->call('crm.item.add', [
                'entityTypeId' => $entityTypeId,
                'fields' => $fields,
            ])
        );
    }
    
    // Other CRUD methods: get, list, update, delete, fields
    // Additional methods: countByFilter
}
```

#### 2. Batch Service (Service/Batch.php)

```php
#[ApiBatchServiceMetadata(new Scope(['crm']))]
class Batch
{
    public function __construct(protected BatchOperationsInterface $batch, protected LoggerInterface $log) {}

    #[ApiBatchMethodMetadata(
        'crm.item.list', 
        'https://apidocs.bitrix24.com/api-reference/crm/universal/crm-item-list.html',
        'Method returns array with SPA items with entityTypeId.'
    )]
    public function list(int $entityTypeId, array $order, array $filter, array $select, ?int $limit = null): Generator
    {
        foreach ($this->batch->getTraversableList('crm.item.list', $order, $filter, $select, $limit, ['entityTypeId' => $entityTypeId]) as $key => $value) {
            yield $key => new ItemItemResult($value);
        }
    }
}
```

#### 3. Results (Result/*)

```php
// Single item result
class ItemResult extends AbstractResult
{
    public function item(): ItemItemResult
    {
        return new ItemItemResult($this->getCoreResponse()->getResponseData()->getResult()['item']);
    }
}

// Typed item DTO
class ItemItemResult extends AbstractCrmItem
{
    // Automatic properties via magic methods and PHP Doc
}
```

### Standard API Methods

Each service usually implements standard set of methods:

1. **add(array $fields)** - add item
2. **get(int $id)** - get item by ID
3. **list(array $order, array $filter, array $select, int $start)** - get list
4. **update(int $id, array $fields)** - update item  
5. **delete(int $id)** - delete item
6. **fields()** - get fields description

### Registration in ServiceBuilder

Each new service should be registered in corresponding ServiceBuilder:

```php
// In CRMServiceBuilder.php
public function item(): Item\Service\Item
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new Item\Service\Item(
            new Item\Service\Batch($this->batch, $this->log),
            $this->core,
            $this->log
        );
    }
    return $this->serviceCache[__METHOD__];
}
```

## Scopes for Implementation

### Fully Implemented Scopes

- **crm** - CRM (almost complete, many subscopes)
- **catalog** - Product catalog (partial)
- **user** - Users
- **telephony** - Telephony  
- **bizproc** - Business processes
- **entity** - Universal storage
- **ai_admin** - AI services

### Scopes Requiring Implementation

According to `src/Core/Credentials/Scope.php`, following scopes are supported:

```php
protected static array $availableScope = [
    'ai_admin',
    'appform',
    'baas',  
    'biconnector',
    'bizproc',
    'calendar',
    'calendarmobile', 
    'call',
    'cashbox',
    'catalog',
    'catalogmobile',
    'configuration.import',
    'contact_center',
    'crm',
    'delivery',
    'department', 
    'disk',
    'documentgenerator',
    'entity',
    'faceid',
    'forum',
    'humanresources.hcmlink',
    'iblock',
    'im',
    'imopenlines',
    'intranet',
    'landing',
    'lists',
    'log',
    'mailservice',
    'messageservice',
    'mobile',
    'notifications',
    'pay_system',
    'placement',
    'pull',
    'rating',
    'rpa',
    'sale',
    'salescenter',
    'socialnetwork',
    'sonet_group',
    'style',
    'task',
    'tasks',
    'telephony',
    'timeman',
    'user',
    'user.basic',
    'userconsent'
];
```

### Priority Scopes for Implementation

1. **task/tasks** - Tasks and projects
2. **calendar** - Calendar
3. **disk** - Disk (files)
4. **im/imopenlines** - Messages and open lines (partially implemented)
5. **sale** - Sales/orders
6. **landing** - Landings
7. **lists** - Lists
8. **socialnetwork** - Social network
9. **rpa** - Robotic Process Automation
10. **documentgenerator** - Document generator

## Development Recommendations

### Implementation Priorities

1. **Tasks (task/tasks)** - one of the most used scopes
2. **Calendar (calendar)** - important for most applications  
3. **Disk (disk)** - file work is critical for many integrations
4. **Sales (sale)** - CRM complement for orders work
5. **Lists (lists)** - universal tool for data

### New Services Creation Methodology

1. Study official API documentation for scope
2. **Check scope availability** in `src/Core/Credentials/Scope.php` 
3. **Create scope-level service builder** extending `AbstractServiceBuilder`
4. **Register scope builder** in root `ServiceBuilder.php`
5. Create folder structure following CRM/Item pattern
6. Implement main service with CRUD methods
7. Add Batch service for mass operations
8. Create typed result classes with proper PHPDoc properties
9. Register service in corresponding ServiceBuilder
10. Add metadata attributes for all methods
11. Write unit and integration tests
12. **Run code quality checks** (PHPStan, CS Fixer, Rector)
13. **Update documentation** and run `make build-documentation`
14. **Update changelog**

### Detailed Implementation Steps

#### Step 1: Environment Setup
```bash
# Fork repository and clone
git clone https://github.com/YOUR-USERNAME/b24phpsdk.git
cd b24phpsdk

# Switch to dev branch (latest development)
git checkout dev

# Initialize development environment  
make docker-init
```

#### Step 2: Development Environment for Testing
Create webhook and application bridge for integration tests:

**Webhook setup:**
```bash
cp /tests/.env /tests/.env.local
# Add BITRIX24_WEBHOOK with maximum scope
```

**Application bridge setup:**
```bash  
cp /tests/ApplicationBridge/.env /tests/ApplicationBridge/.env.local
# Add application credentials for token-based auth
```

#### Step 3: Scope and Service Builder Setup
```php
// 1. Check/add scope in src/Core/Credentials/Scope.php
// 2. Create scope service builder
#[ApiServiceBuilderMetadata(new Scope(['new_scope']))]
class NewScopeServiceBuilder extends AbstractServiceBuilder
{
    public function someService(): SomeService\Service\SomeService
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new SomeService\Service\SomeService(
                $this->core,
                $this->log
            );
        }
        return $this->serviceCache[__METHOD__];
    }
}

// 3. Register in root ServiceBuilder.php
public function getNewScope(): NewScopeServiceBuilder
{
    if (!isset($this->serviceCache[__METHOD__])) {
        $this->serviceCache[__METHOD__] = new NewScopeServiceBuilder(
            $this->core,
            $this->batch,
            $this->bulkItemsReader,
            $this->log
        );
    }
    return $this->serviceCache[__METHOD__];
}
```

#### Step 4: Result Classes with Lazy DTO
Create proper result classes with typed properties:

```php
/**
 * @property-read int $id
 * @property-read non-empty-string $name
 * @property-read SomeEnum $status
 * @property-read CarbonImmutable $dateCreate
 */
class SomeItemResult extends AbstractItem
{
    public function __get($offset)
    {
        switch ($offset) {
            case 'id':
                return $this->data[$offset] ? (int)$this->data[$offset] : null;
            case 'status':
                return SomeEnum::from($this->data[$offset]);
            case 'dateCreate':
                return CarbonImmutable::createFromTimestamp($this->data[$offset]);
            default:
                return $this->data[$offset] ?? null;
        }
    }
}
```

**Auto-generate field descriptions:**
```bash
make dev-show-fields-description
```

#### Step 5: Integration Testing
Create comprehensive integration tests:

```php
#[CoversClass(SomeService::class)]
class SomeServiceTest extends TestCase
{
    protected ServiceBuilder $serviceBuilder;

    protected function setUp(): void
    {
        $this->serviceBuilder = Fabric::getServiceBuilder();
    }
    
    // Test all CRUD methods with proper setup/teardown
}
```

**Add test suite to phpunit.xml.dist:**
```xml
<testsuite name="integration_tests_scope_new">
    <directory>./tests/Integration/Services/NewScope/</directory>
</testsuite>
```

**Add Makefile target:**
```makefile
test-integration-scope-new:
    docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_new
```

#### Step 6: Code Quality Pipeline
Run all quality checks before committing:

```bash
# License check
make lint-allowed-licenses

# Code style
make lint-cs-fixer

# Static analysis  
make lint-phpstan

# Code modernization
make lint-rector

# Unit tests
make test-unit

# Integration tests
make test-integration-scope-new
```

## Development Requirements

### Prerequisites

- **PHP**: 8.3 or 8.4
- **Composer**: Latest version
- **Git**: Version control
- **Make**: Build automation
- **Docker**: Containerization for development
- **IDE**: PhpStorm recommended or other IDE

### Branch Strategy

- **main** - Latest stable release (e.g., v1.3.0)
- **dev** - Development branch with upcoming features (e.g., v1.4.0-dev)
- **feature/** - Feature branches for new functionality
- **bugfix/** - Bug fix branches

**Important**: Always work from `dev` branch, not `main`!

### Development Tools

- **PHPStan** - static analysis
- **PHP CS Fixer** - code formatting
- **PHPUnit** - testing  
- **Rector** - code refactoring

### Code Standards and Best Practices

#### Code Style Requirements

- **PSR-12** coding standards compliance
- **Type declarations** for all parameters and return types
- **Clear, descriptive docblocks** for all classes and methods
- **Small, focused methods** with single responsibility
- **Meaningful naming** for variables, methods, and classes

#### Documentation Links

All API endpoint metadata must include:
- Method name (e.g., `crm.item.add`)
- **Updated documentation URL** (prefer `apidocs.bitrix24.com` over `training.bitrix24.com`)
- Clear description of method purpose

Example:
```php
#[ApiEndpointMetadata(
    'crm.item.add',
    'https://apidocs.bitrix24.com/api-reference/crm/universal/crm-item-add.html',
    'Method creates new SPA item with entityTypeId.'
)]
```

#### Result Class Conventions

**Naming patterns:**
- `SomeResult` - single item result container
- `SomeItemsResult` - multiple items result container  
- `SomeItemResult` - individual item DTO (lazy loading)

**Property documentation:**
```php
/**
 * @property-read int $id
 * @property-read non-empty-string $name
 * @property-read CustomEnum $status
 * @property-read CarbonImmutable $dateCreate
 */
```

### Pull Request Guidelines

#### Feature Contributions
- Target **`dev`** branch for new features
- Note any **backward compatibility breaks** in PR description
- BC breaks → next major version
- Non-BC features → next minor version

#### Bug Fixes
- Target **oldest applicable version** for bug fixes
- Clearly explain **what bug** you're fixing and **how**

This document serves as a guide for understanding SDK architecture and creating new wrapper services for Bitrix24 REST API.

## Анализ архитектуры системы событий Битрикс24

### Основные компоненты

1. **Структура классов событий**
   - Каждое событие представлено двумя основными классами:
     - **Класс события** (например, `OnCrmCompanyAdd`) - Наследуется от `AbstractEventRequest`, отвечает за обработку запроса события и предоставление полезной нагрузки
     - **Класс полезной нагрузки** (например, `OnCrmCompanyAddPayload`) - Наследуется от `AbstractItem`, представляет данные события в структурированном формате

2. **Иерархия наследования**
   - **Классы событий**: `[Конкретный класс события]` → `AbstractEventRequest` → `AbstractRequest` → реализует `EventInterface`
   - **Классы полезной нагрузки**: `[Конкретный класс полезной нагрузки]` → `AbstractItem` → реализует `IteratorAggregate`

3. **Реализация паттерна Фабрика**
   - Каждый модуль имеет свою фабрику событий (например, `CrmCompanyEventsFactory`, `TelephonyEventsFactory`)
   - Все фабрики реализуют интерфейс `EventsFabricInterface` с двумя ключевыми методами:
     - `isSupport(string $eventCode): bool` - Проверяет, может ли фабрика обработать конкретный код события
     - `create(Request $eventRequest): EventInterface` - Создает соответствующий объект события из запроса

4. **Центральный координатор фабрик**
   - `RemoteEventsFactory` управляет всеми фабриками событий и направляет входящие запросы событий в соответствующую фабрику
   - Он использует код события из полезной нагрузки для определения, какая фабрика должна обрабатывать событие

5. **Регистрация и управление событиями**
   - Класс `EventManager` позволяет регистрировать обработчики событий через API Битрикс24
   - Сервисный класс `Event` предоставляет прямой доступ к API для операций, связанных с событиями:
     - `bind` - Регистрация нового обработчика события
     - `unbind` - Отмена регистрации обработчика события
     - `get` - Получение списка зарегистрированных обработчиков событий
     - `test` - Тестирование обработки событий

### Поток обработки событий

1. Приложение получает HTTP-запрос, содержащий данные события
2. Запрос передается в `RemoteEventsFactory->createEvent()`
3. На основе кода события определяется соответствующая фабрика, которая используется для создания конкретного объекта события
4. Объект события обрабатывает запрос и предоставляет структурированный доступ к данным события через свой класс полезной нагрузки
5. Код приложения затем может соответствующим образом обработать событие

### Существующие типы событий

В SDK в настоящее время реализованы события для нескольких модулей:

1. **Модуль CRM**:
   - События компаний (добавление, обновление, удаление, операции с пользовательскими полями)
   - События сделок (операции с повторяющимися сделками)
   - События коммерческих предложений (аналогичные шаблоны с событиями компаний)

2. **Модуль Задачи**:
   - События задач (добавление, обновление, удаление)

3. **Жизненный цикл приложения**:
   - События установки/удаления приложения

4. **Телефония**:
   - События, связанные с вызовами

### Используемые паттерны проектирования

1. **Паттерн Фабрика** - Для создания объектов событий
2. **Паттерн Стратегия** - Разные фабрики обрабатывают разные типы событий
3. **Паттерн Наблюдатель** - Для системы уведомления о событиях
4. **Неизменяемые объекты** - Объекты полезной нагрузки спроектированы как неизменяемые для обеспечения целостности данных

### Примеры использования

1. **Регистрация обработчиков событий**:
   ```php
   $eventManager->bindEventHandlers([
       new EventHandlerMetadata(
           OnCrmCompanyAdd::CODE,
           'https://your-handler-url.com',
           $userId
       ),
   ]);
   ```

2. **Обработка входящих событий**:
   ```php
   $event = $remoteEventsFactory->createEvent($request, $applicationToken);
   if ($event instanceof OnCrmCompanyAdd) {
       $payload = $event->getPayload();
       // Обработка события добавления компании
   }
   ```

### Итоговое резюме анализа

PHP SDK для Битрикс24 реализует надежную, расширяемую систему событий, следуя принципам чистого ООП и хорошо зарекомендовавшим себя паттернам проектирования. Ключевые характеристики включают:

1. **Модульная конструкция** - Обработка событий организована по модулям (CRM, Задачи и т.д.), что облегчает расширение
2. **Типобезопасность** - Строгая типизация с конкретными классами событий и полезной нагрузки обеспечивает лучшее автодополнение кода и обнаружение ошибок
3. **Паттерн Фабрика** - Фабрики предоставляют чистый способ создания объектов событий из HTTP-запросов
4. **Функции безопасности** - Встроенная поддержка валидации токена приложения
5. **Неизменяемость** - Объекты полезной нагрузки события неизменяемы, что предотвращает случайное изменение данных события
6. **Стандартизированный интерфейс** - Все события следуют единому шаблону интерфейса, что упрощает реализацию новых типов событий

Для создания новых классов событий для системы Битрикс24 необходимо:

1. Создать класс события, наследующий от `AbstractEventRequest`
2. Создать класс полезной нагрузки, наследующий от `AbstractItem`
3. Создать или обновить фабрику событий, реализующую `EventsFabricInterface`
4. Зарегистрировать фабрику в методе `RemoteEventsFactory::init()`

Эта архитектура позволяет бесшовно интегрироваться с системой уведомлений о событиях Битрикс24 на основе вебхуков, сохраняя при этом чистый, тестируемый код.
