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

namespace Bitrix24\SDK\Tests\Integration\Services\IM\Counters\Service;

use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\IM\Counters\Service\Counters;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[CoversClass(Counters::class)]
class CountersTest extends TestCase
{
    private Counters $countersService;

    #[\Override]
    protected function setUp(): void
    {
        $this->countersService = Factory::getServiceBuilder()->getIMScope()->counters();
    }

    /**
     * @throws BaseException
     * @throws TransportException
     */
    #[Test]
    #[TestDox('im.counters.get returns a CountersItemResult with valid counter values')]
    public function testGet(): void
    {
        $counters = $this->countersService->get()->counters();

        $this->assertIsArray($counters->TYPE);
        $this->assertIsArray($counters->CHAT);
        $this->assertIsArray($counters->CHAT_MUTED);
        $this->assertIsArray($counters->CHAT_UNREAD);
        $this->assertIsArray($counters->LINES);
        $this->assertIsArray($counters->DIALOG);
        $this->assertIsArray($counters->DIALOG_UNREAD);
    }
}
