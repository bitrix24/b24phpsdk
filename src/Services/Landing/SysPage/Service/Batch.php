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

namespace Bitrix24\SDK\Services\Landing\SysPage\Service;

use Bitrix24\SDK\Attributes\ApiBatchMethodMetadata;
use Bitrix24\SDK\Attributes\ApiBatchServiceMetadata;
use Bitrix24\SDK\Core\Credentials\Scope;
use Bitrix24\SDK\Core\Exceptions\BaseException;
use Bitrix24\SDK\Core\Exceptions\InvalidArgumentException;
use Bitrix24\SDK\Core\Result\UpdatedItemBatchResult;
use Bitrix24\SDK\Services\Landing;
use Bitrix24\SDK\Services\Landing\SysPage\SysPageType;
use Generator;
use Psr\Log\LoggerInterface;

#[ApiBatchServiceMetadata(new Scope(['landing']))]
class Batch
{
    public function __construct(protected Landing\Batch $batch, protected LoggerInterface $log)
    {
    }

    /**
     * Batch setting special pages for sites
     *
     * If pageId is not provided or null, the special page binding for the type is removed.
     *
     * @link https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-set.html
     *
     * @param array<int, array{siteId: int, type: SysPageType|string, pageId?: int|null}> $sysPages
     *
     * @return Generator<int, UpdatedItemBatchResult>
     * @throws BaseException
     * @throws InvalidArgumentException
     */
    #[ApiBatchMethodMetadata(
        'landing.syspage.set',
        'https://apidocs.bitrix24.com/api-reference/landing/page/special-pages/landing-syspage-set.html',
        'Batch setting special pages for sites'
    )]
    public function set(array $sysPages): Generator
    {
        $items = [];
        foreach ($sysPages as $cnt => $sysPage) {
            if (!array_key_exists('siteId', $sysPage) || !array_key_exists('type', $sysPage)) {
                throw new InvalidArgumentException(
                    sprintf('array keys «siteId» and «type» are required in special page at position %s', $cnt)
                );
            }

            $item = [
                'id' => $sysPage['siteId'],
                'type' => $sysPage['type'] instanceof SysPageType ? $sysPage['type']->value : $sysPage['type'],
            ];

            if (($sysPage['pageId'] ?? null) !== null) {
                $item['lid'] = $sysPage['pageId'];
            }

            $items[] = $item;
        }

        foreach ($this->batch->processEntityItems('landing.syspage.set', $items) as $key => $item) {
            yield $key => new UpdatedItemBatchResult($item);
        }
    }
}
