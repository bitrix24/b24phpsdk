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

namespace Bitrix24\SDK\Tests\Unit\Services\Task\AccessField\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldResult;
use Bitrix24\SDK\Services\Task\AccessField\Result\AccessFieldsResult;
use Bitrix24\SDK\Services\Task\AccessField\Service\AccessField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(AccessField::class)]
class AccessFieldTest extends TestCase
{
    private AccessField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new AccessField(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsAccessFieldResult(): void
    {
        $this->assertInstanceOf(
            AccessFieldResult::class,
            $this->service->get('id')
        );
    }

    #[Test]
    public function testListReturnsAccessFieldsResult(): void
    {
        $this->assertInstanceOf(
            AccessFieldsResult::class,
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
