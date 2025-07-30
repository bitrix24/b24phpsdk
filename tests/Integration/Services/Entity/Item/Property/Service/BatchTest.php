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
use Bitrix24\SDK\Services\Entity\Item\Property\Service\Batch;
use Bitrix24\SDK\Services\Entity\Item\Property\Service\Property;
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
     * @throws TransportException
     * @throws BaseException
     */
    public function testGet(): void
    {
        $propertyCount = 60;
        $properties = [];
        $propCodes = [];
        for ($i = 0; $i < $propertyCount; $i++) {
            $fields = $this->getPropertyFields($i);
            $properties[] = $fields;
            $propCodes[] = $fields['PROPERTY'];
        }

        $cnt = 0;
        foreach ($this->propertyService->batch->add($this->entity, $properties) as $result) {
            $cnt++;
        }

        $cnt = 0;
        foreach ($this->propertyService->batch->get($this->entity, $propCodes) as $item) {
            $cnt++;
        }

        $this->assertEquals($propertyCount, $cnt);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $propertyCount = 60;
        $properties = [];
        for ($i = 0; $i < $propertyCount; $i++) {
            $properties[] = $this->getPropertyFields($i);
        }

        $cnt = 0;
        foreach ($this->propertyService->batch->add($this->entity, $properties) as $result) {
            $cnt++;
        }

        $this->assertEquals($propertyCount, $cnt);
    }

    public function testDelete(): void
    {
        $propertyCount = 60;
        $properties = [];
        $propCodes = [];
        for ($i = 0; $i < $propertyCount; $i++) {
            $fields = $this->getPropertyFields($i);
            $properties[] = $fields;
            $propCodes[] = $fields['PROPERTY'];
        }

        foreach ($this->propertyService->batch->add($this->entity, $properties) as $result) {
        }

        foreach ($this->propertyService->batch->delete($this->entity, $propCodes) as $result) {
            $this->assertTrue($result->isSuccess());
        }

        $this->assertEquals(0, count($this->propertyService->get($this->entity)->getProperties() ));
    }

    public function testUpdate(): void
    {
        $propertyCount = 60;
        $properties = [];
        $propNames = [];
        $propCodes = [];
        for ($i = 0; $i < $propertyCount; $i++) {
            $fields = $this->getPropertyFields($i);
            $properties[] = $fields;
            $propNames[] = [
                'PROPERTY' => $fields['PROPERTY'],
                'NAME' => $fields['NAME'],
            ];
            $propCodes[] = $fields['PROPERTY'];
        }

        foreach ($this->propertyService->batch->add($this->entity, $properties) as $result) {
        }

        $modifiedNames = [];
        foreach ($propNames as $item) {
            $modifiedNames[$item['PROPERTY']] = [
                'PROPERTY' => $item['PROPERTY'],
                'NAME' => 'updated ' . Uuid::v7()->toRfc4122(),
            ];
        }

        foreach ($this->propertyService->batch->update($this->entity, array_values($modifiedNames)) as $result) {
            $this->assertTrue($result->isSuccess());
        }

        $updatedNames = [];
        foreach ($this->propertyService->batch->get($this->entity, $propCodes) as $item) {
            $updatedNames[$item->PROPERTY] = $item->NAME;
        }

        foreach ($modifiedNames as $code => $fields) {
            $this->assertEquals(
                $fields['NAME'],
                $updatedNames[$code],
            );
        }
    }
    
    private function getPropertyFields($num=1) {
        return [
            'PROPERTY' => 'TEST_PROPERTY_' . $num,
            'NAME' => Uuid::v7()->toRfc4122() . $num,
            'TYPE' => 'S',
        ];
    }
}
