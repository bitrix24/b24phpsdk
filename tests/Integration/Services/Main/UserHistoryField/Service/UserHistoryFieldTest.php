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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\UserHistoryField\Service;

use Bitrix24\SDK\Services\Main\UserHistoryField\Service\UserHistoryField;
use Bitrix24\SDK\Tests\Integration\Services\Main\UserHistory\HistoryFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UserHistoryField::class)]
class UserHistoryFieldTest extends TestCase
{
    use HistoryFixture;

    private UserHistoryField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = $this->mainScope()->userHistoryField();
    }

    public function testListReturnsNamedFieldDescriptors(): void
    {
        $result = $this->service->list();
        $items = $result->getUserHistoryFields();
        $descriptions = $result->getFieldsDescription();
        $rawItems = $result->getCoreResponse()->getResponseData()->getResult()['items'];

        self::assertNotEmpty($items);
        self::assertCount(count($rawItems), $items);
        self::assertCount(count($rawItems), $descriptions);
        self::assertArrayHasKey('id', $descriptions);
        self::assertArrayHasKey('dateInsert', $descriptions);
        foreach ($rawItems as $rawItem) {
            self::assertSame($rawItem, $descriptions[$rawItem['name']]);
        }
    }

    public function testGetPreservesApiObjectMetadata(): void
    {
        $item = $this->service->get('dateInsert')->getUserHistoryField();

        self::assertSame('dateInsert', $item->name);
        self::assertSame('object', $item->type);
    }

    public function testGetWithPartialSelection(): void
    {
        $item = $this->service->get('dateInsert', ['name', 'type'])->getUserHistoryField();

        self::assertSame('dateInsert', $item->name);
        self::assertSame('object', $item->type);
        self::assertNull($item->title);
        self::assertNull($item->filterable);
    }

    public function testListWithPartialSelection(): void
    {
        $result = $this->service->list(['name', 'type']);
        $items = $result->getUserHistoryFields();

        self::assertNotEmpty($items);
        foreach ($items as $item) {
            self::assertNotEmpty($item->name);
            self::assertNotEmpty($item->type);
            self::assertNull($item->title);
            self::assertNull($item->editableGroups);
        }

        self::assertSame('object', $result->getFieldsDescription()['dateInsert']['type']);
    }
}
