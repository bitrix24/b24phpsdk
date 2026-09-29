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

namespace Bitrix24\SDK\Tests\Unit\Services\Telephony\FollowUpField\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Telephony\FollowUpField\Result\FollowUpFieldResult;
use Bitrix24\SDK\Services\Telephony\FollowUpField\Result\FollowUpFieldsResult;
use Bitrix24\SDK\Services\Telephony\FollowUpField\Service\FollowUpField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(FollowUpField::class)]
class FollowUpFieldTest extends TestCase
{
    private FollowUpField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new FollowUpField(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsFollowUpFieldResult(): void
    {
        $this->assertInstanceOf(FollowUpFieldResult::class, $this->service->get('callId'));
    }

    #[Test]
    public function testListReturnsFollowUpFieldsResult(): void
    {
        $this->assertInstanceOf(FollowUpFieldsResult::class, $this->service->list());
    }

    #[Test]
    public function testGetThrowsOnEmptyName(): void
    {
        $this->expectException(InvalidArgumentException::class);
        /** @phpstan-ignore argument.type */
        $this->service->get('');
    }
}
