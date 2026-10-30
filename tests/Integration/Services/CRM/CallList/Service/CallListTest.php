<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\CRM\CallList\Service\CallList;
use Bitrix24\SDK\Tests\Integration\Factory;
use Bitrix24\SDK\Tests\Integration\Services\CRM\CallList\CallListTestCase;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CallList::class)]
class CallListTest extends CallListTestCase
{
    public function testGetAndFilteredList(): void
    {
        $item = $this->callList->get(self::$listId)->callList();
        self::assertSame(self::$listId, $item->ID);
        self::assertSame('CONTACT', $item->ENTITY_TYPE);
        self::assertSame(3, $item->ENTITY_TYPE_ID);
        self::assertInstanceOf(CarbonImmutable::class, $item->DATE_CREATE);
        $items = $this->callList->list(['ID' => 'DESC'], ['ID' => self::$listId])->getCallLists();
        self::assertCount(1, $items);
        self::assertSame(self::$listId, $items[0]->ID);
        $selected = $this->callList->list([], ['ID' => self::$listId], ['ID'])->getCallLists();
        self::assertCount(1, $selected);
        self::assertSame(self::$listId, $selected[0]->ID);
        self::assertSame([], $this->callList->list([], ['ID' => 0])->getCallLists());
    }

    public function testContactMembershipCanBeUpdated(): void
    {
        self::assertTrue($this->callList->update(self::$listId, 'CONTACT', [self::$contactIds[1]])->isSuccess());
        $members = $this->callList->getItems(self::$listId)->getItems();
        self::assertCount(1, $members);
        self::assertSame(self::$contactIds[1], $members[0]->ID);
        self::assertSame(3, $members[0]->ENTITY_TYPE);
        self::assertSame('IN_WORK', $members[0]->STATUS);
        self::assertCount(1, $this->callList->getItems(self::$listId, ['STATUS' => 'IN_WORK'])->getItems());
        self::assertTrue($this->callList->update(self::$listId, 'CONTACT', self::$contactIds)->isSuccess());
        self::assertCount(2, $this->callList->getItems(self::$listId)->getItems());
        self::assertNull($this->callList->get(self::$listId)->callList()->WEBFORM_ID);
    }

    public function testCompanyCallList(): void
    {
        $crm = Factory::getServiceBuilder()->getCRMScope();
        $id = (int)$crm->core->call('crm.company.add', ['fields' => [
            'TITLE' => 'SDK #208 ' . bin2hex(random_bytes(6)),
            'PHONE' => [['VALUE' => '+15550002082', 'VALUE_TYPE' => 'WORK']],
        ]])->getResponseData()->getResult()[0];
        self::$companyIds[] = $id;
        $listId = $this->callList->add('COMPANY', [$id])->getId();
        self::$retainedListIds[] = $listId;
        self::assertSame('COMPANY', $this->callList->get($listId)->callList()->ENTITY_TYPE);
        self::assertTrue($this->callList->update($listId, 'COMPANY', [$id])->isSuccess());
        $items = $this->callList->getItems($listId)->getItems();
        self::assertCount(1, $items);
        self::assertSame($id, $items[0]->ID);
        self::assertSame(4, $items[0]->ENTITY_TYPE);
    }

    public function testStatuses(): void
    {
        $statuses = $this->callList->statusList()->getStatuses();
        self::assertNotEmpty($statuses);
        self::assertContains('IN_WORK', array_map(static fn ($item): string => $item->STATUS_ID, $statuses));
    }

    public function testStatusFilterWithoutMatchesPropagatesApiError(): void
    {
        $this->expectException(BaseException::class);
        $this->expectExceptionMessage('access_error');
        $this->callList->getItems(self::$listId, ['STATUS' => 'SUCCESS'])->getItems();
    }

    public function testExplicitZeroWebformIdPropagatesApiError(): void
    {
        foreach (['add', 'update'] as $operation) {
            try {
                if ($operation === 'add') {
                    $this->callList->add('CONTACT', self::$contactIds, 0)->getId();
                } else {
                    $this->callList->update(self::$listId, 'CONTACT', self::$contactIds, 0)->isSuccess();
                }

                self::fail('Bitrix24 must reject the unknown web form ID zero');
            } catch (BaseException $exception) {
                self::assertStringContainsString('webform_error', $exception->getMessage());
            }
        }
    }

    public function testUnknownCallListPropagatesApiError(): void
    {
        $this->expectException(BaseException::class);
        $this->callList->get(PHP_INT_MAX)->callList();
    }

    public function testUnknownStatusPropagatesApiError(): void
    {
        $this->expectException(BaseException::class);
        $this->callList->getItems(self::$listId, ['STATUS' => 'SDK_208_INVALID_STATUS'])->getItems();
    }
}
