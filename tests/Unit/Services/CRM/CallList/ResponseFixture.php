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

namespace Bitrix24\SDK\Tests\Unit\Services\CRM\CallList;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Response\Response;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

trait ResponseFixture
{
    private function response(mixed $result, string $method = 'crm.calllist.get'): Response
    {
        return $this->responseBody(['result' => $result], $method);
    }

    private function responseBody(array $body, string $method): Response
    {
        $logger = new NullLogger();
        $httpClient = new MockHttpClient(new MockResponse(json_encode($body, JSON_THROW_ON_ERROR)));

        return new Response(
            $httpClient->request('POST', 'https://example.com/rest/' . $method),
            new Command($method, []),
            new ApiLevelErrorHandler($logger),
            $logger
        );
    }
}
