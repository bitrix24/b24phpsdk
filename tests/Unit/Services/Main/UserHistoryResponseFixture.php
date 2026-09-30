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

namespace Bitrix24\SDK\Tests\Unit\Services\Main;

use Bitrix24\SDK\Core\ApiLevelErrorHandler;
use Bitrix24\SDK\Core\Commands\Command;
use Bitrix24\SDK\Core\Response\Response;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

trait UserHistoryResponseFixture
{
    private static function response(array $result): Response
    {
        $mockHttpClient = new MockHttpClient(new MockResponse(json_encode(['result' => $result], JSON_THROW_ON_ERROR)));
        return new Response(
            $mockHttpClient->request('POST', 'https://example.test/'),
            new Command('main.user.history.list', []),
            new ApiLevelErrorHandler(new NullLogger()),
            new NullLogger()
        );
    }
}
