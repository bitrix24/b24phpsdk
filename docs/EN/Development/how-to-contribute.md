# How to Contribute to Bitrix24 PHP SDK

This guide provides step-by-step instructions for contributing to the Bitrix24 PHP SDK.

## Prerequisites

- PHP `8.3`, `8.4`
- Composer
- Git
- make
- Docker (optional, but strong **recommended**)

## Setting Up Your Development Environment

1. **Fork the Repository**
    - Visit the [Bitrix24 PHP SDK repository](https://github.com/bitrix24/bitrix24-php-sdk)
    - Click the "Fork" button in the top-right corner
    - This creates your own copy of the repository under your GitHub account

2. **Clone Your Fork**
   ```shell
   git clone https://github.com/YOUR-USERNAME/bitrix24-php-sdk.git
   cd bitrix24-php-sdk
   ```

3. **Add the Original Repository as Upstream**
   ```shell
   git remote add upstream https://github.com/bitrix24/bitrix24-php-sdk.git
   ```

4. **Install Dependencies**
   ```shell
   make init
   make composer-update
   ```

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
2. Create a New Branch
3. Make Your Changes
4. Update documentation
4. Run Code Quality Checks
5. Run Tests
6. Commit Your Changes
7. Push to Your Fork
8. Create a Pull Request to main repository
9. Release new version of SDK

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

      //todo add in make file start \ stop local server
      //todo add in make file start \ stop ngrok

3. **Run exists integration test with both tokens**
   // todo add special test with make file target

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
  
  Lets create return result for method `Engine::list`
  Add 

 ```php



 ```

12. **Add integration test for new scope**


2. **Make Your Changes**
    - Write your code
    - Add tests for your changes
    - Run tests frequently to ensure your code works as expected

3. **Run Code Quality Checks**
   Using the Makefile commands:
   ```shell
   make lint-phpstan     # Static analysis
   make lint-rector      # Code upgrades check
   make lint-rector-fix  # Apply code upgrades
   make lint-cs-fixer    # Code style check
   ```

4. **Run Tests**
   ```shell
   make test-unit              # Run unit tests
   make test-integration-core  # Run core integration tests
   ```

   To run all tests:
   ```shell
   vendor/bin/phpunit
   ```

5. **Commit Your Changes**
   ```shell
   git commit -am "Your descriptive commit message"
   ```

6. **Push to Your Fork**
   ```shell
   git push origin your-branch-name
   ```

7. **Create a Pull Request**
    - Go to your fork on GitHub
    - Click "New Pull Request"
    - Select the appropriate branches
    - Provide a clear description of your changes
    - If your changes include BC breaks, mention this in the PR description


5. **Create integration tests**
    - Create a corresponding test folder in `tests/Integration/Services/MyNewScope`
    - Create test class for your service:
      ```php
      <?php
 
      declare(strict_types=1);
 
      namespace Bitrix24\SDK\Tests\Integration\Services\MyNewScope;
 
      use Bitrix24\SDK\Tests\Integration\Fabric;
      use PHPUnit\Framework\TestCase;
 
      class MyNewScopeServiceTest extends TestCase
      {
          public function testAdd(): void
          {
              $b24App = Fabric::getB24App();
              
              $addResult = $b24App->myNewScope()
                  ->myNewScopeService()
                  ->add(['TITLE' => 'Test Item']);
              
              $this->assertGreaterThan(0, $addResult->getId());
          }
      }
      ```

6. **Update testsuite in PHPUnit configuration**
    - Open `phpunit.xml.dist`
    - Add your test folder to the appropriate testsuite:
      ```xml
      <testsuite name="integration-services">
          <!-- other test directories -->
          <directory>./tests/Integration/Services/MyNewScope</directory>
      </testsuite>
      ```

7. **Update the Makefile**
    - Add a new target for your scope-specific integration tests:
      ```makefile
      test-integration-mynewscope: ## run integration tests for MyNewScope
         docker-compose run --rm php-cli vendor/bin/phpunit --testsuite integration-mynewscope
      ```
    - Add your target to the `test-integration` target dependencies

8. **Add documentation**
    - Document your new scope and its available methods
    - Run `make build-documentation` to update the API documentation

## Code Standards

- Follow PSR-12 coding standards
- Use type declarations for parameters and return types
- Write clear, descriptive docblocks for all classes and methods
- Keep methods small and focused on a single responsibility
- Use meaningful variable and method names

## Documentation

If your changes require documentation updates:

1. Update the relevant markdown files in the `docs/` directory
2. For documentation of API methods, run:
   ```shell
   make build-documentation
   ```

## Getting Help

If you have questions or need assistance with your contribution, please:

- Open an issue on GitHub
- Ask for clarification in your pull request
- Review existing code for examples of implementation patterns

Thank you for contributing to the Bitrix24 PHP SDK!