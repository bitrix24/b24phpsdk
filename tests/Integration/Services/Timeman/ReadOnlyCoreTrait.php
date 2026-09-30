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

namespace Bitrix24\SDK\Tests\Integration\Services\Timeman;

use Bitrix24\SDK\Core\Contracts\CoreInterface;
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Psr\Log\NullLogger;

/** Read-only legacy timeman tests must never enable tracking or replace office ranges. */
trait ReadOnlyCoreTrait
{
    private function getReadOnlyCore(): CoreInterface
    {
        return (new CoreBuilder())
            ->withLogger(new NullLogger())
            ->withCredentials(Credentials::createFromWebhook(new WebhookUrl($_ENV['BITRIX24_WEBHOOK'])))
            ->build();
    }
}
