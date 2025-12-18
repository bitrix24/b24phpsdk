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

namespace Bitrix24\SDK\Tests\Integration\Services\Entity\Section\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\Entity\Section\Service\Batch;
use Bitrix24\SDK\Services\Entity\Section\Service\Section;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
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
    
    private Section $sectionService;
    
    private string $entity = '';

    #[\Override]
    protected function setUp(): void
    {
        $this->sb = Factory::getServiceBuilder(true);
        $this->sectionService = $this->sb->getEntityScope()->section();
        $this->entity = (string)time();
        $this->sb->getEntityScope()->entity()->add($this->entity, 'Test entity', []);
    }

    #[\Override]
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
        $sectionsCount = 60;
        $sections = [];
        for ($i = 0; $i < $sectionsCount; $i++) {
            $sections[] = [
                'NAME' => 'name ' . Uuid::v7()->toRfc4122(),
            ];
        }

        $addedSectionIds = [];
        foreach ($this->sectionService->batch->add($this->entity, $sections) as $result) {
            $addedSectionIds[] = $result->getId();
        }

        $this->assertCount($sectionsCount, $addedSectionIds);

        $resultSections = [];
        foreach ($this->sectionService->batch->get($this->entity, [], []) as $item) {
            $resultSections[$item->ID] = $item;
        }

        foreach ($addedSectionIds as $addedSectionId) {
            $this->assertArrayHasKey($addedSectionId, $resultSections);
            $this->assertEquals($addedSectionId, $resultSections[$addedSectionId]->ID);
        }
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $sectionsCount = 60;
        $sections = [];
        for ($i = 0; $i < $sectionsCount; $i++) {
            $sections[] = [
                'NAME' => 'name ' . Uuid::v7()->toRfc4122(),
            ];
        }

        $addedSectionIds = [];
        foreach ($this->sectionService->batch->add($this->entity, $sections) as $result) {
            $addedSectionIds[] = $result->getId();
        }

        $this->assertCount($sectionsCount, $addedSectionIds);
    }

    public function testDelete(): void
    {
        $sectionsCount = 60;
        $sections = [];
        for ($i = 0; $i < $sectionsCount; $i++) {
            $sections[] = [
                'NAME' => 'name ' . Uuid::v7()->toRfc4122(),
            ];
        }

        $addedSectionIds = [];
        foreach ($this->sectionService->batch->add($this->entity, $sections) as $result) {
            $addedSectionIds[] = $result->getId();
        }

        $this->assertCount($sectionsCount, $addedSectionIds);

        foreach ($this->sectionService->batch->delete($this->entity, $addedSectionIds) as $result) {
            $this->assertTrue($result->isSuccess());
        }

        $this->assertEquals([], $this->sectionService->get($this->entity, [], ['ID' => $addedSectionIds])->getSections());
    }

    public function testUpdate(): void
    {
        $sectionsCount = 60;
        $sections = [];
        for ($i = 0; $i < $sectionsCount; $i++) {
            $sections[] = [
                'NAME' => 'name ' . Uuid::v7()->toRfc4122(),
            ];
        }

        $addedSectionIds = [];
        foreach ($this->sectionService->batch->add($this->entity, $sections) as $result) {
            $addedSectionIds[] = $result->getId();
        }

        $this->assertCount($sectionsCount, $addedSectionIds);

        $resultSections = [];
        foreach ($this->sectionService->batch->get($this->entity, [], []) as $item) {
            $resultSections[] = $item;
        }

        $modifiedSections = [];
        foreach ($resultSections as $item) {
            $modifiedSections[$item->ID] = [
                'ID' => $item->ID,
                'NAME' => 'updated name ' . Uuid::v7()->toRfc4122(),
            ];
        }

        // batch update elements
        foreach ($this->sectionService->batch->update($this->entity, array_values($modifiedSections)) as $result) {
            $this->assertTrue($result->isSuccess());
        }

        // read elements in batch mode
        $updatedElements = [];
        foreach ($this->sectionService->batch->get($this->entity, [], []) as $item) {
            $updatedElements[$item->ID] = $item;
        }

        foreach ($modifiedSections as $id => $fields) {
            $this->assertEquals(
                $fields['NAME'],
                $updatedElements[$id]->NAME,
            );
        }
    }
}
