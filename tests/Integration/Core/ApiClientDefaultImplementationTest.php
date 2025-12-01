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

namespace Bitrix24\SDK\Tests\Integration\Core;

use Bitrix24\SDK\Core\Contracts\ApiClientInterface;
use Bitrix24\SDK\Tests\Integration\Factory;
use PHPUnit\Framework\TestCase;
class ApiClientDefaultImplementationTest extends TestCase
{
    protected ApiClientInterface $apiClient;

    public function testFixProblemMethodsInApiClient(): void
    {
        // prepare data
        $taskId = (int)$this->apiClient->getResponse('tasks.task.add', [
            'fields' => [
                'TITLE' => sprintf('test task %s', time()),
                'RESPONSIBLE_ID' => (int)$this->apiClient->getResponse('user.current')->toArray(false)['result']['ID']
            ]
        ])->toArray(false)['result']['task']['id'];
        $this->apiClient->getResponse('task.elapseditem.add', [
            'TASKID' => $taskId,
            'ARFIELDS' => [
                'SECONDS' => 60
            ]
        ])->toArray(false);

        // call a problem method
        // without fix in apiclient or api v2 we have an
        // Symfony\Component\HttpClient\Exception\ClientException: HTTP/2 400 exception
        $result = $this->apiClient->getResponse('task.elapseditem.getlist', [
            'TASKID' => $taskId,
        ])->toArray();
        $this->assertIsArray($result);
    }

    public function setUp(): void
    {
        $this->apiClient = Factory::getCore()->getApiClient();
    }
}