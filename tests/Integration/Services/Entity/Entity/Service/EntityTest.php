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

namespace Bitrix24\SDK\Tests\Integration\Services\Entity\Entity\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Entity\Entity\Service\Entity;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Entity::class)]
#[CoversMethod(Entity::class, 'add')]
#[CoversMethod(Entity::class, 'get')]
#[CoversMethod(Entity::class, 'delete')]
#[CoversMethod(Entity::class, 'rights')]
#[CoversMethod(Entity::class, 'update')]
class EntityTest extends TestCase
{
    protected ServiceBuilder $sb;

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Entity::add')]
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
        $this->assertTrue($this->sb->getEntityScope()->entity()->delete($entity)->isSuccess());
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

        $newName = Uuid::v7()->toRfc4122();

        $this->assertTrue($this->sb->getEntityScope()->entity()->update($entity, $newName)->isSuccess());
        $this->assertContains(
            $newName,
            array_column($this->sb->getEntityScope()->entity()->get()->getEntities(), 'NAME')
        );

        $this->assertTrue($this->sb->getEntityScope()->entity()->delete($entity)->isSuccess());
    }

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
        $entities = $this->sb->getEntityScope()->entity()->get()->getEntities();
        $this->assertContains($entity, array_column($entities, 'ENTITY'));


        $this->assertTrue($this->sb->getEntityScope()->entity()->delete($entity)->isSuccess());
    }

    public function testGetEntity(): void
    {
        $entity = (string)time();
        $this->assertTrue(
            $this->sb->getEntityScope()->entity()->add(
                $entity,
                'test entity',
                []
            )->isSuccess()
        );
        $entities = $this->sb->getEntityScope()->entity()->get($entity)->getEntities();
        $this->assertContains($entity, array_column($entities, 'ENTITY'));

        $this->assertTrue($this->sb->getEntityScope()->entity()->delete($entity)->isSuccess());
    }

    public function testRights(): void
    {
        $entity = (string)time();
        $this->assertTrue(
            $this->sb->getEntityScope()->entity()->add(
                $entity,
                'test entity',
                []
            )->isSuccess()
        );
        $this->assertEquals('X', current($this->sb->getEntityScope()->entity()->rights($entity)->getRights()));
        $this->assertTrue($this->sb->getEntityScope()->entity()->delete($entity)->isSuccess());
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
        $this->assertTrue($this->sb->getEntityScope()->entity()->delete($entity)->isSuccess());

        $entities = $this->sb->getEntityScope()->entity()->get()->getEntities();
        $this->assertNotContains($entity, array_column($entities, 'ENTITY'));
    }

    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder(true);
    }
}