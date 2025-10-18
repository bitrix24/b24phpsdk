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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Item\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Exceptions\ItemNotFoundException;
use Bitrix24\SDK\Services\CRM\Contact\Service\Contact;
use Bitrix24\SDK\Services\CRM\Item\Service\Item;
use Bitrix24\SDK\Services\CRM\Type\Service\Type;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(Item::class)]
class ItemTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Type $typeService;

    protected Item $itemService;

    protected Contact $contactService;

    public function testAdd(): void
    {
        $title = sprintf('%s test SPA type', time());
        $addedTypeItemResult = $this->typeService->add($title);
        $this->assertEquals($title, $addedTypeItemResult->type()->title);

        // add item to SP
        $this->itemService->add($addedTypeItemResult->type()->entityTypeId, [
            'title' => sprintf('test sp item %s', time()),
            'xmlId' => sprintf('b24-php-sdk-test-item-%s', time())
        ])->item();

        $this->assertTrue($this->typeService->delete($addedTypeItemResult->getId())->isSuccess());
    }

    public function testUpdate(): void
    {
        $title = sprintf('%s test SPA type', time());
        $addedTypeItemResult = $this->typeService->add($title);
        $this->assertEquals($title, $addedTypeItemResult->type()->title);

        // add item to SP
        $itemItemResult = $this->itemService->add($addedTypeItemResult->type()->entityTypeId, [
            'title' => sprintf('test sp item %s', time()),
            'xmlId' => sprintf('b24-php-sdk-test-item-%s', time())
        ])->item();

        // update item
        $newTitle = sprintf('test sp item %s updated', time());
        $updatedItemResult = $this->itemService->update($addedTypeItemResult->type()->entityTypeId, $itemItemResult->id, ['title' => $newTitle]);
        $this->assertEquals($newTitle, $updatedItemResult->item()->title);
        $this->assertTrue($updatedItemResult->isSuccess());

        $updatedItem = $this->itemService->get($addedTypeItemResult->type()->entityTypeId, $itemItemResult->id)->item();
        $this->assertEquals($newTitle, $updatedItem->title);

        $this->assertTrue($this->typeService->delete($addedTypeItemResult->getId())->isSuccess());
    }

    public function testGet(): void
    {
        $title = sprintf('%s test SPA type', time());
        $addedTypeItemResult = $this->typeService->add($title);
        $this->assertEquals($title, $addedTypeItemResult->type()->title);

        // add item to SP
        $itemItemResult = $this->itemService->add($addedTypeItemResult->type()->entityTypeId, [
            'title' => sprintf('test sp item %s', time()),
            'xmlId' => sprintf('b24-php-sdk-test-item-%s', time())
        ])->item();

        $item = $this->itemService->get($addedTypeItemResult->type()->entityTypeId, $itemItemResult->id)->item();
        $this->assertEquals($itemItemResult->title, $item->title);
        $this->assertEquals($itemItemResult->xmlId, $item->xmlId);

        $this->assertTrue($this->typeService->delete($addedTypeItemResult->type()->entityTypeId)->isSuccess());
    }

    public function testList(): void
    {
        $title = sprintf('%s test SPA type', time());
        $addedTypeItemResult = $this->typeService->add($title);
        $this->assertEquals($title, $addedTypeItemResult->type()->title);

        // add item to SP
        $this->itemService->add($addedTypeItemResult->type()->entityTypeId, [
            'title' => sprintf('test sp item %s', time()),
            'xmlId' => sprintf('b24-php-sdk-test-item-%s', time())
        ])->item();
        $this->itemService->add($addedTypeItemResult->type()->entityTypeId, [
            'title' => sprintf('test sp item %s', time()),
            'xmlId' => sprintf('b24-php-sdk-test-item-%s', time())
        ])->item();

        $items = $this->itemService->list($addedTypeItemResult->type()->entityTypeId, [], [], [])->getItems();
        $this->assertCount(2, $items);

        $this->assertTrue($this->typeService->delete($addedTypeItemResult->type()->entityTypeId)->isSuccess());
    }

    public function testGetSmartProcessItem(): void
    {
        $title = sprintf('%s test SPA type', time());
        $addedTypeItemResult = $this->typeService->add($title, null, [
            'relations' => [
                'child' => [
                    [
                        // allow bind to contact
                        'entityTypeId' => 3,
                        'isChildrenListEnabled' => 'N',
                        'isPredefined' => 'N'
                    ]
                ]
            ]
        ]);
        $this->assertEquals($title, $addedTypeItemResult->type()->title);

        // add item to SP
        $itemItemResult = $this->itemService->add($addedTypeItemResult->type()->entityTypeId, [
            'title' => sprintf('test sp item %s', time()),
            'xmlId' => sprintf('b24-php-sdk-test-item-%s', time())
        ])->item();

        // add contact with sp item
        // @phpstan-ignore-next-line argument.type
        $b24ContactId = $this->contactService->add([
            'NAME' => sprintf('Test contact %s', time()),
            'PARENT_ID_' . $addedTypeItemResult->type()->entityTypeId => $itemItemResult->id,
        ])->getId();
        $contact = $this->contactService->get($b24ContactId)->contact();
        $this->assertEquals(
            $itemItemResult->id,
            $contact->getSmartProcessItem($addedTypeItemResult->type()->entityTypeId)
        );
        $this->expectException(InvalidArgumentException::class);
        $contact->getSmartProcessItem(1);

        $b24ContactId = $this->contactService->add([
            'NAME' => sprintf('Test contact %s', time())
        ])->getId();
        $this->assertNull($this->contactService->get($b24ContactId)->contact()->getSmartProcessItem($addedTypeItemResult->type()->entityTypeId));

        $this->assertTrue($this->typeService->delete($addedTypeItemResult->type()->entityTypeId)->isSuccess());
    }

    public function testDelete(): void
    {
        $title = sprintf('%s test SPA type', time());
        $addedTypeItemResult = $this->typeService->add($title);
        $this->assertEquals($title, $addedTypeItemResult->type()->title);

        // add item to SP
        $itemItemResult = $this->itemService->add($addedTypeItemResult->type()->entityTypeId, [
            'title' => sprintf('test sp item %s', time()),
            'xmlId' => sprintf('b24-php-sdk-test-item-%s', time())
        ])->item();

        $this->assertTrue($this->itemService->delete($addedTypeItemResult->type()->entityTypeId, $itemItemResult->id)->isSuccess());

        $this->expectException(ItemNotFoundException::class);
        $this->itemService->get($addedTypeItemResult->type()->entityTypeId, $itemItemResult->id)->item();

        $this->assertTrue($this->typeService->delete($addedTypeItemResult->type()->entityTypeId)->isSuccess());
    }

    protected function setUp(): void
    {
        $this->typeService = Fabric::getServiceBuilder()->getCRMScope()->type();
        $this->itemService = Fabric::getServiceBuilder()->getCRMScope()->item();
        $this->contactService = Fabric::getServiceBuilder()->getCRMScope()->contact();
    }
}
