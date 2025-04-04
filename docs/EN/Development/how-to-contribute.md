# How to Contribute to Bitrix24 PHP SDK

This guide provides step-by-step instructions for contributing to the Bitrix24 PHP SDK.

## Prerequisites

- PHP `8.3` or `8.4`
- Composer
- Git
- make
- Docker
- PhpStorm or other IDE

## Guidelines for Different Types of Contributions

### Adding New Features

- Create pull requests for new features against the `master` branch
- If your feature introduces backward compatibility breaks (BC breaks), note this in your PR comment
- BC breaking changes will be included in the next major version
- Non-BC breaking features will be added in the next minor version

### Bug Fixes and Patches

1. Identify the oldest applicable version for your bug fix
2. Create the PR against that version
3. Explain what bug you're fixing and how

### Default development workflow

1. Planning add new feature
2. Fork main repository
3. Create a New Branch
4. Make Your Changes
5. Update documentation
6. Run Code Quality Checks
7. Run Tests
8. Commit Your Changes
9. Push to Your Fork
10. Create a Pull Request to main repository
11. Wait for review for Your Pull Request form maintainers.
12. Wait for shipping new release with your changes.

## Setting Up Your Development Environment

1. **Fork the Repository**
    - Visit the [Bitrix24 PHP SDK repository](https://github.com/bitrix24/b24phpsdk)
    - Click the "Fork" button in the top-right corner
    - This creates your own copy of the repository under your GitHub account
    - disable checkbox "Copy the main branch only", because last actual code in dev-branch.

2. **Clone Your Fork on your computer**
   ```shell
   git clone https://github.com/YOUR-USERNAME/b24phpsdk.git
   cd b24phpsdk
   ```
   or in PhpStorm You can create new project from version control dialog.

3. **Switch to branch `dev` and get latest changes**

   In main branch You have the latest release version, example - `1.3.0`, but in branch dev you have a code for upcoming release, for example - `1.4.0`.
   That's why You **must** get code from `dev` branch.

4. **Init developer environment**
   ```shell
   make docker-init
   ```
5. **That's all, let's contribute! 🚀**

## Step-by-Step Guide for Adding a new feature with new scope

Register **development** bitrix24 portal.
You **must** run integration tests in development environment.

1. **Set up your development environment – register incoming webhook**
    - Create new incoming webhook with **maximum** scope
    - Create local env-file
   ```
   cp /tests/.env /tests/.env.local
   ```
    - Add webhook url to env variable `BITRIX24_WEBHOOK`

2. **Set up your development environment – register a local application**  
   Certain scopes and API methods require application credentials. To handle this, integration tests utilize an application bridge to obtain actual tokens with
   the necessary application scope.

    - Create new local application with maximum scope
    - Create local env-file
   ```
   cp /tests/ApplicationBridge/.env /tests/ApplicationBridge/.env.local
   ```
    - Add application parameters to file `.env.local`
    - Install local application from `tests/ApplicationBridge`

3. **Run exists integration test with both auth contexts: incoming webhook and application auth tokens**
    - Great! Now You can contribute and run tests

4. **Planning add new feature**
    - Read [documentation](https://apidocs.bitrix24.com/) about adding method
   ```
   In this example we add this method and register new scope in service builder
   https://apidocs.bitrix24.com/api-reference/ai/ai-engine-register.html
   ```
    - Create new issue with [feature](https://github.com/bitrix24/b24phpsdk/issues/new?template=2_feature_request_sdk.yaml) request in repository.
    - Try to describe some use cases for work with this method.

5. **Create a New Branch**
    - For new features:
   ```shell
   git checkout -b feature/issue-id-short-issue-name
   ```

6. **Add the new scope in the Scope class**
    - check is scope exists in `Bitrix24\SDK\Core\Credentials\Scope` class in `src/Core/Credentials/Scope.php`
    - if the scope doesn't exist, add the new scope to this class
    - run `tests/Integration/Core/Credentials/ScopeTest.php`

7. **Create file structure for new service**
    - Go to documentation and find list of methods for this scope
    ```
    Example:
    ai.engine.register
    ai.engine.list
    ai.engine.unregister
    ```
    - Create scope level namespace for service
    ```shell
    mkdir src/Services/AI  
    ```
8. **Create scope level service builder for this scope**
    - Your service builder must extend class `src/Services/AbstractServiceBuilder.php`
    - Create empty scope level service builder
   ```php 
    declare(strict_types=1);
    
    namespace Bitrix24\SDK\Services\AI;
    
    use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
    use Bitrix24\SDK\Core\Credentials\Scope;
    use Bitrix24\SDK\Services\AbstractServiceBuilder;
   
    #[ApiServiceBuilderMetadata(new Scope(['ai_admin']))]
    class AIServiceBuilder extends AbstractServiceBuilder
    {
    }
   ```
9. **Register new scope-level service builder in root service builder**
    - Add getter method in file `src/Services/ServiceBuilder.php`
   ```php
    public function getAiAdminScope(): AIServiceBuilder
    {
        if (!isset($this->serviceCache[__METHOD__])) {
            $this->serviceCache[__METHOD__] = new AIServiceBuilder(
                $this->core,
                $this->batch,
                $this->bulkItemsReader,
                $this->log
            );
        }

        return $this->serviceCache[__METHOD__];
    }
   ```
10. **Implement the API service**
    - Create folder structure for future service

   ```shell
   mkdir src/Services/AI/Engine
   mkdir src/Services/AI/Engine/Result
   mkdir src/Services/AI/Engine/Service
   ```
   
   - Create service for methods `ai.engine.*`
   - You must extend class `src/Services/AbstractService.php`
   ```php
   declare(strict_types=1);
   
   namespace Bitrix24\SDK\Services\AI\Engine\Service;
   
   use Bitrix24\SDK\Attributes\ApiServiceMetadata;
   use Bitrix24\SDK\Core\Credentials\Scope;
   use Bitrix24\SDK\Services\AbstractService;
   
   #[ApiServiceMetadata(new Scope(['ai_admin']))]
   class Engine extends AbstractService
   { 
   }
   ```
    - Register service `Engine` in scope-level service builder
```php
 declare(strict_types=1);
 
 namespace Bitrix24\SDK\Services\AI;
 
 use Bitrix24\SDK\Attributes\ApiServiceBuilderMetadata;
 use Bitrix24\SDK\Core\Credentials\Scope;
 use Bitrix24\SDK\Services\AbstractServiceBuilder;
 use Bitrix24\SDK\Services\AI;
 #[ApiServiceBuilderMetadata(new Scope(['ai_admin']))]
 
 class AIServiceBuilder extends AbstractServiceBuilder
 {
     public function engine(): AI\Engine\Service\Engine
     {
         if (!isset($this->serviceCache[__METHOD__])) {
             $this->serviceCache[__METHOD__] = new AI\Engine\Service\Engine(
                 $this->core,
                 $this->log
             );
         }
 
         return $this->serviceCache[__METHOD__];
     }
 }
``` 

11. **Implement methods for service**

    - Go to documentation page for current endpoint and get list of methods

```
https://apidocs.bitrix24.com/api-reference/ai/index.html

we have methods:
ai.engine.register
ai.engine.list
ai.engine.unregister
```

- Add first method to service `src/Services/AI/Engine/Service/Engine.php`
- Read documentation for [method](https://apidocs.bitrix24.com/api-reference/ai/ai-engine-register.html)
- Add method call

```php
     declare(strict_types=1);
     
     namespace Bitrix24\SDK\Services\AI\Engine\Service;
     
     use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
     use Bitrix24\SDK\Attributes\ApiServiceMetadata;
     use Bitrix24\SDK\Core\Credentials\Scope;
     use Bitrix24\SDK\Core\Exceptions\BaseException;
     use Bitrix24\SDK\Core\Exceptions\TransportException;
     use Bitrix24\SDK\Services\AbstractService;
     use Bitrix24\SDK\Services\AI\Engine\EngineSettings;
     
     #[ApiServiceMetadata(new Scope(['ai_admin']))]
     class Engine extends AbstractService
     {
         /**
          * Register the AI service
          *
          * @throws BaseException
          * @throws TransportException
          * @see https://apidocs.bitrix24.com/api-reference/ai/ai-engine-register.html
          */
         #[ApiEndpointMetadata(
             'ai.engine.register',
             'https://apidocs.bitrix24.com/api-reference/ai/ai-engine-register.html',
             'REST method for adding a custom service. This method registers an engine and updates it upon subsequent calls. This is not quite an embedding location, as the endpoint of the partner must adhere to strict formats.'
         )]
         public function register(
             string $name,
             string $code,
             string $category,
             string $completionsUrl,
             EngineSettings $settings,
         ) {
             return $this->core->call('ai.engine.register', [
                 'name' => $name,
                 'code' => $code,
                 'category' => $category,
                 'completions_url' => $completionsUrl,
                 'settings' => $settings->toArray(),
             ]);
         }
     }
```

- Add return types to method calls  
  If the method performs standard CRUD operations, you can use standardized result types from `src/Core/Result`.
  Add return result for method `register`

  ```php
      public function register(
        string $name,
        string $code,
        EngineCategory $category,
        string $completionsUrl,
        EngineSettings $settings,
    ): AddedItemResult {
        return new AddedItemResult($this->core->call('ai.engine.register', [
            'name' => $name,
            'code' => $code,
            'category' => $category->value,
            'completions_url' => $completionsUrl,
            'settings' => $settings->toArray(),
        ]));
    }
  ``` 

If method needs return specialized result, you can add result to related folder - `Result` for current service.

In our example target folder is `src/Services/AI/Engine/Result`, let's implement custom result for method.

Results for methods returned one item and list methods are use same approach - «lazy DTO».

For both methods list and item you must use prefix `Result`.

For result container with «lazy DTO» you must add prefix `ItemResult`.

Let's create return result for method `Engine::list`
Add files:

  ```
  - EnginesResult.php result for list items
  - EngineResult.php restul for one item
  - EngineItemResult.php result data storage for registered AI Engine data structure
  ```

Files `EnginesResult` and `EngineResult` must extend `Bitrix24\SDK\Core\Response\Response\AbstractResult`
File `EngineItemResult` must extend `Bitrix24\SDK\Core\Result\AbstractItem` or his inheritor
Results for EnginesResult.php

  ```php
    declare(strict_types=1);

    namespace Bitrix24\SDK\Services\AI\Engine\Result;
    
    use Bitrix24\SDK\Core\Exceptions\BaseException;
    use Bitrix24\SDK\Core\Result\AbstractResult;
    
    class EnginesResult extends AbstractResult
    {
       /**
       * @return EngineItemResult[]
       * @throws BaseException
       */
       public function getEngines(): array
       {
           $res = [];
           foreach ($this->getCoreResponse()->getResponseData()->getResult() as $item) {
              $res[] = new EngineItemResult($item);
           }
       
           return $res;
       }
    }
  ```

File EngineResult.php in current example not implemented because we don't have method `ai.engine.get`  
Results for file `EngineItemResult.php`

  ```php
   declare(strict_types=1);

   namespace Bitrix24\SDK\Services\AI\Engine\Result;
   
   use Bitrix24\SDK\Core\Result\AbstractItem;
   use Bitrix24\SDK\Services\AI\Engine\EngineCategory;
   use Bitrix24\SDK\Services\AI\Engine\EngineSettings;
   use Carbon\CarbonImmutable;
   
     /**
     * @property-read int $id
     * @property-read non-empty-string $app_code
     * @property-read non-empty-string $name
     * @property-read non-empty-string $code
     * @property-read EngineCategory $category
     * @property-read non-empty-string $completionsUrl
     * @property-read EngineSettings $settings
     * @property-read CarbonImmutable $dateCreate
     */
     class EngineItemResult extends AbstractItem
     {
         /**
         * @param int|string $offset
         *
         * @return bool|CarbonImmutable|int|mixed|null
         */
         public function __get($offset)
         {
            switch ($offset) {
               case 'id':
                  if ($this->data[$offset] !== '' && $this->data[$offset] !== null) {
                     return (int)$this->data[$offset];
                  }
   
                  return null;
              case 'category':
                  return EngineCategory::from($this->data[$offset]);
              case 'settings':
                  return EngineSettings::fromArray($this->data[$offset]);
              case 'dateCreate':
                  return CarbonImmutable::createFromTimestamp($this->data[$offset]);
              default:
                  return $this->data[$offset] ?? null;
           }
         }
   }
  ``` 

Pay attention to the cap with php-dooc comments

  ```php
     /**
     * @property-read int $id
     * @property-read non-empty-string $app_code
     * @property-read non-empty-string $name
     * @property-read non-empty-string $code
     * @property-read EngineCategory $category
     * @property-read non-empty-string $completionsUrl
     * @property-read EngineSettings $settings
     * @property-read CarbonImmutable $dateCreate
     */
  ```

Thanks to these comments, IDE can make tips on the structure of data that Bitrix24 returns.
You can generate such comments by automatically calling the command and following the instructions of the wizard.

  ```shell
  make dev-show-fields-description
  ```

Unfortunately, the `ai_admin` scope does not have a method of` fields` so you will have to watch the data structure in the result of the call of the
API-method and documentation, and not use the call `make dev-show-fields-description`

12. **Add integration test for new scope**
    - Go to folder `tests/Integration/Services` and create folder `tests/Integration/Services/AI/Engine/Service/`
    - In folder create integration test `EngineTest.php` for all implemented methods

  ```php
     declare(strict_types=1);
     
     namespace Bitrix24\SDK\Tests\Integration\Services\AI\Engine\Service;
     
     use Bitrix24\SDK\Services\AI\Engine\EngineCategory;
     use Bitrix24\SDK\Services\AI\Engine\EngineSettings;
     use Bitrix24\SDK\Services\AI\Engine\Service\Engine;
     use Bitrix24\SDK\Services\ServiceBuilder;
     use Bitrix24\SDK\Tests\Integration\Fabric;
     use PHPUnit\Framework\Attributes\CoversClass;
     use PHPUnit\Framework\Attributes\CoversMethod;
     use PHPUnit\Framework\Attributes\TestDox;
     use PHPUnit\Framework\TestCase;
     use Symfony\Component\Uid\Uuid;
     
     #[CoversClass(Engine::class)]
     #[CoversMethod(Engine::class,'register')]
     #[CoversMethod(Engine::class,'list')]
     #[CoversMethod(Engine::class,'unregister')]
     class EngineTest extends TestCase
     {
         protected ServiceBuilder $serviceBuilder;
         protected array $engineCodes = [];
     
         #[TestDox('Test Engine::list method')]
         public function testList(): void
         {
             $engineCode = Uuid::v7()->toRfc4122();
             $engineId = $this->serviceBuilder->getAiAdminScope()->engine()->register(
                 'test-llm-1',
                 $engineCode,
                 EngineCategory::text,
                 'https://bitrix24.com/',
                 new EngineSettings(
                     'custom llm'
                 )
             )->getId();
             $this->engineCodes[] = $engineCode;
     
             $this->assertGreaterThanOrEqual(1, count($this->serviceBuilder->getAiAdminScope()->engine()->list()->getEngines()));
         }
     
         public function testRegister(): void
         {
             $engineCode = Uuid::v7()->toRfc4122();
             $engineId = $this->serviceBuilder->getAiAdminScope()->engine()->register(
                 'test-llm-1',
                 $engineCode,
                 EngineCategory::text,
                 'https://bitrix24.com/',
                 new EngineSettings(
                     'custom llm'
                 )
             )->getId();
             $this->engineCodes[] = $engineCode;
     
             $this->assertGreaterThanOrEqual(1, $engineId);
         }
         
         public function testUnregister(): void
         {
             $engineCode = Uuid::v7()->toRfc4122();
     
             // Register a test engine
             $this->serviceBuilder->getAiAdminScope()->engine()->register(
                 'test-llm-unregister',
                 $engineCode,
                 EngineCategory::text,
                 'https://bitrix24.com/',
                 new EngineSettings('test engine for unregister')
             );
     
             // Unregister the engine
             $result = $this->serviceBuilder->getAiAdminScope()->engine()->unregister($engineCode);
             $this->assertTrue($result->isSuccess(), 'Engine should be successfully unregistered.');
     
             $this->assertNotContains(
                 $engineCode,
                 array_map(
                     static fn($engine) => $engine->code,
                     $this->serviceBuilder->getAiAdminScope()->engine()->list()->getEngines()
                 ),
                 'Engine code should not exist after unregistration.'
             );
         }
     
         protected function setUp(): void
         {
             $this->serviceBuilder = Fabric::getServiceBuilder();
         }
     
         protected function tearDown(): void
         {
             foreach ($this->engineCodes as $code) {
                 $this->serviceBuilder->getAiAdminScope()->engine()->unregister($code);
             }
         }
     }
  ```

    - Run test in IDE – all checks are passed
    - Add new testsuite in `phpunit.xml.dist`

  ```
<testsuite name="integration_tests_scope_ai_admin">
    <directory>./tests/Integration/Services/AI/</directory>
</testsuite>  
  ```

    - Add new target in make file in root folder

  ```
    test-integration-scope-ai-admin:
      docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration_tests_scope_ai_admin
  ```

13. **Run checks**
    - Allowed license
    ```shell
    make lint-allowed-licenses 
    ```
    - PHP CS fixer
    ```shell 
    make lint-cs-fixer
    ```
    - phpstan
    ```shell 
    make lint-phpstan
    ```
    - Rector
    ```shell 
    make lint-rector
    ```                
    - Run unit tests
    ```shell
    make test-unit
    ```
    - Run integration tests, scope by scope and core
    - if all checks passed you can commit changes.

14. **Update documentation**
    - Document your new scope and its available methods
    - Run `make build-documentation` to update the API documentation
    - Commit changes

16. **Update changelog**

    ```
    Added service `Services\AI\Engine\Service\Engine` with support methods:
      - `ai.engine.register` - method registers an engine and updates it upon subsequent calls
      - `ai.engine.list` - get the list of ai services
      - `ai.engine.unregister` - Delete registered ai service 
    ```
17. **Commit changes**

18. **Open Pull Request to the main repository**
    - You must open Pull Request to main repository into branch `bitrix:dev` from your feature or bugfix branch         

## Code Standards

- Follow PSR-12 coding standards
- Use type declarations for parameters and return types
- Write clear, descriptive docblocks for all classes and methods
- Keep methods small and focused on a single responsibility
- Use meaningful variable and method names

## Getting Help

If you have questions or need assistance with your contribution, please:

- Open an issue on GitHub
- Ask for clarification in your pull request
- Review existing code for examples of implementation patterns

Thank you for contributing to the Bitrix24 PHP SDK!