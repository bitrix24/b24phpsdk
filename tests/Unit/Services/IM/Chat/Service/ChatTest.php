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

namespace Bitrix24\SDK\Tests\Unit\Services\IM\Chat\Service;

use Bitrix24\SDK\Services\IM\Chat\Service\Chat;
use Bitrix24\SDK\Tests\Unit\Stubs\NullCore;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

#[CoversClass(Chat::class)]
class ChatTest extends TestCase
{
    private Chat $service;

    #[\Override]
    protected function setUp(): void
    {
        $this->service = new Chat(new NullCore(), new NullLogger());
    }

    #[Test]
    public function testServiceInstantiates(): void
    {
        $this->assertInstanceOf(Chat::class, $this->service);
    }
}
