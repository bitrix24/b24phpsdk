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

use Bitrix24\SDK\Attributes\OpenApiEntity;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(OpenApiEntity::class)]
class OpenApiEntityAttributeTest extends TestCase
{
    #[Test]
    #[TestDox('attribute can be created with entityKey only')]
    public function testCreateWithEntityKeyOnly(): void
    {
        $oaEntity = new OpenApiEntity('bitrix.tasks.taskdto');

        $this->assertSame('bitrix.tasks.taskdto', $oaEntity->entityKey);
        $this->assertNull($oaEntity->selectBuilder);
        $this->assertNull($oaEntity->itemBuilder);
    }

    #[Test]
    #[TestDox('attribute stores all provided class references')]
    public function testCreateWithAllParameters(): void
    {
        $oaEntity = new OpenApiEntity(
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
        #[OpenApiEntity('bitrix.tasks.taskdto')]
        class {};

        $attrs = (new \ReflectionClass($target))->getAttributes(OpenApiEntity::class);

        $this->assertCount(1, $attrs);

        /** @var OpenApiEntity $oaEntity */
        $oaEntity = $attrs[0]->newInstance();
        $this->assertSame('bitrix.tasks.taskdto', $oaEntity->entityKey);
    }
}
