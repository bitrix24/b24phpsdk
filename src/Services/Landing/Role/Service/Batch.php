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

namespace Bitrix24\SDK\Services\Landing\Role\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Services\Landing;
use Bitrix24\SDK\Services\Landing\Role\Result\RoleItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['landing']))]
class Batch
{
    public function __construct(protected Landing\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of roles for several site types, one command per site type
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/rights/role-model/landing-role-get-list.html
     *
     * @param string[] $scopes Site types: GROUP, KNOWLEDGE, MAINPAGE; empty string means sites and stores.
     *                         If not provided, roles for sites and stores are returned
     *
     * @return Generator<int, RoleItemResult>
     * @throws BaseException
     */
    #[ApiBatchMethodMetadata(
        'landing.role.getList',
        'https://apidocs.bitrix24.com/api-reference/landing/rights/role-model/landing-role-get-list.html',
        'Batch list of roles for several site types'
    )]
    public function list(array $scopes = ['']): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'scopes' => $scopes,
            ]
        );

        $commandsParameters = [];
        foreach ($scopes as $scope) {
            $commandsParameters[] = $scope === '' ? [] : ['scope' => $scope];
        }

        foreach ($this->batch->getTraversableListByCommands('landing.role.getList', $commandsParameters) as $key => $value) {
            yield $key => new RoleItemResult($value);
        }
    }
}
