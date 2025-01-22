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
use Bitrix24\SDK\Services\Entity\Item\Service\Item;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Item::class)]
#[CoversMethod(Item::class, 'add')]
#[CoversMethod(Item::class, 'get')]
#[CoversMethod(Item::class, 'delete')]
#[CoversMethod(Item::class, 'update')]
class ItemTest extends TestCase
{
    private ServiceBuilder $sb;
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
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $entity = (string)time();
        $this->assertTrue(
            $this->sb->getEntityScope()->entity()->add(
                $entity,
                'test entity',
                []
            )->isSuccess()
        );
        $this->entities[] = $entity;


        $name = Uuid::v7()->toRfc4122();
        $itemId = $this->sb->getEntityScope()->item()->add($entity, $name, [])->getId();
        $items = $this->sb->getEntityScope()->item()->get($entity, [], ['ID' => $itemId])->getItems();
        $this->assertEquals($itemId, current(array_column($items, 'ID')));
        $this->assertEquals($name, current(array_column($items, 'NAME')));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $entity = (string)time();
        $this->assertTrue(
            $this->sb->getEntityScope()->entity()->add(
                $entity,
                'test entity',
                []
            )->isSuccess()
        );
        $this->entities[] = $entity;

        $name = Uuid::v7()->toRfc4122();
        $itemId = $this->sb->getEntityScope()->item()->add($entity, $name, [])->getId();

        $items = $this->sb->getEntityScope()->item()->get($entity, [], ['ID' => $itemId])->getItems();
        $this->assertEquals($itemId, current(array_column($items, 'ID')));
    }

    public function testDelete(): void
    {
        $entity = (string)time();
        $this->assertTrue(
            $this->sb->getEntityScope()->entity()->add(
                $entity,
                'test entity',
                []
            )->isSuccess()
        );
        $this->entities[] = $entity;

        $name = Uuid::v7()->toRfc4122();
        $itemId = $this->sb->getEntityScope()->item()->add($entity, $name, [])->getId();

        $this->assertTrue($this->sb->getEntityScope()->item()->delete($entity, $itemId)->isSuccess());
        $this->assertEquals([], $this->sb->getEntityScope()->item()->get($entity, [], ['ID' => $itemId])->getItems());
    }

    public function testUpdate(): void
    {
        $entity = (string)time();
        $this->assertTrue(
            $this->sb->getEntityScope()->entity()->add(
                $entity,
                'test entity',
                []
            )->isSuccess()
        );
        $this->entities[] = $entity;

        $name = Uuid::v7()->toRfc4122();
        $itemId = $this->sb->getEntityScope()->item()->add($entity, $name, [])->getId();

        $items = $this->sb->getEntityScope()->item()->get($entity, [], ['ID' => $itemId])->getItems();
        $this->assertEquals($itemId, current(array_column($items, 'ID')));

        $newName = Uuid::v7()->toRfc4122();
        $this->assertTrue($this->sb->getEntityScope()->item()->update(
            $entity,$itemId, ['NAME'=>$newName]
        )->isSuccess());

        $items = $this->sb->getEntityScope()->item()->get($entity, [], ['ID' => $itemId])->getItems();
        $this->assertEquals($newName, current(array_column($items, 'NAME')));
    }
}