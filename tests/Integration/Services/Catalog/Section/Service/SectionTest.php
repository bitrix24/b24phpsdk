<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Dmitriy Ignatenko <algonexys@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Tests\Integration\Services\Catalog\Section\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Catalog\Section\Service\Section;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Section::class)]
class SectionTest extends TestCase
{
    private Section $sectionService;

    private int $iblockId;

    /**
     * @var int[]
     */
    private array $createdSectionIds = [];

    #[\Override]
    protected function setUp(): void
    {
        $serviceBuilder = Factory::getServiceBuilder(true);
        $this->sectionService = $serviceBuilder->getCatalogScope()->section();
        $this->iblockId = $serviceBuilder->getCatalogScope()->catalog()
            ->list([], [], [], 1)->getCatalogs()[0]->iblockId;
    }

    #[\Override]
    protected function tearDown(): void
    {
        foreach ($this->createdSectionIds as $sectionId) {
            try {
                $this->sectionService->delete($sectionId);
            } catch (\Throwable) {
                // already removed, ignore
            }
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Section::add, Section::update, Section::get, Section::list, Section::delete')]
    public function testAddUpdateGetListDelete(): void
    {
        $name = sprintf('test section %s', time());
        $addResult = $this->sectionService->add([
            'name' => $name,
            'iblockId' => $this->iblockId,
        ]);
        $sectionId = $addResult->section()->id;
        $this->createdSectionIds[] = $sectionId;

        $this->assertSame($name, $addResult->section()->name);
        $this->assertSame($this->iblockId, $addResult->section()->iblockId);

        $updatedName = sprintf('updated test section %s', time());
        $updateResult = $this->sectionService->update($sectionId, ['name' => $updatedName, 'iblockId' => $this->iblockId]);
        $this->assertSame($updatedName, $updateResult->section()->name);

        $getResult = $this->sectionService->get($sectionId);
        $this->assertSame($updatedName, $getResult->section()->name);

        $listResult = $this->sectionService->list([], ['iblockId' => $this->iblockId, 'id' => $sectionId]);
        $this->assertCount(1, $listResult->getSections());

        $this->assertTrue($this->sectionService->delete($sectionId)->isSuccess());
        $this->createdSectionIds = array_diff($this->createdSectionIds, [$sectionId]);
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[TestDox('test Section::getFields')]
    public function testGetFields(): void
    {
        $fieldsDescription = $this->sectionService->getFields()->getFieldsDescription();
        $this->assertIsArray($fieldsDescription);
        $this->assertArrayHasKey('iblockId', $fieldsDescription);
        $this->assertArrayHasKey('name', $fieldsDescription);
    }
}
