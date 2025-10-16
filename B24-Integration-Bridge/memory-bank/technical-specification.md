# ⚙️ Техническая спецификация интеграционной платформы

## 1. АРХИТЕКТУРНАЯ МОДЕЛЬ

### 1.1. Микросервисная архитектура
```yaml
Services:
  webhook_handler:
    port: 8000
    protocol: HTTP/HTTPS
    framework: FastAPI
    workers: 4
    
  bitrix_client:
    type: SDK Client
    library: b24pysdk v0.1.0a1
    connections:
      - source: OAuth/Webhook
      - target: Webhook
    
  data_processor:
    type: Worker
    queue: Redis
    concurrency: 10
    
  database:
    engine: PostgreSQL 14
    pool_size: 20
    
  cache:
    engine: Redis 7
    persistence: RDB+AOF
```

### 1.2. API Specifications

#### Webhook Endpoint
```http
POST /webhook/deal
Authorization: X-API-Key
Content-Type: application/json

Request Body:
{
  "event": "ONCRMDEALUPDATE",
  "data": {
    "FIELDS": {
      "ID": integer
    }
  },
  "auth": {
    "domain": string,
    "application_token": string
  }
}

Response:
{
  "status": "success|skipped|duplicate",
  "source_deal_id": integer,
  "target_deal_id": integer,
  "timestamp": ISO-8601
}
```

### 1.3. Интеграционные точки

#### Входящие интеграции:
- Роботы Битрикс24 (webhook)
- REST API запросы (OAuth 2.0)
- Batch операции (до 50 элементов)

#### Исходящие интеграции:
- Битрикс24 REST API (CRM методы)
- Webhook целевого портала
- Prometheus metrics endpoint

## 2. МОДЕЛЬ ДАННЫХ

### 2.1. Структура сделки
```python
DealModel = {
    # Основные поля
    "ID": int,                    # Идентификатор
    "TITLE": str,                 # Название
    "STAGE_ID": str,              # Стадия
    "CATEGORY_ID": int,           # Воронка
    "TYPE_ID": str,               # Тип сделки
    
    # Финансовые поля
    "OPPORTUNITY": float,         # Сумма
    "CURRENCY_ID": str,           # Валюта
    "TAX_VALUE": float,          # Налог
    
    # Связи
    "COMPANY_ID": int,           # Компания
    "CONTACT_IDS": List[int],    # Контакты
    "ASSIGNED_BY_ID": int,       # Ответственный
    
    # Даты
    "BEGINDATE": datetime,       # Дата начала
    "CLOSEDATE": datetime,       # Планируемая дата закрытия
    "DATE_CREATE": datetime,     # Дата создания
    "DATE_MODIFY": datetime,     # Дата изменения
    
    # Дополнительно
    "COMMENTS": str,             # Комментарии
    "SOURCE_ID": str,            # Источник
    "UF_*": Any,                 # Пользовательские поля
    
    # Системные
    "PARENT_ID_*": int,          # Связи с SPA
}
```

### 2.2. Маппинг полей между системами

```python
FIELD_MAPPING = {
    # Прямой маппинг
    "TITLE": "TITLE",
    "OPPORTUNITY": "OPPORTUNITY",
    "CURRENCY_ID": "CURRENCY_ID",
    
    # Трансформация стадий
    "STAGE_ID": {
        "C1:NEW": "NEW",
        "C1:PREPARATION": "PREPARATION",
        "C1:PREPAYMENT_INVOICE": "PREPAYMENT",
        "C1:EXECUTING": "EXECUTING",
        "C1:FINAL_INVOICE": "FINAL",
        "C1:WON": "WON",
        "C1:LOSE": "LOSE"
    },
    
    # Кастомные поля
    "UF_CRM_SOURCE_ID": "UF_CRM_PARTNER_ID",
    "UF_CRM_CONTRACT": "UF_CRM_ESB_CONTRACT",
    
    # Вычисляемые поля
    "SOURCE_DESCRIPTION": lambda deal: f"Import from {settings.source_domain}",
    "UF_CRM_IMPORT_DATE": lambda: datetime.utcnow().isoformat()
}
```

## 3. АЛГОРИТМЫ ОБРАБОТКИ

