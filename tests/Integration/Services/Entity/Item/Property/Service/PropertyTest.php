<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Entity\Item\Property\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Entity\Item\Property\Service\Property;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Property::class)]
#[CoversMethod(Property::class, 'add')]
#[CoversMethod(Property::class, 'get')]
#[CoversMethod(Property::class, 'delete')]
#[CoversMethod(Property::class, 'update')]
class PropertyTest extends TestCase
{
    private ServiceBuilder $sb;
    
    private Property $propertyService;
    
    private string $entity = '';

    protected function setUp(): void
    {
        $this->sb = Fabric::getServiceBuilder(true);
        $this->propertyService = $this->sb->getEntityScope()->itemProperty();
        $this->entity = (string)time();
        $this->sb->getEntityScope()->entity()->add($this->entity, 'Test entity', []);
    }

    protected function tearDown(): void
    {
        $this->sb->getEntityScope()->entity()->delete($this->entity);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $code = 'TEST_PROPERTY';
        $res = $this->addProperty($code);
        $this->assertTrue($res->isSuccess());
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $code = 'TEST_PROPERTY';
        $this->addProperty($code);
        $properties = $this->propertyService->get($this->entity, $code)->getProperties();
        
        $this->assertEquals($code, current(array_column($properties, 'PROPERTY')));
    }

    public function testDelete(): void
    {
        $code = 'TEST_PROPERTY';
        $this->addProperty($code);

        $this->assertTrue($this->propertyService->delete($this->entity, $code)->isSuccess());
    }

    public function testUpdate(): void
    {
        $code = 'TEST_PROPERTY';;
        $this->addProperty($code);
        $properties = $this->propertyService->get($this->entity, $code)->getProperties();
        
        $this->assertEquals($code, current(array_column($properties, 'PROPERTY')));

        $newName = Uuid::v7()->toRfc4122();
        $this->assertTrue($this->propertyService->update(
            $this->entity,$code, ['NAME'=>$newName]
        )->isSuccess());

        $properties = $this->propertyService->get($this->entity, $code)->getProperties();
        $this->assertEquals($newName, current(array_column($properties, 'NAME')));
    }
    
    private function addProperty($code) {
        $name = Uuid::v7()->toRfc4122();
        $type = 'S';
        
        return $this->propertyService->add($this->entity, $code, $name, $type);
    }
}
