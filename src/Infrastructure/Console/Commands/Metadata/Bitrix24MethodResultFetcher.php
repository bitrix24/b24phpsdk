<?php

declare(strict_types=1);

namespace Bitrix24\SDK\Infrastructure\Console\Commands\Metadata;

use Bitrix24\SDK\Core\CoreBuilder;
use Bitrix24\SDK\Core\Credentials\Credentials;
use Bitrix24\SDK\Core\Credentials\WebhookUrl;
use Psr\Log\LoggerInterface;

class Bitrix24MethodResultFetcher
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function fetch(string $webhook, string $methodName, array $params = []): array
    {
        return (new CoreBuilder())
            ->withLogger($this->logger)
            ->withCredentials(Credentials::createFromWebhook(new WebhookUrl($webhook)))
            ->build()
            ->call($methodName, $params)
            ->getResponseData()
            ->getResult();
    }
}
