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
    protected array $engineIds = [];
    #[TestDox('Test Engine::list method')]
    public function testList(): void
    {
        $items = $this->serviceBuilder->getAiAdminScope()->engine()->list()->getResponseData()->getResult();
        dd($items);

    }

    public function testRegister(): void
    {
        $engineId = $this->serviceBuilder->getAiAdminScope()->engine()->register(
            'test-llm-1',
            Uuid::v7()->toRfc4122(),
            EngineCategory::text,
            'https://consent-management.ru/',
            new EngineSettings(
                'custom lllm'
            )
        )->getId();
        $this->engineIds[] = $engineId;

        $this->assertGreaterThanOrEqual(1, $engineId);

    }

    protected function setUp(): void
    {
        $this->serviceBuilder = Fabric::getServiceBuilder();
    }
}