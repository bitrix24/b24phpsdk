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

namespace Bitrix24\SDK\Tests\Integration\Services\Entity\Item\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Entity\Item\Service\Batch;
use Bitrix24\SDK\Services\Entity\Item\Service\Item;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Batch::class)]
#[CoversMethod(Batch::class, 'add')]
#[CoversMethod(Batch::class, 'delete')]
#[CoversMethod(Batch::class, 'get')]
#[CoversMethod(Batch::class, 'update')]
class BatchTest extends TestCase
{
    private ServiceBuilder $sb;
    /**
     * @var array<int,non-empty-string>
     */
    private array $entities;

    protected function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder(true);
    }

    protected function tearDown(): void
    {
        foreach ($this->entities as $entity) {
            $this->sb->getEntityScope()->entity()->delete($entity);
        }
    }

    /**
     * @throws TransportException
     * @throws BaseException
     */
    public function testGet(): void
    {
        $elementsCount = 160;
        $entity = (string)time();

        $elements = [];
        for ($i = 0; $i < $elementsCount; $i++) {
            $elements[] = [
                'NAME' => 'name ' . Uuid::v7()->toRfc4122(),
            ];
        }

        //create entity
        $this->assertTrue(
            $this->sb->getEntityScope()->entity()->add(
                $entity,
                'test entity',
                []
            )->isSuccess()
        );
        $this->entities[] = $entity;

        //add items in batch mode
        $addedItemsIds = [];
        foreach ($this->sb->getEntityScope()->item()->batch->add($entity, $elements) as $key => $result) {
            $addedItemsIds[] = $result->getId();
        }
        $this->assertCount($elementsCount, $addedItemsIds);

        // read elements in batch mode
        $resultElements = [];
        foreach ($this->sb->getEntityScope()->item()->batch->get($entity, [], []) as $key => $item) {
            $resultElements[] = $item;
        }

        for ($i = 0; $i < $elementsCount; $i++) {
            $this->assertArrayHasKey($i, $resultElements);
            $this->assertEquals($addedItemsIds[$i], $resultElements[$i]->ID);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $elementsCount = 160;
        $entity = (string)time();

        $elements = [];
        for ($i = 0; $i < $elementsCount; $i++) {
            $elements[] = [
                'NAME' => 'name ' . Uuid::v7()->toRfc4122(),
            ];
        }

        //create entity
        $this->assertTrue(
            $this->sb->getEntityScope()->entity()->add(
                $entity,
                'test entity',
                []
            )->isSuccess()
        );
        $this->entities[] = $entity;

        //add items in batch mode
        $addedItemsIds = [];
        foreach ($this->sb->getEntityScope()->item()->batch->add($entity, $elements) as $result) {
            $addedItemsIds[] = $result->getId();
        }
        $this->assertCount($elementsCount, $addedItemsIds);

        // delete elements
        foreach ($this->sb->getEntityScope()->item()->batch->delete($entity, $addedItemsIds) as $result) {
            $this->assertTrue($result->isSuccess());
        }
    }

    public function testDelete(): void
    {
        $elementsCount = 160;
        $entity = (string)time();

        $elements = [];
        for ($i = 0; $i < $elementsCount; $i++) {
            $elements[] = [
                'NAME' => 'name ' . Uuid::v7()->toRfc4122(),
            ];
        }

        //create entity
        $this->assertTrue(
            $this->sb->getEntityScope()->entity()->add(
                $entity,
                'test entity',
                []
            )->isSuccess()
        );
        $this->entities[] = $entity;

        //add items in batch mode
        $addedItemsIds = [];
        foreach ($this->sb->getEntityScope()->item()->batch->add($entity, $elements) as $result) {
            $addedItemsIds[] = $result->getId();
        }
        $this->assertCount($elementsCount, $addedItemsIds);

        // delete elements
        foreach ($this->sb->getEntityScope()->item()->batch->delete($entity, $addedItemsIds) as $result) {
            $this->assertTrue($result->isSuccess());
        }
        $this->assertEquals([], $this->sb->getEntityScope()->item()->get($entity, [], ['ID' => $addedItemsIds])->getItems());
    }

    public function testUpdate(): void
    {
        $elementsCount = 160;
        $entity = (string)time();

        $elements = [];
        for ($i = 0; $i < $elementsCount; $i++) {
            $elements[] = [
                'NAME' => 'name ' . Uuid::v7()->toRfc4122(),
            ];
        }

        //create entity
        $this->assertTrue(
            $this->sb->getEntityScope()->entity()->add(
                $entity,
                'test entity',
                []
            )->isSuccess()
        );
        $this->entities[] = $entity;

        //add items in batch mode
        $addedItemsIds = [];
        foreach ($this->sb->getEntityScope()->item()->batch->add($entity, $elements) as $result) {
            $addedItemsIds[] = $result->getId();
        }
        $this->assertCount($elementsCount, $addedItemsIds);

        // read elements in batch mode
        $resultElements = [];
        foreach ($this->sb->getEntityScope()->item()->batch->get($entity, [], []) as $key => $item) {
            $resultElements[] = $item;
        }

        // prepare data for update
        $modifiedElements = [];
        foreach ($resultElements as $key => $item) {
            $modifiedElements[] = [
                'id' => $item->ID,
                'NAME' => 'updated name ' . Uuid::v7()->toRfc4122(),
            ];
        }

        // batch update elements
        foreach ($this->sb->getEntityScope()->item()->batch->update($entity, $modifiedElements) as $result) {
            $this->assertTrue($result->isSuccess());
        }

        // read elements in batch mode
        $updatedElements = [];
        foreach ($this->sb->getEntityScope()->item()->batch->get($entity, [], []) as $key => $item) {
            $updatedElements[] = $item;
        }

        for ($i = 0; $i < $elementsCount; $i++) {
            $this->assertEquals(
                $modifiedElements[$i]['NAME'],
                $updatedElements[$i]->NAME,
            );
        }

        // delete elements
        foreach ($this->sb->getEntityScope()->item()->batch->delete($entity, $addedItemsIds) as $result) {
            $this->assertTrue($result->isSuccess());
        }
    }
}