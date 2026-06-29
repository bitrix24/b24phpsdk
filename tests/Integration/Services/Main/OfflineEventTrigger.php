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

namespace Bitrix24\SDK\Tests\Integration\Services\Main;

use Bitrix24\SDK\Services\ServiceBuilder;
use Bitrix24\SDK\Tests\Integration\Factory;

/**
 * Helper for offline-events integration tests.
 *
 * An application does not receive offline events for changes it makes itself, so the tests must
 * trigger the change from a different context — the incoming webhook — while reading the queue via
 * application credentials.
 *
 * Loading application credentials (`Factory::getServiceBuilder(true)`) blanks `BITRIX24_WEBHOOK`
 * in `$_ENV`, so the webhook URL is captured once (process-wide) and restored before each webhook
 * service builder is created. Call this BEFORE building any application service builder in a test.
 */
final class OfflineEventTrigger
{
    public static function webhookServiceBuilder(): ServiceBuilder
    {
        static $webhookUrl = null;
        if ($webhookUrl === null && !empty($_ENV['BITRIX24_WEBHOOK'])) {
            $webhookUrl = $_ENV['BITRIX24_WEBHOOK'];
        }

        if (is_string($webhookUrl)) {
            $_ENV['BITRIX24_WEBHOOK'] = $webhookUrl;
        }

        return Factory::getServiceBuilder(false);
    }
}
