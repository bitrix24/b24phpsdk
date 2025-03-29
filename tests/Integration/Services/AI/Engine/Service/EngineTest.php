<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Maksim Mesilov <mesilov.maxim@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\AI\Engine\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AI\Engine\EngineCategory;
use Bitrix24\SDK\Services\AI\Engine\EngineSettings;
use Bitrix24\SDK\Services\AI\Engine\Service\Engine;
use Bitrix24\SDK\Services\Catalog\Catalog\Service\Catalog;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Engine::class)]
class EngineTest extends TestCase
{
    protected ServiceBuilder $serviceBuilder;
    protected array $engineCodes = [];

    #[TestDox('Test Engine::list method')]
    public function testList(): void
    {
        
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
                'custom lllm'
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