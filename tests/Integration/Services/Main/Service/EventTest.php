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

namespace Bitrix24\SDK\Tests\Integration\Services\Main\Service;

use Bitrix24\SDK\Core\ValueObjects\Url;
use Bitrix24\SDK\Services\Main\Service\Event;
use Bitrix24\SDK\Services\Task\Events\OnTaskAdd\OnTaskAdd;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Event::class)]
class EventTest extends TestCase
{
    public function testHandlerLifecycleWithUrl(): void
    {
        $event = Factory::getServiceBuilder(true)->getMainScope()->event();
        $url = new Url(rtrim((string) $_ENV['BITRIX24_PHP_SDK_APPLICATION_DOMAIN_URL'], '/') . '/sdk533-inert-' . bin2hex(random_bytes(6)));
        $bound = $event->bind(OnTaskAdd::CODE, $url);
        try {
            self::assertTrue($bound->isBinded());
            $handlers = array_map(static fn ($item): string => $item->handler, $event->get()->getEventHandlers());
            self::assertContains($url->getUrl(), $handlers);
        } finally {
            self::assertSame(1, $event->unbind(OnTaskAdd::CODE, $url)->getUnbindedHandlersCount());
        }
    }
}