### 3.1. Алгоритм дедупликации
```python
def deduplicate_request(payload: WebhookPayload) -> bool:
    """
    Алгоритм предотвращения дублирования обработки
    
    1. Генерация SHA-256 хэша от payload
    2. Проверка существования в Redis (TTL 1 час)
    3. Atomic SET NX операция
    4. Возврат результата проверки
    """
    hash_key = sha256(f"{payload.event}:{payload.data}:{payload.ts}")
    
    # Atomic check and set
    result = redis.set(
        key=f"webhook:{hash_key}",
        value="processing",
        nx=True,  # Only set if not exists
        ex=3600   # Expire in 1 hour
    )
    
    return result is not None
```

### 3.2. Алгоритм retry с экспоненциальной задержкой
```python
def exponential_backoff_retry(
    func: Callable,
    max_retries: int = 3,
    base_delay: float = 1.0,
    max_delay: float = 30.0
):
    """
    Retry механизм с экспоненциальной задержкой
    
    delay = min(base_delay * (2 ** attempt), max_delay) + jitter
    """
    for attempt in range(max_retries):
        try:
            return func()
        except (BitrixTimeout, ConnectionError) as e:
            if attempt == max_retries - 1:
                raise
            
            delay = min(base_delay * (2 ** attempt), max_delay)
            jitter = random.uniform(0, delay * 0.1)
            
            await asyncio.sleep(delay + jitter)
```

## 4. ПАРАМЕТРЫ ПРОИЗВОДИТЕЛЬНОСТИ

### 4.1. Лимиты и ограничения
```yaml
Bitrix24 API:
  rate_limit: 2 requests/second
  batch_size: 50 items
  timeout: 30 seconds
  max_retries: 3

Application:
  workers: 4
  connections_per_worker: 100
  request_timeout: 60 seconds
  max_request_size: 10MB

Database:
  connection_pool: 20
  query_timeout: 5 seconds
  max_connections: 100

Redis:
  max_memory: 2GB
  eviction_policy: allkeys-lru
  persistence: RDB snapshots every 5 minutes
```

### 4.2. Метрики производительности
- **RPS**: до 100 запросов в секунду
- **Latency P99**: < 500ms
- **Success Rate**: > 99.5%
- **Throughput**: до 5000 сделок/час

## 5. БЕЗОПАСНОСТЬ

### 5.1. Аутентификация и авторизация
```python
Security_Schema = {
    "api_key": {
        "type": "header",
        "name": "X-API-Key",
        "length": 32,
        "rotation": "monthly"
    },
    "oauth": {
        "type": "OAuth 2.0",
        "grant_type": "authorization_code",
        "token_refresh": "automatic",
        "scope": ["crm", "user"]
    },
    "webhook": {
        "type": "secret_token",
        "validation": "domain_whitelist",
        "allowed_domains": ["*.bitrix24.ru"]
    }
}
```

### 5.2. Шифрование и хранение данных
- **Transport**: TLS 1.3
- **Secrets**: Environment variables + Docker secrets
- **Logs**: Маскирование чувствительных данных
- **Database**: Encryption at rest (AES-256)

## 6. МОНИТОРИНГ И ЛОГИРОВАНИЕ

### 6.1. Структура логов
```json
{
  "timestamp": "2025-01-15T10:00:00Z",
  "level": "INFO|WARNING|ERROR",
  "service": "webhook_handler",
  "event": "deal_transfer",
  "correlation_id": "uuid-v7",
  "details": {
    "source_deal_id": 123,
    "target_deal_id": 456,
    "duration_ms": 234,
    "status": "success"
  }
}
```

### 6.2. Prometheus метрики
```python
# Счетчики
webhook_received_total{domain="partner1.bitrix24.ru"}
webhook_processed_total{status="success"}
webhook_errors_total{error_type="validation"}

# Гистограммы
webhook_processing_duration_seconds{quantile="0.99"}
api_request_duration_seconds{method="crm.deal.add"}

# Gauges
active_connections{service="webhook_handler"}
redis_memory_usage_bytes
```

## 7. DISASTER RECOVERY

### 7.1. Backup стратегия
- **Database**: Ежедневные полные бэкапы + WAL архивы
- **Redis**: RDB snapshots каждые 5 минут
- **Logs**: Ротация и архивирование через 30 дней
- **Configuration**: Git версионирование

### 7.2. Recovery процедуры
- **RTO (Recovery Time Objective)**: 1 час
- **RPO (Recovery Point Objective)**: 5 минут
- **Rollback**: Blue-Green deployment
- **Failover**: Automatic через health checks
