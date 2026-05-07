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

namespace Bitrix24\SDK\Tests\Unit\Services\Task\FileField\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Task\FileField\Result\FileFieldResult;
use Bitrix24\SDK\Services\Task\FileField\Result\FileFieldsResult;
use Bitrix24\SDK\Services\Task\FileField\Service\FileField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FileField::class)]
class FileFieldTest extends TestCase
{
    private FileField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new FileField(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsFileFieldResult(): void
    {
        $this->assertInstanceOf(
            FileFieldResult::class,
            $this->service->get('taskId')
        );
    }

    #[Test]
    public function testListReturnsFileFieldsResult(): void
    {
        $this->assertInstanceOf(
            FileFieldsResult::class,
            $this->service->list()
        );
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        $this->service->get('');
    }
}
