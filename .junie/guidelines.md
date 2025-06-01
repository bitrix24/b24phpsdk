# Bitrix24 PHP SDK Development Guidelines

This document provides essential information for developers working on the Bitrix24 PHP SDK project.

## Build and Configuration Instructions

### Environment Setup

The project uses Docker for development and testing. The Docker environment is defined in `docker-compose.yaml` and `docker/php-cli/Dockerfile`.

1. **Docker Setup**:
   - The project uses PHP 8.3 CLI with Alpine Linux
   - Required PHP extensions: bcmath, intl, excimer
   - Composer is pre-installed in the Docker image

2. **Getting Started**:
   ```bash
   # Clone the repository
   git clone https://github.com/bitrix24/b24phpsdk.git
   cd b24phpsdk
   
   # Build the Docker image and install dependencies
   docker-compose build
   docker-compose run --rm php-cli composer install
   ```

## Testing Information

### Test Structure

Tests are organized in the `tests` directory with the following structure:
- `Unit/`: Unit tests that don't require external services
- `Integration/`: Integration tests that interact with the Bitrix24 API
  - Further subdivided by API scope (Telephony, IM, User, etc.)
- `Application/`: Tests for the Application component
- `ApplicationBridge/`: Tests for application integration
- `Builders/`: Test builders/factories
- `CustomAssertions/`: Custom PHPUnit assertions

### Running Tests

Tests can be run using Make targets or directly with PHPUnit:

```bash
# Run all unit tests
make test-unit

# Run integration tests for a specific API scope
make test-integration-scope-telephony
make test-integration-scope-user
# etc.

# Run a specific test file
docker-compose run --rm php-cli vendor/bin/phpunit path/to/TestFile.php
```

### Environment Configuration for Tests

1. **Environment Variables**:
   - Tests use environment variables defined in `tests/.env`
   - For integration tests, create a `tests/.env.local` file with your Bitrix24 webhook URL:
     ```
     BITRIX24_WEBHOOK=https://your-portal.bitrix24.ru/rest/1/your-webhook-token/
     ```

2. **Test Bootstrap**:
   - Tests are bootstrapped by `tests/bootstrap.php`
   - This file loads the autoloader and environment variables

### Creating New Tests

1. **Unit Tests**:
   - Create a new test class in the appropriate subdirectory of `tests/Unit/`
   - Extend `PHPUnit\Framework\TestCase`
   - Use the `#[\PHPUnit\Framework\Attributes\CoversClass]` attribute to specify which class is being tested
   - Follow the naming convention: `ClassNameTest.php`

2. **Integration Tests**:
   - Create a new test class in the appropriate subdirectory of `tests/Integration/`
   - Follow the same conventions as unit tests
   - Ensure you have the necessary environment variables set in `.env.local`

### Example Test

Here's a simple example of a unit test:

```php
<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Utility;

use Bitrix24\SDK\Utility\StringUtility;
use PHPUnit\Framework\TestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Bitrix24\SDK\Utility\StringUtility::class)]
class StringUtilityTest extends TestCase
{
    public function testReverse(): void
    {
        $this->assertEquals('olleh', StringUtility::reverse('hello'));
    }
    
    public function testIsPalindrome(): void
    {
        $this->assertTrue(StringUtility::isPalindrome('racecar'));
        $this->assertFalse(StringUtility::isPalindrome('hello'));
    }
}
```

## Code Quality and Linting

The project uses several tools for code quality:

1. **PHPStan**:
   ```bash
   make lint-phpstan
   ```

2. **Rector**:
   ```bash
   # Check for issues
   make lint-rector
   
   # Fix issues automatically
   make lint-rector-fix
   ```

## Development Server

For development and testing of application bridges:

```bash
# Start the development server
make php-dev-server-up

# Stop the development server
make php-dev-server-down
```

## Project Structure

- `src/`: Source code
  - `Application/`: Application-related code
  - `Core/`: Core functionality
  - `Services/`: API services organized by Bitrix24 API scope
- `tests/`: Test code
- `examples/`: Example code demonstrating SDK usage
- `docs/`: Documentation
- `docker/`: Docker configuration
- `tools/`: Development tools

## Additional Resources

- `CHANGELOG.md`: Project changelog
- `CONTRIBUTING.md`: Contribution guidelines
- `README.md`: Project overview
- `SECURITY.md`: Security policy