<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\Userfieldconfig\Service;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Response\Response;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\AddedUserfieldConfigItemResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\DeletedUserfieldConfigItemResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UpdatedUserfieldConfigItemResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UserfieldConfigResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UserfieldConfigsResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Result\UserfieldConfigTypesResult;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Service\Batch;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Service\Userfieldconfig;
use Bitrix24\SDK\Services\CRM\Userfieldconfig\Batch as UserfieldconfigBatch;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Userfieldconfig::class)]
class UserfieldconfigTest extends TestCase
{
    public function testAddBuildsParameters(): void
    {
        $core = $this->mockCore('userfieldconfig.add', [
            'moduleId' => 'crm',
            'field' => ['entityId' => 'CRM_7', 'fieldName' => 'UF_CRM_7_TEST', 'userTypeId' => 'string'],
        ]);

        self::assertInstanceOf(
            AddedUserfieldConfigItemResult::class,
            $this->makeService($core)->add('crm', [
                'entityId' => 'CRM_7',
                'fieldName' => 'UF_CRM_7_TEST',
                'userTypeId' => 'string',
            ])
        );
    }

    public function testUpdateBuildsParameters(): void
    {
        $core = $this->mockCore('userfieldconfig.update', [
            'moduleId' => 'crm',
            'id' => 7095,
            'field' => ['mandatory' => 'Y'],
        ]);

        self::assertInstanceOf(
            UpdatedUserfieldConfigItemResult::class,
            $this->makeService($core)->update('crm', 7095, ['mandatory' => 'Y'])
        );
    }

    public function testGetBuildsParameters(): void
    {
        $core = $this->mockCore('userfieldconfig.get', [
            'moduleId' => 'crm',
            'id' => 7095,
        ]);

        self::assertInstanceOf(UserfieldConfigResult::class, $this->makeService($core)->get('crm', 7095));
    }

    public function testListBuildsParameters(): void
    {
        $core = $this->mockCore('userfieldconfig.list', [
            'moduleId' => 'crm',
            'select' => ['*'],
            'order' => ['id' => 'DESC'],
            'filter' => ['multiple' => 'Y'],
            'start' => 0,
        ]);

        self::assertInstanceOf(
            UserfieldConfigsResult::class,
            $this->makeService($core)->list('crm', ['*'], ['id' => 'DESC'], ['multiple' => 'Y'])
        );
    }

    public function testListDefaultsToWildcardSelect(): void
    {
        $core = $this->mockCore('userfieldconfig.list', [
            'moduleId' => 'crm',
            'select' => ['*'],
            'order' => [],
            'filter' => [],
            'start' => 0,
        ]);

        self::assertInstanceOf(UserfieldConfigsResult::class, $this->makeService($core)->list('crm'));
    }

    public function testDeleteBuildsParameters(): void
    {
        $core = $this->mockCore('userfieldconfig.delete', [
            'moduleId' => 'crm',
            'id' => 7095,
        ]);

        self::assertInstanceOf(DeletedUserfieldConfigItemResult::class, $this->makeService($core)->delete('crm', 7095));
    }

    public function testGetTypesBuildsParameters(): void
    {
        $core = $this->mockCore('userfieldconfig.getTypes', [
            'moduleId' => 'crm',
        ]);

        self::assertInstanceOf(UserfieldConfigTypesResult::class, $this->makeService($core)->getTypes('crm'));
    }

    private function makeService(CoreInterface $core): Userfieldconfig
    {
        return new Userfieldconfig(new Batch(new UserfieldconfigBatch($core, new NullLogger()), new NullLogger()), $core, new NullLogger());
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
