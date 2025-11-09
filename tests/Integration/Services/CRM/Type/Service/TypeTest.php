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

namespace Bitrix24\SDK\Tests\Integration\Services\CRM\Type\Service;

use Bitrix24\SDK\Core\Fields\FieldsFilter;
use Bitrix24\SDK\Services\CRM\Type\Result\TypeItemResult;
use Bitrix24\SDK\Services\CRM\Type\Service\Type;
use Bitrix24\SDK\Tests\Integration\Fabric;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Bitrix24\SDK\Tests\CustomAssertions\CustomBitrix24Assertions;

#[CoversClass(Type::class)]
class TypeTest extends TestCase
{
    use CustomBitrix24Assertions;

    protected Type $typeService;

    // in response, we have all system fields and some additional system fields
    //    public function testAllSystemFieldsAnnotated(): void
    //    {
    //        $propListFromApi = (new FieldsFilter())->filterSystemFields(array_keys($this->typeService->fields()->getFieldsDescription()['fields']));
    //        $this->assertBitrix24AllResultItemFieldsAnnotated($propListFromApi, TypeItemResult::class);
    //    }

    public function testAdd(): void
    {
        $title = sprintf('%s test SPA type', time());
        $result = $this->typeService->add($title);
        $this->assertEquals($title, $result->type()->title);
        $this->assertTrue($this->typeService->delete($result->getId())->isSuccess());
    }

    public function testUpdate(): void
    {
        $title = sprintf('%s test SPA type', time());
        $result = $this->typeService->add($title);
        $this->assertEquals($title, $result->type()->title);

        $title = sprintf('%s updated SPA type', time());
        $updatedResult = $this->typeService->update($result->getId(), ['title' => $title]);
        $this->assertEquals($title, $updatedResult->type()->title);

        $this->assertTrue($this->typeService->delete($result->getId())->isSuccess());
    }

    public function testGet(): void
    {
        $title = sprintf('%s test SPA type', time());
        $addResult = $this->typeService->add($title);

        $result = $this->typeService->get($addResult->getId());
        $this->assertEquals($title, $addResult->type()->title);
        $this->assertEquals($result->type()->id, $addResult->type()->id);
        $this->assertTrue($this->typeService->delete($addResult->getId())->isSuccess());
    }

    public function testList(): void
    {
        $title = sprintf('%s test SPA type', time());
        $addResult = $this->typeService->add($title);
        $result = $this->typeService->list([], ['id' => $addResult->getId()])->getTypes()[0];
        $this->assertEquals($title, $addResult->type()->title);
        $this->assertEquals($result->id, $addResult->type()->id);
        $this->assertTrue($this->typeService->delete($addResult->getId())->isSuccess());
    }

    public function testGetByEntityTypeId(): void
    {
        $title = sprintf('%s test SPA type', time());
        $result = $this->typeService->add($title);
        $this->assertEquals($title, $result->type()->title);

        $resultTypeId = $this->typeService->getByEntityTypeId($result->type()->entityTypeId);
        $this->assertEquals($title, $resultTypeId->type()->title);
        $this->assertEquals($result->type()->id, $resultTypeId->type()->id);
        $this->assertTrue($this->typeService->delete($result->getId())->isSuccess());
    }

    public function testDelete(): void
    {
        $title = sprintf('%s test SPA type', time());
        $result = $this->typeService->add($title);
        $this->assertEquals($title, $result->type()->title);
        $this->assertTrue($this->typeService->delete($result->getId())->isSuccess());
    }

    protected function setUp(): void
    {
        $this->typeService = Fabric::getServiceBuilder()->getCRMScope()->type();
    }
}
