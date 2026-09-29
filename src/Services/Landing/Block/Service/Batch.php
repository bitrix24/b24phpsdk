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

namespace Bitrix24\SDK\Services\Landing\Block\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Services\Landing;
use Bitrix24\SDK\Services\Landing\Block\Result\BlockItemResult;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['landing']))]
class Batch
{
    public function __construct(protected Landing\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch list of blocks for several pages, one command per page
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-list.html
     *
     * @param int[] $pageIds Page identifiers
     * @param array $params Parameters: edit_mode (0|1), deleted (0|1), get_content (0|1)
     *
     * @return Generator<int, BlockItemResult>
     * @throws BaseException
     * @throws InvalidArgumentException
     */
    #[ApiBatchMethodMetadata(
        'landing.block.getlist',
        'https://apidocs.bitrix24.com/api-reference/landing/block/methods/landing-block-get-list.html',
        'Batch list of blocks for several pages'
    )]
    public function list(array $pageIds, array $params = []): Generator
    {
        $this->log->debug(
            'batchList',
            [
                'pageIds' => $pageIds,
                'params' => $params,
            ]
        );

        $commandsParameters = [];
        foreach ($pageIds as $cnt => $pageId) {
            if (!is_int($pageId)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'invalid type «%s» of page id «%s» at position %s, page id must be integer type',
                        gettype($pageId),
                        $pageId,
                        $cnt
                    )
                );
            }

            $commandParameters = ['lid' => $pageId];
            if ($params !== []) {
                $commandParameters['params'] = $params;
            }

            $commandsParameters[] = $commandParameters;
        }

        foreach ($this->batch->getTraversableListByCommands('landing.block.getlist', $commandsParameters) as $key => $traversableListByCommand) {
            yield $key => new BlockItemResult($traversableListByCommand);
        }
    }
}
