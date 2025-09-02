<?php

/**
 * This file is part of the bitrix24-php-sdk package.
 *
 * © Vadim Soluyanov <vadimsallee@gmail.com>
 *
 * For the full copyright and license information, please view the MIT-LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Bitrix24\SDK\Services\Task\Planner\Service;

use Bitrix24\SDK\Attributes\ApiEndpointMetadata;
use Bitrix24\SDK\Attributes\ApiServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\TransportException;
use Bitrix24\SDK\Services\AbstractService;
use Psr\Log\LoggerInterface;

#[ApiServiceMetadata(new Scope(['task']))]
class Planner extends AbstractService
{
    /**
     * Retrieves a list of tasks from the "Daily Plan".
     *
     * @link https://apidocs.bitrix24.com/api-reference/tasks/planner/task-planner-get-list.html
     *
     *
     * @throws BaseException
     * @throws TransportException
     */
    #[ApiEndpointMetadata(
        'task.planner.getlist',
        'https://apidocs.bitrix24.com/api-reference/tasks/planner/task-planner-get-list.html',
        'Retrieves a list of tasks from the "Daily Plan"'
    )]
    public function getList(): array
    {
        return $this->core->call(
            'task.planner.getlist',
            [
            ]
        )->getResponseData()->getResult();
    }

}
