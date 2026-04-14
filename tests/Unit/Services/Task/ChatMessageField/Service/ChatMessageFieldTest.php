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

namespace Bitrix24\SDK\Tests\Unit\Services\Task\ChatMessageField\Service;

use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Task\ChatMessageField\Result\ChatMessageFieldResult;
use Bitrix24\SDK\Services\Task\ChatMessageField\Result\ChatMessageFieldsResult;
use Bitrix24\SDK\Services\Task\ChatMessageField\Service\ChatMessageField;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(ChatMessageField::class)]
class ChatMessageFieldTest extends TestCase
{
    private ChatMessageField $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new ChatMessageField(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testGetReturnsChatMessageFieldResult(): void
    {
        $this->assertInstanceOf(
            ChatMessageFieldResult::class,
            $this->service->get('taskId')
        );
    }

    #[Test]
    public function testListReturnsChatMessageFieldsResult(): void
    {
        $this->assertInstanceOf(
            ChatMessageFieldsResult::class,
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
