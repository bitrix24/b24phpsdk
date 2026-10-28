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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\CallList\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Result\AddedItemResult;
use Bitrix24\SDK\Core\Result\UpdatedItemResult;
use Bitrix24\SDK\Services\CRM\CallList\Batch as CallListBatch;
use Bitrix24\SDK\Services\CRM\CallList\Service\Batch;
use Bitrix24\SDK\Services\CRM\CallList\Service\CallList;
use Bitrix24\SDK\Services\CRM\CRMServiceBuilder;
use Bitrix24\SDK\Tests\Unit\Services\CRM\CallList\ResponseFixture;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBatch;
use Bitrix24\SDK\Tests\Unit\Stubs\NullBulkItemsReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use ReflectionClass;
use ReflectionMethod;

#[CoversClass(CallList::class)]
#[CoversClass(CRMServiceBuilder::class)]
class CallListTest extends TestCase
{
    use ResponseFixture;

    public static function webforms(): array
    {
        return ['omitted' => [null, []], 'zero' => [0, ['WEBFORM_ID' => 0]], 'linked' => [7, ['WEBFORM_ID' => 7]]];
    }

    #[DataProvider('webforms')]
    public function testAddUsesFlatPayloadAndCoreScalarResult(?int $webformId, array $optional): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')
            ->with('crm.calllist.add', ['ENTITY_TYPE' => 'CONTACT', 'ENTITIES' => [1, 2]] + $optional)
            ->willReturn($this->response('42', 'crm.calllist.add'));
        $result = $this->service($core)->add('CONTACT', [1, 2], $webformId);
        self::assertSame(AddedItemResult::class, $result::class);
        self::assertSame(42, $result->getId());
    }

    #[DataProvider('webforms')]
    public function testUpdateUsesListIdAndCoreBooleanResult(?int $webformId, array $optional): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')
            ->with('crm.calllist.update', ['LIST_ID' => 42, 'ENTITY_TYPE' => 'COMPANY', 'ENTITIES' => [3]] + $optional)
            ->willReturn($this->response(true, 'crm.calllist.update'));
        $result = $this->service($core)->update(42, 'COMPANY', [3], $webformId);
        self::assertSame(UpdatedItemResult::class, $result::class);
        self::assertTrue($result->isSuccess());
    }

    public function testGetReadsFlatResult(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('crm.calllist.get', ['ID' => 42])
            ->willReturn($this->response(['ID' => '42', 'ENTITY_TYPE' => 'CONTACT']));
        self::assertSame(42, $this->service($core)->get(42)->callList()->ID);
    }

    public function testListPassesParametersWithoutOffsetAndReadsPartialRecords(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('crm.calllist.list', [
            'ORDER' => ['DATE_CREATE' => 'DESC'], 'FILTER' => ['CREATED_BY_ID' => 1], 'SELECT' => ['ID'],
        ])->willReturn($this->response([['ID' => '42']], 'crm.calllist.list'));
        $items = $this->service($core)->list(['DATE_CREATE' => 'DESC'], ['CREATED_BY_ID' => 1], ['ID'])->getCallLists();
        self::assertCount(1, $items);
        self::assertSame(42, $items[0]->ID);
        self::assertNull($items[0]->ENTITY_TYPE);
    }

    public function testListDefaultsAndEmptyResult(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('crm.calllist.list', ['ORDER' => [], 'FILTER' => [], 'SELECT' => []])
            ->willReturn($this->response([], 'crm.calllist.list'));
        self::assertSame([], $this->service($core)->list()->getCallLists());
    }

    public function testGetItemsPreservesUnknownStatusAndFilter(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('crm.calllist.items.get', ['LIST_ID' => 42, 'FILTER' => ['STATUS' => 'CUSTOM']])
            ->willReturn($this->response([['ID' => '3', 'STATUS' => 'CUSTOM', 'ENTITY_TYPE' => '3']], 'crm.calllist.items.get'));
        $item = $this->service($core)->getItems(42, ['STATUS' => 'CUSTOM'])->getItems()[0];
        self::assertSame(3, $item->ID);
        self::assertSame('CUSTOM', $item->STATUS);
        self::assertSame(3, $item->ENTITY_TYPE);
    }

    public function testGetItemsDefaultsToEmptyFilter(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('crm.calllist.items.get', ['LIST_ID' => 42, 'FILTER' => []])
            ->willReturn($this->response([], 'crm.calllist.items.get'));
        self::assertSame([], $this->service($core)->getItems(42)->getItems());
    }

    public function testStatusListUsesNoParametersAndFlatResult(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->with('crm.calllist.statuslist', [])
            ->willReturn($this->response([['ID' => '1', 'NAME' => 'Waiting', 'SORT' => '10', 'STATUS_ID' => 'CUSTOM']], 'crm.calllist.statuslist'));
        $item = $this->service($core)->statusList()->getStatuses()[0];
        self::assertSame(1, $item->ID);
        self::assertSame('Waiting', $item->NAME);
        self::assertSame(10, $item->SORT);
        self::assertSame('CUSTOM', $item->STATUS_ID);
    }

    public function testFailuresPropagate(): void
    {
        $exception = new BaseException('Call list access denied');
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::once())->method('call')->willThrowException($exception);
        $this->expectExceptionObject($exception);
        $this->service($core)->get(42);
    }

    public function testBuilderCachesServiceAndWiresWorkingBatch(): void
    {
        $core = $this->createMock(CoreInterface::class);
        $core->expects(self::never())->method('call');
        $builder = new CRMServiceBuilder($core, new NullBatch(), new NullBulkItemsReader(), new NullLogger());
        self::assertInstanceOf(CallList::class, $builder->callList());
        self::assertSame($builder->callList(), $builder->callList());
        self::assertSame([], iterator_to_array($builder->callList()->batch->update([])));
    }

    public function testEndpointMetadataUsesLegacyVersionAndEnglishDocumentation(): void
    {
        $methods = ['add' => 'add', 'get' => 'get', 'list' => 'list', 'update' => 'update', 'statusList' => 'statuslist', 'getItems' => 'items-get'];
        foreach ($methods as $method => $suffix) {
            $metadata = (new ReflectionMethod(CallList::class, $method))->getAttributes(ApiEndpointMetadata::class)[0]->newInstance();
            self::assertSame('crm.calllist.' . str_replace('-', '.', $suffix), $metadata->name);
            self::assertSame('https://apidocs.bitrix24.com/api-reference/crm/call-list/crm-calllist-' . $suffix . '.html', $metadata->documentationUrl);
            self::assertSame(ApiVersion::v1, $metadata->apiVersion);
        }

        self::assertCount(1, (new ReflectionClass(CallList::class))->getAttributes(ApiServiceMetadata::class));
    }

    private function service(CoreInterface $core): CallList
    {
        $logger = new NullLogger();

        return new CallList(new Batch(new CallListBatch($core, $logger), $logger), $core, $logger);
    }
}
