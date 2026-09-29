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

namespace Bitrix24\SDK\Tests\Integration\Services\Pull;

use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Services\ServiceBuilderFactory;
use Psr\Log\NullLogger;

/** Prevent sensitive channel descriptors from appearing in integration debug logs. */
final class Factory
{
    public static function getServiceBuilder(): ServiceBuilder
    {
        return ServiceBuilderFactory::createServiceBuilderFromWebhook(
            $_ENV['BITRIX24_PHP_SDK_PLAYGROUND_WEBHOOK'] ?? $_ENV['BITRIX24_WEBHOOK'],
            logger: new NullLogger()
        );
    }
}
