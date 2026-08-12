<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\Catalog\RoundingRule\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Core\Result\DeletedItemResult;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Batch as RoundingRuleBatch;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Result\RoundingRuleFieldsResult;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Result\RoundingRuleResult;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Result\RoundingRulesResult;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Service\Batch;
use Bitrix24\SDK\Services\Catalog\RoundingRule\Service\RoundingRule;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(RoundingRule::class)]
class RoundingRuleTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.roundingRule.add', [
            'fields' => ['catalogGroupId' => 1, 'price' => 1000.0, 'roundType' => 4, 'roundPrecision' => 100.0],
        ]);

        self::assertInstanceOf(
            RoundingRuleResult::class,
            $this->makeService($core)->add(['catalogGroupId' => 1, 'price' => 1000.0, 'roundType' => 4, 'roundPrecision' => 100.0])
        );
    }

    public function testUpdateBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.roundingRule.update', [
            'id' => 2,
            'fields' => ['catalogGroupId' => 1, 'price' => 1500.0, 'roundType' => 2, 'roundPrecision' => 10.0],
        ]);

        self::assertInstanceOf(
            RoundingRuleResult::class,
            $this->makeService($core)->update(2, ['catalogGroupId' => 1, 'price' => 1500.0, 'roundType' => 2, 'roundPrecision' => 10.0])
        );
    }

    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.roundingRule.get', ['id' => 1]);

        self::assertInstanceOf(RoundingRuleResult::class, $this->makeService($core)->get(1));
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.roundingRule.list', [
            'select' => ['id', 'price'],
            'filter' => ['modifiedBy' => 1],
            'order' => ['id' => 'ASC'],
        ]);

        self::assertInstanceOf(
            RoundingRulesResult::class,
            $this->makeService($core)->list(['id', 'price'], ['modifiedBy' => 1], ['id' => 'ASC'])
        );
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.roundingRule.delete', ['id' => 2]);

        self::assertInstanceOf(DeletedItemResult::class, $this->makeService($core)->delete(2));
    }

    public function testGetFieldsBuildsParameters(): void
    {
        $core = $this->mockCore('catalog.roundingRule.getFields', []);

        self::assertInstanceOf(RoundingRuleFieldsResult::class, $this->makeService($core)->getFields());
    }

    private function makeService(CoreInterface $core): RoundingRule
    {
        return new RoundingRule(new Batch(new RoundingRuleBatch($core, new NullLogger()), new NullLogger()), $core, new NullLogger());
    }

    private function mockCore(string $method, array $parameters): CoreInterface
    {
        $response = $this->createStub(Response::class);
        $core = $this->createMock(CoreInterface::class);
        $core->expects($this->once())
            ->method('call')
            ->with($method, $parameters)
            ->willReturn($response);

        return $core;
    }
}
