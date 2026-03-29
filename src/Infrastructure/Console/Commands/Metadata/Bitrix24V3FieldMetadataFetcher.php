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

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Metadata;

use Bitrix24\SDK\Core\Contracts\ApiVersion;
use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Psr\Log\LoggerInterface;

class Bitrix24V3FieldMetadataFetcher
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function fetch(string $webhook, string $methodName): array
    {
        return (new CoreBuilder())
            ->withLogger($this->logger)
            ->withCredentials(Credentials::createFromWebhook(new WebhookUrl($webhook)))
            ->build()
            ->call($methodName, apiVersion: ApiVersion::v3)
            ->getResponseData()
            ->getResult();
    }
}
