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

namespace Bitrix24\SDK\Services\IM\Department\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\IM;
use Bitrix24\SDK\Services\IM\User\Result\UserItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['im']))]
class Batch
{
    public function __construct(protected IM\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list colleagues of the current user with detailed user data
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-colleagues-list.html
     *
     * @return Generator<int, UserItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.department.colleagues.list',
        'https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-colleagues-list.html',
        'Batch list colleagues of the current user with detailed user data'
    )]
    public function colleaguesList(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchColleaguesList',
            [
                'limit' => $limit,
            ]
        );

        $colleagues = $this->batch->getTraversableListByOffset(
            'im.department.colleagues.list',
            ['USER_DATA' => 'Y'],
            $limit
        );
        foreach ($colleagues as $key => $value) {
            yield $key => new UserItemResult($value);
        }
    }

    /**
     * Batch list user IDs of colleagues of the current user
     *
     * @link https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-colleagues-list.html
     *
     * @return Generator<int, int>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'im.department.colleagues.list',
        'https://apidocs.bitrix24.com/api-reference/chats/departments/im-department-colleagues-list.html',
        'Batch list user IDs of colleagues of the current user'
    )]
    public function colleaguesIdsList(?int $limit = null): Generator
    {
        $this->log->debug(
            'batchColleaguesIdsList',
            [
                'limit' => $limit,
            ]
        );

        $colleagueIds = $this->batch->getTraversableListByOffset(
            'im.department.colleagues.list',
            ['USER_DATA' => 'N'],
            $limit
        );
        foreach ($colleagueIds as $key => $value) {
            yield $key => (int)$value;
        }
    }
}
