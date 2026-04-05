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

namespace Bitrix24\SDK\Tests\Unit\Attributes;

use Bitrix24\SDK\Attributes\OaEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(OaEntity::class)]
class OaEntityAttributeTest extends TestCase
{
    #[Test]
    #[TestDox('attribute can be created with entityKey only')]
    public function testCreateWithEntityKeyOnly(): void
    {
        $oaEntity = new OaEntity('bitrix.tasks.taskdto');

        $this->assertSame('bitrix.tasks.taskdto', $oaEntity->entityKey);
        $this->assertNull($oaEntity->selectBuilder);
        $this->assertNull($oaEntity->itemBuilder);
    }

    #[Test]
    #[TestDox('attribute stores all provided class references')]
    public function testCreateWithAllParameters(): void
    {
        $oaEntity = new OaEntity(
            entityKey:     'bitrix.tasks.taskdto',
            selectBuilder: \stdClass::class,
            itemBuilder:   \stdClass::class,
        );

        $this->assertSame('bitrix.tasks.taskdto', $oaEntity->entityKey);
        $this->assertSame(\stdClass::class, $oaEntity->selectBuilder);
        $this->assertSame(\stdClass::class, $oaEntity->itemBuilder);
    }

    #[Test]
    #[TestDox('attribute is readable via reflection on a class')]
    public function testReadableViaReflection(): void
    {
        $target = new
        #[OaEntity('bitrix.tasks.taskdto')]
        class {};

        $attrs = (new \ReflectionClass($target))->getAttributes(OaEntity::class);

        $this->assertCount(1, $attrs);

        /** @var OaEntity $oaEntity */
        $oaEntity = $attrs[0]->newInstance();
        $this->assertSame('bitrix.tasks.taskdto', $oaEntity->entityKey);
    }
}
