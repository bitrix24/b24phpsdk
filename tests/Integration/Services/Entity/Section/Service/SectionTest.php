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
use Bitrix24\SDK\Services\Entity\Section\Service\Section;
use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

#[CoversClass(Section::class)]
#[CoversMethod(Section::class, 'add')]
#[CoversMethod(Section::class, 'get')]
#[CoversMethod(Section::class, 'delete')]
#[CoversMethod(Section::class, 'update')]
class SectionTest extends TestCase
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
     * @throws BaseException
     * @throws TransportException
     */
    public function testAdd(): void
    {
        $name = Uuid::v7()->toRfc4122();
        $sectionId = $this->sectionService->add($this->entity, $name, [])->getId();
        $sections = $this->sectionService->get($this->entity, [], ['ID' => $sectionId])->getSections();
        $this->assertEquals($sectionId, current(array_column($sections, 'ID')));
        $this->assertEquals($name, current(array_column($sections, 'NAME')));
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    public function testGet(): void
    {
        $name = Uuid::v7()->toRfc4122();
        $sectionId = $this->sectionService->add($this->entity, $name, [])->getId();
        $sections = $this->sectionService->get($this->entity, [], ['ID' => $sectionId])->getSections();
        
        $this->assertEquals($sectionId, current(array_column($sections, 'ID')));
    }

    public function testDelete(): void
    {
        $name = Uuid::v7()->toRfc4122();
        $sectionId = $this->sectionService->add($this->entity, $name, [])->getId();

        $this->assertTrue($this->sectionService->delete($this->entity, $sectionId)->isSuccess());
        $this->assertEquals([], $this->sectionService->get($this->entity, [], ['ID' => $sectionId])->getSections());
    }

    public function testUpdate(): void
    {
        $name = Uuid::v7()->toRfc4122();
        $sectionId = $this->sectionService->add($this->entity, $name, [])->getId();
        $sections = $this->sectionService->get($this->entity, [], ['ID' => $sectionId])->getSections();
        
        $this->assertEquals($sectionId, current(array_column($sections, 'ID')));

        $newName = Uuid::v7()->toRfc4122();
        $this->assertTrue($this->sectionService->update(
            $this->entity,$sectionId, ['NAME'=>$newName]
        )->isSuccess());

        $sections = $this->sectionService->get($this->entity, [], ['ID' => $sectionId])->getSections();
        $this->assertEquals($newName, current(array_column($sections, 'NAME')));
    }
}
